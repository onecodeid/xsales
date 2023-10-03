<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0">
            <v-layout row wrap>
                <v-flex xs12>
                    <v-layout row wrap>
                        <v-flex xs3 class="text-xs-right" pl-0 pr-2>
                            <v-text-field
                                solo
                                hide-details
                                placeholder="Pencarian" v-model="query"
                                @change="search"
                            >
                                <template v-slot:prepend>
                                    <v-btn color="brown" dark class="ma-0 mr-2 btn-icon" @click="printMe2" v-show="!is_sales">
                                        <v-icon>print</v-icon>
                                    </v-btn>

                                    <v-btn color="orange" dark class="ma-0 mr-2 btn-icon" @click="printMe" v-show="!is_sales">
                                        <v-icon>print</v-icon>
                                    </v-btn>  

                                    <v-btn color="primary" class="ma-0 btn-icon" @click="search">
                                        <v-icon>search</v-icon>
                                    </v-btn> 
                                </template>
                            </v-text-field>
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
                        
                        <v-flex xs7>
                            <h3 class="display-1 font-weight-light zalfa-text-title text-xs-right">{{title}}</h3>
                        </v-flex>
                    </v-layout>
                </v-flex>
                <v-flex xs12>
                    <v-layout row wrap pt-1>
                        <v-flex xs12>
                            <v-autocomplete
                                :items="accounts"
                                return-object
                                clearable
                                item-text="account_name"
                                item-value="account_id"
                                placeholder="Semua Akun"
                                item-disabled="parent"
                                v-model="selected_accounts"
                                label="Akun / Rekening"
                                solo
                                hide-details
                                multiple
                            >
                                <template slot="item" slot-scope="data">
                                    <div class="v-list__tile__content">
                                        <div class="v-list__tile__title"><span class="blue--text mr-2">{{data.item.account_code}}</span> {{data.item.account_name}}</div> 
                                    </div>
                                    
                                </template>

                                <template slot="selection" slot-scope="data">
                                    <v-chip close @input="removeAccount(data.index)"><span class="blue--text mr-2">{{data.item.account_code}}</span> {{data.item.account_name}}</v-chip>
                                    <!-- <v-layout row wrap>
                                        <div class="v-list__tile__title"><span class="blue--text mr-2">{{data.item.account_code}}</span> {{data.item.account_name}}</div>
                                    </v-layout> -->
                                </template>

                                <template slot="prepend-inner">
                                    <v-icon class="mr-2 blue--text">account_circle</v-icon></template>
                            </v-autocomplete>
                        </v-flex>
                    </v-layout>
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
                        <td :colspan="3" class="py-2 px-3 text-xs-left"><b><span class="blue--text mr-2">{{ props.item.account_code.toUpperCase() }}</span> {{ props.item.account_name.toUpperCase() }}</b></td>
                        <td class="py-2 px-2 text-xs-right"><b>DEBIT</b></td>
                        <td class="py-2 px-2 text-xs-right"><b>CREDIT</b></td>
                        <td class="py-2 px-2 text-xs-right"><b>SALDO</b></td>
                    </tr>

                    <!-- SALDO AWAL -->
                    <tr class="">
                        <td colspan="2"></td>
                        <td :colspan="headers.length - 5" class="py-2 px-2 text-xs-left"><b>SALDO AWAL</b></td>
                        <td class="py-2 px-2 text-xs-right"></td>
                        <td class="py-2 px-2 text-xs-right"></td>

                        <td class="pa-2 text-xs-right"><span class="grey--text caption">Rp</span> 
                            <b v-show="props.item.balance_open >= 0">{{ oneMoney(props.item.balance_open) }}</b>
                            <b v-show="props.item.balance_open < 0">({{ oneMoney(0-props.item.balance_open) }})</b>
                        </td>
                    </tr>
                    <!-- <tr>
                        <td class="text-xs-center pa-2" @click="select(props.item)">{{ s_date }}</td>
                        <td class="text-xs-left pa-2" @click="select(props.item)"></td>
                        <td class="text-xs-left pa-2" @click="select(props.item)">SALDO AWAL</td>
                        <td class="text-xs-right pa-2" @click="select(props.item)"></td>
                        <td class="text-xs-right pa-2" @click="select(props.item)"></td>
                        
                        <td class="text-xs-right pa-2" @click="select(props.item)" v-show="props.item.balance_open >= 0">{{ oneMoney(props.item.balance_open) }}</td>
                        <td class="text-xs-right pa-2" @click="select(props.item)" v-show="props.item.balance_open < 0">({{ oneMoney(0-props.item.balance_open) }})</td>
                    </tr> -->
                    <!-- END OF SALDO AWAL -->

                    <tr v-for="(inv, n) in props.item.details" :key="props.item.account_id+'-'+inv.detail_id">
                        <td class="text-xs-center pa-2" @click="select(props.item)">{{ inv.journal_date }}</td>
                        <td class="text-xs-left pa-2" @click="select(props.item)"><a href="javascript:;" @click="select(props.item),detail($event, inv)">{{ inv.journal_number }}</a></td>
                        <td class="text-xs-left pa-2" @click="select(props.item)">
                            <a href="javascript:;" @click="select(props.item),detailRef($event, inv)" v-show="journal_types_link.indexOf(inv.journal_type)>-1">
                                {{ inv.ledger_note }}
                            </a>
                            <span v-show="journal_types_link.indexOf(inv.journal_type)<0">{{ inv.ledger_note }} 
                                <span v-show="inv.ledger_note != '' && inv.journal_ref_note != ''">~</span> {{ inv.journal_ref_note }}</span>
                        </td>
                        <td class="text-xs-left pa-2" @click="select(props.item)">
                            <span v-show="inv.journal_credit==0" v-for="(ac,acn) in inv.acredit" :key="'c-'+acn" :style="inv.journal_credit==0?'display:block':''">
                                <span class="primary--text mr-2">{{ ac[0] }}</span>{{ ac[1] }}</span>
                            <span v-show="inv.journal_debit==0" v-for="(ac,acn) in inv.adebit" :key="'d-'+acn" :style="inv.journal_debit==0?'display:block':''"><span class="primary--text mr-2">{{ ac[0] }}</span>{{ ac[1] }}</span>
                        </td>
                        <td class="text-xs-left pa-2" @click="select(props.item)">
                            <span v-show="journal_types_link.indexOf(inv.journal_type)<0">{{ JSON.parse(inv.journal_tags).join(', ') }}</span>
                        </td>
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
                    </tr>
                </template>

                <template slot="footer">
                    <tr class="cyan lighten-4 orange--text">
                        <td :colspan="headers.length - 3" class="py-2 px-3 text-xs-right orange lighten-3 white--text"><b>GRAND TOTAL</b></td>
                        <td class="py-2 px-2 text-xs-right yellow lighten-3"><span class="grey--text caption">Rp</span> <b>{{ oneMoney(grand_total.debit) }}</b></td>
                        <td class="py-2 px-2 text-xs-right yellow lighten-3"><span class="grey--text caption">Rp</span> <b>{{ oneMoney(grand_total.credit) }}</b></td>
                        <td class="pa-2 text-xs-right yellow lighten-3"> 
                        </td>
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
        "invoice-detail" : httpVueLoader("../../sales-invoice/components/sales-invoice-new.vue" + t),
        "trans-journal-view" : httpVueLoader("../../trans-journal/components/trans-journal-new.vue" + t)
    },

    data () {
        return {
            journal_types_link: ['J.01', 'J.11', 'J.12', 'J.13', 'J.14', 'J.18', 'J.20', 'J.RN'],
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
                    text: "DESKRIPSI / PENGIRIM / PENERIMA",
                    align: "left",
                    sortable: false,
                    width: "20%",
                    class: "py-3 px-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "KONTRA AKUN",
                    align: "left",
                    sortable: false,
                    width: "15%",
                    class: "py-3 px-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "TAGS",
                    align: "left",
                    sortable: false,
                    width: "15%",
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
            set (v) { this.$store.commit('fin002/set_object', ['search', v]) }
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
        },

        selected_journal () {
            return this.$store.state.fin002.selected_journal
        },

        selected_accounts : {
            get () { return this.$store.state.fin002.selected_accounts },
            set (v) { 
                this.$store.commit('fin002/set_object', ['selected_accounts', v]) 
                this.search()
            }
        },

        grand_total () {
            let gt = {debit:0, credit:0}
            for (let l of this.ledgers) {
                for (let d of l.details) {
                    gt.debit += (d.journal_debit == null ? 0 : parseFloat(d.journal_debit))
                    gt.credit += (d.journal_credit == null ? 0 : parseFloat(d.journal_credit))
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

        printMe2 () {
            this.$store.dispatch("fin002/collect").then((x) => {
                window.open(this.$store.state.fin002.URL + 'report/one_fin_002/excel2?' + x)
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
        },

        detailRef (x, y) {
            x.preventDefault()
            this.$store.commit('fin002/set_object', ['selected_journal', y])
            
            if (y.journal_type == 'J.01')
                window.open('../../finance-journal-general/new/?id=' + this.selected_journal.journal_id, '_blank')
            if (y.journal_type == 'J.11')
                window.open('../../finance-payable/pay/' + this.selected_journal.journal_ref_id, '_blank')
            if (y.journal_type == 'J.12')
                window.open('../../finance-bill/pay/' + this.selected_journal.journal_ref_id, '_blank')
            if (y.journal_type == 'J.13')
                window.open('../../finance-cash/receive/?id=' + this.selected_journal.journal_ref_id, '_blank')
            if (y.journal_type == 'J.14')
                window.open('../../finance-cash/pay/?id=' + this.selected_journal.journal_ref_id, '_blank')
            if (y.journal_type == 'J.18')
                window.open('../../finance-cash/transfer/?id=' + this.selected_journal.journal_ref_id, '_blank')
            if (y.journal_type == 'J.20')
                window.open('../../sales-invoice/view/' + this.selected_journal.journal_ref_id, '_blank')
            if (y.journal_type == 'J.RN')
                window.open('../../finance-journal-import/new/?id=' + this.selected_journal.journal_id, '_blank')
                // this.$store.dispatch('fin002/searchRef')
        },

        removeAccount (x) {
            let y = JSON.parse(JSON.stringify(this.selected_accounts))
            y.splice(x, 1)

            this.selected_accounts = y
        }
    },

    mounted () {
        // this.$store.dispatch("fin002/search").then(() => {
        //     this.$store.dispatch("report_param/search_account")
        // })
    }
}