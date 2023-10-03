<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="900px"
        transition="dialog-transition"
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <h3>HISTORI TRANSAKSI</h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex v-for="(history, n) in histories" :key="n" class="px-1">
                        <v-card>
                            <v-card-title primary-title class="text-xs-center pa-2 justify-center orange white--text">
                                {{titles(n)}}
                            </v-card-title>

                            <v-card-text class="pa-0">
                                <v-data-table
                                    :items="history"
                                    hide-actions
                                    hide-headers
                                    class="elevation-1"
                                    loading="true"
                                >
                                    <template slot="items" slot-scope="props">
                                        <td class="pa-2" @click="search_detail(props.item)">
                                            <div><span class="blue--text">{{ props.item.log_date}}</span> / {{props.item.warehouse_name}}</div>
                                            <div v-show="props.item.customer_name!=''">{{props.item.customer_name}}</div>
                                            <div v-show="props.item.vendor_name!=''">{{props.item.vendor_name}}</div>
                                        </td>
                                        <td class="pa-2 text-xs-right">{{ one_money(props.item.log_qty) }} {{ props.item.unit_name }}</td>
                                    </template>
                                </v-data-table>
                            </v-card-text>
                        </v-card>
                    </v-flex>
                </v-layout>
            </v-card-text>

            <v-card-actions>
                <v-btn color="primary" @click="dialog=!dialog" >Tutup</v-btn>                
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
var t = Math.ceil(Math.random() * 1e10)
module.exports = {
    components : {
    },

    data () {
        return {
            headers: [
                {
                    text: "#",
                    align: "center",
                    sortable: false,
                    width: "15%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "CUSTOMER",
                    align: "left",
                    sortable: false,
                    width: "40%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "ALIAS",
                    align: "left",
                    sortable: false,
                    width: "45%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                }
            ]
        }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.item.dialog_history },
            set (v) { this.$store.commit('item/set_common', ['dialog_history', v]) }
        },

        histories () {
            return this.$store.state.item.histories
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        select (x) {
            this.$store.commit('item/set_object', ['selected_history', x])
        },

        search_detail (x) {
            this.select(x)
            let URL = this.$store.state.item.URL + '../ui/app/'
            if (x.log_code == "SALES.DELIVERY")
                URL = URL + 'sales-order'
            if (x.log_code == 'PURCHASE.RECEIVE')
                URL = URL + 'purchase-order'
            if (x.log_code == 'INV.ADJUSTMENT')
                URL = URL + 'inventory-item-adjustment'
            if (x.log_code.indexOf('INV.TRANSFER') > -1)
                URL = URL + 'inventory-item-transfer'

            window.open(URL + '/?no='+x.log_trans_number+'&sdate='+x.log_trans_date+'&edate='+x.log_trans_date, '_blank').focus()
            // this.$store.dispatch('item/search_history_detail')
            // this.$store.commit('item/set_common', ['dialog_history_detail', true])
        },

        titles (x) {
            switch (x) {
                case 'SALES.DELIVERY':
                    return 'PENJUALAN'
                case 'PURCHASE.RECEIVE':
                    return 'PEMBELIAN'
                case 'INV.ADJUSTMENT':
                    return 'PENYESUAIAN'
                case 'INV.TRANSFER':
                    return 'TRANSFER GUDANG'
                default:
                    return 'UNTITLED'
            }
        }
    },

    mounted () {

    },

    watch : {
        // item_assembly (n, o) {
        //     if (n=="N" && o=="Y") {
        //         if (this.assemblies.length > 0) {
        //             this.item_assembly = "Y"
        //         }
        //     }
        // }
    }
}
</script>