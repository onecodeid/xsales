<template>
    <v-card>
        <v-card-title primary-title class="teal lighten-5">
            <h3 class="display-1 font-weight-light zalfa-text-title">PENGEMBALIAN KREDIT MEMO </h3>
        </v-card-title>
        <v-card-text>
            <v-card class="mb-4" elevation="1">
                <v-card-text>
                    <v-layout row wrap>
                        <v-flex xs3>
                            <v-autocomplete
                                :items="cash_accounts"
                                return-object
                                clearable
                                v-show="!selected_account"
                                item-text="account_name"
                                item-value="account_id"
                                placeholder="Pilih..."
                                item-disabled="parent"
                                v-model="selected_account"
                                label="Akun / Perkiraan Asal"
                            >
                                <template slot="item" slot-scope="data">
                                    <div class="v-list__tile__content">
                                        <!-- <span class="blue--text mr-2">{{data.item.account_code}}</span> -->
                                        <div class="v-list__tile__title">{{data.item.account_name}}</div> 
                                    </div>
                                    
                                </template>

                                <template slot="selection" slot-scope="data">
                                    <v-layout row wrap>
                                        <div class="v-list__tile__title">{{data.item.account_name}}</div>
                                    </v-layout>
                                </template>
                            </v-autocomplete>

                            <v-text-field
                                label="Akun / Perkiraan Penerimaan"
                                :value="selected_account?selected_account.account_name:''"
                                v-show="!!selected_account"
                                clearable readonly
                                @click:clear="selected_account=null"
                            >
                            </v-text-field>
                            
                        </v-flex>
                        <v-flex xs9>
                            <v-layout row wrap>
                                <v-flex xs12>
                                    <h3 class="text-xs-right display-1 mb-3">TOTAL MEMO : <span class="blue--text font-weight-bold">Rp. {{one_money(memo_amount)}}</span></h3>
                                    <h3 class="text-xs-right mb-3 red--text">Terpakai : Rp. {{one_money(memo_used+memo_refunded)}}</h3>
                                </v-flex>
                            </v-layout>
                        </v-flex>
                    </v-layout>        
                </v-card-text>
            </v-card>
            
            <v-layout row wrap>
                <v-flex xs6 pr-4>
                    <v-text-field
                        label="Penerima"
                        :value="selected_customer?selected_customer.customer_name:''"
                        readonly
                    ></v-text-field>
                </v-flex>
                <v-flex xs2 pr-4 offset-xs2>
                    <common-datepicker
                        label="Tanggal Transaksi"
                        :date="refund_date" data="0" hints=" "
                        @change="change_refund_date"
                        :details="true" :solo="false"
                    ></common-datepicker>
                </v-flex>
                <v-flex xs2>
                    <v-text-field
                        label="Nomor Transaksi"
                        :value="refund_number"
                        placeholder="(Otomatis)"
                        readonly
                    ></v-text-field>
                </v-flex>
            </v-layout>

            <v-layout row wrap>
                <v-flex xs12>
                    <v-data-table 
                        hide-actions
                        class="elevation-1"
                        :items="[1]">
                        <template slot="headers" slot-scope="props">
                            <tr>
                                <th role="columnheader" scope="col" width="20%" aria-label="MEMO KREDIT: Not sorted." aria-sort="none" class="column text-xs-left py-2 px-3 zalfa-bg-purple lighten-3 white--text">MEMO KREDIT</th>
                                <th role="columnheader" scope="col" width="50%" aria-label="CATATAN: Not sorted." aria-sort="none" class="column text-xs-left py-2 px-3 zalfa-bg-purple lighten-3 white--text">CATATAN</th>
                                <th role="columnheader" scope="col" width="15%" aria-label="JUMLAH MEMO: Not sorted." aria-sort="none" class="column text-xs-right py-2 px-3 zalfa-bg-purple lighten-3 white--text">JUMLAH MEMO TERSEDIA</th>
                                <th role="columnheader" scope="col" width="15%" aria-label="JUMLAH PENGEMBALIAN: Not sorted." aria-sort="none" class="column text-xs-right py-2 px-3 zalfa-bg-purple lighten-3 white--text">JUMLAH PENGEMBALIAN</th>
                            </tr>
                        </template>
                        <template  v-slot:items="props">
                            <td class="py-2 px-3">{{ memo_number }}</td>
                            <td class="py-2 px-3">
                                <v-text-field
                                    solo hide-details
                                    v-model="refund_note"
                                ></v-text-field>
                            </td>
                            <td class="text-xs-right py-2 px-3 blue--text">Rp <b>{{ one_money(memo_amount-memo_used-memo_refunded) }}</b></td>
                            <td class="text-xs-right py-2 px-3">
                                <v-text-field
                                    solo hide-details reverse
                                    v-model="refund_amount"
                                ></v-text-field>
                            </td>
                        </template>
                    </v-data-table>
                </v-flex>

                <v-flex xs12>
                    <v-layout row wrap mt-3>
                        <v-flex xs10 class="text-xs-right headline">Total Refund :</v-flex>
                        <v-flex xs2 class="text-xs-right px-3"><h3 class="headline">Rp. {{ one_money(refund_amount)}}</h3></v-flex>
                    </v-layout>
                </v-flex>
            </v-layout>
        </v-card-text>
        <v-card-actions class="text-xs-right">
            <v-layout row wrap>
                <v-flex xs12 class="text-xs-center">
                    <v-btn color="success" @click="cancel" outline>Batal</v-btn>
                    <v-btn color="success" @click="save" :disabled="!btn_save_enabled">Simpan</v-btn>
                </v-flex>
            </v-layout>
        </v-card-actions>
    </v-card>
</template>

<script>
let rnd = Math.random() * 1e10
module.exports = {
    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        'common-tag' : httpVueLoader('../../common/components/common-tag.vue?t='+rnd)
    },

    data () {
        return {
            imageName: '',
            imageUrl: '',
            imageFile: ''
        }
    },

    computed : {
        state () {
            return this.$store.state.memoRefund
        },

        cash_accounts () {
            return this.$store.state.memo.cash_accounts
        },

        refund_date () {
            return this.state.refund_date
        },

        refund_number () {
            return this.state.refund_number
        },

        selected_account : {
            get () { return this.state.selected_account },
            set (v) { this.setObject('selected_account', v) }
        },

        memo_number () {
            return this.state.memo_number
        },

        memo_amount () {
            return this.state.memo_amount
        },

        memo_used () {
            return this.state.memo_used
        },

        memo_refunded () {
            return this.state.memo_refunded
},

        refund_amount : {
            get () { return this.state.refund_amount },
            set (v) { this.setObject('refund_amount', v) }
        },

        refund_note : {
            get () { return this.state.refund_note },
            set (v) { this.setObject('refund_note', v) }
        },

        selected_customer () {
            return this.state.selected_customer
        },

        memo_id : {
            get () { return this.state.memo_id },
            set (v) { this.setObject('memo_id', v) }
        },

        refund_id : {
            get () { return this.state.refund_id },
            set (v) { this.setObject('refund_id', v) }
        },
        
        btn_save_enabled () {
            if (!this.selected_customer || 
                !this.selected_account ||
                !this.refund_amount)
                return false
            return true
        }
    },

    methods : {
        one_money(x) {
            return window.one_money(x)
        },

        setObject(x, y) {
            return this.$store.commit('memoRefund/set_object', 
                    [x, y])
        },

        setObjects(x, y) {
            for (let v of y)
                this.setObject(v, x[v])
        },

        change_refund_date(x) {
            this.setObject("refund_date", x.new_date)
        },

        save() {
            this.$store.dispatch("memoRefund/save").then(res => {
                if (res) {
                    if (this.state.dialog_new) {
                        this.setObject("dialog_new", false)
                        this.$store.dispatch("memo/search")
                    } else {
                        window.location.replace('../list')
                    }
                }
            })
        },

        cancel () {
            if (this.state.dialog_new)
                this.setObject("dialog_new", false)
            else
                window.location.replace('../list')
        }
    },

    mounted () {
        if (this.refund_id != 0) {
            this.$store.dispatch("memoRefund/search_id").then(res => {
                this.setObjects(res, [
                    "memo_number",
                    "memo_amount",
                    "refund_id",
                    "refund_amount",
                    "refund_number",
                    "refund_note"
                ])
                this.setObject("selected_customer", res.customer)
                this.setObject("selected_account", res.credit_account)
                this.setObject("edit", true)
            })
        } else if (this.memo_id != 0) {
            this.$store.dispatch("memoRefund/search_memo_id").then(res => {
                this.setObjects(res, [
                    "memo_number",
                    "memo_amount",
                    "memo_used",
                    "memo_refunded"
                ])
                this.setObject("selected_customer", res.customer)
            })
        }
    }
}