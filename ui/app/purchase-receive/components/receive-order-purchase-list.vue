<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="800px"
        transition="dialog-transition"
        content-class="dialog-purchase"
        
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <h3 class="headline">
                    PENERIMAAN BARANG
                </h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs1 style="writing-mode:tb-rl;height:auto;transform:scale(-1)" pt-2 pl-2>
                        <span class="orange white--text px-2 py-3 d-flex justify-center align-center subheading" style="width:100%">PILIH GUDANG</span>
                    </v-flex>
                    <v-flex xs11>
                        <!-- <h3>Pilih Gudang</h3> -->
                        <!-- <v-divider class="my-2"></v-divider> -->
                        <warehouse-list></warehouse-list>
                    </v-flex>
                </v-layout>

                <v-divider class="my-2"></v-divider>
                <v-layout row wrap>
                    <v-flex xs1 style="writing-mode:tb-rl;height:auto;transform:scale(-1)" pl-2>
                        <span class="cyan white--text px-2 py-3 d-flex justify-center align-center subheading" style="width:100%">PILIH PURCHASE ORDER</span>
                    </v-flex>
                    <v-flex xs11>

                        <v-layout row wrap>
                            <v-flex xs6>
                        <v-pagination
                            style="margin-top:10px;margin-bottom:10px"
                            v-model="curr_page"
                            :length="xtotal_page"
                            @input="change_page"
                        ></v-pagination>
                    </v-flex>
                    <v-flex xs6 pt-2>
                        <v-text-field
                            solo
                            hide-details
                            placeholder="Pencarian" v-model="query"
                            @change="search"
                        >
                            <template v-slot:append-outer>
                                <v-btn color="primary" class="ma-0 btn-icon" @click="search">
                                    <v-icon>search</v-icon>
                                </v-btn>
                            </template>
                        </v-text-field>
                    </v-flex>
                    <v-flex xs12>
                        <v-data-table 
                            :headers="headers"
                            :items="purchases"
                            :loading="false"
                            hide-actions
                            class="elevation-1">
                            <template slot="items" slot-scope="props">
                                <tr :class="{'cyan lighten-5':is_selected(props.item)}">
                                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.purchase_date }}</td>
                                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.purchase_number }}</td>
                                    <td class="text-xs-left pa-2" @click="select(props.item)">
                                        {{ props.item.vendor_name }}
                                    </td>
                                </tr>
                                <tr :class="{'cyan lighten-5':is_selected(props.item)}" v-show="is_selected(props.item)">
                                    <td colspan="3" class="text-xs-left pa-2" @click="select(props.item)">
                                        <v-layout row wrap>
                                            <v-flex xs6 v-for="(d, n) in props.item.details" :key="d.item.item_id+n" class="pa-1">
                                                <v-layout row wrap>
                                                    <v-flex xs9 class="cyan lighten-4 py-2 px-2">
                                                        {{d.item.item_name}}        
                                                    </v-flex>
                                                    <v-flex xs3 class="cyan lighten-1 white--text font-weight-bold py-2 px-2 text-xs-right">
                                                        {{one_money(d.unreceived)}} {{d.unit}}
                                                    </v-flex>
                                                </v-layout>
                                            </v-flex>
                                        </v-layout>
                                    </td>
                                </tr>
                            </template>
                        </v-data-table>   
                        <v-divider></v-divider>  
                    </v-flex>

                        </v-layout>
                    </v-flex>
                    
                </v-layout>

            
            </v-card-text>

            <v-card-actions>
                <v-btn color="primary" @click="dialog=!dialog" flat>Tutup</v-btn>
                <v-spacer></v-spacer>
                <v-btn color="primary" @click="choose" :disabled="!selected_purchase||selected_purchase=={}||!selected_warehouse" :dark="!!selected_purchase&&!!selected_warehouse">Pilih</v-btn>
            </v-card-actions>

        </v-card>

        
    </v-dialog>
</template>

<style>
.dialog-purchase .v-input__append-outer {
    margin-top: 0px !important;
    margin-left: 0px !important;
}

.dialog-purchase .v-text-field.v-text-field--solo .v-input__control {
    min-height: 36px;
    padding: 0;
}
</style>

<script>
module.exports = {
    components : {
        "warehouse-list" : httpVueLoader("./receive-order-warehouse-list-block.vue"),
    },

    data () {
        return {
            headers: [
                {
                    text: "TANGGAL PO",
                    align: "left",
                    sortable: false,
                    width: "15%",
                    class: "pa-2 cyan lighten-3 white--text"
                },
                {
                    text: "NOMOR PO",
                    align: "left",
                    sortable: false,
                    width: "25%",
                    class: "pa-2 cyan lighten-3 white--text"
                },
                {
                    text: "VENDOR",
                    align: "left",
                    sortable: false,
                    width: "60%",
                    class: "pa-2 cyan lighten-3 white--text"
                }
            ]
        }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.receive_new.dialog_purchase },
            set (v) { this.$store.commit('receive_new/set_common', ['dialog_purchase', v]) }
        },

        purchases () {
            return this.$store.state.purchase.purchases
        },

        selected_purchase : {
            get () { return this.$store.state.purchase.selected_purchase },
            set (v) { this.$store.commit('purchase/set_selected_purchase', v) }
        },

        curr_page : {
            get () { return this.$store.state.purchase.current_page },
            set (v) { this.$store.commit('purchase/update_current_page', v) }
        },

        xtotal_page () {
            return this.$store.state.purchase.total_purchase_page
        },

        query : {
            get () { return this.$store.state.purchase.search },
            set (v) { this.$store.commit('purchase/update_search', v) }
        },

        selected_warehouse () {
            return this.$store.state.receive_new.selected_warehouse
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        select (x) {
            this.$store.commit('purchase/set_selected_purchase', x)
        },

        is_selected (x) {
            if (!this.selected_purchase)
                return false

            if (this.selected_purchase.purchase_id == x.purchase_id)
                return true

            return false
        },

        choose () {
            this.$store.commit('receive_new/set_common', ['dialog_new', true])
            this.$store.commit('receive_new/set_common', ['dialog_purchase', false])

            for (let v of this.$store.state.receive_new.vendors)
                if (v.vendor_id == this.selected_purchase.vendor_id)
                    this.$store.commit('receive_new/set_selected_vendor', v)

            this.$store.dispatch('receive_new/search_item')
        },

        change_page(x) {
            this.curr_page = x
            this.$store.dispatch('purchase/search', {})
        },

        search () {
            return this.$store.dispatch('purchase/search', {done:'N'})
        }
    }
}
</script>