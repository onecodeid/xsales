<template>
    <v-card>
        <v-card-text primary-title class="pt-2 pb-0">
            <v-layout row wrap>
                <v-flex xs12>
                    <v-layout row wrap>
                        <v-flex xs7>
                            <h3 class="display-1 font-weight-light zalfa-text-title">MTD PROJO TAHUNAN</h3>       
                        </v-flex>
                        <v-flex xs3>
                            &nbsp;
                        </v-flex>
                        <v-flex xs2>
                            <v-select
                                :items="years"
                                v-model="selected_year"
                                label="Periode Tahun"
                                item-value="id"
                                item-text="text"
                                return-object
                            ></v-select>
                            <!-- <v-layout row wrap>
                                <v-flex xs6 pl-5 pr-0 offset-xs6>
                                    <common-datepicker
                                        label="Tanggal Achievement"
                                        :date="edate"
                                        data="0"
                                        @change="change_edate"
                                        hints=" "
                                        :details="true"
                                        :solo="false"
                                    ></common-datepicker>
                                </v-flex>
                            </v-layout> -->
                        </v-flex>
                    </v-layout>
                    
                </v-flex>

                <v-flex xs12>
                    <v-data-table
                        
                        :headers="headers"

                        :items="sales"
                        :pagination.sync="pagination"
                        select-all
                        item-key="qty"
                        class="elevation-1"
                    >
                        <template v-slot:headers="props">
                            <tr class="cyan lighten-4">
                                <th
                                v-for="header in props.headers"
                                :key="header.text"
                                :class="['px-2 column sortable', pagination.descending ? 'desc' : 'asc', header.value === pagination.sortBy ? 'active' : '', 'text-xs-' + header.align]"
                                @click="changeSort(header.value)"
                                :width="header.width"
                                >
                                <v-icon small>arrow_upward</v-icon>
                                {{ header.text }}
                                </th>
                            </tr>
                        </template>
                        <!-- <template v-slot:footer>
                            <tr class="cyan lighten-4">
                                <td v-for="(h, n) in headers" :key="n" :width="h.width"
                                :class="['px-2 column ', 'text-xs-' + h.align]">
                                    <b v-if="n > 0">{{ one_money(total(h.value)) }}</b>
                                    <b v-if="n === 0">TOTAL</b>
                                </td>
                            </tr>
                        </template> -->
                        <template v-slot:items="props">
                        <tr :active="props.selected" @click="props.selected = !props.selected">
                            <td class="text-xs-left px-2">{{ props.item.month }}</td>
                            <td class="text-xs-right px-2">{{ one_money(props.item.ach) }}</td>
                            <td class="text-xs-right px-2">{{ one_money(props.item.act_ach) }}</td>
                            <td class="text-xs-right px-2">{{ one_money(props.item.target) }}</td>
                            <td class="text-xs-right px-2">{{ one_money(props.item.projo) }}</td>
                            <td class="text-xs-right px-2">{{ one_money(props.item.daily_need) }}</td>
                            <td class="text-xs-right px-2">{{ one_money(props.item.act_ach_pct, '0,000.00') }}</td>
                            <td class="text-xs-right px-2">{{ one_money(props.item.target_pct, '0,000.00') }}</td>
                        </tr>
                        </template>
                    </v-data-table>
                    <v-divider></v-divider>
                </v-flex>
            </v-layout>

            
        </v-card-text>
        <!-- <v-card-text class="pt-2">
            
        </v-card-text> -->
        
        <common-dialog-delete :data="item_id" @confirm_del="confirm_del" v-if="dialog_delete"></common-dialog-delete>
        <common-dialog-print :report_url="report_url" v-if="dialog_report"></common-dialog-print>
    </v-card>
</template>

<style scoped>
.v-text-field.v-text-field--solo .v-input__control {
    min-height: 36px;
}
.v-text-field.v-text-field--solo .v-input__append-outer {
    margin-top: 0px;
    margin-left: 0px;
}

.v-select__selection, .legends > div {
    width: 100%;
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: pre;
}
</style>

<script>
module.exports = {
    components : {
        "common-dialog-delete" : httpVueLoader("../../common/components/common-dialog-delete.vue"),
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        "common-dialog-print" : httpVueLoader("../../common/components/common-dialog-print-size.vue")
    },

    data () {
        return {
            curr_page: 1,
            xtotal_page: 1,
            report_url: '',

            headers: [
                {
                    text: "BULAN",
                    align: "left",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'staff_name'
                },
                {
                    text: "ACHIEVEMENT",
                    align: "right",
                    sortable: false,
                    width: "13%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'target'
                },
                {
                    text: "ACT ACHIEVEMENT",
                    align: "right",
                    sortable: false,
                    width: "13%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'ach'
                },
                {
                    text: "MTD",
                    align: "right",
                    sortable: false,
                    width: "13%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'mtd_1st'
                },
                {
                    text: "PROJO",
                    align: "right",
                    sortable: false,
                    width: "13%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'projo'
                },
                {
                    text: "MONTHLY NEED",
                    align: "right",
                    sortable: false,
                    width: "13%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'daily_need'
                },
                {
                    text: "% ACT ACHIEVEMENT",
                    align: "right",
                    sortable: false,
                    width: "13%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'projo'
                },
                {
                    text: "% MTD",
                    align: "right",
                    sortable: false,
                    width: "13%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'daily_need'
                }
                // {
                //     text: "ACTION",
                //     align: "center",
                //     sortable: false,
                //     width: "8%",
                //     class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                // }
            ]
        }
    },

    computed : {
        sales () {
            return this.$store.state.sales.sales
        },

        dialog_delete () {
            return this.$store.state.dialog_delete
        },

        item_id () {
            return this.$store.state.sales.selected_sales.I_AdjustID
        },

        edate : {
            get () { return this.$store.state.sales.edate },
            set (v) { this.$store.commit('sales/set_edate', v) }
        },

        query : {
            get () { return this.$store.state.sales.query },
            set (v) { this.$store.commit('sales/set_common', ['query', v]) }
        },

        pagination : {
            get () { return this.$store.state.sales.pagination },
            set (v) { this.$store.commit('sales/set_pagination', v) }
        },

        dialog_report : {
            get () { return this.$store.state.dialog_print },
            set (v) { this.$store.commit('set_dialog_print', v) }
        },

        years () {
            return this.$store.state.sales.years
        },

        selected_year : {
            get () { return this.$store.state.sales.selected_year },
            set (v) { 
                this.$store.commit('sales/set_object', ['selected_year', v]) 
                this.search()
            }
        }
    },

    methods : {
        one_money (x, y) {
            return window.one_money(x, !!y ? y : '0,000')
        },

        add () {
            return false
        },

        edit (x) {
            return x
        },

        del (x) {
            this.select(x)
            this.$store.commit('set_dialog_delete', true)
        },

        confirm_del (x) {
            this.$store.dispatch('sales.del', {id:x.data})
        },

        select (x) {
            this.$store.commit('sales.set_selected_item', x)
        },

        reverse_date(x) {
            return ""
        },

        change_edate(x) {
            this.edate = x.new_date
            this.search()
        },

        change_sdate(x) {
            this.sdate = x.new_date
            this.search()
        },

        search () {
            this.$store.dispatch('sales/search', {})
        },
        
        changeSort (column) {
            let x = JSON.parse(JSON.stringify(this.pagination))
            if (x.sortBy === column) {
                x.descending = !x.descending
            } else {
                x.sortBy = column
                x.descending = true
            }

            this.pagination = x
        },

        print_pdf () {
            let x = this.$store.state.sales
            this.report_url = x.URL+"report/one_an_001?sdate="+this.sdate+"&edate="+this.edate
                                +"&warehouse="+(this.selected_warehouse?this.selected_warehouse.warehouse_id:0)
                                +"&orderby="+[x.pagination.sortBy, x.pagination.descending?'desc':'asc'].join(" ")
            this.dialog_report = true
        },

        print_excel () {
            let x = this.$store.state.sales
            this.report_url = x.URL+"report/one_an_001_excel?sdate="+this.sdate+"&edate="+this.edate
                                +"&warehouse="+(this.selected_warehouse?this.selected_warehouse.warehouse_id:0)
                                +"&orderby="+[x.pagination.sortBy, x.pagination.descending?'desc':'asc'].join(" ")
            this.dialog_report = true
        },

        status_class (s) {
            let cls = ''
            switch (s) {
                case 'A' :
                    cls = 'green lighten-3'
                    break
                case 'B' :
                    cls = 'blue lighten-3'
                    break
                case 'C' :
                    cls = 'orange lighten-3'
                    break
                default :
                    cls = 'yellow lighten-3'
            }

            return cls
        },

        total (x) {
            let total = 0
            for (let s of this.sales)
                total += parseFloat(s[x])

            return total
        }
    },

    mounted () {
        this.$store.dispatch("sales/searchYear").then((x) => {
            console.log(this.years)
            this.selected_year = this.years[0]
        })
    }
}
</script>