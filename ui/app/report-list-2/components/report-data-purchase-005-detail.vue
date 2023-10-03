<template>
    <v-dialog
        v-model="dialog"
        persistent
        max-width="1280px"
        transition="dialog-transition"
        content-class="zalfa-dialog-print"
        scrollable
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pb-2 pt-2">
                <h3 class="ml-2">
                    DETAIL PEMBELIAN PRODUK
                </h3>
                <v-spacer></v-spacer>
                <v-btn color="red" dark @click="dialog=!dialog" class="ma-0" small style="min-width:0px">
                    <v-icon>clear</v-icon>
                </v-btn>
            </v-card-title>

            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs4 v-for="(d, n) in details" :key="n">
                        <v-list>
                            <v-list-tile
                            @click=""
                            class="mb-2 xs6"
                            >
                                <v-list-tile-content>
                                    <v-list-tile-sub-title v-html="n"></v-list-tile-sub-title>
                                    <v-list-tile-title v-html="d"></v-list-tile-title>       
                                </v-list-tile-content>
                            </v-list-tile>
                        </v-list>
                    </v-flex>
                </v-layout>

                <v-data-table 
                    :headers="headers"
                    :items="bills"
                    :loading="false"
                    hide-actions
                    class="elevation-1">
                    <template slot="items" slot-scope="props">
                        <tr class="">
                            <td class="py-3 px-2 text-xs-left">{{ props.item.bill_date }}<br>{{ props.item.bill_number }}</td>
                            <td class="py-2 px-2 text-xs-left">{{ props.item.vendor_name }}</td>
                            <td class="py-2 px-2 text-xs-right">{{ oneMoney(props.item.detail_qty) }} <span class="grey--text">{{ props.item.unit_name }}</span></td>
                            <td class="py-2 px-2 text-xs-right"><span class="grey--text">Rp</span> {{ oneMoney(props.item.detail_price) }}</td>
                            <td class="py-2 px-2 text-xs-right"><span class="grey--text">Rp</span> {{ oneMoney(props.item.detail_disctotal) }}</td>
                            <td class="py-2 px-2 text-xs-right"><span class="grey--text">Rp</span> {{ oneMoney(props.item.detail_ppnamount) }}</td>
                            <td class="py-2 px-2 text-xs-right"><span class="grey--text">Rp</span> <b>{{ oneMoney(props.item.detail_total) }}</b></td>
                            <td class="py-2 px-2 text-xs-right red lighten-4"><span class="grey--text">Rp</span> {{ oneMoney(props.item.retur_qty) }}</td>
                            <td class="py-2 px-2 text-xs-right red lighten-4"><span class="grey--text">Rp</span> <b>{{ oneMoney(props.item.retur_nominal) }}</b></td>
                            <!-- <td class="py-2 px-2 text-xs-left">
                                <a href="#" @click="detail($event, props.item)"><b>{{ props.item.item_name }}</b></a></td>
                            <td class="pa-2 text-xs-right"><b>{{ one_money(Math.round(props.item.detail_qty)) }}</b> <span class="grey--text caption">{{ props.item.unit_name }}</span></td>

                            <td class="pa-2 text-xs-right"><b>{{ one_money(Math.round(props.item.retur_qty)) }}</b> <span class="grey--text caption">{{ props.item.unit_name }}</span></td>

                            <td class="pa-2 text-xs-right"><span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.detail_total)) }}</b></td>
                            <td class="pa-2 text-xs-right"><span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.retur_nominal)) }}</b></td> -->
                            
                        </tr>
                    </template>
                </v-data-table>
                <!-- <v-layout row wrap>
                    <v-flex xs6 v-for="(d, n) in details" :key="n">
                        <v-list>
                            <v-list-tile
                            @click=""
                            class="mb-2 xs6"
                            >

                                <v-list-tile-content>
                                    <v-list-tile-sub-title v-html="n"></v-list-tile-sub-title>
                                    <v-list-tile-title v-html="d"></v-list-tile-title>
                                    
                                </v-list-tile-content>

                                </v-list-tile>
                        </v-list>
                    </v-flex>
                </v-layout> -->

                <!-- <v-layout row wrap>
                    <v-flex v-for="(itm, n) in prices" :class="[itm.width, itm.align?'text-xs-'+itm.align:null]" class="pa-2 cyan white--text">{{ itm.header }}</v-flex>
                </v-layout>
                <v-layout row wrap>
                    <v-flex v-for="(itm, n) in prices" :class="[itm.width, itm.align?'text-xs-'+itm.align:null]" class="pa-2">{{ itm.content }}</v-flex>
                </v-layout> -->
                <v-divider></v-divider>

            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="red" dark @click="dialog=!dialog">Tutup</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<style>
.zalfa-dialog-print {
    /* margin: 12px !important;
    max-height: 95% !important; */
}
</style>

<script>
let t = "?t=" + Math.round(Math.random() * 1e10)
module.exports = {
    props : ['data', 'report_url'],
    components: {
    },

    data () {
        return {
            headers: [
                {
                    text: "TANGGAL / NOMOR",
                    align: "left",
                    sortable: false,
                    width: "15%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "VENDOR",
                    align: "left",
                    sortable: false,
                    width: "21%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "QTY",
                    align: "right",
                    sortable: false,
                    width: "8%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "HARGA",
                    align: "right",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "POTONGAN",
                    align: "right",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "PPN",
                    align: "right",
                    sortable: false,
                    width: "8%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "TOTAL",
                    align: "right",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "RETUR QTY",
                    align: "right",
                    sortable: false,
                    width: "8%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "RETUR NOMINAL",
                    align: "right",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                }
            ],
        }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.purchase005.dialog_detail },
            set (v) { 
                this.$store.commit('purchase005/set_object', ['dialog_detail', v])
            }
        },

        selected_item () {
            if (this.$store.state.purchase005.selected_item)
                return this.$store.state.purchase005.selected_item
            return {}
        },

        bills () {
            return this.selected_item.bills ? this.selected_item.bills : []
        },

        details () {
            return {
                'NAMA ITEM' : this.selected_item.item_name,
                'KODE ITEM' : this.selected_item.item_code,
                'PERIODE' : this.$store.state.purchase005.s_date + ' s/d ' + this.$store.state.purchase005.e_date
                // 'SALES' : this.selected_item.sales_name,
                // 'VENDOR ASAL' : this.$store.state.purchase004.selected_bill.vendor_name,
                // 'GUDANG TUJUAN' : this.selected_item.warehouse_name
            }
        },

        // prices () { 
        //     return [
        //         { header:'QTY', 
        //             content:this.oneMoney(this.selected_item.detail_qty) + ' ' + this.selected_item.unit_name, width:'xs2', align:'right' },
        //         { header:'HARGA', 
        //             content:'Rp ' + this.oneMoney(this.selected_item.detail_price), width:'xs2', align:'right' },
        //         { header:'POTONGAN', 
        //             content:'Rp ' + this.oneMoney(this.discTotal), width:'xs2', align:'right' },
        //         { header:'PPN', 
        //             content:'Rp ' + this.oneMoney(this.selected_item.detail_ppnamount / this.selected_item.detail_qty), width:'xs2', align:'right' },
        //         { header:'TOTAL', 
        //             content:'Rp ' + this.oneMoney(this.selected_item.detail_total), width:'xs4', align:'right' }
        //     ] 
        // },

        // discTotal () {
        //     if (!this.selected_item) return 0
        //     let si = this.selected_item
        //     return parseFloat(si.detail_disc * si.detail_price / 100) + parseFloat(si.detail_discrp)
        // }
    },

    methods : {
        oneMoney (x) {
            return window.one_money(x)
        }
    },

    mounted () {
    }
}
</script>