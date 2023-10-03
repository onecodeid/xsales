<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="1200px"
        transition="dialog-transition"
        content-class="dialog-new"
        
    >
        <delivery-order-new-form></delivery-order-new-form>
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
</style>
<script>
module.exports = {
    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        'delivery-order-new-form' : httpVueLoader('./delivery-order-new-form.vue')
    },

    data () {
        return { }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.delivery_new.dialog_new },
            set (v) { this.$store.commit('delivery_new/set_common', ['dialog_new', v]) }
        },

        snackbar : {
            get () { return this.$store.state.delivery_new.snackbar },
            set (v) { this.$store.commit('delivery_new/set_common', ['snackbar', v]) }
        },

        snackbar_text : {
            get () { return this.$store.state.delivery_new.snackbar_text },
            set (v) { this.$store.commit('delivery_new/set_common', ['snackbar_text', v]) }
        },

        delivery_note : {
            get () { return this.$store.state.delivery_new.delivery_note },
            set (v) { this.$store.commit('delivery_new/set_common', ['delivery_note', v]) }
        },

        delivery_memo : {
            get () { return this.$store.state.delivery_new.delivery_memo },
            set (v) { this.$store.commit('delivery_new/set_common', ['delivery_memo', v]) }
        },

        delivery_number : {
            get () { return this.$store.state.delivery_new.delivery_number },
            set (v) { this.$store.commit('delivery_new/set_common', ['delivery_number', v]) }
        },

        delivery_ref_number : {
            get () { return this.$store.state.delivery_new.delivery_ref_number },
            set (v) { this.$store.commit('delivery_new/set_common', ['delivery_ref_number', v]) }
        },

        delivery_ppn : {
            get () { return this.$store.state.delivery_new.delivery_ppn },
            set (v) { 
                this.$store.commit('delivery_new/set_common', ['delivery_ppn', v]) 
                this.retotal(0)
            }
        },

        delivery_send_note : {
            get () { return this.$store.state.delivery_new.delivery_send_note },
            set (v) { this.$store.commit('delivery_new/set_common', ['delivery_send_note', v]) }
        },

        details () {
            return this.$store.state.delivery_new.details
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

        delivery_date () {
            return this.$store.state.delivery_new.delivery_date
        },

        delivery_post () {
            let x = this.$store.state
            if (!x.delivery_new.edit) return false
            if (x.delivery.selected_delivery.delivery_confirm == 'N') return false
            return true
        },

        items () {
            return this.$store.state.delivery_new.items
        },

        btn_save_enabled () {
            // let ttl = this.total
            // if (ttl.debit == 0 && ttl.credit == 0) return false
            // if (ttl.debit != ttl.credit) return false
            // if (this.delivery_note == '') return false
            // if (!!this.$store.state.delivery_new.save) return false
            // if (!!this.delivery_post) return false
            // if (this.view) return false

            // for (let d of this.details)
            //     if (!d.account && (Math.round(d.credit) > 0 || Math.round(d.debit) > 0))
            //         return false

            return true
        },

        btn_add_enabled () {
            if (!!this.single)
                return false
            // let d = this.details[this.details.length-1]
            // if (!d) return false
            // if (!d.account) return false
            // if (this.is_sales) return false
            // if (this.view) return false

            // if (this.$store.state.delivery_new.edit) {
            //     let j = this.$store.state.delivery.selected_delivery
            //     if (j.delivery_post=='Y') return false
            // }
            
            return true
        },

        is_sales() {
            if (this.$store.state.filter.indexOf("J.03")>-1)
                return true
            return false
        },

        edit() {
            return this.$store.state.delivery_new.edit
        },

        view () {
            let r = this.$store.state.delivery.selected_delivery
            if (!this.edit) return false
            if (r.delivery_invoice!=0) return true
            // if (r.delivery_invoice!=0 || r.delivery_confirm=='Y') return true
            return false
            // return this.$store.state.delivery_new.view
        },

        customers () {
            return this.$store.state.delivery_new.customers
        },

        selected_customer : {
            get () { return this.$store.state.delivery_new.selected_customer },
            set (v) { 
                this.$store.commit('delivery_new/set_selected_customer', v)
                this.$store.dispatch('delivery_new/search_item')
                this.$store.dispatch('delivery_new/search_address')
                this.$store.dispatch('delivery_new/search_sales')
            }
        },

        delivery_total () {
            let total = 0
            for (let x of this.details)
                total = total + Math.round(x.total)
            return total
        },

        warehouses () {
            return this.$store.state.delivery_new.warehouses
        },

        selected_warehouse : {
            get () { return this.$store.state.delivery_new.selected_warehouse },
            set (v) { 
                this.$store.commit('delivery_new/set_selected_warehouse', v)
                this.$store.dispatch('delivery_new/search_item')
            }
        },

        deliverytypes () {
            return this.$store.state.delivery_new.deliverytypes
        },

        selected_deliverytype : {
            get () { return this.$store.state.delivery_new.selected_deliverytype },
            set (v) { 
                this.$store.commit('delivery_new/set_selected_deliverytype', v)
            }
        },

        staffs () {
            return this.$store.state.delivery_new.staffs
        },

        selected_staff : {
            get () { return this.$store.state.delivery_new.selected_staff },
            set (v) { this.$store.commit('delivery_new/set_selected_staff', v) }
        },

        addresses () {
            return this.$store.state.delivery_new.addresses
        },

        selected_address : {
            get () { return this.$store.state.delivery_new.selected_address },
            set (v) { 
                this.$store.commit('delivery_new/set_selected_address', v)
            }
        },

        single () {
            return this.$store.state.delivery_new.single
            // return this.$store.state.delivery_new.view
        },

        sales_name () {
            if (!!this.single && !this.edit
                && this.$store.state.sales_new.selected_staff)
                return this.$store.state.sales_new.selected_staff.staff_name

            if (this.details.length > 0 && this.details[0].item)
                return this.details[0].item.staff_name

            return ''
        },

        expeditions () {
            return this.$store.state.sales_new.expeditions
        },

        selected_expedition : {
            get () { return this.$store.state.delivery_new.selected_expedition },
            set (v) { this.$store.commit('delivery_new/set_selected_expedition', v) }
        },

        expedition_name : {
            get () { return this.$store.state.delivery_new.expedition_name },
            set (v) { this.$store.commit('delivery_new/set_common', ['expedition_name', v]) }
        },

        expedition_mode : {
            get () { return this.$store.state.delivery_new.expedition_mode },
            set (v) { this.$store.commit('delivery_new/set_common', ['expedition_mode', v]) }
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
            this.$store.commit('delivery_new/set_common', ['save_n_confirm', false])
            this.$store.dispatch('delivery_new/save')
        },

        save_confirm () {
            this.$store.commit('delivery_new/set_common', ['save_n_confirm', true])
            this.$store.dispatch('delivery_new/save')
        },

        update_amount (idx, side, amount) {
            let d = this.details            
            d[idx][side] = amount
            this.retotal(idx)
            // this.$store.commit('delivery_new/set_details', d)
        },

        add_detail () {
            let d = this.details
            let dfl = JSON.parse(JSON.stringify(this.$store.state.delivery_new.detail_default))
            d.push(dfl)
            this.$store.commit('delivery_new/set_details', d)
        },

        set_details () {
            this.$store.commit('delivery_new/set_details', this.details)
        },

        del_detail (x) {
            let d = this.details
            d.splice(x, 1)
            this.$store.commit('delivery_new/set_details', d)
        },

        change_delivery_date(x) {
            this.$store.commit('delivery_new/set_common', ['delivery_date', x.new_date])
        },

        update_item(idx, v) {
            let d = this.details
            d[idx].item = v

            if (idx == 0) {
                for (let add of this.addresses) {
                    if (add.address_id == v.address_id)
                        this.selected_address = v
                }
            }
            
            this.$store.commit('delivery_new/set_details', d)
        },

        change_ppn(idx, v) {
            let d = this.details
            d[idx].ppn = v
            this.retotal(idx)
        },

        retotal(idx) {
            let d = this.details
            if (idx == 0) {
                for (let idxn in d) {
                    d[idxn].total = d[idxn].price * d[idxn].qty * (100 - d[idxn].disc) * (d[idxn].ppn == "Y" && this.delivery_ppn == "N"?1.1:1) / 100
                }
            } else {
                d[idx].total = d[idx].price * d[idx].qty * (100 - d[idx].disc) * (d[idx].ppn == "Y" && this.delivery_ppn == "N"?1.1:1) / 100
                // console.log(d[idx].price)
                // console.log(d[idx].qty)
                // console.log(d[idx].disc)
                // console.log(d[idx].ppn)
                
            }
        },

        change_item_name (x) {
            this.$store.commit('delivery_new/set_object', ['custom_item_name', x.item.item_name])
            this.$store.commit('delivery_new/set_object', ['selected_item', x])
            this.$store.commit('delivery_new/set_common', ['dialog_itemname', true])
        }
    },

    mounted () {
        this.$store.dispatch('delivery_new/search_customer')
        this.$store.dispatch('delivery_new/search_staff')
        this.$store.dispatch('delivery_new/search_warehouse')
        this.$store.dispatch('delivery_new/search_deliverytype')
        // this.$store.dispatch('delivery_new/search_item')
    },

    watch : {
        // details (v, o) {
        //     this.$store.dispatch('delivery_new/search_stock')
        // }
    }
}
</script>