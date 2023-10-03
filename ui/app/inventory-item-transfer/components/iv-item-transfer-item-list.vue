<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="600px"
        transition="dialog-transition"
    >
        <v-card>
            <v-card-title primary-title>
                <v-layout row wrap>
                    <v-flex xs6>
                        <h3>DAFTAR ITEM</h3>
                    </v-flex>
                </v-layout>
                
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                        <v-data-table 
                            :headers="headers"
                            :items="items"
                            :loading="false"
                            hide-actions
                            class="elevation-1">
                            <template slot="items" slot-scope="props">
                                <td class="text-xs-center pa-2" @click="select(props.item)" v-bind:class="is_selected(props.item)">{{ props.item.M_ItemCode }}</td>
                                <td class="text-xs-left pa-2" @click="select(props.item)" v-bind:class="is_selected(props.item)">{{ props.item.M_ItemName }}, {{ props.item.address_kelurahan }}</td>
                                <td class="text-xs-right pa-2" @click="select(props.item)" v-bind:class="is_selected(props.item)">{{ props.item.M_ItemMinStock }}</td>
                                <td class="text-xs-right pa-2" @click="select(props.item)" v-bind:class="is_selected(props.item)">{{ props.item.I_StockQty }}</td>
                                <td class="text-xs-center pa-0" @click="select(props.item)" v-bind:class="is_selected(props.item)">
                                    <v-btn color="primary" class="btn-icon ma-0" small @click="pick(props.item)" :disabled="is_selected(props.item) != ''"><v-icon>get_app</v-icon></v-btn>
                                </td>
                            </template>
                        </v-data-table>
                        <v-divider></v-divider>
                        

                    </v-flex>
                </v-layout>
            </v-card-text>

            <v-card-actions>
                <v-btn color="primary" @click="dialog=!dialog">Tutup</v-btn>                
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
module.exports = {
    data () {
        return {
            headers: [
                {
                    text: "KODE",
                    align: "center",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "NAMA ITEM",
                    align: "center",
                    sortable: false,
                    width: "50%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "MIN STOCK",
                    align: "right",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "STOCK SEKARANG",
                    align: "right",
                    sortable: false,
                    width: "20%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "ACTION",
                    align: "center",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                }
            ]
        }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.transfer_new.dialog_item },
            set (v) { this.$store.commit('transfer_new/set_common', ['dialog_item', v]) }
        },

        items () { 
            return this.$store.state.transfer_new.items_av
        }
    },

    methods : {
        select (x) {
            this.$store.commit('transfer_new/set_selected_item_av', x)
        },

        pick (x) {
            this.select(x)
            let items = this.$store.state.transfer_new.items

            // CHECK IF EXISTS
            for (let item of items)
                if (item.item_id == x.M_ItemID)
                    return

            items.push({
                item_id:x.M_ItemID,
                item_code:x.M_ItemCode,
                item_name:x.M_ItemName,
                item_qty:0,
                item_bf_qty:x.I_StockQty,
                item_af_qty:x.I_StockQty,
                item_to_bf_qty:x.to_qty,
                item_to_af_qty:x.to_qty,
                detail_id:0
            })

            this.$store.commit('transfer_new/set_items', items)
            // this.dialog = false
        },

        is_selected (item) {
            let itm = this.$store.state.transfer_new.items
            for (let i of itm) {
                if (i.item_id == item.M_ItemID)
                    return 'green lighten-4'
            }
            return ''
        }
    },

    watch : {
    },

    mounted () {
        this.$store.dispatch('transfer_new/search_item', {})
    }
}
</script>