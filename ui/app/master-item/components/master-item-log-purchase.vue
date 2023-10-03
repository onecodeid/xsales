<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="1000px"
        transition="dialog-transition"
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <h3>HISTORI PEMBELIAN BARANG :: {{ item_name }}</h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>

                    <v-data-table 
                :headers="headers"
                :items="purchases"
                :loading="false"
                hide-actions
                class="elevation-1">
                <template slot="items" slot-scope="props">
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.purchase_date }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.purchase_number }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)" >
                        {{ props.item.vendor_name }}
                    </td> 
                    <td class="text-xs-right pa-2" @click="select(props.item)">
                        <div><span class="caption grey--text"></span> <span class="black--text">{{ one_money(props.item.item_qty) }}</span></div>
                    </td>
                    <td class="text-xs-right pa-2" @click="select(props.item)">
                        <div><span class="caption grey--text">Rp</span> <b class="blue--text">{{ one_money(props.item.item_price) }}</b></div>
                    </td>
                    <td class="text-xs-right pa-2" @click="select(props.item)">
                        <div v-show="props.item.item_discrp>0"><span class="caption grey--text">Rp</span> <span class="black--text">{{ one_money(props.item.item_disc) }}</span></div>
                        <div v-show="props.item.item_discrp==0"><span class="black--text">{{ one_money(props.item.item_disc) }}</span> <span class="caption grey--text">%</span></div>
                    </td>
                    <td class="text-xs-right pa-2" @click="select(props.item)">
                        <div><span class="caption grey--text">Rp</span> <span class="blue--text">{{ one_money(props.item.item_ppn) }}</span></div>
                    </td>
                    <td class="text-xs-right pa-2" @click="select(props.item)">
                        <div><span class="caption grey--text">Rp</span> <b class="blue--text">{{ one_money(props.item.item_subtotal) }}</b></div>
                    </td>

                    
                </template>
            </v-data-table>

                         
                    </v-flex>
                </v-layout>
            </v-card-text>

            <v-card-actions>
                <v-btn color="primary" @click="dialog=!dialog">Tutup</v-btn>                
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<style>
.input-dense .v-input__control {
    min-height: 36px !important;
}
</style>
<script>
module.exports = {
    components : {
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
                    text: "NOMOR",
                    align: "left",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "VENDOR",
                    align: "left",
                    sortable: false,
                    width: "25%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "QTY",
                    align: "right",
                    sortable: false,
                    width: "10%",
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
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "SUBTOTAL",
                    align: "right",
                    sortable: false,
                    width: "15%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                }
            ]
        }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.item_logpurchase.dialog },
            set (v) { this.$store.commit('item_logpurchase/set_common', ['dialog', v]) }
        },

        purchases () {
            return this.$store.state.item_logpurchase.purchases
        },

        item_name () {
            return this.$store.state.item_logpurchase.item_name
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        one_mask_money (x) {
            return window.one_mask_money(x)
        },

        select (a) {
            return
        }
    },

    mounted () {
        
    },

    watch : {
        
    }
}
</script>