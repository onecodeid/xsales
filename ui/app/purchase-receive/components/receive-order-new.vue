<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="1200px"
        transition="dialog-transition"
        content-class="dialog-new"
        
    >
        <receive-order-new-form></receive-order-new-form>
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
        'receive-order-new-form' : httpVueLoader('./receive-order-new-form.vue')
    },

    data () {
        return { }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.receive_new.dialog_new },
            set (v) { this.$store.commit('receive_new/set_common', ['dialog_new', v]) }
        },

        receive_note : {
            get () { return this.$store.state.receive_new.receive_note },
            set (v) { this.$store.commit('receive_new/set_common', ['receive_note', v]) }
        },

        receive_memo : {
            get () { return this.$store.state.receive_new.receive_memo },
            set (v) { this.$store.commit('receive_new/set_common', ['receive_memo', v]) }
        },

        receive_number : {
            get () { return this.$store.state.receive_new.receive_number },
            set (v) { this.$store.commit('receive_new/set_common', ['receive_number', v]) }
        },

        receive_ref_number : {
            get () { return this.$store.state.receive_new.receive_ref_number },
            set (v) { this.$store.commit('receive_new/set_common', ['receive_ref_number', v]) }
        },

        receive_ppn : {
            get () { return this.$store.state.receive_new.receive_ppn },
            set (v) { 
                this.$store.commit('receive_new/set_common', ['receive_ppn', v]) 
                this.retotal(0)
            }
        },

        details () {
            return this.$store.state.receive_new.details
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

        receive_date () {
            return this.$store.state.receive_new.receive_date
        },

        receive_post () {
            let x = this.$store.state
            if (!x.receive_new.edit) return false
            if (x.receive.selected_receive.receive_confirm == 'N') return false
            return true
        },

        items () {
            return this.$store.state.receive_new.items
        },

        btn_save_enabled () {
            // let ttl = this.total
            // if (ttl.debit == 0 && ttl.credit == 0) return false
            // if (ttl.debit != ttl.credit) return false
            // if (this.receive_note == '') return false
            // if (!!this.$store.state.receive_new.save) return false
            // if (!!this.receive_post) return false
            // if (this.view) return false

            // for (let d of this.details)
            //     if (!d.account && (Math.round(d.credit) > 0 || Math.round(d.debit) > 0))
            //         return false

            return true
        },

        btn_add_enabled () {
            // let d = this.details[this.details.length-1]
            // if (!d) return false
            // if (!d.account) return false
            // if (this.is_sales) return false
            // if (this.view) return false

            // if (this.$store.state.receive_new.edit) {
            //     let j = this.$store.state.receive.selected_receive
            //     if (j.receive_post=='Y') return false
            // }
            
            return true
        },

        is_sales() {
            if (this.$store.state.filter.indexOf("J.03")>-1)
                return true
            return false
        },

        edit() {
            return this.$store.state.receive_new.edit
        },

        view () {
            let r = this.$store.state.receive.selected_receive
            if (!this.edit) return false
            if (r.receive_bill!=0 || r.receive_confirm=='Y') return true
            return false
            // return this.$store.state.receive_new.view
        },

        vendors () {
            return this.$store.state.receive_new.vendors
        },

        selected_vendor : {
            get () { return this.$store.state.receive_new.selected_vendor },
            set (v) { 
                this.$store.commit('receive_new/set_selected_vendor', v)
                this.$store.dispatch('receive_new/search_item')
            }
        },

        receive_total () {
            let total = 0
            for (let x of this.details)
                total = total + Math.round(x.total)
            return total
        },

        warehouses () {
            return this.$store.state.receive_new.warehouses
        },

        selected_warehouse : {
            get () { return this.$store.state.receive_new.selected_warehouse },
            set (v) { 
                this.$store.commit('receive_new/set_selected_warehouse', v)
                this.$store.dispatch('receive_new/search_item')
            }
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
            this.$store.commit('receive_new/set_common', ['save_n_confirm', false])
            this.$store.dispatch('receive_new/save').then((d => {
                console.log(d)
            }))
        },

        save_confirm () {
            this.$store.commit('receive_new/set_common', ['save_n_confirm', true])
            this.$store.dispatch('receive_new/save').then((d => {
                let e = JSON.parse(d)
                // console.log(e)
            }))
        },

        update_amount (idx, side, amount) {
            let d = this.details            
            d[idx][side] = amount
            this.retotal(idx)
            // this.$store.commit('receive_new/set_details', d)
        },

        add_detail () {
            let d = this.details
            let dfl = JSON.parse(JSON.stringify(this.$store.state.receive_new.detail_default))
            d.push(dfl)
            this.$store.commit('receive_new/set_details', d)
        },

        set_details () {
            this.$store.commit('receive_new/set_details', this.details)
        },

        del_detail (x) {
            let d = this.details
            d.splice(x, 1)
            this.$store.commit('receive_new/set_details', d)
        },

        change_receive_date(x) {
            this.$store.commit('receive_new/set_common', ['receive_date', x.new_date])
        },

        update_item(idx, v) {
            let d = this.details
            d[idx].item = v
            
            this.$store.commit('receive_new/set_details', d)
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
                    d[idxn].total = d[idxn].price * d[idxn].qty * (100 - d[idxn].disc) * (d[idxn].ppn == "Y" && this.receive_ppn == "N"?1.1:1) / 100
                }
            } else {
                d[idx].total = d[idx].price * d[idx].qty * (100 - d[idx].disc) * (d[idx].ppn == "Y" && this.receive_ppn == "N"?1.1:1) / 100
                console.log(d[idx].price)
                console.log(d[idx].qty)
                console.log(d[idx].disc)
                console.log(d[idx].ppn)
                
            }
        }
    },

    mounted () {
        this.$store.dispatch('receive_new/search_vendor')
        this.$store.dispatch('receive_new/search_warehouse')
        // this.$store.dispatch('receive_new/search_item')
    },

    watch : {}
}
</script>