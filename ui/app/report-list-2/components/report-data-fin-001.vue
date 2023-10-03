<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0">
            <v-layout row wrap>
                <v-flex xs5>
                    <h3 class="display-1 font-weight-light zalfa-text-title">{{title}}</h3>
                </v-flex>
                <v-flex xs3 pr-2>
                    
                </v-flex>
                <v-flex xs1 pr-1>
                    <common-datepicker
                        label="Dari Tanggal"
                        :date="s_date"
                        data="0"
                        @change="change_s_date"
                        classs=""
                        hints=" "
                        :details="false"
                        :solo="true"
                    ></common-datepicker>
                </v-flex>
                <v-flex xs1 pl-1>
                    <common-datepicker
                        label="Dari Tanggal"
                        :date="e_date"
                        data="0"
                        @change="change_e_date"
                        classs=""
                        hints=" "
                        :details="false"
                        :solo="true"
                    ></common-datepicker>
                </v-flex>
                <v-flex xs2 class="text-xs-right" pl-2>
                    <v-text-field
                        solo
                        hide-details
                        placeholder="Pencarian" v-model="query"
                        @change="search"
                    >
                        <template v-slot:append-outer>
                            <v-btn color="primary" class="ma-0 btn-icon" @click="search">
                                <v-icon>search</v-icon>
                            </v-btn>      

                            <v-btn color="orange" dark class="ma-0 ml-2 btn-icon" @click="printMe" v-show="!is_sales">
                                <v-icon>print</v-icon>
                            </v-btn>  
                        </template>
                    </v-text-field>
                </v-flex>
            </v-layout>
        </v-card-title>
        <v-card-text class="pt-2">
            <v-data-table 

                :items="balances"
                :loading="false"
                hide-actions
                class="elevation-1">

                <template slot="headers">
                    <tr class="text-xs-center orange white--text">
                        <td rowspan="2" width="28%">NAMA REKENING</td>
                        <td colspan="2" class="border-bottom">SALDO AWAL</td>
                        <td colspan="2" class="lime border-bottom">PERGERAKAN</td>
                        <td colspan="2" class="cyan border-bottom">SALDO AKHIR</td>
                    </tr>
                    <tr class="text-xs-right orange white--text">
                        <td width="12%" class="pa-2">DEBIT</td>
                        <td width="12%" class="pa-2">KREDIT</td>
                        <td width="12%" class="pa-2 lime">DEBIT</td>
                        <td width="12%" class="pa-2 lime">KREDIT</td>
                        <td width="12%" class="pa-2 cyan">DEBIT</td>
                        <td width="12%" class="pa-2 cyan">KREDIT</td>
                    </tr>
                </template>
                <template slot="items" slot-scope="props">
                    <tr class="">
                        <td :colspan="7" class="py-2 px-3 text-xs-left"><b>{{ props.item.group_name.toUpperCase() }}</b></td>
                    </tr>

                    <tr v-for="(inv, n) in props.item.details" :key="inv.account_id">
                        <td class="text-xs-left py-2 pl-4 pr-3" @click="select(props.item)">{{ inv.account_name }}</td>
                        <td class="text-xs-right pa-2 orange lighten-5" @click="select(props.item)"><span class="grey--text caption">Rp</span> {{ oneMoney(inv.b_debit) }}</td>
                        <td class="text-xs-right pa-2 orange lighten-5" @click="select(props.item)"><span class="grey--text caption">Rp</span> {{ oneMoney(inv.b_credit) }}</td>
                        <td class="text-xs-right pa-2 lime lighten-4" @click="select(props.item)"><span class="grey--text caption">Rp</span> {{ oneMoney(inv.j_debit) }}</td>
                        <td class="text-xs-right pa-2 lime lighten-4" @click="select(props.item)"><span class="grey--text caption">Rp</span> {{ oneMoney(inv.j_credit) }}</td>

                        <td v-show="(inv.b_debit+inv.j_debit)>=(inv.b_credit+inv.j_credit)"
                            class="text-xs-right pa-2 cyan lighten-5" @click="select(props.item)"><span class="grey--text caption">Rp</span> {{ oneMoney(inv.b_debit+inv.j_debit-(inv.b_credit+inv.j_credit)) }}</td>
                        <td v-show="(inv.b_debit+inv.j_debit)>=(inv.b_credit+inv.j_credit)"
                            class="text-xs-right pa-2 cyan lighten-5" @click="select(props.item)"><span class="grey--text caption">Rp</span> 0</td>

                        <td v-show="(inv.b_debit+inv.j_debit)<(inv.b_credit+inv.j_credit)"
                            class="text-xs-right pa-2 cyan lighten-5" @click="select(props.item)"><span class="grey--text caption">Rp</span> 0</td>
                        <td v-show="(inv.b_debit+inv.j_debit)<(inv.b_credit+inv.j_credit)"
                            class="text-xs-right pa-2 cyan lighten-5" @click="select(props.item)"><span class="grey--text caption">Rp</span> {{ oneMoney(inv.b_credit+inv.j_credit-(inv.b_debit+inv.j_debit)) }}</td>
                    </tr>
                </template>

                <template slot="footer">
                    <tr class="cyan lighten-4 orange--text">
                        <td :colspan="1" class="py-2 px-3 text-xs-center orange lighten-3 white--text"><b>TOTAL</b></td>
                        <td class="py-2 px-2 text-xs-right yellow lighten-3"><span class="grey--text caption">Rp</span> <b>{{ oneMoney(grand_total.balance_start.debit) }}</b></td>
                        <td class="py-2 px-2 text-xs-right yellow lighten-3"><span class="grey--text caption">Rp</span> <b>{{ oneMoney(grand_total.balance_start.credit) }}</b></td>
                        <td class="py-2 px-2 text-xs-right yellow lighten-3"><span class="grey--text caption">Rp</span> <b>{{ oneMoney(grand_total.trans.debit) }}</b></td>
                        <td class="py-2 px-2 text-xs-right yellow lighten-3"><span class="grey--text caption">Rp</span> <b>{{ oneMoney(grand_total.trans.credit) }}</b></td>
                        <td class="py-2 px-2 text-xs-right yellow lighten-3"><span class="grey--text caption">Rp</span> <b>{{ oneMoney(grand_total.balance_end.debit) }}</b></td>
                        <td class="py-2 px-2 text-xs-right yellow lighten-3"><span class="grey--text caption">Rp</span> <b>{{ oneMoney(grand_total.balance_end.credit) }}</b></td>
                    </tr>
                </template>
            </v-data-table>

            <trans-journal-view></trans-journal-view>
        </v-card-text>
    </v-card>
</template>

<style scoped>
table.v-table tbody td {
    height: auto !important;
}
.v-text-field.v-text-field--solo .v-input__control {
    min-height: 36px;
}
.v-text-field.v-text-field--solo .v-input__append-outer {
    margin-top: 0px;
    margin-left: 0px;
}

table.v-table thead tr {
    height: 30px;
}

.border-bottom {
    border-bottom: solid 1px #FFF !important;
}

table tbody tr td:not(:first-child), table thead tr td:not(:first-child) {
    border-left: solid 1px rgba(0,0,0,.12) !important;
}
</style>

<script>
let t = '?t=' + Math.random() * 1e10
module.exports = {
    components : {
        "common-dialog-delete" : httpVueLoader("../../common/components/common-dialog-delete.vue"),
        "common-dialog-confirm" : httpVueLoader("../../common/components/common-dialog-confirm.vue"),
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        "common-dialog-print" : httpVueLoader("../../common/components/common-dialog-print-size.vue"),
        "invoice-detail" : httpVueLoader("../../sales-invoice/components/sales-invoice-new.vue" + t),
        "trans-journal-view" : httpVueLoader("../../trans-journal/components/trans-journal-new.vue" + t)
    },

    data () {
        return {
            headers: [
                {
                    text: "TANGGAL",
                    align: "center",
                    sortable: false,
                    width: "10%",
                    class: "py-3 px-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "NOMOR",
                    align: "left",
                    sortable: false,
                    width: "10%",
                    class: "py-3 px-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "DESKRIPSI",
                    align: "left",
                    sortable: false,
                    width: "50%",
                    class: "py-3 px-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: " ",
                    align: "right",
                    sortable: false,
                    width: "10%",
                    class: "py-3 px-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: " ",
                    align: "right",
                    sortable: false,
                    width: "10%",
                    class: "py-3 px-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: " ",
                    align: "right",
                    sortable: false,
                    width: "10%",
                    class: "py-3 px-2 zalfa-bg-purple lighten-3 white--text"
                }
            ],
        }
    },

    computed : {
        is_sales () { return false },
        s_date : {
            get () { return this.$store.state.fin001.s_date },
            set (v) { this.$store.commit('fin001/set_common', ['s_date', v]) }
        },

        e_date : {
            get () { return this.$store.state.fin001.e_date },
            set (v) { this.$store.commit('fin001/set_common', ['e_date', v]) }
        },

        title() {
            return "NERACA SALDO"
        },

        query : {
            get () { return this.$store.state.fin001.search },
            set (v) { this.$store.commit('fin001/set_object', ['search', v]) }
        },

        balances () {
            return this.$store.state.fin001.balances
        },

        selected_balance : {
            get () { return this.$store.state.fin001.selected_balance },
            set (v) { this.$store.commit('fin001/set_object', ['selected_balance', v]) }
        },

        accounts () {
            return this.$store.state.report_param.accounts
        },

        selected_account : {
            get () { return this.$store.state.fin001.selected_account },
            set (v) { 
                this.$store.commit('fin001/set_object', ['selected_account', v]) 
                this.search()
            }
        },

        grand_total () {
            let gt = {balance_start:{debit:0, credit:0},trans:{debit:0, credit:0},balance_end:{debit:0, credit:0}}
            for (let l of this.balances) {
                for (let d of l.details) {
                    gt.balance_start.debit += parseFloat(d.b_debit)
                    gt.balance_start.credit += parseFloat(d.b_credit)
                    gt.trans.debit += parseFloat(d.j_debit)
                    gt.trans.credit += parseFloat(d.j_credit)
                    gt.balance_end.debit += parseFloat(d.b_debit+d.j_debit)
                    gt.balance_end.credit += parseFloat(d.b_credit+d.j_credit)
                }       
            }

            return gt
        }
    },

    methods : {
        oneMoney (x) {
            return window.one_money(x)
        },

        change_s_date(x) {
            this.$store.commit('fin001/set_common', ['s_date', x.new_date])
            this.$store.dispatch('fin001/search')
        },

        change_e_date(x) {
            this.$store.commit('fin001/set_common', ['e_date', x.new_date])
            this.$store.dispatch('fin001/search')
        },

        search () {
            return this.$store.dispatch('fin001/search')
        },

        printMe () {
            this.$store.dispatch("fin001/collect").then((x) => {
                window.open(this.$store.state.fin001.URL + 'report/one_fin_001/excel?' + x)
            })
        },

        select (x) {
            this.selected_balance = x
        },

        journal(x) {
            this.$store.commit('fin001/set_object', ['selected_journal', x])
            // this.select(x)
            // this.$store.commit('journal/set_selected_journal', x)
            this.$store.commit('journal_new/set_common', ['dialog_new', true])
            this.$store.commit('journal_new/set_common', ['view', true])
            this.$store.commit('journal_new/set_common', ['journal_note', x.journal_note])
            this.$store.commit('journal_new/set_common', ['journal_receipt', x.journal_receipt])
            this.$store.commit('journal_new/set_common', ['journal_date', x.journal_date])

            this.$store.dispatch('fin001/searchJournalDetail').then((x) => {
                let accs = []
                for (let y of x)
                    accs.push(y.account)
                this.$store.commit('journal_new/set_accounts', accs)
                this.$store.commit('journal_new/set_details', x)
            })
            // let accs = []
            // for (let d of x.accounts)
            //     if (d.account.account_id != null)
            //         accs.push(d.account)
            // this.$store.commit('journal_new/set_accounts', accs)
            // this.$store.commit('journal_new/set_details', x.accounts)
        },

        detail (x, y) {
            x.preventDefault()
            return this.journal(y)
        }
    },

    mounted () {
        // this.$store.dispatch("fin001/search").then(() => {
        //     this.$store.dispatch("report_param/search_account")
        // })
    }
}