<template>
    <v-dialog
        v-model="dialog"
        persistent
        max-width="1000px"
        transition="dialog-transition"
        content-class="zalfa-dialog-print"
        scrollable
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pb-2 pt-2">
                <h3 class="ml-2">
                    DETAIL PERGERAKAN BARANG
                </h3>
                <v-spacer></v-spacer>
                <v-btn color="red" dark @click="dialog=!dialog" class="ma-0" small style="min-width:0px">
                    <v-icon>clear</v-icon>
                </v-btn>
            </v-card-title>

            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs6 pb-2>
                        <div class="caption grey--text">NAMA ITEM</div>
                        <div class="body-2">{{ selected_item.item_name }}</div>
                    </v-flex>
                    <v-flex xs3 pb-2>
                        <div class="caption grey--text">GUDANG</div>
                        <div class="body-2">{{ selected_item.warehouse_name }}</div>
                    </v-flex>
                    <v-flex xs3>
                        <div class="caption grey--text">PERIODE</div>
                        <div class="body-2">{{ s_date }} s/d {{ e_date }}</div>
                    </v-flex>

                    <v-flex xs12>
                        <v-data-table 
                            :headers="headers"
                            :items="logs"
                            :loading="false"
                            hide-actions
                            class="elevation-1">
                            <template slot="items" slot-scope="props">
                                

                                <tr>
                                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.stock_date }}</td>
                                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.type_text }}</td>
                                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.ref_number }}</td>
                                    <td class="text-xs-right pa-2" @click="select(props.item)">{{ props.item.stock_before_qty }} <span class="grey--text caption">{{ props.item.unit_name }}</span></td>
                                    
                                    <td class="text-xs-right pa-2" @click="select(props.item)" v-show="!is_sales && props.item.stock_qty > 0">
                                        <b>{{ one_money(Math.round(props.item.stock_qty)) }}</b> <span class="grey--text caption">{{ props.item.unit_name }}</span>
                                    </td>
                                    <td class="text-xs-right pa-2" @click="select(props.item)" v-show="!is_sales && props.item.stock_qty <= 0">-</td>

                                    <td class="text-xs-right pa-2" @click="select(props.item)" v-show="!is_sales && props.item.stock_qty < 0">
                                        <b>{{ one_money(Math.round(props.item.stock_qty)) }}</b> <span class="grey--text caption">{{ props.item.unit_name }}</span>
                                    </td>
                                    <td class="text-xs-right pa-2" @click="select(props.item)" v-show="!is_sales && props.item.stock_qty >= 0">-</td>

                                    <td class="text-xs-right pa-2" @click="select(props.item)" v-show="!is_sales">
                                        <b>{{ one_money(Math.round(props.item.stock_after_qty)) }} <span class="grey--text caption">{{ props.item.unit_name }}</span></b>
                                    </td>
                                </tr>
                            </template>
                        </v-data-table>
                    </v-flex>
                </v-layout>

                

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
                    text: "TANGGAL",
                    align: "left",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "TRANSAKSI",
                    align: "left",
                    sortable: false,
                    width: "27%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "REF",
                    align: "left",
                    sortable: false,
                    width: "15%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "SALDO AWAL",
                    align: "right",
                    sortable: false,
                    width: "12%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "QTY MASUK",
                    align: "right",
                    sortable: false,
                    width: "12%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "QTY KELUAR",
                    align: "right",
                    sortable: false,
                    width: "12%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "SALDO AKHIR",
                    align: "right",
                    sortable: false,
                    width: "12%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                }
            ],
        }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.iv005.dialog_detail },
            set (v) { 
                this.$store.commit('iv005/set_object', ['dialog_detail', v])
            }
        },

        selected_item () {
            if (this.$store.state.iv005.selected_item)
                return this.$store.state.iv005.selected_item
            return {}
        },

        logs () {
            return this.selected_item.logs
        },

        s_date () {
            return this.$store.state.iv005.s_date
        },

        e_date () {
            return this.$store.state.iv005.e_date
        }

        // details () {
        //     return {
        //         'NAMA ITEM' : this.selected_item.item_name,
        //         'KODE ITEM' : this.selected_item.item_code,
        //         'TANGGAL / NOMOR PENERIMAAN' : this.selected_item.receive_date + ' / ' + this.selected_item.receive_number,
        //         'SALES' : this.selected_item.sales_name,
        //         'VENDOR ASAL' : this.$store.state.purchase004.selected_bill.vendor_name,
        //         'GUDANG TUJUAN' : this.selected_item.warehouse_name
        //     }
        // },

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
        one_money (x) {
            return window.one_money(x)
        }
    },

    mounted () {
    }
}
</script>