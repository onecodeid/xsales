<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="800px"
        transition="dialog-transition"
        content-class="dialog-new"
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <h3>{{selected_jtype?selected_jtype.journaltype_name.toUpperCase():''}} MELALUI {{selected_account?selected_account.M_AccountName.toUpperCase():''}}</h3>
            </v-card-title>
            <v-card-text>
                
                <v-layout row wrap>
                    <v-flex xs12>
                        <finance-cash-invoice-form></finance-cash-invoice-form>
                    </v-flex>
                </v-layout>

                

                <finance-cash-receive-detail v-if="jtype=='J.13'||jtype=='J.14'"></finance-cash-receive-detail>
                <finance-cash-invoice-detail v-if="jtype=='J.11'"></finance-cash-invoice-detail>
                <finance-cash-bill-detail v-if="jtype=='J.12'"></finance-cash-bill-detail>

                <v-layout row wrap>
                    <v-flex xs12>
                        <finance-cash-tag></finance-cash-tag>
                    </v-flex>
                </v-layout>
            </v-card-text>

            <v-card-actions>
                <v-btn color="primary" flat @click="dialog=!dialog">Batal</v-btn>
                <v-spacer></v-spacer>
                <!-- <v-btn color="primary" @click="add_detail">Add</v-btn>                 -->
                <v-btn color="primary" @click="save" :disabled="!btn_save_enabled" :dark="btn_save_enabled">Simpan</v-btn>                
            </v-card-actions>
        </v-card>
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

.dialog-new .v-text-field--solo-flat .v-input__slot {
    margin-bottom: -5px;
}

.dialog-new .v-text-field--solo-flat .v-text-field__details {
    margin-bottom: 0px;
}
</style>
<script>
var t = Math.ceil(Math.random() * 1e10)

module.exports = {
    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        "finance-cash-invoice-form" : httpVueLoader("./finance-cash-invoice-form.vue?t="+t),
        "finance-cash-receive-detail" : httpVueLoader("./finance-cash-receive-detail.vue?t="+t),
        "finance-cash-invoice-detail" : httpVueLoader("./finance-cash-invoice-detail.vue?t="+t),
        "finance-cash-bill-detail" : httpVueLoader("./finance-cash-bill-detail.vue?t="+t),
        "finance-cash-tag" : httpVueLoader("./finance-cash-tag.vue?t="+t)
    },

    data () {
        return { }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.cash_invoice.dialog_new },
            set (v) { this.$store.commit('cash_invoice/set_common', ['dialog_new', v]) }
        },

        payment_note : {
            get () { return this.$store.state.cash_invoice.payment_note },
            set (v) { this.$store.commit('cash_invoice/set_common', ['payment_note', v]) }
        },

        payment_receipt : {
            get () { return this.$store.state.cash_invoice.payment_receipt },
            set (v) { this.$store.commit('cash_invoice/set_common', ['payment_receipt', v]) }
        },

        details () {
            return this.$store.state.cash_invoice.details
        },

        total () {
            let x = 0
            let y = this.details
            if (this.jtype == 'J.12')
                y = this.$store.state.cash_bill.details
            if (this.jtype == 'J.13' || this.jtype == 'J.14')
                y = this.$store.state.cash_receive.details
            
            for (let j of y) {
                x = x + Math.round(j.amount)
            }
            return {amount:x}
        },

        payment_date () {
            return this.$store.state.cash_invoice.payment_date
        },

        payment_post () {
            let x = this.$store.state
            if (!x.cash_invoice.edit) return false
            if (x.cash_invoice.payment_post == 'N') return false
            return true
        },

        invoices () {
            return this.$store.state.cash_invoice.invoices
        },

        btn_save_enabled () {
            let ttl = this.total
            
            if (ttl.amount == 0) return false
            if (this.payment_note == '') return false
            if (!!this.$store.state.cash_invoice.save) return false
            if (!!this.payment_post) return false
            for (let d of this.details)
            {
                if (!d.invoice && (Math.round(d.amount) > 0 ))
                    return false
                if (d.is_retur == 'Y' && !d.retur)
                    return false
            }
                

            return true
        },

        btn_add_enabled () {
            let d = this.details[this.details.length-1]
            if (!d) return false
            if (!d.invoice) return false

            if (this.$store.state.cash_invoice.edit) {
                let j = this.$store.state.cash_invoice
                if (j.payment_post=='Y') return false
            }
            
            return true
        },

        search_customer : {
            get () { return this.$store.state.cash_invoice.search_customer },
            set (v) { this.$store.commit('cash_invoice/set_common', ['search_customer', v]) }
        },

        selected_customer : {
            get () { return this.$store.state.cash_invoice.selected_customer },
            set (v) { 
                this.$store.commit('cash_invoice/set_selected_customer', v) 
                this.$store.dispatch('cash_invoice/search_invoice')
            }
        },

        customers () {
            return this.$store.state.cash_invoice.customers
        },

        disc () {
            return this.$store.state.cash_invoice.disc
        },

        accounts () {
            return this.$store.state.cash.accounts
        },

        selected_account () {
            return this.$store.state.cash.selected_account
        },

        selected_jtype () {
            return this.$store.state.cash.selected_journaltype
        },

        jtype () {
            if (this.$store.state.cash.selected_journaltype)
                return this.$store.state.cash.selected_journaltype.journaltype_code
            return ''
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
            if (this.jtype == 'J.11')
                this.$store.dispatch('cash_invoice/save')
            if (this.jtype == 'J.12')
                this.$store.dispatch('cash_bill/save')
            else if (this.jtype == 'J.13' || this.jtype == 'J.14')
                this.$store.dispatch('cash_receive/save')
        },

        update_amount (idx, side, amount) {
            let d = this.details            
            d[idx][side] = amount
            // this.$store.commit('cash_invoice/set_details', d)
        },

        add_detail () {
            let d = this.details
            let dfl = JSON.parse(JSON.stringify(this.$store.state.cash_invoice.detail_default))
            d.push(dfl)
            this.$store.commit('cash_invoice/set_details', d)
        },

        set_details () {
            this.$store.commit('cash_invoice/set_details', this.details)
        },

        del_detail (x) {
            let d = this.details
            d.splice(x, 1)
            if (d.length < 1)
                this.add_detail()
            this.$store.commit('cash_invoice/set_details', d)
        },

        change_payment_date(x) {
            this.$store.commit('cash_invoice/set_common', ['payment_date', x.new_date])
        },

        update_invoice(idx, v) {
            let d = this.details
            d[idx].invoice = v
            this.$store.commit('cash_invoice/set_details', d)
        },

        thr_search: _.debounce( function () {
            this.$store.dispatch("cash_invoice/search_customer")
        })
    },

    mounted () {
        // this.$store.dispatch('cash_invoice/search_account')
        // this.$store.dispatch('cash_invoice/search_customer')
        // this.$store.dispatch('cash_invoice/search_invoice')
    },

    watch : {
        search_customer(val, old) {
            if (val == null || typeof val == 'undefined') val = ""
            if (val == old ) return
            if (this.$store.state.system.search_status == 1 ) return  
            this.$store.commit("cash_invoice/set_common", ['search_customer', val])
            this.$store.commit("cash_invoice/set_customers", [])
            this.thr_search()
        }
    }
}
</script>