<template>
    <v-layout row wrap>
        <v-flex xs12>
            <v-layout row wrap>
                <v-flex :class="{'xs3':is_cash,'xs2 pr-2':is_bank||is_giro}">
                    <common-datepicker
                        label="Tanggal Transaksi"
                        :date="payment_date"
                        data="0"
                        @change="change_payment_date"
                        classs=""
                        hints=" "
                        :details="true"
                        :solo="false"
                        v-if="dialog&&!payment_post"
                    ></common-datepicker>
                    <v-text-field
                        label="Tanggal Transaksi"
                        :value="payment_date"
                        v-show="!!payment_post"
                        readonly
                    ></v-text-field>

                    <v-text-field
                        label="Akun"
                        :value="selected_account?selected_account.M_AccountName:''"
                        readonly
                    >
                    </v-text-field>
                </v-flex>

                <v-flex xs5 v-show="is_bank" pl-2>
                    <v-select
                        :items="bank_accounts"
                        v-model="selected_bank_account"
                        label="Tujuan Transfer"
                        return-object
                        item-text="account_name"
                        item-value="account_id"
                        persistent-hint
                        :hint="account_number"
                    >
                        <template slot="item" slot-scope="data">
                            <div class="v-list__tile__content">
                                <div class="v-list__tile__title">
                                    <span class="blue--text mr-2">{{data.item.bank_name}} a/n {{data.item.account_name}}</span></div> 
                                <div class="v-list__tile__sub-title caption">No. {{data.item.account_number}}</div>
                            </div>
                        </template>
                        <template slot="selection" slot-scope="data">
                            <div class="v-list__tile__content">
                                <div class="v-list__tile__title">
                                    <span class="blue--text mr-2">{{data.item.bank_name}} a/n {{data.item.account_name}}</span></div>
                            </div>
                        </template>
                    </v-select>
                </v-flex>

                <v-flex xs4 v-show="is_giro" pl-2>
                    <v-layout row wrap>
                        <v-flex xs6>
                            <v-select
                                :items="banks"
                                v-model="selected_bank"
                                label="Bank Giro"
                                return-object
                                item-text="bank_name"
                                item-value="bank_id"
                                persistent-hint
                            >
                                <!-- <template slot="item" slot-scope="data">
                                    <div class="v-list__tile__content">
                                        <div class="v-list__tile__title"><span class="blue--text mr-2">{{data.item.bank_name}} a/n {{data.item.account_name}}</div> 
                                        <div class="v-list__tile__sub-title caption">No. {{data.item.account_number}}</div>
                                    </div>
                                </template>
                                <template slot="selection" slot-scope="data">
                                    <div class="v-list__tile__content">
                                        <div class="v-list__tile__title"><span class="blue--text mr-2">{{data.item.bank_name}} a/n {{data.item.account_name}}</div>
                                    </div>
                                </template> -->
                            </v-select>        
                        </v-flex>
                        <v-flex xs6 pl-3>
                            <common-datepicker
                                label="Tanggal Giro"
                                :date="giro_date"
                                data="0"
                                @change="change_giro_date"
                                classs=""
                                hints=" "
                                :details="true"
                                :solo="false"
                                v-if="dialog&&!payment_post"
                            ></common-datepicker>
                            <v-text-field
                                label="Tanggal Giro"
                                :value="giro_date"
                                v-show="!!payment_post"
                                readonly
                            ></v-text-field>
                        </v-flex>
                    </v-layout>
                    

                    <v-text-field
                        label="Nomor Giro"
                        v-model="giro_number"

                    ></v-text-field>
                </v-flex>

                <v-flex :class="{'xs9':is_cash,'xs5':is_bank,'xs6':is_giro}" pl-4 v-show="jtype=='J.11'">
                    <v-autocomplete
                        label="Customer"
                        v-model="selected_customer"
                        :items="customers"
                        :search-input.sync="search_customer"
                        :readonly="!!selected_customer"
                        auto-select-first
                        no-filter
                        return-object
                        :clearable="true"
                        item-text="customer_name"
                        :loading="false"
                        no-data-text="Pilih Customer"
                        placeholder="Pilih Customer"         
                        :disabled="details.length > 0 && !!details[0].invoice"
                        v-if="!edit"
                        >
                        <template
                            slot="item"
                            slot-scope="{ item }"
                            >
                            <v-list-tile-content>
                                <v-list-tile-title v-text="item.customer_name"></v-list-tile-title>
                            <v-list-tile-sub-title v-text="item.city_name"></v-list-tile-sub-title>
                            </v-list-tile-content>
                        </template>

                    </v-autocomplete>

                    <v-text-field
                        label="Customer"
                        :value="selected_customer?selected_customer.customer_name:''"
                        readonly
                        v-if="!!edit"
                    ></v-text-field>

                    <v-text-field
                        label="Catatan"
                        v-model="payment_note"
                        :readonly="payment_post"
                    ></v-text-field>
                </v-flex>

                <v-flex :class="{'xs9':is_cash,'xs5':is_bank,'xs6':is_giro}" pl-4 v-show="jtype=='J.12'">
                    <v-autocomplete
                        label="Supplier"
                        v-model="selected_supplier"
                        :items="suppliers"
                        :search-input.sync="search_supplier"
                        :readonly="!!selected_supplier"
                        auto-select-first
                        no-filter
                        return-object
                        :clearable="true"
                        item-text="vendor_name"
                        :loading="false"
                        no-data-text="Pilih Supplier"
                        placeholder="Pilih Supplier"         
                        :disabled="details.length > 0 && !!details[0].invoice"
                        v-if="!edit"
                        >
                        <template
                            slot="item"
                            slot-scope="{ item }"
                            >
                            <v-list-tile-content>
                                <v-list-tile-title v-text="item.vendor_name"></v-list-tile-title>
                            <v-list-tile-sub-title v-text="item.city_name"></v-list-tile-sub-title>
                            </v-list-tile-content>
                        </template>

                    </v-autocomplete>

                    <v-text-field
                        label="Supplier"
                        :value="selected_supplier?selected_supplier.vendor_name:''"
                        readonly
                        v-if="!!edit"
                    ></v-text-field>

                    <v-text-field
                        label="Catatan"
                        v-model="payment_note"
                        :readonly="payment_post"
                    ></v-text-field>
                </v-flex>

                <v-flex :class="{'xs9':is_cash,'xs5':is_bank,'xs6':is_giro}" pl-4 v-show="jtype=='J.13'||jtype=='J.14'">
                    <v-text-field
                        label="Catatan"
                        v-model="payment_note"
                        :readonly="payment_post"
                    ></v-text-field>
                </v-flex>
            </v-layout>
        </v-flex>
    </v-layout>
</template>

<script>
module.exports = {
    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue')
    },

    data () {
        return { 
        }
    },

    computed : {
        dialog() { return this.$store.state.cash_invoice.dialog_new },
        details () { return this.$store.state.cash_invoice.details },

        payment_note : {
            get () { return this.$store.state.cash_invoice.payment_note },
            set (v) { this.$store.commit('cash_invoice/set_common', ['payment_note', v]) }
        },

        payment_receipt : {
            get () { return this.$store.state.cash_invoice.payment_receipt },
            set (v) { this.$store.commit('cash_invoice/set_common', ['payment_receipt', v]) }
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

        search_customer : {
            get () { return this.$store.state.cash_invoice.search_customer },
            set (v) { this.$store.commit('cash_invoice/set_common', ['search_customer', v]) }
        },

        search_supplier : {
            get () { return this.$store.state.cash_bill.search_supplier },
            set (v) { this.$store.commit('cash_bill/set_common', ['search_supplier', v]) }
        },

        selected_customer : {
            get () { return this.$store.state.cash_invoice.selected_customer },
            set (v) { 
                this.$store.commit('cash_invoice/set_selected_customer', v) 
                this.$store.dispatch('cash_invoice/search_invoice')
            }
        },

        selected_supplier : {
            get () { return this.$store.state.cash_bill.selected_supplier },
            set (v) { 
                this.$store.commit('cash_bill/set_selected_supplier', v) 
                this.$store.dispatch('cash_bill/search_bill')
            }
        },

        customers () {
            return this.$store.state.cash_invoice.customers
        },

        suppliers () {
            return this.$store.state.cash_bill.suppliers
        },

        accounts () {
            return this.$store.state.cash.accounts
        },

        selected_account () {
            return this.$store.state.cash.selected_account
        },

        bank_accounts () {
            return this.$store.state.cash_invoice.bank_accounts
        },

        selected_bank_account : {
            get () { 
                return this.$store.state.cash_invoice.selected_bank_account },
            set (v) { 
                this.account_number = 'No. '+v.account_number
                this.$store.commit('cash_invoice/set_selected_bank_account', v) 
            }
        },

        account_number : {
            get () { return this.$store.state.cash_invoice.account_number },
            set (v) { this.$store.commit('cash_invoice/set_common', ['account_number', v]) }
        },

        banks () {
            return this.$store.state.cash_invoice.banks
        },

        selected_bank : {
            get () { return this.$store.state.cash_invoice.selected_bank },
            set (v) {
                this.$store.commit('cash_invoice/set_selected_bank', v) 
            }
        },

        is_cash () {
            if (!this.selected_account) return false
            if (this.selected_account.M_AccountCode != '1-10001') return false
            return true
        },

        is_bank () {
            if (!this.selected_account) return false
            if (this.selected_account.M_AccountCode.indexOf('1-10002') < 0) return false
            return true
        },

        is_giro () {
            if (!this.selected_account) return false
            if (this.selected_account.M_AccountCode != '1-10003') return false
            return true
        },

        giro_number : {
            get () { return this.$store.state.cash_invoice.giro_number },
            set (v) { this.$store.commit('cash_invoice/set_common', ['giro_number', v]) }
        },

        giro_date () {
            return this.$store.state.cash_invoice.giro_date
        },

        edit () {
            return this.$store.state.cash_invoice.edit
        },

        jtype () {
            if (this.$store.state.cash.selected_journaltype)
                return this.$store.state.cash.selected_journaltype.journaltype_code
            return ''
        },

        customer () {
            try {
                let c = this.$store.state.journal_detail.selected_journal.payment
                return {customer_id:c.customer_id, customer_name:c.customer_name}
            } catch (error) {
                return {}
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

        change_payment_date(x) {
            this.$store.commit('cash_invoice/set_common', ['payment_date', x.new_date])
        },

        change_giro_date(x) {
            this.$store.commit('cash_invoice/set_common', ['giro_date', x.new_date])
        },

        thr_search: _.debounce( function () {
            this.$store.dispatch("cash_invoice/search_customer")
        }, 500),

        thr_search2: _.debounce( function () {
            this.$store.dispatch("cash_bill/search_supplier")
        }, 500)
    },

    mounted () {
        this.$store.dispatch('cash_invoice/search_customer')
        this.$store.dispatch('cash_invoice/search_bank_account')
        this.$store.dispatch('cash_invoice/search_bank')
    },

    watch : {
        search_customer(val, old) {
            if (this.edit) return false
            if (val == null || typeof val == 'undefined') val = ""
            if (val == old ) return
            if (this.$store.state.system.search_status == 1 ) return  
            this.$store.commit("cash_invoice/set_common", ['search_customer', val])
            this.$store.commit("cash_invoice/set_customers", [])
            this.thr_search()
        },

        search_supplier(val, old) {
            if (this.edit) return false
            if (val == null || typeof val == 'undefined') val = ""
            if (val == old ) return
            if (this.$store.state.system.search_status == 1 ) return  
            this.$store.commit("cash_bill/set_common", ['search_supplier', val])
            this.$store.commit("cash_bill/set_suppliers", [])
            this.thr_search2()
        }
    }
}
</script>