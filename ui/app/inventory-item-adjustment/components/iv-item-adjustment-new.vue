<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="800px"
        transition="dialog-transition"
        content-class="dialog-new"
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <v-layout row wrap>
                    <v-flex xs6>
                        <h3 v-show="!edit">PENYESUAIAN STOK [ BARU ]</h3>
                        <h3 v-show="edit">PENYESUAIAN STOK NO <span class="orange--text">{{ selected_adjustment.I_AdjustNumber }}</span></h3>
                    </v-flex>
                    <v-flex xs6 class="text-xs-right">
                        <v-btn color="success" small @click="add_item" v-show="!edit"
                            :disabled="!selected_warehouse">
                            <v-icon class="mr-2 zalfa-font-12">add</v-icon> Tambah Item</v-btn>
                    </v-flex>
                </v-layout>
                
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <!-- <v-flex xs6 pr-4>
                        <v-layout row wrap>
                            <v-flex xs12>
                                <v-text-field
                                    label="Nomor"
                                    hide-details
                                    v-model="adjustment_number"
                                    readonly
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs12 mt-3>
                                <v-text-field
                                    label="Tanggal"
                                    hide-details
                                    v-model="adjustment_date"
                                ></v-text-field>
                            </v-flex>
                        </v-layout>
                    </v-flex> -->

                    <v-flex xs12>
                        <v-layout row wrap>
                            <v-flex xs4>
                                <v-select
                                    :items="warehouses"
                                    v-model="selected_warehouse"
                                    label="Gudang"
                                    return-object
                                    item-text="warehouse_name"
                                    item-value="warehouse_id"
                                    :disabled="true"
                                ></v-select>
                            </v-flex>
                            <v-flex xs8 pl-4>
                                <v-text-field
                                    label="Catatan"
                                    v-model="adjustment_note"
                                    
                                    :disabled="edit"
                                    placeholder="Tuliskan catatan anda disini">
                                </v-text-field>
                            </v-flex>
                        </v-layout>
                    </v-flex>
                </v-layout>

                <v-layout row wrap>
                    <v-flex xs12>
                        
                        <v-data-table 
                            :headers="headers"
                            :items="items"
                            :loading="false"
                            hide-actions
                            class="elevation-1">
                            <template slot="headers" slot-scope="props">
                                <tr>
                                    <th role="columnheader" scope="col" width="20%" aria-label="KODE: Not sorted." aria-sort="none" class="column text-xs-left pa-2 zalfa-bg-purple lighten-3 white--text" rowspan="2">KODE</th>
                                    <th role="columnheader" scope="col" width="40%" aria-label="KODE: Not sorted." aria-sort="none" class="column text-xs-left pa-2 zalfa-bg-purple lighten-3 white--text" rowspan="2">NAMA PRODUK / ITEM</th>
                                    <th role="columnheader" scope="col" width="45%" aria-label="KODE: Not sorted." aria-sort="none" class="column text-xs-center pa-2 zalfa-bg-purple lighten-3 white--text" colspan="3">QTY</th>
                                    <!-- <th role="columnheader" scope="col" width="20%" aria-label="KODE: Not sorted." aria-sort="none" class="column text-xs-center pa-2 zalfa-bg-purple lighten-3 white--text">KODE</th> -->
                                    <th role="columnheader" scope="col" width="15%" aria-label="KODE: Not sorted." aria-sort="none" class="column text-xs-center pa-2 zalfa-bg-purple lighten-3 white--text" rowspan="2">ACTION</th>
                                </tr>
                                <tr>
                                    <th role="columnheader" scope="col" aria-label="KODE: Not sorted." aria-sort="none" class="column text-xs-right pa-2 zalfa-bg-purple lighten-3 white--text">SEBELUM</th>
                                    <th role="columnheader" scope="col" aria-label="KODE: Not sorted." aria-sort="none" class="column text-xs-right pa-2 zalfa-bg-purple lighten-3 white--text">ADJUSTMENT</th>
                                    <th role="columnheader" scope="col" aria-label="KODE: Not sorted." aria-sort="none" class="column text-xs-right pa-2 zalfa-bg-purple lighten-3 white--text">SETELAH</th>
                                </tr>
                            </template>
                            <template slot="items" slot-scope="props">
                                <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.item_code }}</td>
                                <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.item_name }}</td>
                                <td class="text-xs-right pa-2" @click="select(props.item)">{{ one_money(props.item.item_bf_qty) }}</td>
                                <td class="text-xs-left pa-2" @click="select(props.item)" v-if="!edit">
                                    <v-text-field
                                        solo
                                        hide-details
                                        :value="props.item.item_qty"
                                        reverse
                                        @input="change_qty(props.index, $event)"
                                        class="zalfa-input-super-dense"
                                    ></v-text-field>
                                </td>
                                <td class="text-xs-right pa-2" @click="select(props.item)" v-if="edit">{{ one_money(props.item.item_qty) }}</td>
                                <td class="text-xs-right pa-2" @click="select(props.item)">{{ one_money(props.item.item_af_qty) }}</td>
                                <td class="text-xs-left pa-0" @click="select(props.item)">
                                    <v-btn color="red" dark class="btn-icon ma-0" small @click="del(props.index)" :disabled="edit"><v-icon>delete</v-icon></v-btn>
                                </td>
                                <!-- <td class="text-xs-center pa-2" v-bind:class="{'amber lighten-4':isSelected(props.item)}" @click="selectMe(props.item)">{{ props.item.M_DoctorHP}}</td>
                                <td class="text-xs-left pa-2" v-bind:class="{'amber lighten-4':isSelected(props.item)}" @click="selectMe(props.item)">{{ props.item.status}}</td> -->
                            </template>
                        </v-data-table>
                        <v-divider></v-divider>
                        

                    </v-flex>
                </v-layout>
            </v-card-text>

            <v-card-actions>
                <v-btn color="primary" flat @click="dialog=!dialog">Tutup</v-btn>
                <v-spacer></v-spacer>
                <v-btn color="primary" @click="save" v-show="!edit" :disabled="!btn_save_enable">Simpan</v-btn>                
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<style>
.zalfa-input-super-dense .v-input__control {
    min-height: 36px !important;
}

.zalfa-font-12 {
    font-size: 1.2em;
}

.dialog-new table.v-table thead tr {
    height: auto !important;
}
</style>

<script>
module.exports = {
    data () {
        return {
            headers: [
                {
                    text: "KODE",
                    align: "center",
                    sortable: false,
                    width: "20%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "NAMA ITEM",
                    align: "center",
                    sortable: false,
                    width: "40%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "QTY SBLM",
                    align: "left",
                    sortable: false,
                    width: "15%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "QTY ADJUSTMENT",
                    align: "left",
                    sortable: false,
                    width: "15%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "QTY SSDH",
                    align: "left",
                    sortable: false,
                    width: "15%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "ACTION",
                    align: "center",
                    sortable: false,
                    width: "15%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                }
            ]
        }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.adjustment_new.dialog_new },
            set (v) { this.$store.commit('adjustment_new/set_common', ['dialog_new', v]) }
        },

        adjustment_number : {
            get () { return this.$store.state.adjustment_new.adjustment_number },
            set (v) { this.$store.commit('adjustment_new/set_common', ['adjustment_number', v]) }
        },

        adjustment_date : {
            get () { return this.$store.state.adjustment_new.adjustment_date },
            set (v) { this.$store.commit('adjustment_new/set_common', ['adjustment_date', v]) }
        },

        adjustment_note : {
            get () { return this.$store.state.adjustment_new.adjustment_note },
            set (v) { this.$store.commit('adjustment_new/set_common', ['adjustment_note', v]) }
        },

        items () { 
            return this.$store.state.adjustment_new.items
        },

        edit () {
            return this.$store.state.adjustment_new.edit
        },

        btn_save_enable () {
            if (this.adjustment_note != '' && this.items.length > 0)
                return true

            return false
        },

        warehouses () {
            return this.$store.state.adjustment_new.warehouses
        },

        selected_warehouse : {
            get () { return this.$store.state.adjustment_new.selected_warehouse },
            set (v) { 
                this.$store.commit('adjustment_new/set_selected_warehouse', v)
            }
        },

        selected_adjustment () {
            return this.$store.state.adjustment.selected_adjustment
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        select (x) {
            this.$store.commit('adjustment_new/set_selected_item', x)
        },

        save () {
            this.$store.dispatch('adjustment_new/save')
        },

        add_item () {
            this.$store.dispatch('adjustment_new/search_item', {})
            this.$store.commit('adjustment_new/set_common', ['dialog_item', true])
        },

        change_qty(i, v) {
            let x = this.items
            x[i][`item_qty`] = v
            x[i][`item_af_qty`] = parseFloat(x[i][`item_bf_qty`]) + parseFloat(v)

            this.$store.commit('adjustment_new/set_items', x)
        },

        del (idx) {
            this.items.splice(idx, 1)
        }
    },

    watch : {
    },

    mounted () {
        // this.$store.dispatch('adjustment_new/search_warehouse')
    }
}
</script>