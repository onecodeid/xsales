<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="600px"
        transition="dialog-transition"
        content-class="dialog-new"
        
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <h3>
                    <span v-show="!edit">PENERIMAAN PEMBAYARAN</span>
                    <span v-show="!!edit">UBAH DATA PENERIMAAN PEMBAYARAN</span>
                </h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                        <v-layout row wrap>

                            <v-flex xs3>
                                <common-datepicker
                                    label="Tanggal Penerimaan"
                                    :date="payment_date"
                                    data="0"
                                    @change="change_payment_date"
                                    classs=""
                                    hints=" "
                                    :details="true"
                                    :solo="false"
                                    v-if="dialog"
                                ></common-datepicker>

                                <v-text-field
                                    label="Nomor Transaksi"
                                    v-model="payment_number"
                                    :readonly="true"
                                    :disabled="view"
                                    placeholder="( kosongkan saja )"
                                ></v-text-field>
                            </v-flex>
                            <v-flex xs9 pl-4>
                                <v-autocomplete
                                    :items="customers"
                                    v-model="selected_customer"
                                    return-object
                                    item-text="customer_name"
                                    item-value="customer_id"
                                    label="Customer"
                                    :disabled="false"
                                ></v-autocomplete>

                                <v-text-field
                                    label="Catatan"
                                    rows="4"
                                    v-model="payment_note"
                                ></v-text-field>                                
                            </v-flex>                            
                        </v-layout>

                        <v-divider class="py-2"></v-divider>

                        <v-layout row wrap>
                            <v-flex xs12>
                                <finance-payment-new-detail></finance-payment-new-detail>
                            </v-flex>
                        </v-layout>

                        <v-layout row wrap>
                            <v-flex xs7>
                                <v-layout row wrap>
                                    <v-flex xs12>
                                        <v-select
                                            :items="accounts"
                                            v-model="selected_account"
                                            return-object
                                            item-text="M_AccountName"
                                            item-value="M_AccountID"
                                            label="Rekening Tujuan"
                                            :disabled="false"
                                            item-disabled="parent"
                                            v-if="dialog"
                                            class="me"
                                        >
                                            <template slot="item" slot-scope="data">
                                                <v-layout row wrap>
                                                    <v-flex xs12 :class="{'pl-3':data.item.level!=0}">
                                                       {{data.item.M_AccountName}}
                                                       <span class="d-block caption blue--text" v-if="data.item.map_type=='PAY.02'&&!data.item.parent">
                                                           no {{data.item.ref_note.account_number}}
                                                       </span>
                                                    </v-flex>
                                                </v-layout>
                                            </template>
                                        </v-select>
                                    </v-flex>
                                </v-layout>

                                <v-layout row wrap>
                                    <!-- <v-flex xs6>
                                        <v-select
                                            :items="paymenttypes"
                                            v-model="selected_paymenttype"
                                            return-object
                                            item-text="paymenttype_name"
                                            item-value="paymenttype_id"
                                            label="Cara Pembayaran"
                                            :disabled="false"
                                        ></v-select>
                                    </v-flex> -->
                                    <v-flex xs6 v-if="dialog&&selected_account&&selected_account.map_type=='PAY.03'">
                                        <common-datepicker
                                            label="Tanggal Transfer / Giro"
                                            :date="payment_giro_date"
                                            data="0"
                                            @change="change_payment_giro_date"
                                            classs=""
                                            hints=" "
                                            :details="true"
                                            :solo="false"
                                            v-if="dialog&&selected_account&&selected_account.map_type=='PAY.03'"
                                        ></common-datepicker>
                                    </v-flex>

                                    <v-flex xs6 v-if="dialog&&selected_account&&selected_account.map_type=='PAY.02'">
                                        <common-datepicker
                                            label="Tanggal Transfer / Giro"
                                            :date="payment_transfer_date"
                                            data="0"
                                            @change="change_payment_transfer_date"
                                            classs=""
                                            hints=" "
                                            :details="true"
                                            :solo="false"
                                            v-if="dialog&&selected_account&&selected_account.map_type=='PAY.02'"
                                        ></common-datepicker>
                                    </v-flex>
                                </v-layout>
                                
                                <v-layout row wrap>
                                    <v-flex xs6>
                                        <v-autocomplete
                                            :items="banks"
                                            v-model="selected_bank"
                                            return-object
                                            item-text="bank_name"
                                            item-value="bank_id"
                                            label="Bank Giro"
                                            :disabled="false"
                                            v-if="dialog&&selected_account&&selected_account.map_type=='PAY.03'"
                                            placeholder="Pilih salah satu"
                                            clearable
                                        ></v-autocomplete>
                                    </v-flex>

                                    <v-flex xs5 offset-xs1>
                                        <v-text-field
                                            label="Nomor Giro"
                                            v-model="payment_giro_number"
                                            v-if="dialog&&selected_account&&selected_account.map_type=='PAY.03'"
                                            placeholder="BG-"
                                        ></v-text-field>
                                    </v-flex>

                                    <v-flex xs12>
                                        <!-- <v-select
                                            :items="accounts"
                                            v-model="selected_account"
                                            return-object
                                            item-text="M_AccountName"
                                            item-value="M_AccountID"
                                            label="Rekening Tujuan"
                                            :disabled="false"
                                            item-disabled="parent"
                                            v-if="dialog"
                                            class="me"
                                        >
                                            <template slot="item" slot-scope="data">
                                                <v-layout row wrap>
                                                    <v-flex xs12 :class="{'pl-3':data.item.level!=0}">
                                                       {{data.item.M_AccountName}}
                                                       <span class="d-block caption blue--text" v-if="data.item.map_type=='PAY.02'&&!data.item.parent">
                                                           no {{data.item.ref_note.account_number}}
                                                       </span>
                                                    </v-flex>
                                                </v-layout>
                                            </template>
                                        </v-select> -->

                                        <!-- <v-select
                                            :items="bankaccounts"
                                            v-model="selected_bankaccount"
                                            return-object
                                            item-text="account_name"
                                            item-value="account_id"
                                            label="Rekening Tujuan"
                                            :disabled="false"
                                            v-if="dialog&&selected_paymenttype&&selected_paymenttype.paymenttype_code=='PAY.TRANSFER'"
                                        >
                                            <template slot="item" slot-scope="data">
                                                <v-layout row wrap>
                                                    <v-flex xs12>
                                                       {{data.item.bank_name}} a/n 
                                                        {{data.item.account_name}} 
                                                    </v-flex>
                                                    <v-flex xs12 class="caption blue--text">
                                                        no {{data.item.account_number}}
                                                    </v-flex>
                                                </v-layout>
                                            </template>
                                            <template slot="selection" slot-scope="data">
                                                <v-layout row wrap>
                                                    <v-flex xs12>
                                                       {{data.item.bank_name}} a/n 
                                                        {{data.item.account_name}} 
                                                    </v-flex>
                                                </v-layout>
                                            </template>
                                        </v-select> -->
                                    </v-flex>

                                    
                                </v-layout>
                            </v-flex>
                            <v-flex xs4 offset-xs1>
                                <v-text-field
                                    label="Nominal"
                                    reverse
                                    suffix="Rp"
                                    placeholder="000"
                                    :value="one_money(payment_total.amount)"
                                ></v-text-field>
                            </v-flex>
                        </v-layout>
                    </v-flex>
                </v-layout>
            </v-card-text>

            <v-card-actions>
                <v-btn color="primary" flat @click="dialog=!dialog" v-show="!view">Batal</v-btn>
                <v-spacer></v-spacer>
                <!-- <v-btn color="primary" @click="add_detail">Add</v-btn>                 -->
                <v-btn color="primary" @click="save" :disabled="!btn_save_enabled" :dark="btn_save_enabled" v-show="!view">Simpan</v-btn>
                <v-btn color="primary" @click="dialog=!dialog" v-show="view">Tutup</v-btn>
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

.dialog-new .v-list__tile {
    height: 0px !important;
    min-height: 36px;
}
</style>
<script>
module.exports = {
    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        "finance-payment-new-detail" : httpVueLoader("./finance-payment-new-detail.vue?t=asd")
    },

    data () {
        return {
            tempo: true
         }
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

        payment_number : {
            get () { return this.$store.state.payment_new.payment_number },
            set (v) { this.$store.commit('payment_new/set_common', ['payment_number', v]) }
        },

        payment_amount : {
            get () { return this.$store.state.payment_new.payment_amount },
            set (v) { 
                this.$store.commit('payment_new/set_common', ['payment_amount', v]) 
            }
        },

        payment_giro_number : {
            get () { return this.$store.state.payment_new.payment_giro_number },
            set (v) { this.$store.commit('payment_new/set_common', ['payment_giro_number', v]) }
        },

        paymenttypes () {
            return this.$store.state.payment_new.paymenttypes
        },

        selected_paymenttype : {
            get () { return this.$store.state.payment_new.selected_paymenttype },
            set (v) { 
                this.$store.commit('payment_new/set_selected_paymenttype', v)
            }
        },

        banks () {
            return this.$store.state.payment_new.banks
        },

        selected_bank : {
            get () { return this.$store.state.payment_new.selected_bank },
            set (v) { 
                this.$store.commit('payment_new/set_selected_bank', v)
            }
        },

        bankaccounts () {
            return this.$store.state.payment_new.bankaccounts
        },

        selected_bankaccount : {
            get () { return this.$store.state.payment_new.selected_bankaccount },
            set (v) { 
                this.$store.commit('payment_new/set_selected_bankaccount', v)
            }
        },

        accounts () {
            let accs = []
            for (let acc of this.$store.state.payment_new.accounts) {
                if (acc.parent!==true)
                    acc.parent=false
                accs.push(acc)
            }

            return accs
        },

        selected_account : {
            get () { return this.$store.state.payment_new.selected_account },
            set (v) { this.$store.commit('payment_new/set_selected_account', v) }
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

        payment_giro_date () {
            return this.$store.state.payment_new.payment_giro_date
        },

        payment_transfer_date () {
            return this.$store.state.payment_new.payment_transfer_date
        },

        payment_post () {
            return false
        },

        items () {
            return this.$store.state.payment_new.items
        },

        btn_save_enabled () {
            
            return true
        },

        btn_add_enabled () {
            
            return true
        },

        is_sales() {
            if (this.$store.state.filter.indexOf("J.03")>-1)
                return true
            return false
        },

        edit() {
            return this.$store.state.payment_new.edit
        },

        view () {
            return this.$store.state.payment_new.view
        },

        customers () {
            return this.$store.state.payment_new.customers
        },

        selected_customer : {
            get () { return this.$store.state.payment_new.selected_customer },
            set (v) { 
                this.$store.commit('payment_new/set_selected_customer', v)
                this.$store.dispatch('payment_new/search_invoice')
            }
        },

        details () {
            return this.$store.state.payment_new.details
        },

        payment_total () {
            let x = 0
            for (let j of this.details) {
                x = x + Math.round(j.amount)
            }
            return {amount:x}

            let total = 0
            for (let x of this.details)
                for (let y of x.items)
                    total = total + Math.round(y.subtotal)
            return total
        },

        selected_dp () {
            return this.$store.state.dp.selected_dp
        },

        details () {
            return this.$store.state.payment_new.details
        },

        invoices () {
            return this.$store.state.payment_new.invoices
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
            this.retotal(idx)
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

        change_payment_giro_date(x) {
            this.$store.commit('payment_new/set_common', ['payment_giro_date', x.new_date])
        },

        change_payment_transfer_date(x) {
            this.$store.commit('payment_new/set_common', ['payment_transfer_date', x.new_date])
        },

        update_item(idx, v) {
            let d = this.details
            d[idx].receive = v
            d[idx].items = []
            if (v)
                if (v.items)
                    d[idx].items = v.items
            
            this.$store.commit('payment_new/set_details', d)
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
                    d[idxn].total = d[idxn].price * d[idxn].qty * (100 - d[idxn].disc) * (d[idxn].ppn == "Y" && this.payment_ppn == "N"?1.1:1) / 100
                }
            } else {
                d[idx].total = d[idx].price * d[idx].qty * (100 - d[idx].disc) * (d[idx].ppn == "Y" && this.payment_ppn == "N"?1.1:1) / 100
                console.log(d[idx].price)
                console.log(d[idx].qty)
                console.log(d[idx].disc)
                console.log(d[idx].ppn)
                
            }
        }
    },

    mounted () {
        this.$store.dispatch('payment_new/search_customer')
        this.$store.dispatch('payment_new/search_bank')
        this.$store.dispatch('payment_new/search_bankaccount')
        this.$store.dispatch('payment_new/search_account')
        this.$store.dispatch('payment_new/search_paymenttype')
        // this.$store.dispatch('payment_new/search_item')
    },

    watch : {
        payment_due_date_check (n, o) {
            this.tempo = n == "Y" ? true : false
        }
    }
}
</script>