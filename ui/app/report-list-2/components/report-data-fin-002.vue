<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0">
            <v-layout row wrap>
                <v-flex xs5>
                    <h3 class="display-1 font-weight-light zalfa-text-title">{{title}}</h3>
                </v-flex>
                <v-flex xs3 pr-2>
                    <v-autocomplete
                        :items="accounts"
                        return-object
                        clearable
                        :readonly="!!selected_account"
                        item-text="account_name"
                        item-value="account_id"
                        placeholder="Pilih..."
                        item-disabled="parent"
                        v-model="selected_account"
                        label="Akun / Rekening"
                        solo
                        hide-details
                    >
                        <template slot="item" slot-scope="data">
                            <div class="v-list__tile__content">
                                <div class="v-list__tile__title"><span class="blue--text mr-2">{{data.item.account_code}}</span> {{data.item.account_name}}</div> 
                            </div>
                            
                        </template>

                        <template slot="selection" slot-scope="data">
                            <v-layout row wrap>
                                <div class="v-list__tile__title"><span class="blue--text mr-2">{{data.item.account_code}}</span> {{data.item.account_name}}</div>
                            </v-layout>
                        </template>
                    </v-autocomplete>
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
                :headers="headers"
                :items="ledgers"
                :loading="false"
                hide-actions
                class="elevation-1">

                <template slot="items" slot-scope="props">
                    <tr class="cyan lighten-4">
                        <td :colspan="2" class="py-2 px-3 text-xs-left yellow lighten-3"><b>{{ props.item.group_name.toUpperCase() }}</b></td>
                        <td class="py-2 px-3 text-xs-center"><b>{{ props.item.account_name.toUpperCase() }}</b></td>
                        <td :colspan="2" class="py-2 px-3 text-xs-left yellow lighten-3 cyan--text"><b>SALDO AWAL</b></td>
                        <td class="pa-2 text-xs-right yellow lighten-3"><span class="grey--text caption">Rp</span> 
                            <b v-show="props.item.balance_open >= 0">{{ oneMoney(props.item.balance_open) }}</b>
                            <b v-show="props.item.balance_open < 0">({{ oneMoney(0-props.item.balance_open) }})</b>
                        </td>
                    </tr>
                    <tr v-for="(inv, n) in props.item.details" :key="props.item.account_id+'-'+inv.detail_id">
                        <td class="text-xs-center pa-2" @click="select(props.item)">{{ inv.journal_date }}</td>
                        <td class="text-xs-left pa-2" @click="select(props.item)"><a href="#" @click="select(props.item),detail($event, inv)">{{ inv.journal_number }}</a></td>
                        <td class="text-xs-left pa-2" @click="select(props.item)">{{ inv.ledger_note }}</td>
                        <td class="text-xs-right pa-2" @click="select(props.item)">{{ oneMoney(inv.journal_debit) }}</td>
                        <td class="text-xs-right pa-2" @click="select(props.item)">{{ oneMoney(inv.journal_credit) }}</td>
                        
                        <td class="text-xs-right pa-2" @click="select(props.item)" v-show="inv.balance >= 0">{{ oneMoney(inv.balance) }}</td>
                        <td class="text-xs-right pa-2" @click="select(props.item)" v-show="inv.balance < 0">({{ oneMoney(0-inv.balance) }})</td>
                    </tr>
                    <tr class="cyan lighten-4 orange--text">
                        <td :colspan="headers.length - 3" class="py-2 px-3 text-xs-center orange lighten-3">&nbsp;</td>
                        <td :colspan="2" class="py-2 px-3 text-xs-left yellow lighten-3"><b>SALDO AKHIR</b></td>
                        <td class="pa-2 text-xs-right yellow lighten-3"><span class="grey--text caption">Rp</span> 
                            <b v-show="props.item.balance_close >= 0">{{ oneMoney(props.item.balance_close) }}</b>
                            <b v-show="props.item.balance_close < 0">({{ oneMoney(0-props.item.balance_close) }})</b>
                        </td>
                    </tr>
                    <tr class="cyan lighten-4">
                        <td :colspan="headers.length" class="py-1 px-3 text-xs-center white"></td>
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
                    text: "DEBIT",
                    align: "right",
                    sortable: false,
                    width: "10%",
                    class: "py-3 px-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "KREDIT",
                    align: "right",
                    sortable: false,
                    width: "10%",
                    class: "py-3 px-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "SALDO",
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
            get () { return this.$store.state.fin002.s_date },
            set (v) { this.$store.commit('fin002/set_common', ['s_date', v]) }
        },

        e_date : {
            get () { return this.$store.state.fin002.e_date },
            set (v) { this.$store.commit('fin002/set_common', ['e_date', v]) }
        },

        title() {
            return "LAPORAN BUKU BESAR"
        },

        query : {
            get () { return this.$store.state.fin002.search },
            set (v) { this.$store.commit('fin002/update_search', v) }
        },

        ledgers () {
            return this.$store.state.fin002.ledgers
        },

        selected_ledger : {
            get () { return this.$store.state.fin002.selected_ledger },
            set (v) { this.$store.commit('fin002/set_object', ['selected_ledger', v]) }
        },

        accounts () {
            return this.$store.state.report_param.accounts
        },

        selected_account : {
            get () { return this.$store.state.fin002.selected_account },
            set (v) { 
                this.$store.commit('fin002/set_object', ['selected_account', v]) 
                this.search()
            }
        }
    },

    methods : {
        oneMoney (x) {
            return window.one_money(x)
        },

        change_s_date(x) {
            this.$store.commit('fin002/set_common', ['s_date', x.new_date])
            this.$store.dispatch('fin002/search')
        },

        change_e_date(x) {
            this.$store.commit('fin002/set_common', ['e_date', x.new_date])
            this.$store.dispatch('fin002/search')
        },

        search () {
            return this.$store.dispatch('fin002/search', {})
        },

        printMe () {
            this.$store.dispatch("fin002/collect").then((x) => {
                window.open(this.$store.state.fin002.URL + 'report/one_fin_002/excel?' + x)
            })
        },

        select (x) {
            this.selected_ledger = x
        },

        journal(x) {
            this.$store.commit('fin002/set_object', ['selected_journal', x])
            // this.select(x)
            // this.$store.commit('journal/set_selected_journal', x)
            this.$store.commit('journal_new/set_common', ['dialog_new', true])
            this.$store.commit('journal_new/set_common', ['view', true])
            this.$store.commit('journal_new/set_common', ['journal_note', x.journal_note])
            this.$store.commit('journal_new/set_common', ['journal_receipt', x.journal_receipt])
            this.$store.commit('journal_new/set_common', ['journal_date', x.journal_date])

            this.$store.dispatch('fin002/searchJournalDetail').then((x) => {
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
        // this.$store.dispatch("fin002/search").then(() => {
        //     this.$store.dispatch("report_param/search_account")
        // })
    }
}