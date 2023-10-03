<template>
    <v-card>
        <v-card-title primary-title class="teal lighten-5">
            <h3 class="display-1 font-weight-light zalfa-text-title">KREDIT MEMO </h3>
        </v-card-title>
        <v-card-text>
            <v-layout row wrap>
                <v-flex xs6 pr-4>
                    <v-layout row wrap>
                        <v-flex xs12>
                            <v-autocomplete
                                :items="customers"
                                v-model="selected_customer"
                                return-object
                                item-text="customer_name"
                                item-value="customer_id"
                                label="Customer"
                                :loading="customers.length<1"
                                v-show="!selected_customer"
                            >
                            </v-autocomplete>

                            <v-text-field
                                label="Customer"
                                :value="selected_customer?selected_customer.customer_name:''"
                                readonly
                                :clearable="memo_id==0"
                                @click:clear="selected_customer=null"
                                v-show="!!selected_customer"
                            ></v-text-field>
                        </v-flex>
                    </v-layout>

                    <v-layout row wrap v-show="invoice_id==0">
                        <v-flex xs12>
                            <v-select
                                :items="accounts"
                                v-model="selected_account"
                                return-object
                                item-text="account_name"
                                item-value="account_id"
                                label="Rekening Tujuan"
                                :loading="accounts.length<1"
                                v-show="!selected_account"
                            >
                            </v-select>

                            <v-text-field
                                label="Rekening Tujuan"
                                :value="selected_account?selected_account.account_name:''"
                                readonly
                                :clearable="invoice_id==0"
                                @click:clear="selected_account=null"
                                v-show="!!selected_account"
                            ></v-text-field>
                        </v-flex>
                    </v-layout>

                    <v-layout row wrap>
                        <v-flex xs6 pr-4>
                            <common-datepicker
                                label="Tanggal Transaksi"
                                :date="memo_date" data="0" hints=" "
                                @change="change_memo_date"
                                :details="true" :solo="false"
                                v-if="dialog"
                            ></common-datepicker>        
                        </v-flex>
                        <v-flex xs6>
                            <v-text-field
                                label="Nomor Memo"
                                :value="memo_number"
                                readonly
                            ></v-text-field>
                        </v-flex>
                    </v-layout>
                </v-flex>
                <v-flex xs6 pl-4>
                    <v-layout row wrap>
                        <v-flex xs12>
                            <h3 class="text-xs-right display-1 mt-1 mb-3">TOTAL MEMO : Rp. {{one_money(memo_amount)}}</h3>
                        </v-flex>
                    </v-layout>
                    <common-tag mode="outline" label="Tag(s)"></common-tag>
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
                                <th role="columnheader" scope="col" width="15%" aria-label="NOMOR INVOICE: Not sorted." aria-sort="none" class="column text-xs-left py-2 px-3 zalfa-bg-purple lighten-3 white--text" v-show="invoice_id!=0">NOMOR INVOICE</th>
                                <th role="columnheader" scope="col" width="70%" aria-label="NOMOR INVOICE: Not sorted." aria-sort="none" class="column text-xs-left py-2 px-3 zalfa-bg-purple lighten-3 white--text" :colspan="invoice_id==0?2:1">DESKRIPSI</th>
                                <th role="columnheader" scope="col" width="15%" aria-label="NOMOR INVOICE: Not sorted." aria-sort="none" class="column text-xs-right py-2 px-3 zalfa-bg-purple lighten-3 white--text">JUMLAH</th>
                            </tr>
                        </template>
                        <template  v-slot:items="props">
                            <td class="py-2 px-3" v-show="invoice_id!=0">{{ invoice_number }}</td>
                            <!-- <td class="py-2 px-3" v-show="!edit">{{ memo_note }}</td> -->
                            <td class="py-2 px-3" :colspan="invoice_id==0?2:1"><v-text-field
                                solo hide-details dense
                                v-model="memo_note" :readonly="invoice_id!=0"
                            ></v-text-field></td>

                            <!-- <td class="text-xs-right py-2 px-3" v-show="invoice_id!=0">{{ one_money(memo_amount) }}</td> -->
                            <td class="text-xs-right py-2 px-3"><v-text-field
                                solo dense hide-details reverse
                                v-model="memo_amount" class="text-xs-right"
                                :readonly="invoice_id!=0"
                            ></v-text-field></td>

                        </template>
                    </v-data-table>
                </v-flex>
                <v-flex xs12>
                    <v-layout row wrap mt-3>
                        <v-flex xs10 class="text-xs-right headline">Total :</v-flex>
                        <v-flex xs2 class="text-xs-right px-3"><h3 class="headline">Rp. {{ one_money(memo_amount)}}</h3></v-flex>
                    </v-layout>
                    <v-layout row wrap mt-3 class="subheading">
                        <v-flex xs10 class="text-xs-right">Digunakan :</v-flex>
                        <v-flex xs2 class="text-xs-right px-3"><h3 class="">Rp. {{one_money(memo_used)}}</h3></v-flex>
                    </v-layout>
                    <v-layout row wrap mt-3 class="subheading">
                        <v-flex xs10 class="text-xs-right">Dikembalikan :</v-flex>
                        <v-flex xs2 class="text-xs-right px-3"><h3 class="">Rp. {{one_money(memo_refunded)}}</h3></v-flex>
                    </v-layout>
                </v-flex>
            </v-layout>
        </v-card-text>
        <v-card-actions>
            <v-flex xs12 class="text-xs-right">
                <v-btn color="primary" @click="cancel" outline>Batal</v-btn>
                <v-btn color="primary" @click="save">Simpan</v-btn>
            </v-flex>
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
            return this.$store.state.memoNew
        },

        dialog () {
            return this.$store.state.memoNew.dialog_new
        },

        memo_date () {
                return this.$store.state.memoNew.memo_date
        },

        edit () {
            return this.$store.state.memoNew.edit
        },

        memo_id () {
            return this.state.memo_id
        },

        memo_number () {
            return this.state.memo_number
        },

        memo_amount : {
            get () { return this.state.memo_amount },
            set (v) { this.setObject("memo_amount", v) }
        },

        memo_used () {
            return this.state.memo_used
        },

        memo_refunded () {
            return this.state.memo_refunded
        },

        memo_note : {
            get () { return this.state.memo_note },
            set (v) { this.setObject("memo_note", v) }
        },

        invoice_number () {
            return this.state.invoice_number
        },

        invoice_id () {
            return this.state.invoice_id
        },

        selected_customer : {
            get () { return this.state.selected_customer },
            set (v) { this.setObject("selected_customer", v) }
        },

        customers () {
            return this.state.customers
        },

        accounts () {
            return this.state.accounts
        },

        selected_account : {
            get () { return this.state.selected_account },
            set (v) { this.setObject("selected_account", v) }
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        setObject(x, y) {
            return this.$store.commit('memoNew/set_object', 
                    [x, y])
        },

        setObjects(x, y) {
            for (let v of y)
                this.setObject(v, x[v])
        },

        change_memo_date(x) {
            this.setObject("memo_date", x.new_date)
        },

        amountValidation(e) {
            if (!(e.key.match(/[0-9\,\b]/) || [8,37,39].indexOf(e.keyCode) > -1))
                e.preventDefault()
        },

        save () {
            this.$store.dispatch('memoNew/save').then(res => {
                if (!!this.$store.state.memoNew.dialog_new) {
                    this.$store.commit("memoNew/set_common", ["dialog_new", false])

                    // IF POSITION STILL IN ACCOUNT LIST == TAB.01
                    // if (this.$store.state.memo.selected_tab == "TAB.01")
                    //     this.$store.commit("memo/set_object", ["selected_tab", "TAB.02"])
                    this.$store.dispatch("memo/search")
                } else {
                    window.location = "../list/" + this.selected_account_to.account_id
                }
            })
        },

        cancel () {
            if (!!this.$store.state.memoNew.dialog_new) {
                this.$store.commit("memoNew/set_common", ["dialog_new", false])
            } else {
                window.location = "../list/" + this.selected_account_to.account_id
            }
        }
    },

    mounted () {
        let id = window.getParameter('id')
        if (id)
            this.setObject('memo_id', id)

        this.$store.dispatch('memoNew/search_account').then((y) => {
            this.$store.dispatch('memoNew/search_customer').then((x) => {
            

            })
        })

        if (this.$store.state.memoNew.memo_id) {
            // this.setObject('memo_id', id)
            this.setObject("edit", true)

            this.$store.dispatch('memo/search_id').then(res => {
                this.setObjects(res, [
                    "memo_date",
                    "memo_memo",
                    "memo_note",
                    "memo_date",
                    "memo_from",
                    "memo_number",
                    "memo_amount",
                    "memo_disc",
                    "memo_discrp",
                    "memo_img"
                ])

                this.$store.commit("tag/set_object", ['selected_tagnames', JSON.parse(res.memo_tags)])

                if (res.tax_id != 0) {
                    this.setObject("selected_tax", {tax_id:res.tax_id,tax_name:res.tax_name,tax_amount:res.tax_amount})
                }
                this.setObject("selected_account", {account_id:res.from_account_id,account_name:res.from_account_name})
            })
        }
    },

    watch : {
    }
}