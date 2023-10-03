<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0">
            <v-layout row wrap>
                <v-flex xs7>
                    <h3 class="display-1 font-weight-light zalfa-text-title">{{title}}</h3>
                </v-flex>
                <!-- <v-flex xs2 pr-2>
                    <v-select
                        :items="warehouses"
                        return-object
                        item-text="warehouse_short_name"
                        item-value="warehouse_id"
                        placeholder="SEMUA GUDANG"
                        item-disabled="parent"
                        v-model="selected_warehouse"
                        label="Gudang"
                        solo
                        hide-details
                    >
                    </v-select>
                </v-flex> -->
                <v-flex xs3>
                    <v-layout row wrap>
                        <v-flex xs6 pr-1>
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
                        <v-flex xs6 pl-1>
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
                    </v-layout>
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
            <v-layout row wrap>
                <v-flex xs12>
                    <v-data-table 
                        :headers="headers"
                        :items="items"
                        :loading="false"
                        hide-actions
                        class="elevation-1">
                        <template slot="items" slot-scope="props">
                            <tr class="">
                                <td class="py-2 px-2 text-xs-center">{{ props.index + 1 }}</td>
                                <!-- <td class="py-2 px-2 text-xs-left">{{ props.item.warehouse_name }}</td> -->
                                <td class="py-2 px-2 text-xs-left">{{ props.item.warehouse_name }}</td>
                                <td class="py-2 px-2 text-xs-right cyan lighten-5">{{ oneMoney(props.item.delivery_count) }}</td>
                                <td class="py-2 px-2 text-xs-right cyan lighten-5">{{ oneMoney(props.item.percentage_count) }} %</td>
                                <td class="py-2 px-2 text-xs-right green lighten-5">{{ oneMoney(props.item.delivery_item) }}</td>
                                <td class="py-2 px-2 text-xs-right green lighten-5">{{ oneMoney(props.item.percentage_sum) }} %</td>
                            </tr>
                        </template>

                        <template slot="footer">
                            <tr class="cyan lighten-4 orange--text">
                                <td class="py-2 px-3 text-xs-right orange lighten-3 white--text"></td>
                                <td class="py-2 px-2 text-xs-left orange lighten-3 white--text"><b>GRAND TOTAL</b></td>
                                <td class="py-2 px-2 text-xs-right yellow lighten-3"><b>{{ oneMoney(items[0]?items[0].total_count:0) }}</b></td>
                                <td class="py-2 px-2 text-xs-right yellow lighten-3"><b>100 %</b></td>
                                <td class="py-2 px-2 text-xs-right yellow lighten-3"><b>{{ oneMoney(items[0]?items[0].total_sum:0) }}</b></td>
                                <td class="py-2 px-2 text-xs-right yellow lighten-3"><b>100 %</b></td>
                            </tr>
                        </template>
                    </v-data-table>
                </v-flex>

                <v-flex xs12 mt-4 id="chart_container">
                    <v-layout row wrap v-for="(i, n) in items" :key="n" class="mb-4">
                        <v-flex xs2 class="d-flex justify-end align-center pr-2">
                            {{ i.warehouse_name }}
                        </v-flex>
                        <v-flex xs9>
                            <v-progress-linear
                                color="cyan"
                                height="20"
                                :value="i.percentage_count"
                                class="mt-0 mb-1 caption"
                            ><span class="ml-2">{{ oneMoney(i.delivery_count) }}</span></v-progress-linear>
                            <v-progress-linear
                                color="success"
                                height="20"
                                :value="i.percentage_sum"
                                class="my-0"
                            ><span class="ml-2 caption">{{ oneMoney(i.delivery_item) }}</span></v-progress-linear>
                        </v-flex>
                    </v-layout>
                    
                </v-flex>

                <!-- <v-flex xs12 v-show="activeTab==4">
                    <v-layout row wrap>
                        <v-flex xs12 lg6 v-for="(i, n) in items" :key="n" class="px-2 pb-3">
                            <v-textarea outline hide-details
                                :label="i.title" :class="i.color.replace(/lighten\-[0-9]/,'lighten-5')" :value="analysis[i.id]" @change="changeAnalytic(i.id, $event)"></v-textarea>
                        </v-flex>
                        <v-flex xs12 lg6>
                            <v-btn color="success" @click="save">Simpan</v-btn>
                        </v-flex>
                    </v-layout>
                </v-flex> -->

                <!-- <v-flex xs12 v-show="activeTab==5">
                    <v-layout row wrap>
                        <table class="v-datatable v-table theme--light">
                            <thead>
                                <tr class="text-xs-center zalfa-bg-purple lighten-3">
                                    <th width="10%" class="column py-3 px-2 white--text">NO</th>
                                    <th width="30%" class="column text-xs-left py-3 px-2 white--text">KLASIFIKASI STOK</th>
                                    <th width="30%" class="column text-xs-right py-3 px-2 white--text">JUMLAH PERSEDIAAN</th>
                                    <th width="30%" class="column text-xs-right py-3 px-2 white--text">PROSENTASE (%)</th>
                                </tr>
                            </thead>
                            
                            <tbody>
                                <tr v-for="(i, n) in items" :key="n">
                                    <td class="py-2 px-2 text-xs-center">{{ n+1 }}</td> 
                                    <td class="py-2 px-2 text-xs-left">{{ i.title }}</td> 
                                    <td class="py-2 px-2 text-xs-right">{{ oneMoney(grand_total[i.id][2]) }}</td> 
                                    <td class="py-2 px-2 text-xs-right">{{ oneMoney(grand_total[i.id][2] * 100 / grand_totalx, "0,000.00") }} %</td>
                                </tr>
                            </tbody>
                            
                            <tfoot>
                                <tr class="cyan lighten-4 orange--text">
                                    <td colspan="2" class="py-2 px-3 text-xs-center orange lighten-3 white--text"><b>TOTAL</b></td> 
                                    <td colspan="1" class="py-2 px-2 text-xs-right yellow lighten-3"><b>{{ oneMoney(grand_totalx) }}</b></td> 
                                    <td colspan="1" class="py-2 px-2 text-xs-right yellow lighten-3"><b>100 %</b></td>
                                </tr>
                            </tfoot>
                        </table>

                        <v-layout row wrap>
                            <v-flex xs12>
                                <v-textarea outline hide-details
                                label="Analisis" class="cyan lighten-5 mt-3" @change="changeAnalytic('PRC', $event)" :value="analysis['PRC']"></v-textarea>        
                            </v-flex>
                            <v-flex xs12 mt-2>
                                <v-btn color="success" @click="save" class="ma-0">Simpan</v-btn>        
                            </v-flex>
                        </v-layout>
                    </v-layout>
                </v-flex> -->
            </v-layout>

            <v-layout row wrap>
                <v-flex xs12>
                    <v-textarea outline hide-details
                    label="Analisis" class="cyan lighten-5 mt-3" @change="changeAnalytic('MAIN', $event)" :value="analysis['MAIN']"></v-textarea>        
                </v-flex>
                <v-flex xs12 mt-2>
                    <v-btn color="success" @click="save" class="ma-0">Simpan</v-btn>        
                </v-flex>
            </v-layout>
            

            <trans-journal-view></trans-journal-view>
            <v-snackbar
                v-model="snackbar"
                multi-line
                :timeout="6000"
                top
                vertical
                >
                {{ snackbar_text }}
                <v-btn
                    color="pink"
                    flat
                    @click="snackbar = false"
                >
                    Tutup
                </v-btn>
            </v-snackbar>
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
            // status_class : {A:'green lighten-3',B:'blue lighten-3',C:'orange lighten-3',D:'yellow lighten-3'},
            headers: [
                {
                    text: "NO",
                    align: "center",
                    sortable: false,
                    width: "5%",
                    class: "py-3 px-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "NAMA GUDANG",
                    align: "left",
                    sortable: false,
                    width: "25%",
                    class: "py-3 px-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "JUMLAH PENGIRIMAN",
                    align: "right",
                    sortable: false,
                    width: "20%",
                    class: "py-3 px-2 cyan lighten-3 white--text"
                },
                {
                    text: "PROSENTASE",
                    align: "right",
                    sortable: false,
                    width: "15%",
                    class: "py-3 px-2 cyan lighten-3 white--text"
                },
                {
                    text: "QTY BARANG KELUAR",
                    align: "right",
                    sortable: false,
                    width: "20%",
                    class: "py-3 px-2 green lighten-3 white--text"
                },
                {
                    text: "PROSENTASE",
                    align: "right",
                    sortable: false,
                    width: "15%",
                    class: "py-3 px-2 green lighten-3 white--text"
                }
            ],
            snackbar: false,
            snackbar_text: 'Analisis berhasil disimpan :)'
        }
    },

    computed : {
        is_sales () { return false },

        tabs () {
            let tabs = []
            for (let i of this.items)
                tabs.push({title:i.title,color:i.color})
            tabs.push({title:'ANALISIS',color:'cyan'})
            tabs.push({title:'PROSENTASE',color:'red'})

            return tabs
        },

        activeTab : {
            get () { return this.$store.state.iv012.current_tab },
            set (v) { 
                this.$store.commit('iv012/set_object', ['current_tab', v]) 
            }
        },

        s_date : {
            get () { return this.$store.state.iv012.s_date },
            set (v) { this.$store.commit('iv012/set_common', ['s_date', v]) }
        },

        e_date : {
            get () { return this.$store.state.iv012.e_date },
            set (v) { this.$store.commit('iv012/set_common', ['e_date', v]) }
        },

        title() {
            return "REKAPITULASI PENGIRIMAN & BARANG KELUAR"
        },

        query : {
            get () { return this.$store.state.iv012.search },
            set (v) { this.$store.commit('iv012/set_object', ['search', v]) }
        },

        items () {
            return this.$store.state.iv012.items
        },

        selected_item : {
            get () { return this.$store.state.iv012.selected_item },
            set (v) { this.$store.commit('iv012/set_object', ['selected_item', v]) }
        },

        warehouses () {
            return this.$store.state.report_param.warehouses
        },

        selected_warehouse : {
            get () { return this.$store.state.iv012.selected_warehouse },
            set (v) { 
                this.$store.commit('iv012/set_object', ['selected_warehouse', v]) 
                this.search().then((x) => {
                    this.$store.dispatch("iv012/searchAnalytic")
                })
            }
        },

        grand_total () {
            let items = this.items, gt = {}
            for (let l of items) {
                gt[l.id] = [0, 0, 0]
                for (let i of l.items) {
                    gt[l.id][0] += parseFloat(i.omzet_freq)
                    gt[l.id][1] += parseFloat(i.omzet_qty)
                    gt[l.id][2] += parseFloat(i.item_stock)
                }
            }

            return gt
        },

        grand_totalx () {
            let items = this.items, gt = 0
            for (let l of items) {
                for (let i of l.items) {
                    gt += parseFloat(i.item_stock)
                }
            }

            return gt
        },

        analysis () {
            return this.$store.state.iv012.analysis
        }
    },

    methods : {
        oneMoney (x, y) {
            return window.one_money(x, y)
        },

        change_s_date(x) {
            this.$store.commit('iv012/set_common', ['s_date', x.new_date])
            this.$store.dispatch('iv012/search')
        },

        change_e_date(x) {
            this.$store.commit('iv012/set_common', ['e_date', x.new_date])
            this.$store.dispatch('iv012/search')
        },

        search () {
            return this.$store.dispatch('iv012/search', {})
        },

        printMe () {
            this.$store.dispatch("iv012/collect").then((x) => {
                window.open(this.$store.state.iv012.URL + 'report/one_iv_009/excel?' + x)
            })
        },

        select (x) {
            this.selected_ledger = x
        },

        journal(x) {
            this.$store.commit('iv012/set_object', ['selected_journal', x])
            // this.select(x)
            // this.$store.commit('journal/set_selected_journal', x)
            this.$store.commit('journal_new/set_common', ['dialog_new', true])
            this.$store.commit('journal_new/set_common', ['view', true])
            this.$store.commit('journal_new/set_common', ['journal_note', x.journal_note])
            this.$store.commit('journal_new/set_common', ['journal_receipt', x.journal_receipt])
            this.$store.commit('journal_new/set_common', ['journal_date', x.journal_date])

            this.$store.dispatch('iv012/searchJournalDetail').then((x) => {
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

        save () {
            this.$store.dispatch('iv012/saveAnalytic').then((x) => {
                this.snackbar = true
            })
        },

        changeAnalytic (code, desc) {
            let ans = {}
            let an = JSON.parse(JSON.stringify(this.analysis))

            for (let i in an) ans[i] = an[i]
            ans[code] = desc

            this.$store.commit('iv012/set_object', ['analysis', ans])
        }
    },

    mounted () {
        this.$store.dispatch("report_param/search_warehouse").then((x)=> {
            this.selected_warehouse = x[0]
            this.$store.dispatch("iv012/search")
            this.$store.dispatch("iv012/searchAnalytic")
        })
        
        // this.$store.dispatch("iv012/search").then(() => {
        //     this.$store.dispatch("report_param/search_account")
        // })
    }
}