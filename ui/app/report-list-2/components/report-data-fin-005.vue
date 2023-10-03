<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0">
            <v-layout row wrap>
                <v-flex xs7>
                    <h3 class="display-1 font-weight-light zalfa-text-title">{{title}}</h3>
                </v-flex>
                
                <v-flex xs3 pr-2 class="pl-2 offset-xs2">
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
        <v-card-text class="pt-2 pb-1">
            <v-layout row wrap>
                <v-flex xs6 pr-1>
                    <v-data-table 
                        :headers="headers"
                        :items="leftSide"
                        :loading="false"
                        hide-actions
                        class="elevation-1">

                        <template slot="items" slot-scope="props">
                            <tr class="cyan lighten-4">
                                <td  class="py-1 px-3 text-xs-left">{{ props.item.label }}</td>
                                <td class="py-1 px-3 text-xs-right"></td>
                            </tr>
                            <tr v-for="(d, n) in props.item.details" :key="'d'+props.index+'-'+d.account_id">
                                <td  class="py-1 px-4 text-xs-left white">
                                    <a :href="'../one-fin-002/?accid='+d.account_id+'&sdate='+selected_endyear.sdate+'&edate='+selected_endyear.edate" target="_blank">{{ d.account_name }}</a></td>
                                <td class="py-1 px-3 text-xs-right white"><span class="font-weight-thin" v-show="n==0">Rp</span> {{ oneMoney(d.journal_balance) }}</td>
                            </tr>
                            <tr class="cyan lighten-4">
                                <td  class="py-1 px-3 text-xs-left white font-weight-bold">Total {{ props.item.label }}</td>
                                <td class="py-1 px-3 text-xs-right white font-weight-bold"><span class="font-weight-thin">Rp</span> {{ oneMoney(props.item.sub_total) }}</td>
                            </tr>
                        </template>
                        <template slot="footer">
                            <tr class="font-weight-bold orange white--text">
                                <!-- <td class="py-2 px-3">TOTAL AKTIVA</td>
                                <td class="py-2 px-3 pl-2 text-xs-right"><span class="font-weight-regular">Rp</span> {{ oneMoney(total[0]) }}</td> -->
                            </tr>
                        </template>
                    </v-data-table>
                </v-flex>
                <v-flex xs6 pl-1>
                    <v-data-table 
                        :headers="headers"
                        :items="rightSide"
                        :loading="false"
                        hide-actions
                        class="elevation-1">

                        <template slot="items" slot-scope="props">
                            <tr class="cyan lighten-4">
                                <td  class="py-1 px-3 text-xs-left">{{ props.item.label }}</td>
                                <td class="py-1 px-3 text-xs-right"></td>
                            </tr>
                            <tr v-for="(d, n) in props.item.details" :key="'d'+props.index+'-'+d.account_id">
                                <td  class="py-1 px-4 text-xs-left white">
                                    <a :href="'../one-fin-002/?accid='+d.account_id+'&sdate='+selected_endyear.sdate+'&edate='+selected_endyear.edate" target="_blank">{{ d.account_name }}</a></td>
                                <td class="py-1 px-3 text-xs-right white"><span class="font-weight-thin" v-show="n==0">Rp</span> {{ oneMoney(d.journal_balance) }}</td>
                            </tr>
                            <tr class="cyan lighten-4">
                                <td  class="py-1 px-3 text-xs-left white font-weight-bold">Total {{ props.item.label }}</td>
                                <td class="py-1 px-3 text-xs-right white font-weight-bold"><span class="font-weight-thin">Rp</span> {{ oneMoney(props.item.sub_total) }}</td>
                            </tr>
                        </template>
                        <template slot="footer">
                            <tr class="font-weight-bold orange white--text">
                                <!-- <td class="py-2 px-3">TOTAL KEWAJIBAN DAN EKUITAS</td>
                                <td class="py-2 px-3 pl-2 text-xs-right"><span class="font-weight-regular">Rp</span> {{ oneMoney(total[1]) }}</td> -->
                            </tr>
                        </template>
                    </v-data-table>
                </v-flex>
            </v-layout>
            

            <trans-journal-view></trans-journal-view>
        </v-card-text>
        <v-card-actions class="px-3 pt-0 pb-3">
            <v-layout row wrap>
                <v-flex xs6 pr-1>
                    <v-layout row wrap class="orange white--text font-weight-bold">
                        <v-flex xs6 class="py-2 px-3">
                            TOTAL AKTIVA
                        </v-flex>
                        <v-flex xs6 class="py-2 px-3 text-xs-right">
                            <span class="font-weight-regular">Rp</span> {{ oneMoney(total[0]) }}
                        </v-flex>
                    </v-layout>
                </v-flex>
                <v-flex xs6 pl-1>
                    <v-layout row wrap class="orange white--text font-weight-bold">
                        <v-flex xs6 class="py-2 px-3">
                            TOTAL KEWAJIBAN DAN EKUITAS
                        </v-flex>
                        <v-flex xs6 class="py-2 px-3 text-xs-right">
                            <span class="font-weight-regular">Rp</span> {{ oneMoney(total[1]) }}
                        </v-flex>
                    </v-layout>
                </v-flex>
            </v-layout>
        </v-card-actions>
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
            others: 0,

            leftSide: [{id:'BALANCE.ASSET.LIQUID',label:'ASET LANCAR',details:[],sub_total:0}, 
                        {id:'BALANCE.ASSET.FIXED',label:'ASET TETAP', details:[],sub_total:0}],
            rightSide: [{id:'BALANCE.LIABILITY.CURRENT',label:'KEWAJIBAN LANCAR',details:[],sub_total:0}, 
                        {id:'BALANCE.LIABILITY.LONGTERM',label:'KEWAJIBAN JANGKA PANJANG',details:[],sub_total:0},
                        {id:'BALANCE.EQUITY',label:'EKUITAS',details:[],sub_total:0}]
        }
    },

    computed : {
        is_sales () { return false },

        title() {
            return "LAPORAN NERACA"
        },

        endyears () {
            let x = JSON.parse(JSON.stringify(this.$store.state.report_param.months2))
            // x.unshift({header:"PER PERIODE"})
            // x.unshift({sdate:"1971-01-01",edate:"2023-12-12",label:"PER TANGGAL"})
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
            get () { return this.$store.state.fin005.s_date },
            set (v) { this.$store.commit('fin005/set_object', ['s_date', v]) }
        },

        e_date : {
            get () { return this.$store.state.fin005.e_date },
            set (v) { this.$store.commit('fin005/set_object', ['e_date', v]) }
        },

        accounts () {
            return this.$store.state.fin005.accounts
        },

        byDate () {
            if (!this.selected_endyear) return false
            if (this.selected_endyear.sdate == "1971-01-01") return true
            return false
        },

        total () {
            let total = [0, 0]
            for (let l of this.leftSide)
                total[0] += parseFloat(l.sub_total)
            for (let r of this.rightSide)
                total[1] += parseFloat(r.sub_total)

            return total
        }
    },

    methods : {
        oneMoney (x) {
            return window.one_money(x)
        },

        search () {
            this.$store.dispatch("fin005/search").then((y) => {
                let ls = JSON.parse(JSON.stringify(this.leftSide))
                let rs = JSON.parse(JSON.stringify(this.rightSide))

                for (let l of ls) {
                    l.details = []
                    l.sub_total = 0
                    for (let yy of y) {
                        if (yy.report_type == l.id) {
                            l.details.push(yy)
                            l.sub_total += parseFloat(yy.journal_balance)
                        }
                    }
                }

                for (let r of rs) {
                    r.details = []
                    r.sub_total = 0
                    for (let yy of y) {
                        if (yy.report_type == r.id) {
                            r.details.push(yy)
                            r.sub_total += parseFloat(yy.journal_balance)
                        }
                    }
                }

                this.leftSide = ls
                this.rightSide = rs
                // this.grossProfit = 0, this.operationalProfit = 0, this.nettProfit = 0, this.others = 0

                // for (let a of y) {
                //     if (['INCOME.SALES'].indexOf(a.sub_type)>-1) this.grossProfit += parseFloat(a.sub_total) 
                //     if (['INCOME.HPP'].indexOf(a.sub_type)>-1) this.grossProfit -= parseFloat(a.sub_total)
                //     if (a.sub_type == 'INCOME.EXPENSE') this.operationalProfit -= parseFloat(a.sub_total)
                //     if (['INCOME.OTHER'].indexOf(a.sub_type)>-1) this.others += parseFloat(a.sub_total)
                //     if (['INCOME.EXPENSE.OTHER'].indexOf(a.sub_type)>-1) this.others -= parseFloat(a.sub_total)
                // }
                // this.operationalProfit += parseFloat(this.grossProfit)
                // this.nettProfit = this.operationalProfit + this.others
            })
        },

        printMe () {
            this.$store.dispatch("fin005/collect").then((x) => {
                window.open(this.$store.state.fin005.URL + 'report/one_fin_005/excel?' + x)
            })
        },

        select (x) {
            this.selected_ledger = x
        },

        change_s_date(x) {
            this.$store.commit('fin005/set_common', ['s_date', x.new_date])
            this.search()
        },

        change_e_date(x) {
            this.$store.commit('fin005/set_common', ['e_date', x.new_date])
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