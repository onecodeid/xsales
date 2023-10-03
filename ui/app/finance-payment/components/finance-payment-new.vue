<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="1000px"
        transition="dialog-transition"
        content-class="dialog-new"
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <h3>INPUT MEMORIAL BARU</h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                        <v-layout row wrap>

                            <v-flex xs3>
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

                                <v-autocomplete
                                    label="Customer"
                                    v-model="selected_customer"
                                    :items="customers"
                                    :search-input.sync="search_customer"
                                    :disabled="!!selected_customer"
                                    auto-select-first
                                    no-filter
                                    return-object
                                    :clearable="true"
                                    item-text="customer_name"
                                    :loading="false"
                                    no-data-text="Pilih Customer"
                                    solo
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
                                    label="Nomor Bukti"
                                    v-model="payment_receipt"
                                    :readonly="payment_post"
                                ></v-text-field>
                            </v-flex>
                            <v-flex xs9 pl-5>
                                <v-textarea
                                    label="Catatan"
                                    rows="4"
                                    v-model="payment_note"
                                    outline
                                    :readonly="payment_post"
                                ></v-textarea>
                            </v-flex>


                            <v-flex xs12>
                                <v-card>
                                    <v-card-title primary-title class="py-2 px-3 cyan white--text">
                                        <v-layout row wrap>
                                            <v-flex xs4><h3 class="subheading">PERKIRAAN</h3></v-flex>
                                            <v-flex xs2 pr-2><h3 class="subheading text-xs-right">CASH</h3></v-flex>
                                            <v-flex xs2 pr-2><h3 class="subheading text-xs-right">TRANSFER</h3></v-flex>
                                            <v-flex xs2 pr-2><h3 class="subheading text-xs-right">GIRO</h3></v-flex>
                                            <v-flex xs2><h3 class="subheading text-xs-right">CEK</h3></v-flex>
                                        </v-layout>
                                    </v-card-title>
                                    <v-card-text class="px-3 py-2">
                                        <v-layout row wrap v-for="(d, n) in details" :key="n" :class="{'mt-2':n>0}">
                                            <v-flex xs4 pr-3>
                                                <v-autocomplete
                                                    :items="invoices"
                                                    :value="d.invoice"
                                                    return-object
                                                    solo
                                                    hide-details
                                                    :clearable="d.post!='Y'"
                                                    :readonly="!!d.invoice"
                                                    @change="update_invoice(n, $event)"
                                                    item-text="invoice_number"
                                                    item-value="invoice_id"
                                                    placeholder="Pilih..."
                                                >
                                                    <template slot="item" slot-scope="data">
                                                        <span class="blue--text mr-2">{{data.item.invoice_date}}</span> {{data.item.invoice_number}}
                                                    </template>

                                                    <template slot="selection" slot-scope="data">
                                                        <span class="blue--text mr-2">{{data.item.invoice_date}}</span> {{data.item.invoice_number}}
                                                    </template>

                                                    <template slot="prepend">
                                                        <v-btn color="red" class="ma-0 mr-1" icon :dark="false" flat @click="del_detail(n)" :disabled="d.post=='Y'"><v-icon>delete</v-icon></v-btn>
                                                    </template>
                                                </v-autocomplete>
                                            </v-flex>
                                            <v-flex xs2 pr-2>
                                                <v-text-field
                                                    label=""
                                                    solo
                                                    hide-details
                                                    :value="d.cash"
                                                    reverse
                                                    dense
                                                    @change="set_details"
                                                    @input="update_amount(n, 'cash', $event)"
                                                    :mask="one_mask_money(d.cash)"
                                                    :readonly="d.post=='Y'"
                                                >
                                                    <template slot="append"><span class="grey--text">Rp</span></template>
                                                </v-text-field>
                                            </v-flex>
                                            <v-flex xs2 pr-2>
                                                <v-text-field
                                                    label=""
                                                    solo
                                                    hide-details
                                                    :value="d.transfer"
                                                    reverse
                                                    dense
                                                    @change="set_details"
                                                    @input="update_amount(n, 'transfer', $event)"
                                                    :mask="one_mask_money(d.transfer)"
                                                    :readonly="d.post=='Y'"
                                                >
                                                    <template slot="append"><span class="grey--text">Rp</span></template>
                                                </v-text-field>
                                            </v-flex>
                                            <v-flex xs2 pr-2>
                                                <v-text-field
                                                    label=""
                                                    solo
                                                    hide-details
                                                    :value="d.giro"
                                                    reverse
                                                    dense
                                                    @change="set_details"
                                                    @input="update_amount(n, 'giro', $event)"
                                                    :mask="one_mask_money(d.giro)"
                                                    :readonly="d.post=='Y'"
                                                >
                                                    <template slot="append"><span class="grey--text">Rp</span></template>
                                                </v-text-field>
                                            </v-flex>
                                            <v-flex xs2>
                                                <v-text-field
                                                    label=""
                                                    solo
                                                    hide-details
                                                    :value="d.cheque"
                                                    reverse
                                                    dense
                                                    @change="set_details"
                                                    @input="update_amount(n, 'cheque', $event)"
                                                    :mask="one_mask_money(d.cheque)"
                                                    :readonly="d.post=='Y'"
                                                >
                                                    <template slot="append"><span class="grey--text">Rp</span></template>
                                                </v-text-field>
                                            </v-flex>
                                            
                                        </v-layout>

                                        <v-layout row wrap>
                                            <v-flex xs12 class="text-xs-center" pt-2>
                                                <v-btn color="primary btn-icon" @click="add_detail" :disabled="!btn_add_enabled"><v-icon>add</v-icon></v-btn>
                                            </v-flex>
                                        </v-layout>
                                        <finance-payment-new-disc></finance-payment-new-disc>
                                    </v-card-text>
                                </v-card>

                                
                                <v-layout row wrap>
                                    <v-flex xs12 px-3 pt-3>
                                        <v-layout row wrap>
                                            <v-flex xs7 py-3>
                                                <h3 class="title">TOTAL</h3>
                                            </v-flex>
                                            <v-flex xs5>
                                                <v-layout row wrap>
                                                    <v-flex xs6 pr-2>
                                                        <v-text-field
                                                            label=""
                                                            solo
                                                            hide-details
                                                            :value="one_money(total.debit)"
                                                            reverse
                                                            dense
                                                            flat
                                                            suffix="Rp"
                                                            readonly
                                                        ></v-text-field>
                                                    </v-flex>
                                                    <v-flex xs6 pl-2>
                                                        <v-text-field
                                                            label=""
                                                            solo
                                                            hide-details
                                                            :value="one_money(total.credit)"
                                                            reverse
                                                            dense
                                                            flat
                                                            suffix="Rp"
                                                            readonly
                                                        ></v-text-field>
                                                    </v-flex>
                                                </v-layout>
                                            </v-flex>    
                                        </v-layout>
                                    </v-flex>
                                    
                                </v-layout>
                                
                            </v-flex>
                        </v-layout>
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
</style>
<script>
module.exports = {
    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        "finance-payment-new-disc" : httpVueLoader("./finance-payment-new-disc.vue")
    },

    data () {
        return { }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.payment_new.dialog_new },
            set (v) { this.$store.commit('payment_new/set_common', ['dialog_new', v]) }
        },

        payment_note : {
            get () { return this.$store.state.payment_new.payment_note },
            set (v) { this.$store.commit('payment_new/set_common', ['payment_note', v]) }
        },

        payment_receipt : {
            get () { return this.$store.state.payment_new.payment_receipt },
            set (v) { this.$store.commit('payment_new/set_common', ['payment_receipt', v]) }
        },

        details () {
            return this.$store.state.payment_new.details
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

        payment_date () {
            return this.$store.state.payment_new.payment_date
        },

        payment_post () {
            let x = this.$store.state
            if (!x.payment_new.edit) return false
            if (x.payment.selected_payment.payment_post == 'N') return false
            return true
        },

        invoices () {
            return this.$store.state.payment_new.invoices
        },

        btn_save_enabled () {
            let ttl = this.total
            if (ttl.debit == 0 && ttl.credit == 0) return false
            if (ttl.debit != ttl.credit) return false
            if (this.payment_note == '') return false
            if (!!this.$store.state.payment_new.save) return false
            if (!!this.payment_post) return false

            for (let d of this.details)
                if (!d.invoice && (Math.round(d.credit) > 0 || Math.round(d.debit) > 0))
                    return false

            return true
        },

        btn_add_enabled () {
            let d = this.details[this.details.length-1]
            if (!d) return false
            if (!d.invoice) return false

            if (this.$store.state.payment_new.edit) {
                let j = this.$store.state.payment.selected_payment
                if (j.payment_post=='Y') return false
            }
            
            return true
        },

        search_customer : {
            get () { return this.$store.state.payment_new.search_customer },
            set (v) { this.$store.commit('payment_new/set_common', ['search_customer', v]) }
        },

        selected_customer : {
            get () { return this.$store.state.payment_new.selected_customer },
            set (v) { 
                this.$store.commit('payment_new/set_selected_customer', v) 
                this.$store.dispatch('payment_new/search_invoice')
            }
        },

        customers () {
            return this.$store.state.payment_new.customers
        },

        disc () {
            return this.$store.state.payment_new.disc
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
            this.$store.dispatch('payment_new/save')
        },

        update_amount (idx, side, amount) {
            let d = this.details            
            d[idx][side] = amount
            // this.$store.commit('payment_new/set_details', d)
        },

        add_detail () {
            let d = this.details
            let dfl = JSON.parse(JSON.stringify(this.$store.state.payment_new.detail_default))
            d.push(dfl)
            this.$store.commit('payment_new/set_details', d)
        },

        set_details () {
            this.$store.commit('payment_new/set_details', this.details)
        },

        del_detail (x) {
            let d = this.details
            d.splice(x, 1)
            this.$store.commit('payment_new/set_details', d)
        },

        change_payment_date(x) {
            this.$store.commit('payment_new/set_common', ['payment_date', x.new_date])
        },

        update_invoice(idx, v) {
            let d = this.details
            d[idx].invoice = v
            this.$store.commit('payment_new/set_details', d)
        },

        thr_search: _.debounce( function () {
            this.$store.dispatch("payment_new/search_customer")
        })
    },

    mounted () {
        // this.$store.dispatch('payment_new/search_account')
        this.$store.dispatch('payment_new/search_customer')
        // this.$store.dispatch('payment_new/search_invoice')
    },

    watch : {
        search_customer(val, old) {
            if (val == null || typeof val == 'undefined') val = ""
            if (val == old ) return
            if (this.$store.state.system.search_status == 1 ) return  
            this.$store.commit("payment_new/set_common", ['search_customer', val])
            this.$store.commit("payment_new/set_customers", [])
            this.thr_search()
        }
    }
}
</script>