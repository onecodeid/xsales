<template>
    <v-card>
        <v-card-text primary-title class="pt-2 pb-0">
            <v-layout row wrap>
                <v-flex xs5 pr-4>
                    <h3 class="display-1 font-weight-light zalfa-text-title">REKAPITULASI TRANSAKSI HARIAN</h3>
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
                        solo
                    >
                        <template slot="item" slot-scope="data"><span class="body-2">{{ data.item.warehouse_name }}</span></template>
                        <template slot="selection" slot-scope="data"><span class="body-2">AW {{ data.item.warehouse_short_name }}</span></template>
                    </v-select>
                </v-flex>

                <v-flex xs3>
                    <v-layout row wrap>
                        <v-flex xs4 offset-xs2>
                            <common-datepicker
                                label="Tanggal Awal"
                                :date="sdate"
                                data="0"
                                @change="change_sdate"
                                classs="mt-0"
                                hints=" "
                                :details="true"
                                
                            ></common-datepicker>
                        </v-flex>
                        <v-flex xs4>
                            <common-datepicker
                                label="Tanggal Akhir"
                                :date="edate"
                                data="0"
                                @change="change_edate"
                                classs="mt-0 ml-1 mr-0"
                                hints=" "
                                :details="true"
                                
                            ></common-datepicker>
                        </v-flex>
                        <v-flex xs2>
                            <v-btn color="primary" class="ma-0 btn-icon" @click="search">
                                <v-icon>search</v-icon>
                            </v-btn>  
                        </v-flex>
                    </v-layout>
                </v-flex>

                <v-flex xs2>
                    <v-layout row wrap>
                        <v-flex xs6 pr-1>
                            <v-btn color="success" block @click="print_excel()" class="ma-0"><v-icon class="mr-2">view_headline</v-icon> Excel</v-btn>
                        </v-flex>
                        <v-flex xs6 pl-1>
                            <v-btn color="orange" dark block @click="print_pdf()" class="ma-0"><v-icon class="mr-2">print</v-icon> PDF</v-btn>
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
                            :class="['px-2 column', pagination.descending ? 'desc' : 'asc', header.value === pagination.sortBy ? 'active' : '', 'text-xs-' + header.align, header.sortable ? 'sortable' : '']"
                            @click="changeSort(header)"
                            :width="header.width"
                            >
                            <v-icon small>arrow_upward</v-icon>
                            {{ header.text }}
                            </th>
                        </tr>
                        </template>
                        <template v-slot:items="props">
                            <tr :active="props.selected" @click="props.selected = !props.selected">
                                <td class="text-xs-left px-2" :rowspan="props.item.rowspan">{{ props.item.invoice_date }}</td>
                                <td class="text-xs-left px-2" :rowspan="props.item.rowspan">{{ props.item.staff_name }}</td>
                                <td class="text-xs-left px-2" :rowspan="props.item.rowspan">{{ props.item.customer_name }}</td>
                                <td class="text-xs-left px-2 border-left">{{ props.item.items[0].item_name }}</td>
                                <td class="text-xs-right px-2">{{ one_money(props.item.items[0].item_qty) }}</td>
                                <td class="text-xs-center px-2 border-right">{{ props.item.items[0].unit_name }}</td>
                                <td class="text-xs-left px-2" :rowspan="props.item.rowspan">{{ props.item.payment_name }}</td>
                                <td class="text-xs-left px-2" :rowspan="props.item.rowspan">
                                    {{ props.item.delivery_name }}
                                    <div class="d-block primary--text">{{ props.item.expedition_name }}</div></td>
                            </tr>

                            <tr v-for="ii in props.item.rowspan-1" :key="props.item.invoice_id+'-'+ii">
                                <td class="text-xs-left px-2 border-left">{{ props.item.items[ii].item_name }}</td>
                                <td class="text-xs-right px-2">{{ one_money(props.item.items[ii].item_qty) }}</td>
                                <td class="text-xs-center px-2 border-right">{{ props.item.items[ii].unit_name }}</td>
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

.border-left {
    border-left: 1px solid rgba(0,0,0,.12);
}

.border-right {
    border-left: 1px solid rgba(0,0,0,.12);
}

td:not(:first-child) {
    border-left: 1px solid rgba(0,0,0,.12);
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
                    text: "TANGGAL",
                    align: "left",
                    sortable: true,
                    width: "8%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'invoice_date'
                },
                {
                    text: "NAMA SALES",
                    align: "left",
                    sortable: true,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'staff_name'
                },
                {
                    text: "NAMA CUSTOMER",
                    align: "left",
                    sortable: true,
                    width: "17%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'customer_name'
                },
                {
                    text: "ITEM",
                    align: "left",
                    sortable: false,
                    width: "27%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'item_name'
                },
                {
                    text: "QTY",
                    align: "right",
                    sortable: false,
                    width: "6%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'omzet_qty'
                },
                {
                    text: "UNIT",
                    align: "center",
                    sortable: false,
                    width: "8%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'omzet_qty'
                },
                {
                    text: "PEMBAYARAN",
                    align: "left",
                    sortable: false,
                    width: "12%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'omzet_freq'
                },
                {
                    text: "VENDOR / EKS",
                    align: "left",
                    sortable: false,
                    width: "12%",
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

        pagination : {
            get () { return this.$store.state.inventory.pagination },
            set (v) { this.$store.commit('inventory/set_pagination', v) }
        },

        dialog_report : {
            get () { return this.$store.state.dialog_print },
            set (v) { this.$store.commit('set_dialog_print', v) }
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
            if (column.sortable) {
                let x = JSON.parse(JSON.stringify(this.pagination))
                if (x.sortBy === column.value) {
                    x.descending = !x.descending
                } else {
                    x.sortBy = column.value
                    x.descending = true
                }

                this.pagination = x
            }
        },

        print_pdf () {
            let x = this.$store.state.inventory
            this.report_url = x.URL+"report/one_an_002?sdate="+this.sdate+"&edate="+this.edate
                                
            this.dialog_report = true
        },

        print_excel () {
            let x = this.$store.state.inventory
            this.report_url = x.URL+"report/one_an_001_excel?sdate="+this.sdate+"&edate="+this.edate
                                
            this.dialog_report = true
        }
    },

    mounted () {
        this.$store.dispatch('item_new/search_warehouse')
    }
}
</script>