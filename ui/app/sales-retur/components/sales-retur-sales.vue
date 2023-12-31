<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="1200px"
        transition="dialog-transition"
        content-class="dialog-retur"
        
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <h3>DAFTAR INVOICE :: {{ customerName }}</h3>
            </v-card-title>
            <v-card-text>
                <v-data-table 
                    :headers="headers"
                    :items="saless"
                    :loading="false"
                    hide-actions
                    class="elevation-1">
                    <template slot="items" slot-scope="props">
                        <td class="text-xs-left pa-1" :class="bg_proforma(props.item)" @click="select(props.item)" style="writing-mode:tb-rl;height:auto;transform:scale(-1)">
                            <span class="orange white--text px-2 py-3 d-block" v-if="props.item.sales_lunas=='N'&&props.item.sales_paid>0">L</span>
                            <span class="success pa-2 py-3 d-block white--text" v-if="props.item.sales_lunas=='Y'">S</span>
                            <span class="grey white--text pa-2 py-3 d-block" v-if="props.item.sales_lunas=='N'&&props.item.sales_paid==0">B</span>
                        </td>
                        <td class="text-xs-left pa-2" :class="bg_proforma(props.item)" @click="select(props.item)">{{ props.item.sales_date }}</td>
                        <td class="text-xs-left pa-2" :class="bg_proforma(props.item)" @click="select(props.item)">{{ props.item.sales_number }}</td>
                        
                        <!-- <td class="text-xs-left pa-2" @click="select(props.item)" v-show="!is_sales">
                            -
                        </td>  -->
                        
                        <td class="text-xs-left pa-2" :class="bg_proforma(props.item)" @click="select(props.item)">
                            {{ props.item.sales_note }}
                            <br />
                            <span class="cyan--text" v-show="props.item.sales_memo!=''"><i>memo : {{ props.item.sales_memo}}</i></span>
                        </td> 
                        <td class="text-xs-right pa-2" :class="bg_proforma(props.item)" @click="select(props.item)">
                            <span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.sales_grand_total)) }}</b>
                        </td>
                        <td class="text-xs-right pa-2" :class="bg_proforma(props.item)" @click="select(props.item)">
                            <span class="grey--text caption">Rp</span> <b class="red--text">{{ one_money(Math.round(props.item.sales_retur)) }}</b>
                        </td>
                        <!-- <td class="text-xs-left pa-2" :class="bg_proforma(props.item)" @click="select(props.item)" v-show="!is_sales">
                            <v-btn color="success" small class="ma-0" block v-show="props.item.sales_lunas=='Y'">Lunas</v-btn>
                            <v-btn color="orange" small class="ma-0" block dark v-show="props.item.sales_lunas=='N'&&props.item.sales_paid>0">Bayar Sebagian</v-btn>
                            <v-btn color="grey" small class="ma-0" block dark v-show="props.item.sales_lunas=='N'&&props.item.sales_paid==0">Belum Dibayar</v-btn>
                        </td> -->
                        <td class="text-xs-center pa-0" :class="bg_proforma(props.item)" @click="select(props.item)">
                            <div class="row">
                                <div class="col-12">
                                    <v-btn color="orange" class="btn-icon ma-0" small @click="select(props.item)" dark title="Cetak invoice">PILIH</v-btn>
                                </div>
                            </div>
                        </td>
                    </template>
                </v-data-table>

                <v-pagination
                    style="margin-top:10px;margin-bottom:10px"
                    v-model="curr_page"
                    :length="xtotal_page"
                    @input="change_page"
                ></v-pagination>
            </v-card-text>
        </v-card>
    </v-dialog>
</template>

<script>
module.exports = {
    components : {
        
    },

    data () {
        return {
            tempo: true,
            headers: [
                {
                    text: "",
                    align: "left",
                    sortable: false,
                    width: "3%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "TANGGAL",
                    align: "left",
                    sortable: false,
                    width: "8%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "NOMOR",
                    align: "left",
                    sortable: false,
                    width: "13%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "PESAN",
                    align: "left",
                    sortable: false,
                    width: "24%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "TOTAL TAGIHAN",
                    align: "right",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "TOTAL RETUR",
                    align: "right",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "ACTION",
                    align: "center",
                    sortable: false,
                    width: "7%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                }
            ]
         }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.retur.dialog_invoice },
            set (v) { this.$store.commit('retur/set_common', ['dialog_invoice', v]) }
        },

        saless () {
            return this.$store.state.sales.saless
        },

        customerName () {
            if (this.$store.state.retur.selected_customer)
                return this.$store.state.retur.selected_customer.customer_name
            return ''
        },

        curr_page : {
            get () { return this.$store.state.sales.current_page },
            set (v) { this.$store.commit('invoice/update_current_page', v) }
        },

        xtotal_page () {
            return this.$store.state.sales.total_sales_page
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        bg_proforma (x) {
            return ''
        },
//         {
//   "id": 568,
//   "item": {
//     "item_id": 5,
//     "item_code": "AO001",
//     "item_name": "KLD STD TAHUN NAGA",
//     "item_code_name": "AO001 - KLD STD TAHUN NAGA",
//     "stocks": [
//       {
//         "warehouse_id": "1",
//         "warehouse_name": "Default",
//         "stock_qty": "300",
//         "item_slow": "N"
//       }
//     ],
//     "stock": 300
//   },
//   "price": 12800,
//   "qty": 10,
//   "disc": 20,
//   "discrp": 0,
//   "disctype": "P",
//   "ppn": "N",
//   "ppn_amount": 0,
//   "total": 102400,
//   "subtotal": 102400,
//   "netto": 102400,
//   "detail_id": 568
// }
        select (x) {
            this.$store.commit('invoice/set_object', ['selected_invoice', x])

            let items = []
            let item = {}
            for (let d of x.details) {
                item.detail_id = d.id
                item.item_id = d.item.item_id
                item.item_name = d.item.item_name
                item.qty = d.qty
                item.price = d.price
                item.disc = d.disc
                item.delivery_id = 0
                item.delivery_number = ''
                item.delivery_date = ''
                item.retur_qty = 0
                item.note = ''
                items.push(JSON.parse(JSON.stringify(item)))
                // for (let i of d.items) {
                //     item = JSON.parse(JSON.stringify(i))
                //     item.delivery_id = d.delivery.delivery_id
                //     item.delivery_number = d.delivery_number
                //     item.delivery_date = d.delivery_date
                //     item.retur_qty = 0
                //     item.note = ''
                //     items.push(JSON.parse(JSON.stringify(item)))
                // }
            }

            this.$store.commit('retur/set_object', ['items', items])
            this.$store.commit('retur/set_object', ['dialog_invoice', false])
        },

        change_page(x) {
            this.curr_page = x
            this.$store.dispatch('invoice/search', {})
        }
    }
}