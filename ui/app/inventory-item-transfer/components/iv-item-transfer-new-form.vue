<template>
    <v-card>
        <v-card-title primary-title class="cyan white--text pt-3">
            <v-layout row wrap>
                <v-flex xs6>
                    <h3 v-show="!edit">TRANSFER GUDANG [ BARU ]</h3>
                    <h3 v-show="edit">TRANSFER GUDANG NO <span class="orange--text">{{ selected_transfer.I_TransferNumber }}</span></h3>
                </v-flex>
                <v-flex xs6 class="text-xs-right">
                    <v-btn color="success" small @click="add_item" v-show="!edit"
                        :disabled="!selected_warehouse||!selected_to_warehouse">
                        <v-icon class="mr-2 zalfa-font-12">add</v-icon> Tambah Item</v-btn>
                </v-flex>
            </v-layout>
            
        </v-card-title>
        <v-card-text>
            <v-layout row wrap>
                <v-flex xs12>
                    <v-layout row wrap>
                        <v-flex xs3>
                            <v-select
                                :items="warehouses"
                                v-model="selected_warehouse"
                                label="Gudang Asal"
                                return-object
                                item-text="warehouse_name"
                                item-value="warehouse_id"
                                :disabled="items.length>0"
                            ></v-select>
                        </v-flex>
                        <v-flex xs6>
                            &nbsp;
                        </v-flex>
                        <v-flex xs3 pl-5>
                            <common-datepicker
                                label="Tanggal Transfer"
                                :date="transfer_date"
                                data="0"
                                @change="transfer_date = $event.new_date"
                                hints="TANGGAL TRANSFER"
                                :details="true"
                                :prepend_icon="true"
                                :solo="true"
                                v-if="dialog && !edit"
                            ></common-datepicker>
                            <v-text-field
                                readonly
                                :value="transfer_date"
                                solo
                                hint="TANGGAL TRANSFER"
                                v-show="!!edit"
                                persistent-hint
                            >
                                <template slot="prepend-inner">
                                    <v-icon class="primary--text">calendar_today</v-icon>
                                </template>
                            </v-text-field>
                        </v-flex>
                        
                        <v-flex xs3>
                            <v-select
                                :items="warehouses"
                                v-model="selected_to_warehouse"
                                label="Gudang Tujuan"
                                return-object
                                item-text="warehouse_name"
                                item-value="warehouse_id"
                                :disabled="items.length>0"
                            ></v-select>
                        </v-flex>
                        <v-flex xs9 pl-4>
                            <v-text-field
                                label="Catatan"
                                v-model="transfer_note"
                                
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
                                <th role="columnheader" scope="col" width="10%" aria-label="KODE: Not sorted." aria-sort="none" class="column text-xs-left pa-2 zalfa-bg-purple lighten-3 white--text" rowspan="2">KODE</th>
                                <th role="columnheader" scope="col" width="35%" aria-label="KODE: Not sorted." aria-sort="none" class="column text-xs-left pa-2 zalfa-bg-purple lighten-3 white--text" rowspan="2">NAMA PRODUK / ITEM</th>
                                <th role="columnheader" scope="col" width="20%" aria-label="KODE: Not sorted." aria-sort="none" class="column text-xs-center pa-2 zalfa-bg-purple lighten-3 white--text" colspan="2">
                                    {{ selected_warehouse?selected_warehouse.warehouse_name.toUpperCase():'GUDANG ASAL' }}
                                </th>
                                <th role="columnheader" scope="col" width="10%" aria-label="KODE: Not sorted." aria-sort="none" class="column text-xs-center pa-2 zalfa-bg-purple lighten-3 white--text" rowspan="2">QTY<br>TRANSFER</th>
                                <th role="columnheader" scope="col" width="20%" aria-label="KODE: Not sorted." aria-sort="none" class="column text-xs-center pa-2 cyan lighten-1 white--text" colspan="2">
                                    {{ selected_to_warehouse?selected_to_warehouse.warehouse_name.toUpperCase():'GUDANG TUJUAN' }}
                                </th>
                                <!-- <th role="columnheader" scope="col" width="20%" aria-label="KODE: Not sorted." aria-sort="none" class="column text-xs-center pa-2 zalfa-bg-purple lighten-3 white--text">KODE</th> -->
                                <th role="columnheader" scope="col" width="5%" aria-label="KODE: Not sorted." aria-sort="none" class="column text-xs-center pa-2 zalfa-bg-purple lighten-3 white--text" rowspan="2" v-show="!edit">ACTION</th>
                                <th role="columnheader" scope="col" width="5%" aria-label="KODE: Not sorted." aria-sort="none" class="column text-xs-center pa-2 zalfa-bg-purple lighten-3 white--text" rowspan="2" v-show="edit">UNIT</th>
                            </tr>
                            <tr>
                                <th role="columnheader" scope="col" aria-label="KODE: Not sorted." aria-sort="none" class="column text-xs-right pa-2 zalfa-bg-purple lighten-3 white--text">SEBELUM</th>
                                <th role="columnheader" scope="col" aria-label="KODE: Not sorted." aria-sort="none" class="column text-xs-right pa-2 zalfa-bg-purple lighten-3 white--text">SETELAH</th>
                                <th role="columnheader" scope="col" aria-label="KODE: Not sorted." aria-sort="none" class="column text-xs-right pa-2 cyan lighten-1 white--text">SEBELUM</th>
                                <th role="columnheader" scope="col" aria-label="KODE: Not sorted." aria-sort="none" class="column text-xs-right pa-2 cyan lighten-1 white--text">SETELAH</th>
                            </tr>
                        </template>
                        <template slot="items" slot-scope="props">
                            <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.item_code }}</td>
                            <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.item_name }}</td>
                            <td class="text-xs-right pa-2" @click="select(props.item)">{{ one_money(props.item.item_bf_qty) }}</td>
                            <td class="text-xs-right pa-2" @click="select(props.item)">{{ one_money(props.item.item_af_qty) }}</td>
                            <td class="text-xs-left pa-2" @click="select(props.item)" v-if="!edit">
                                <v-text-field
                                    solo
                                    hide-details
                                    :value="props.item.item_qty"
                                    type="number"
                                    reverse
                                    @input="change_qty(props.index, $event)"
                                    class="zalfa-input-super-dense"
                                ></v-text-field>
                            </td>
                            <td class="text-xs-right pa-2 font-weight-bold blue--text" @click="select(props.item)" v-if="edit">{{ one_money(props.item.item_qty) }}</td>
                            
                            <td class="text-xs-right pa-2 cyan lighten-5" @click="select(props.item)">{{ one_money(props.item.item_to_bf_qty) }}</td>
                            <td class="text-xs-right pa-2 cyan lighten-5" @click="select(props.item)">{{ one_money(props.item.item_to_af_qty) }}</td>

                            <td class="text-xs-left pa-0" @click="select(props.item)" v-show="!edit">
                                <v-btn color="red" dark class="btn-icon ma-0" small @click="del(props.index)" :disabled="edit"><v-icon>delete</v-icon></v-btn>
                            </td>
                            <td class="text-xs-center pa-0" @click="select(props.item)" v-show="edit">
                                {{ props.item.unit_name }}
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
let t = Math.random()
module.exports = {
    components: {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue?t='+t)
    },

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
            get () { return this.$store.state.transfer_new.dialog_new },
            set (v) { this.$store.commit('transfer_new/set_common', ['dialog_new', v]) }
        },

        transfer_number : {
            get () { return this.$store.state.transfer_new.transfer_number },
            set (v) { this.$store.commit('transfer_new/set_common', ['transfer_number', v]) }
        },

        transfer_date : {
            get () { return this.$store.state.transfer_new.transfer_date },
            set (v) { this.$store.commit('transfer_new/set_common', ['transfer_date', v]) }
        },

        transfer_note : {
            get () { return this.$store.state.transfer_new.transfer_note },
            set (v) { this.$store.commit('transfer_new/set_common', ['transfer_note', v]) }
        },

        items () { 
            return this.$store.state.transfer_new.items
        },

        edit () {
            return this.$store.state.transfer_new.edit
        },

        btn_save_enable () {
            if (this.transfer_note != '' && this.items.length > 0)
                return true

            return false
        },

        warehouses () {
            return this.$store.state.transfer_new.warehouses
        },

        selected_warehouse : {
            get () { return this.$store.state.transfer_new.selected_warehouse },
            set (v) { 
                this.$store.commit('transfer_new/set_selected_warehouse', v)
            }
        },

        selected_to_warehouse : {
            get () { return this.$store.state.transfer_new.selected_to_warehouse },
            set (v) { 
                this.$store.commit('transfer_new/set_selected_to_warehouse', v)
            }
        },

        selected_transfer () {
            return this.$store.state.transfer.selected_transfer
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        select (x) {
            this.$store.commit('transfer_new/set_selected_item', x)
        },

        save () {
            this.$store.dispatch('transfer_new/save')
        },

        add_item () {
            this.$store.dispatch('transfer_new/search_item', {})
            this.$store.commit('transfer_new/set_common', ['dialog_item', true])
        },

        change_qty(i, v) {
            let x = this.items
            x[i][`item_qty`] = v
            x[i][`item_af_qty`] = parseFloat(x[i][`item_bf_qty`]) - parseFloat(v)
            x[i][`item_to_af_qty`] = parseFloat(x[i][`item_to_bf_qty`]) + parseFloat(v)

            this.$store.commit('transfer_new/set_items', x)
        },

        del (idx) {
            this.items.splice(idx, 1)
        }
    },

    watch : {
    },

    mounted () {
        
    }
}
</script>