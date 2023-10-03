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

                            <!-- <v-btn color="orange" dark class="ma-0 ml-2 btn-icon" @click="printMe" v-show="!is_sales">
                                <v-icon>print</v-icon>
                            </v-btn>   -->
                        </template>
                    </v-text-field>
                </v-flex>
            </v-layout>
        </v-card-title>
        <v-card-text class="pt-2">
            <v-data-table 
                :headers="headers"
                :items="logs"
                :loading="false"
                hide-actions
                class="elevation-1">

                <template slot="items" slot-scope="props">
                    <!-- <tr class="cyan lighten-4">
                        <td :colspan="2" class="py-2 px-3 text-xs-left yellow lighten-3"><b>{{ props.item.group_name.toUpperCase() }}</b></td>
                        <td :colspan="1" class="py-2 px-3 text-xs-left"><b>{{ props.item.account_name.toUpperCase() }}</b></td>
                        <td class="py-2 px-2 text-xs-right"><b>DEBIT</b></td>
                        <td class="py-2 px-2 text-xs-right"><b>CREDIT</b></td>
                        <td class="py-2 px-2 text-xs-right"><b>SALDO</b></td>
                    </tr> -->

                    <tr class="">
                        <td class="py-2 px-2 text-xs-center">{{ props.index + 1 }}</td>
                        <td class="py-2 px-2 text-xs-left">{{ props.item.log_date }}</td>
                        <td class="py-2 px-2 text-xs-left">{{ props.item.log_time }}</td>
                        <td class="py-2 px-2 text-xs-left">{{ props.item.log_module }}</td>
                        <td class="py-2 px-2 text-xs-left">{{ props.item.log_what }}</td>
                        <td class="py-2 px-2 text-xs-left">{{ props.item.log_ref_number }}</td>
                        <td class="py-2 px-2 text-xs-left">{{ props.item.log_ref_date }}</td>
                        <td class="py-2 px-2 text-xs-left">{{ props.item.log_staff_name }}</td>

                        <!-- <td class="pa-2 text-xs-right"><span class="grey--text caption">Rp</span> 
                            <b v-show="props.item.balance_open >= 0">{{ oneMoney(props.item.balance_open) }}</b>
                            <b v-show="props.item.balance_open < 0">({{ oneMoney(0-props.item.balance_open) }})</b>
                        </td> -->
                    </tr>

                    <!-- <tr v-for="(inv, n) in props.item.details" :key="props.item.account_id+'-'+inv.detail_id">
                        <td class="text-xs-center pa-2" @click="select(props.item)">{{ inv.journal_date }}</td>
                        <td class="text-xs-left pa-2" @click="select(props.item)"><a href="#" @click="select(props.item),detail($event, inv)">{{ inv.journal_number }}</a></td>
                        <td class="text-xs-left pa-2" @click="select(props.item)">{{ inv.ledger_note }}</td>
                        <td class="text-xs-right pa-2" @click="select(props.item)">{{ oneMoney(inv.journal_debit) }}</td>
                        <td class="text-xs-right pa-2" @click="select(props.item)">{{ oneMoney(inv.journal_credit) }}</td>
                        
                        <td class="text-xs-right pa-2" @click="select(props.item)" v-show="inv.balance >= 0">{{ oneMoney(inv.balance) }}</td>
                        <td class="text-xs-right pa-2" @click="select(props.item)" v-show="inv.balance < 0">({{ oneMoney(0-inv.balance) }})</td>
                    </tr>
                    <tr class="cyan lighten-4 orange--text">
                        <td :colspan="headers.length - 3" class="py-2 px-3 text-xs-right orange lighten-3 white--text"><b>JUMLAH / SALDO AKHIR</b></td>
                        <td class="py-2 px-2 text-xs-right yellow lighten-3"><b>{{ oneMoney(props.item.total_debit) }}</b></td>
                        <td class="py-2 px-2 text-xs-right yellow lighten-3"><b>{{ oneMoney(props.item.total_credit) }}</b></td>

                        <td class="pa-2 text-xs-right yellow lighten-3"><span class="grey--text caption">Rp</span> 
                            <b v-show="props.item.balance_close >= 0">{{ oneMoney(props.item.balance_close) }}</b>
                            <b v-show="props.item.balance_close < 0">({{ oneMoney(0-props.item.balance_close) }})</b>
                        </td>
                    </tr>
                    <tr class="cyan lighten-4">
                        <td :colspan="headers.length" class="py-1 px-3 text-xs-center white"></td>
                    </tr> -->
                </template>

                <template slot="footer">
                    <tr class="cyan lighten-4 orange--text">
                        <td :colspan="9" class="py-2 px-3 text-xs-right orange lighten-3 white--text"></td>
                        <!-- <td :colspan="2" class="py-2 px-2 text-xs-right yellow lighten-3"><span class="grey--text caption">Rp</span> <b>{{ oneMoney(grand_total) }}</b></td> -->
                    </tr>
                </template>
            </v-data-table>

            <v-divider></v-divider>
            <v-pagination
                style="margin-top:10px;margin-bottom:10px"
                v-model="curr_page"
                :length="xtotal_page"
                @input="change_page"
            ></v-pagination>
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

.v-text-field.v-text-field--solo .v-input__prepend-outer {
    margin-top: 0px;
    margin-right: 0px;
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
        // "invoice-detail" : httpVueLoader("../../sales-invoice/components/sales-invoice-new.vue" + t),
        "trans-journal-view" : httpVueLoader("../../trans-journal/components/trans-journal-new.vue" + t)
    },

    data () {
        return {
            headers: [
                {
                    text: "NO",
                    align: "center",
                    sortable: false,
                    width: "5%",
                    class: "py-3 px-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "TANGGAL",
                    align: "left",
                    sortable: false,
                    width: "8%",
                    class: "py-3 px-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "JAM",
                    align: "left",
                    sortable: false,
                    width: "8%",
                    class: "py-3 px-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "MODUL",
                    align: "left",
                    sortable: false,
                    width: "10%",
                    class: "py-3 px-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "ACTION",
                    align: "left",
                    sortable: false,
                    width: "10%",
                    class: "py-3 px-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "REF NUMBER",
                    align: "left",
                    sortable: false,
                    width: "39%",
                    class: "py-3 px-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "REF DATE",
                    align: "left",
                    sortable: false,
                    width: "10%",
                    class: "py-3 px-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "USER",
                    align: "left",
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
            get () { return this.$store.state.log001.s_date },
            set (v) { this.$store.commit('log001/set_common', ['s_date', v]) }
        },

        e_date : {
            get () { return this.$store.state.log001.e_date },
            set (v) { this.$store.commit('log001/set_common', ['e_date', v]) }
        },

        title() {
            return "LAPORAN AKTIVITAS"
        },

        query : {
            get () { return this.$store.state.log001.search },
            set (v) { this.$store.commit('log001/set_object', ['search', v]) }
        },

        logs () {
            return this.$store.state.log001.logs
        },

        selected_log : {
            get () { return this.$store.state.log001.selected_log },
            set (v) { this.$store.commit('log001/set_object', ['selected_log', v]) }
        },

        warehouses () {
            return this.$store.state.report_param.warehouses
        },

        selected_warehouse : {
            get () { return this.$store.state.log001.selected_warehouse },
            set (v) { 
                this.$store.commit('log001/set_object', ['selected_warehouse', v]) 
                this.search()
            }
        },

        grand_total () {
            let gt = 0
            for (let l of this.items) {
                gt += parseFloat(l.stock_qty*l.item_hpp)
                    
            }

            return gt
        },

        curr_page : {
            get () { return this.$store.state.log001.current_page },
            set (v) { this.$store.commit('log001/set_object', ['current_page', v]) }
        },

        xtotal_page () {
            return this.$store.state.log001.total_log_page
        }
    },

    methods : {
        oneMoney (x) {
            return window.one_money(x)
        },

        change_s_date(x) {
            this.$store.commit('log001/set_common', ['s_date', x.new_date])
            this.$store.dispatch('log001/search')
        },

        change_e_date(x) {
            this.$store.commit('log001/set_common', ['e_date', x.new_date])
            this.$store.dispatch('log001/search')
        },

        search () {
            return this.$store.dispatch('log001/search', {})
        },

        printMe () {
            this.$store.dispatch("log001/collect").then((x) => {
                window.open(this.$store.state.log001.URL + 'report/one_log_001/excel?' + x)
            })
        },

        select (x) {
            this.selected_ledger = x
        },

        journal(x) {
            this.$store.commit('log001/set_object', ['selected_journal', x])
            // this.select(x)
            // this.$store.commit('journal/set_selected_journal', x)
            this.$store.commit('journal_new/set_common', ['dialog_new', true])
            this.$store.commit('journal_new/set_common', ['view', true])
            this.$store.commit('journal_new/set_common', ['journal_note', x.journal_note])
            this.$store.commit('journal_new/set_common', ['journal_receipt', x.journal_receipt])
            this.$store.commit('journal_new/set_common', ['journal_date', x.journal_date])

            this.$store.dispatch('log001/searchJournalDetail').then((x) => {
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
        },

        removeAccount (x) {
            let y = JSON.parse(JSON.stringify(this.selected_accounts))
            y.splice(x, 1)

            this.selected_accounts = y
        },

        change_page(x) {
            this.curr_page = x
            this.search()
        }
    },

    mounted () {
        this.$store.dispatch("report_param/search_warehouse").then((x)=> {
            this.selected_warehouse = x[0]
            this.$store.dispatch("log001/search")
        })
        
        // this.$store.dispatch("log001/search").then(() => {
        //     this.$store.dispatch("report_param/search_account")
        // })
    }
}