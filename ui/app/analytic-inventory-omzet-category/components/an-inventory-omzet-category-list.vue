<template>
    <v-card>
        <v-card-text primary-title class="pt-2 pb-0">
            <v-layout row wrap>
                <v-flex xs7 pr-4>
                    <h3 class="display-1 font-weight-light zalfa-text-title">REKAPITULASI OMZET PER KATEGORI</h3>
                </v-flex>

                <v-flex xs2>
                    <!-- <v-layout row wrap>
                        <v-flex xs6 pr-1>
                            <v-btn color="success" block @click="print_excel()" class="ma-0"><v-icon class="mr-2">view_headline</v-icon>Cetak Excel</v-btn>
                        </v-flex>
                        <v-flex xs6 pl-1>
                            <v-btn color="orange" dark block @click="print_pdf()" class="ma-0"><v-icon class="mr-2">print</v-icon> Cetak PDF</v-btn>
                        </v-flex>
                    </v-layout> -->
                    <v-select
                        :items="categories"
                        v-model="selected_category"
                        item-value="category_id"
                        item-text="category_name"
                        return-object
                        label="Kategori Produk"
                        solo
                        clearable
                    ></v-select>
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
                            <tr :active="props.selected" @click="props.selected = !props.selected;select(props.item)" :class="{'cyan lighten-4':is_selected(props.item)}">
                                <td class="text-xs-left px-2" :rowspan="props.item.rowspan">{{ props.item.item_code }}</td>
                                <td class="text-xs-left px-2" :rowspan="props.item.rowspan">{{ props.item.item_name }}</td>

                                <td class="text-xs-left px-2 border-left">{{ props.item.category_name }}</td>
                                <td class="text-xs-right px-2">{{ props.item.item_qty }} {{ props.item.unit_name }}</td>
                                
                                <td class="text-xs-right px-2" :rowspan="props.item.rowspan" v-show="props.item.max_item_price!=props.item.min_item_price">
                                    Rp {{ one_money(props.item.min_item_price) }} - Rp {{ one_money(props.item.max_item_price) }}</td>
                                <td class="text-xs-right px-2" :rowspan="props.item.rowspan" v-show="props.item.max_item_price==props.item.min_item_price">
                                    Rp {{ one_money(props.item.max_item_price) }}</td>

                                <td class="text-xs-right px-2 border-right">Rp {{one_money(props.item.item_disctotal) }}</td>
                                <td class="text-xs-right px-2 border-right">Rp {{ one_money(props.item.item_ppnamount) }}</td>
                                <td class="text-xs-right px-2" :rowspan="props.item.rowspan">Rp {{ one_money(props.item.item_total) }}</td>
                                <td class="text-xs-center px-2" :rowspan="props.item.rowspan">
                                    <v-btn color="orange" dark block class="btn-icon ma-0" @click="print_detail(props.item)">
                                        <v-icon class="ma-0">print</v-icon></v-btn></td>
                                    
                                    <!-- <div class="d-block primary--text">{{ props.item.expedition_name }}</div></td> -->
                            </tr>

                            <!-- <tr v-for="ii in props.item.rowspan-1" :key="props.item.invoice_id+'-'+ii"> -->
                                <!-- <td class="text-xs-left px-2 border-left">{{ props.item.items[ii].item_name }}</td>
                                <td class="text-xs-right px-2">{{ one_money(props.item.items[ii].item_qty) }}</td>
                                <td class="text-xs-center px-2 border-right">{{ props.item.items[ii].unit_name }}</td> -->
                            <!-- </tr> -->
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
        <common-drawer-right :report_url="report_url" v-if="drawer_right"></common-drawer-right>
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
        "common-dialog-print" : httpVueLoader("../../common/components/common-dialog-print-size.vue"),
        "common-drawer-right" : httpVueLoader("../../common/components/common-drawer-right.vue")
    },

    data () {
        return {
            curr_page: 1,
            xtotal_page: 1,
            report_url: '',

            headers: [
                {
                    text: "KODE / SKU",
                    align: "left",
                    sortable: true,
                    width: "8%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'invoice_date'
                },
                {
                    text: "NAMA ITEM",
                    align: "left",
                    sortable: true,
                    width: "25%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'staff_name'
                },
                {
                    text: "KATEGORI",
                    align: "left",
                    sortable: false,
                    width: "12%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'item_name'
                },
                {
                    text: "TOTAL QTY",
                    align: "right",
                    sortable: false,
                    width: "5%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'omzet_qty'
                },
                {
                    text: "HARGA JUAL",
                    align: "right",
                    sortable: false,
                    width: "12%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'omzet_freq'
                },
                {
                    text: "POTONGAN",
                    align: "right",
                    sortable: false,
                    width: "8%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'omzet_qty'
                },
                {
                    text: "TOTAL PPN",
                    align: "right",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'omzet_qty'
                },
                {
                    text: "TOTAL",
                    align: "right",
                    sortable: false,
                    width: "12%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text",
                    value: 'omzet_av'
                },
                {
                    text: "ACTION",
                    align: "center",
                    sortable: false,
                    width: "8%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                }
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

        drawer_right : {
            get () { return this.$store.state.drawer_right },
            set (v) { this.$store.commit('set_drawer_right', v) }
        },

        selected_item () {
            return this.$store.state.inventory.selected_item
        },

        categories () {
            return this.$store.state.inventory.categories
        },

        selected_category : {
            get () { return this.$store.state.inventory.selected_category },
            set (v) { 
                this.$store.commit('inventory/set_selected_category', v) 
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
        },

        print_detail (a) {
            this.select(a)
            let x = this.$store.state.inventory
            this.report_url = x.URL+"report/one_sales_014?sdate="+this.sdate+"&edate="+this.edate+"&itemid="+this.selected_item.item_id

            this.$store.commit('set_drawer_right', true)
        },

        is_selected (x) {
            if (!this.selected_item)
                return false
            if (this.selected_item.item_id == x.item_id)
                return true
            return false
        }
    },

    mounted () {
        this.$store.dispatch('inventory/search_category')
    }
}
</script>