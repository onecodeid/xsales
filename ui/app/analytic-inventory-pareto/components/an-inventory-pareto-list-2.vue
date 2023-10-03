<template>
    <v-card>
        <v-card-text primary-title class="pt-2 pb-0">
            <v-layout row wrap>
                <v-flex xs12>
                    <v-layout row wrap>
                        <v-flex xs5>
                            <h3 class="display-1 font-weight-light zalfa-text-title">PRODUK DIAGNOSTIK</h3> 
                            <v-layout row wrap class="legends caption text-xs-center pb-1">
                                <v-flex xs2 :class="status_class('A')" py-1 px-2>FAST</v-flex>
                                <v-flex xs2 :class="status_class('B')" py-1 px-2>MEDIUM</v-flex>
                                <v-flex xs2 :class="status_class('C')" py-1 px-2>SLOW</v-flex>
                                <v-flex xs2 :class="status_class('D')" py-1 px-2>HIGH SLOW</v-flex>
                            </v-layout>       
                        </v-flex>
                        <v-flex xs3>
                            <v-layout row wrap>
                                <v-flex xs6 pl-5 pr-1>
                                    <common-datepicker
                                        label="Tanggal Awal"
                                        :date="sdate"
                                        data="0"
                                        @change="change_sdate"
                                        hints=" "
                                        :details="true"
                                        :solo="false"
                                    ></common-datepicker>
                                </v-flex>
                                <v-flex xs6 pl-1 pr-5>
                                    <common-datepicker
                                        label="Tanggal Akhir"
                                        :date="edate"
                                        data="0"
                                        @change="change_edate"
                                        
                                        hints=" "
                                        :details="true"
                                        :solo="false"
                                    ></common-datepicker>
                                </v-flex>
                            </v-layout>
                        </v-flex>
                        <v-flex xs2>
                            <v-select
                                :items="warehouses"
                                v-model="selected_warehouse"
                                label="Gudang"
                                return-object
                                item-text="warehouse_name"
                                item-value="warehouse_id"
                                hint=" "
                                persistent-hint
                                clearable
                                placeholder="Semua Gudang"
                            ></v-select>
                        </v-flex>
                        <v-flex xs1 pl-3>
                            <v-btn color="success" block @click="print_excel()"><v-icon class="mr-2">view_headline</v-icon>Excel</v-btn>
                        </v-flex>
                        <v-flex xs1 pl-1>
                            <v-btn color="orange" dark block @click="print_pdf()"><v-icon class="mr-2">print</v-icon> PDF</v-btn>
                        </v-flex>
                    </v-layout>
                    
                </v-flex>

                <v-flex xs12>
                    <v-data-table
                        
                        :headers="headers"
                        :items="items"
                        :pagination.sync="pagination"
                        select-all
                        item-key="qty"
                        class="elevation-1"
                    >
                        <template v-slot:headers="props">
                        <tr>
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
                        <template v-slot:items="props">
                        <tr :active="props.selected" @click="props.selected = !props.selected">
                            <td class="text-xs-left px-2">{{ props.item.warehouse_name }}</td>
                            <td class="text-xs-left px-2">{{ props.item.item_code }}</td>
                            <td class="text-xs-left px-2">{{ props.item.item_name }}</td>
                            <td class="text-xs-right px-2">{{ one_money(props.item.omzet_qty) }}</td>
                            <td class="text-xs-right px-2">{{ one_money(props.item.omzet_freq) }}</td>
                            <td class="text-xs-right px-2">{{ one_money(props.item.omzet_av) }}</td>
                            <td class="text-xs-right px-2">{{ one_money(props.item.recapt_3month_av) }}</td>
                            <td class="text-xs-right px-2" :class="status_class(props.item.recapt_freq_status)">
                                <div class="zalfa-text-dashed">{{ one_money(props.item.item_min_stock) }}</div>
                                <v-layout row wrap class="mt-1 blue--text">
                                    <v-flex class="text-xs-left" pl-2><i>stock</i></v-flex>
                                    <v-flex><i>{{ one_money(props.item.stock_qty) }}</i></v-flex>
                                </v-layout>
                                <!-- <div class="cyan pa-1"><i> : </i></div> -->
                            </td>
                            <td class="text-xs-right px-2">{{ one_money(+props.item.recapt_3month_av + +props.item.item_min_stock) }}</td>
                        </tr>
                        </template>

                        <template slot="footer">
                            <tr class="font-weight-bold">
                                <td colspan="3" class="text-xs-center">TOTAL</td>
                                <td class="text-xs-right px-2">{{ one_money(totals.qty) }}</td>
                                <td class="text-xs-right px-2">{{ one_money(totals.freq) }}</td>
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
                    text: "GUDANG",
                    align: "left",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'warehouse_name'
                },
                {
                    text: "KODE ITEM",
                    align: "left",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'item_code'
                },
                {
                    text: "NAMA ITEM",
                    align: "left",
                    sortable: false,
                    width: "26%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'item_name'
                },
                {
                    text: "QTY SALES",
                    align: "right",
                    sortable: false,
                    width: "9%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'omzet_qty'
                },
                {
                    text: "FREK SALES",
                    align: "right",
                    sortable: false,
                    width: "9%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'omzet_freq'
                },
                {
                    text: "RATA HARIAN",
                    align: "right",
                    sortable: false,
                    width: "9%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'omzet_av'
                },
                {
                    text: "MONTHLY AV",
                    align: "right",
                    sortable: false,
                    width: "9%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'omzet_av'
                },
                {
                    text: "MIN STOCK",
                    align: "right",
                    sortable: false,
                    width: "9%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'omzet_av'
                },
                {
                    text: "STOCK AMAN",
                    align: "right",
                    sortable: false,
                    width: "9%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'omzet_av'
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
        items () {
            return this.$store.state.inventory.items
        },

        dialog_delete () {
            return this.$store.state.dialog_delete
        },

        item_id () {
            return this.$store.state.inventory.selected_inventory.I_AdjustID
        },

        edate : {
            get () { return this.$store.state.inventory.edate },
            set (v) { this.$store.commit('inventory/set_edate', v) }
        },

        sdate : {
            get () { return this.$store.state.inventory.sdate },
            set (v) { this.$store.commit('inventory/set_sdate', v) }
        },

        query : {
            get () { return this.$store.state.inventory.query },
            set (v) { this.$store.commit('inventory/set_common', ['query', v]) }
        },

        warehouses () {
            return this.$store.state.inventory.warehouses
        },

        selected_warehouse : {
            get () { return this.$store.state.inventory.selected_warehouse },
            set (v) { 
                this.$store.commit('inventory/set_selected_warehouse', v)
                this.search()
            }
        },

        pagination : {
            get () { return this.$store.state.inventory.pagination },
            set (v) { this.$store.commit('inventory/set_pagination', v) }
        },

        dialog_report : {
            get () { return this.$store.state.dialog_print },
            set (v) { this.$store.commit('set_dialog_print', v) }
        },

        totals () {
            let t = {qty:0,freq:0}
            for (let i of this.items) {
                t.qty += parseFloat(i.omzet_qty)
                t.freq += parseFloat(i.omzet_freq)
            }

            return t
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
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
            this.$store.dispatch('inventory/del', {id:x.data})
        },

        select (x) {
            this.$store.commit('inventory/set_selected_item', x)
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
            this.$store.dispatch('inventory/search', {})
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
            let x = this.$store.state.inventory
            this.report_url = x.URL+"report/one_an_001?sdate="+this.sdate+"&edate="+this.edate
                                +"&warehouse="+(this.selected_warehouse?this.selected_warehouse.warehouse_id:0)
                                +"&orderby="+[x.pagination.sortBy, x.pagination.descending?'desc':'asc'].join(" ")
            this.dialog_report = true
        },

        print_excel () {
            let x = this.$store.state.inventory
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
        }
    },

    mounted () {
        this.$store.dispatch('item_new/search_warehouse')
    }
}
</script>