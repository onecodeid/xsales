<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0">
            <v-layout row wrap>
                <v-flex xs7>
                    <h3 class="display-1 font-weight-light zalfa-text-title">{{title}}</h3>
                </v-flex>
                <v-flex xs1 pr-1 v-if="byDate">
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
                <v-flex xs1 pl-1 v-if="byDate">
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
                <v-flex xs3 pr-2 class="pl-2" :class="{'offset-xs2':!byDate}">
                    <v-select
                        :items="endyears"
                        item-text="label"
                        item-value="edate"
                        return-object
                        v-model="selected_endyear"
                        label="Periode"
                        clearable
                        hide-details
                        solo
                    >
                        <template v-slot:append-outer>
                            <!-- <v-btn color="primary" class="ma-0 btn-icon" @click="search">
                                <v-icon>search</v-icon>
                            </v-btn>       -->

                            <v-btn color="orange" dark class="ma-0 ml-2 btn-icon" @click="printMe" v-show="!is_sales">
                                <v-icon>print</v-icon>
                            </v-btn>  
                        </template>

                        <template slot="item" slot-scope="data">
                            <span v-show="!!data.item.header" class="orange--text">XYZ</span>
                            <span v-show="!data.item.header" :class="{'pl-3':data.item.sdate!='1971-01-01'}">{{ data.item.label }}</span>
                        </template>
                    </v-select>
                </v-flex>
                
               
            </v-layout>
        </v-card-title>
        <v-card-text class="pt-2">
            <v-data-table 
                :headers="headers"
                :items="accounts"
                :loading="false"
                hide-actions
                class="elevation-1">

                <template slot="items" slot-scope="props">
                    <tr class="cyan lighten-4">
                        <td  class="py-1 px-3 text-xs-left">{{ props.item.sub_title }}</td>
                        <td class="py-1 px-3 text-xs-right"></td>
                    </tr>
                    <tr v-for="(d, n) in props.item.details" :key="'d'+props.index+'-'+d.account_id">
                        <td  class="py-1 px-4 text-xs-left white">
                            <a :href="'../one-fin-002/?accid='+d.account_id+'&sdate='+(selected_endyear.sdate=='1971-01-01'?s_date:selected_endyear.sdate)+'&edate='+selected_endyear.edate" target="_blank">{{ d.account_name }}</a></td>
                        <td class="py-1 px-5 text-xs-right white">{{ oneMoney(d.journal_balance) }}</td>
                    </tr>
                    <tr class="cyan lighten-4">
                        <td  class="py-1 px-3 text-xs-left white font-weight-bold">Total {{ props.item.sub_title }}</td>
                        <td class="py-1 px-5 text-xs-right white font-weight-bold">{{ oneMoney(props.item.sub_total) }}</td>
                    </tr>

                    <!-- SPECIALS -->
                    <tr class="orange lighten-4" v-show="props.item.sub_type=='INCOME.HPP'">
                        <td  class="py-1 px-3 text-xs-left font-weight-bold">LABA KOTOR</td>
                        <td class="py-1 px-3 text-xs-right font-weight-bold">{{ oneMoney(grossProfit) }}</td>
                    </tr>
                    <tr class="orange lighten-4" v-show="props.item.sub_type=='INCOME.EXPENSE'">
                        <td  class="py-1 px-3 text-xs-left font-weight-bold">LABA OPERASIONAL</td>
                        <td class="py-1 px-3 text-xs-right font-weight-bold">{{ oneMoney(operationalProfit) }}</td>
                    </tr>
                    <tr class="orange lighten-4" v-show="props.item.sub_type=='INCOME.EXPENSE.OTHER'">
                        <td  class="py-1 px-3 text-xs-left font-weight-bold">TOTAL PENDAPATAN DAN BEBAN LAIN LAIN</td>
                        <td class="py-1 px-3 text-xs-right font-weight-bold">{{ oneMoney(others) }}</td>
                    </tr>
                    <tr class="orange lighten-4" v-show="props.item.sub_type=='INCOME.EXPENSE.OTHER'">
                        <td  class="py-1 px-3 text-xs-left font-weight-bold">LABA BERSIH SEBELUM PPH</td>
                        <td class="py-1 px-3 text-xs-right font-weight-bold">{{ oneMoney(nettProfit) }}</td>
                    </tr>
                    <!-- <tr class="cyan lighten-4">
                        <td :colspan="2" class="py-2 px-3 text-xs-left yellow lighten-3"><b>{{ props.item.group_name.toUpperCase() }}</b></td>
                        <td :colspan="1" class="py-2 px-3 text-xs-left"><b>{{ props.item.account_name.toUpperCase() }}</b></td>
                        <td class="py-2 px-2 text-xs-right"><b>DEBIT</b></td>
                        <td class="py-2 px-2 text-xs-right"><b>CREDIT</b></td>
                        <td class="py-2 px-2 text-xs-right"><b>SALDO</b></td>
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
                        <td :colspan="headers.length - 3" class="py-2 px-3 text-xs-right orange lighten-3 white--text"><b>JUMLAH</b></td>
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
                    text: "KETERANGAN",
                    align: "left",
                    sortable: false,
                    width: "70%",
                    class: "py-1 px-3 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "NOMINAL",
                    align: "right",
                    sortable: false,
                    width: "30%",
                    class: "py-1 px-3 zalfa-bg-purple lighten-3 white--text"
                }
            ],

            grossProfit: 0,
            operationalProfit: 0,
            nettProfit: 0,
            others: 0
        }
    },

    computed : {
        is_sales () { return false },

        title() {
            return "LAPORAN LABA RUGI"
        },

        endyears () {
            let x = JSON.parse(JSON.stringify(this.$store.state.report_param.months2))
            x.unshift({header:"PER PERIODE"})
            x.unshift({sdate:"1971-01-01",edate:"2023-12-12",label:"PER TANGGAL"})
            return x
        },

        selected_endyear : {
            get () { return this.$store.state.report_param.selected_month2 },
            set (v) { 
                this.$store.commit('report_param/set_object', ['selected_month2', v])
                this.search()
            }
        },

        s_date : {
            get () { return this.$store.state.fin003.s_date },
            set (v) { this.$store.commit('fin003/set_object', ['s_date', v]) }
        },

        e_date : {
            get () { return this.$store.state.fin003.e_date },
            set (v) { this.$store.commit('fin003/set_object', ['e_date', v]) }
        },

        accounts () {
            return this.$store.state.fin003.accounts
        },

        byDate () {
            if (!this.selected_endyear) return false
            if (this.selected_endyear.sdate == "1971-01-01") return true
            return false
        }
    },

    methods : {
        oneMoney (x) {
            return window.one_money(x)
        },

        search () {
            this.$store.dispatch("fin003/search").then((y) => {
                this.grossProfit = 0, this.operationalProfit = 0, this.nettProfit = 0, this.others = 0

                for (let a of y) {
                    if (['INCOME.SALES'].indexOf(a.sub_type)>-1) this.grossProfit += parseFloat(a.sub_total) 
                    if (['INCOME.HPP'].indexOf(a.sub_type)>-1) this.grossProfit -= parseFloat(a.sub_total)
                    if (a.sub_type == 'INCOME.EXPENSE') this.operationalProfit -= parseFloat(a.sub_total)
                    if (['INCOME.OTHER'].indexOf(a.sub_type)>-1) this.others += parseFloat(a.sub_total)
                    if (['INCOME.EXPENSE.OTHER'].indexOf(a.sub_type)>-1) this.others -= parseFloat(a.sub_total)
                }
                this.operationalProfit += parseFloat(this.grossProfit)
                this.nettProfit = this.operationalProfit + this.others
            })
        },

        printMe () {
            this.$store.dispatch("fin003/collect").then((x) => {
                window.open(this.$store.state.fin003.URL + 'report/one_fin_003_2/excel?' + x)
            })
        },

        select (x) {
            this.selected_ledger = x
        },

        change_s_date(x) {
            this.$store.commit('fin003/set_common', ['s_date', x.new_date])
            this.search()
        },

        change_e_date(x) {
            this.$store.commit('fin003/set_common', ['e_date', x.new_date])
            this.search()
        }
    },

    mounted () {
        this.$store.dispatch("report_param/search_months").then((x) => {
            this.$store.commit("report_param/set_object", ["selected_month2", x[0]])
            this.search()
        })
    }
}