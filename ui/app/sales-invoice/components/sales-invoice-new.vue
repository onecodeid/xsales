<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="1200px"
        transition="dialog-transition"
        content-class="dialog-new"
        
    >
        <sales-invoice-new-form></sales-invoice-new-form>
    </v-dialog>
</template>

<style>
.input-dense .v-input__control {
    min-height: 36px !important;
}

.dialog-new .v-input__prepend-outer {
    margin: 0px !important;
}

.dialog-new .v-text-field.v-text-field--solo .v-input__control {
    min-height: 36px;
    padding: 0;
}

/* .dialog-new .v-input__append-outer {
    margin: 0px !important;
} */

.dialog-new .v-input__prepend-outer button {
    min-height: 36px;
}
</style>
<script>
module.exports = {
    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        'sales-invoice-dp' : httpVueLoader('./sales-invoice-dp.vue'),
        'sales-invoice-new-form' : httpVueLoader('./sales-invoice-new-form.vue')
    },

    data () {
        return {
            tempo: true
         }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.invoice_new.dialog_new },
            set (v) { this.$store.commit('invoice_new/set_common', ['dialog_new', v]) }
        },

        ppn () {
            return this.$store.state.system.conf.ppn
        },

        invoice_note : {
            get () { return this.$store.state.invoice_new.invoice_note },
            set (v) { this.$store.commit('invoice_new/set_common', ['invoice_note', v]) }
        },

        invoice_memo : {
            get () { return this.$store.state.invoice_new.invoice_memo },
            set (v) { this.$store.commit('invoice_new/set_common', ['invoice_memo', v]) }
        },

        invoice_number : {
            get () { return this.$store.state.invoice_new.invoice_number },
            set (v) { this.$store.commit('invoice_new/set_common', ['invoice_number', v]) }
        },

        invoice_ppn : {
            get () { return this.$store.state.invoice_new.invoice_ppn },
            set (v) { 
                this.$store.commit('invoice_new/set_common', ['invoice_ppn', v]) 
                this.retotal(0)
            }
        },

        invoice_dp () {
            return this.$store.state.invoice_new.invoice_dp
        },

        invoice_shipping : {
            get () { return this.$store.state.invoice_new.invoice_shipping },
            set (v) { this.$store.commit('invoice_new/set_common', ['invoice_shipping', v]) }
        },

        details () {
            return this.$store.state.invoice_new.details
        },

        total () {
            let x = 0
            let y = 0
            for (let j of this.details) {
                x = x + Math.round(j.debit)
                y = y + Math.round(j.credit)
            }
            return {debit:x,credit:y}
        },

        invoice_date () {
            return this.$store.state.invoice_new.invoice_date
        },

        // invoice_due_date () {
        //     return this.$store.state.invoice_new.invoice_due_date
        // },

        invoice_due_date : {
            get () { return this.$store.state.invoice_new.invoice_due_date },
            set (v) { this.$store.commit('invoice_new/set_common', ['invoice_due_date', v]) }
        },

        invoice_due_date_check : {
            get () { return this.$store.state.invoice_new.invoice_due_date_check },
            set (v) { 
                this.$store.commit('invoice_new/set_common', ['invoice_due_date_check', v])
            }
        },

        invoice_post () {
            let x = this.$store.state
            if (!x.invoice_new.edit) return false
            if (x.invoice.selected_invoice.invoice_post == 'N') return false
            return true
        },

        items () {
            return this.$store.state.invoice_new.items
        },

        btn_save_enabled () {
            if (this.selected_invoice.invoice_paid>0)
                return false
            let d = this.details[this.details.length-1]
            if (!d) return false
            if (!d.delivery) return false
            if (this.view) return false

            if (this.invoice_dps.length > 0) {
                for (let dp of this.invoice_dps) {
                    if (dp.dp.dp_acc && dp.dp.dp_acc == 'N')
                        return false
                }
            }

            return true
        },

        btn_add_enabled () {
            let d = this.details[this.details.length-1]
            
            if (!d) return false
            if (!d.delivery) return false
            
            if (this.is_sales) return false
            if (this.view) return false

            // if (this.$store.state.invoice_new.edit) {
            //     let j = this.$store.state.invoice.selected_invoice
            //     if (j.invoice_post=='Y') return false
            // }
            
            return true
        },

        is_sales() {
            if (this.$store.state.filter.indexOf("J.03")>-1)
                return true
            return false
        },

        edit() {
            return this.$store.state.invoice_new.edit
        },

        view () {
            if (this.selected_invoice.invoice_paid>0)
                return true
            return this.$store.state.invoice_new.view
        },

        customers () {
            return this.$store.state.invoice_new.customers
        },

        selected_customer : {
            get () { return this.$store.state.invoice_new.selected_customer },
            set (v) { 
                this.$store.commit('invoice_new/set_selected_customer', v)
                this.$store.dispatch('invoice_new/search_item')
                this.$store.dispatch('invoice_new/search_dp')
            }
        },

        ppn_total () {
            //new algorithm
            // return this.invoice_total * (this.ppn) / 100


            let ppn = false
            for (let d of this.details)
                for (let i of d.items)
                    if (i.ppn=="Y") ppn = true
            // new algorithm
            let total = this.invoice_total
            total = total * (!!ppn?(this.ppn/100):0)

            return total

            // let total = 0
            // for (let x of this.details)
            //     for (let y of x.items)
            //         total = total + Math.round(y.ppn_amount)
                    
            // return total
        },

        invoice_subtotal () {
            let total = 0
            for (let x of this.details)
                for (let y of x.items)
                    // new algorithm
                    total = total + y.subtotal
                    // total = total + Math.round(y.subtotal)
            return total
        },

        invoice_total () {
            // new algorithm
            return ((parseFloat(this.invoice_subtotal) * parseFloat(100-this.invoice_disc) / 100 )) - parseFloat(this.invoice_discrp)
            
            // return ((parseFloat(this.invoice_subtotal) * parseFloat(100-this.invoice_disc) / 100 ) + parseFloat(this.ppn_total)) - parseFloat(this.invoice_discrp) - parseFloat(this.invoice_dp) + parseFloat(this.invoice_shipping)
        },

        invoice_grandtotal () {
            // new algorithm
            return parseFloat(this.invoice_total) - parseFloat(this.invoice_dp) + parseFloat(this.invoice_shipping) + parseFloat(this.ppn_total)
            
            // return ((parseFloat(this.invoice_subtotal) * parseFloat(100-this.invoice_disc) / 100 ) + parseFloat(this.ppn_total)) - parseFloat(this.invoice_discrp) - parseFloat(this.invoice_dp) + parseFloat(this.invoice_shipping)
        },

        warehouses () {
            return this.$store.state.invoice_new.warehouses
        },

        selected_warehouse : {
            get () { return this.$store.state.invoice_new.selected_warehouse },
            set (v) { 
                this.$store.commit('invoice_new/set_selected_warehouse', v)
                this.$store.dispatch('invoice_new/search_item')
            }
        },

        terms () {
            return this.$store.state.invoice_new.terms
        },

        selected_term : {
            get () { return this.$store.state.invoice_new.selected_term },
            set (v) { 
                this.$store.commit('invoice_new/set_selected_term', v)                 
                this.invoice_due_date = moment(this.invoice_date, "YYYY-MM-DD").add(Math.round(v.term_duration), 'days').format('DD-MM-YYYY')
                }
        },

        invoice_disc : {
            get () { return this.$store.state.invoice_new.invoice_disc },
            set (v) { this.$store.commit('invoice_new/set_common', ['invoice_disc', v]) }
        },

        invoice_discrp : {
            get () { return this.$store.state.invoice_new.invoice_discrp },
            set (v) { this.$store.commit('invoice_new/set_common', ['invoice_discrp', v]) }
        },

        invoice_disctype : {
            get () { return this.$store.state.invoice_new.invoice_disctype },
            set (v) { this.$store.commit('invoice_new/set_common', ['invoice_disctype', v]) }
        },

        selected_invoice () {
            if (this.$store.state.invoice)
                return this.$store.state.invoice.selected_invoice
            return {}
        },

        invoice_dps () {
            return this.$store.state.invoice_new.invoice_dps
        },

        sales_name () {
            return this.$store.state.invoice_new.sales_name
        },

        invoice_address () {
            return this.$store.state.invoice_new.invoice_address
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        one_mask_money (x) {
            return window.one_mask_money(x)
        },

        save () {
            this.$store.dispatch('invoice_new/save')
        },

        update_amount (idx, side, amount) {
            let d = this.details            
            d[idx][side] = amount
            this.retotal(idx)
            // this.$store.commit('invoice_new/set_details', d)
        },

        add_detail () {
            let d = this.details
            let dfl = JSON.parse(JSON.stringify(this.$store.state.invoice_new.detail_default))
            d.push(dfl)
            this.$store.commit('invoice_new/set_details', d)
        },

        set_details () {
            this.$store.commit('invoice_new/set_details', this.details)
        },

        del_detail (x) {
            let d = this.details
            d.splice(x, 1)
            this.$store.commit('invoice_new/set_details', d)
        },

        change_invoice_date(x) {
            this.$store.commit('invoice_new/set_common', ['invoice_date', x.new_date])
        },

        change_invoice_due_date(x) {
            this.$store.commit('invoice_new/set_common', ['invoice_due_date', x.new_date])
        },

        update_item(idx, v) {
            let d = this.details
            d[idx].delivery = v
            d[idx].items = []
            if (v)
                if (v.items)
                    d[idx].items = v.items
            
            this.$store.commit('invoice_new/set_details', d)
        },

        change_ppn(idx, v) {
            let d = this.details

            // patch, one ppn all ppn
            for (let i in d) {
                d[i].ppn = v
                this.retotal(i)    
            }
                
                // d[idx].ppn = v
            // this.retotal(idx)
        },

        retotal(idx) {
            let d = this.details
            let ppn = (100 + this.ppn) / 100
            if (idx == 0) {
                for (let idxn in d) {
                    d[idxn].total = d[idxn].price * d[idxn].qty * (100 - d[idxn].disc) * (d[idxn].ppn == "Y" && this.invoice_ppn == "N"?ppn:1) / 100
                    // d[idxn].total = d[idxn].price * d[idxn].qty * (100 - d[idxn].disc) * (d[idxn].ppn == "Y" && this.invoice_ppn == "N"?ppn:1) / 100
                }
            } else {
                d[idx].total = d[idx].price * d[idx].qty * (100 - d[idx].disc) * (d[idx].ppn == "Y" && this.invoice_ppn == "N"?ppn:1) / 100                
            }
        },

        set_disc (type) {
            this.invoice_disctype = type
            if (type=='R') this.invoice_disc = 0
            if (type=='P') this.invoice_discrp = 0
        }
    },

    mounted () {
        this.$store.dispatch('invoice_new/search_customer')
        this.$store.dispatch('invoice_new/search_warehouse')
        this.$store.dispatch('invoice_new/search_term')
    },

    watch : {
        invoice_due_date_check (n, o) {
            this.tempo = n == "Y" ? true : false
        }
    }
}
</script>