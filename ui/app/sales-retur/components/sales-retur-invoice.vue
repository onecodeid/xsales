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
                    :items="invoices"
                    :loading="false"
                    hide-actions
                    class="elevation-1">
                    <template slot="items" slot-scope="props">
                        <td class="text-xs-left pa-1" :class="bg_proforma(props.item)" @click="select(props.item)" style="writing-mode:tb-rl;height:auto;transform:scale(-1)">
                            <span class="orange white--text px-2 py-3 d-block" v-if="props.item.invoice_lunas=='N'&&props.item.invoice_paid>0">L</span>
                            <span class="success pa-2 py-3 d-block white--text" v-if="props.item.invoice_lunas=='Y'">S</span>
                            <span class="grey white--text pa-2 py-3 d-block" v-if="props.item.invoice_lunas=='N'&&props.item.invoice_paid==0">B</span>
                        </td>
                        <td class="text-xs-left pa-2" :class="bg_proforma(props.item)" @click="select(props.item)">{{ props.item.invoice_date }}</td>
                        <td class="text-xs-left pa-2" :class="bg_proforma(props.item)" @click="select(props.item)">{{ props.item.invoice_number }}</td>
                        
                        <!-- <td class="text-xs-left pa-2" @click="select(props.item)" v-show="!is_sales">
                            -
                        </td>  -->
                        
                        <td class="text-xs-left pa-2" :class="bg_proforma(props.item)" @click="select(props.item)">
                            {{ props.item.invoice_note }}
                            <br />
                            <span class="cyan--text" v-show="props.item.invoice_memo!=''"><i>memo : {{ props.item.invoice_memo}}</i></span>
                        </td> 
                        <td class="text-xs-right pa-2" :class="bg_proforma(props.item)" @click="select(props.item)">
                            <span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.invoice_grand_total)) }}</b>
                        </td>
                        <td class="text-xs-right pa-2" :class="bg_proforma(props.item)" @click="select(props.item)">
                            <span class="grey--text caption">Rp</span> <b class="red--text">{{ one_money(Math.round(props.item.invoice_retur)) }}</b>
                        </td>
                        <!-- <td class="text-xs-left pa-2" :class="bg_proforma(props.item)" @click="select(props.item)" v-show="!is_sales">
                            <v-btn color="success" small class="ma-0" block v-show="props.item.invoice_lunas=='Y'">Lunas</v-btn>
                            <v-btn color="orange" small class="ma-0" block dark v-show="props.item.invoice_lunas=='N'&&props.item.invoice_paid>0">Bayar Sebagian</v-btn>
                            <v-btn color="grey" small class="ma-0" block dark v-show="props.item.invoice_lunas=='N'&&props.item.invoice_paid==0">Belum Dibayar</v-btn>
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

        invoices () {
            return this.$store.state.retur.invoices
        },

        customerName () {
            if (this.$store.state.retur.selected_customer)
                return this.$store.state.retur.selected_customer.customer_name
            return ''
        },

        curr_page : {
            get () { return this.$store.state.invoice.current_page },
            set (v) { this.$store.commit('invoice/update_current_page', v) }
        },

        xtotal_page () {
            return this.$store.state.invoice.total_invoice_page
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        bg_proforma (x) {
            return ''
        },

        select (x) {
            alert('a')
            this.$store.commit('retur/set_object', ['selectedInvoice', x])
alert('b')
            let details = [], item = {}
            for (let d of x.details) {
                console.log(d)
                for (let i of d.items) {
                    item = JSON.parse(JSON.stringify(i))
                    item.delivery_id = d.delivery.delivery_id
                    item.delivery_number = d.delivery_number
                    item.delivery_date = d.delivery_date
                    item.retur_qty = 0
                    item.note = ''
                    details.push(JSON.parse(JSON.stringify(item)))
                }
            }
            console.log(details)

            this.$store.commit('invoice/set_object', ['selected_invoice', x])

            let items = []
            // let item = {}
            for (let d of x.details) {
                for (let i of d.items) {
                    item = JSON.parse(JSON.stringify(i))
                    item.delivery_id = d.delivery.delivery_id
                    item.delivery_number = d.delivery_number
                    item.delivery_date = d.delivery_date
                    item.retur_qty = 0
                    item.note = ''
                    items.push(JSON.parse(JSON.stringify(item)))
                }
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