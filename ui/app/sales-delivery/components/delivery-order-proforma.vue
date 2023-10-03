<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="1200px"
        transition="dialog-transition"
        content-class="dialog-new"
        
    >
        <v-card>
            <v-card-title primary-title class="amber lighten-1 white--text pt-3">
                <h3>
                    <span v-show="!edit">PENGIRIMAN DARI PROFORMA</span>
                </h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    
                    <v-flex xs3>
                        <v-layout row wrap>
                            <v-flex xs12 class="">
                                <v-select
                                    :items="proformas"
                                    v-model="selected_proforma"
                                    label="Pilih SO / Invoice"
                                    item-value="delivery_id"
                                    item-text="invoice_number"
                                    return-object
                                    clearable
                                    width="500"
                                >
                                    <template slot="item" slot-scope="data">
                                        <v-layout row wrap>
                                            <v-flex xs12>

                                                {{data.item.customer_name}}
                                                <span class="blue--text ml-3 caption">No SO :</span> {{data.item.sales_number}}
                                                <span class="blue--text ml-4 caption">No Invoice :</span> {{data.item.invoice_number}} 
                                            </v-flex>
                                            <!-- <v-flex xs4>
                                                <v-layout row wrap>
                                                    <v-flex xs12 class="caption">
                                                        abc
                                                    </v-flex>
                                                    <v-flex xs12 class="caption">
                                                        abc
                                                    </v-flex>
                                                </v-layout>
                                            </v-flex> -->
                                        </v-layout>
                                        
                                    </template>
                                    <template slot="selection" slot-scope="data">
                                        <v-layout row wrap>
                                            <v-flex xs12>
                                                {{data.item.customer_name}}
                                            </v-flex>
                                        </v-layout>
                                    </template>
                                </v-select>
                            </v-flex>
                            <v-flex xs4>
                                <v-text-field
                                    label="Tanggal SO"
                                    :value="selected_proforma?selected_proforma.sales_date:' '"
                                    readonly
                                ></v-text-field>
                            </v-flex>     
                            <v-flex xs8 pl-2>
                                <v-text-field
                                    label="Nomor SO"
                                    :value="selected_proforma?selected_proforma.sales_number:' '"
                                    readonly
                                ></v-text-field>
                            </v-flex> 
                            <v-flex xs4>
                                <v-text-field
                                    label="Tanggal Invoice"
                                    :value="selected_proforma?selected_proforma.invoice_date:' '"
                                    readonly
                                ></v-text-field>
                            </v-flex>     
                            <v-flex xs8 pl-2>
                                <v-text-field
                                    label="Nomor Invoice"
                                    :value="selected_proforma?selected_proforma.invoice_number:' '"
                                    readonly
                                ></v-text-field>
                                
                            </v-flex>
                            <v-flex xs12>
                                <v-text-field
                                    label="Nominal Invoice"
                                    :value="selected_proforma?one_money(selected_proforma.invoice_grandtotal):'0'"
                                    readonly
                                    reverse
                                    suffix="Rp"
                                    hide-details
                                ></v-text-field>
                                <span class="caption blue--text mb-3"><i>{{selected_proforma?selected_proforma.invoice_terbilang+' rupiah':'-'}}</i></span>
                            </v-flex> 
                            <v-flex xs12>
                                <v-select
                                    :items="warehouses"
                                    v-model="selected_warehouse"
                                    return-object
                                    item-text="warehouse_name"
                                    item-value="warehouse_id"
                                    label="Dikirim dari Gudang"
                                ></v-select>
                            </v-flex>
                            <v-flex xs12>
                                <v-select
                                    :items="deliverytypes"
                                    v-model="selected_deliverytype"
                                    return-object
                                    item-text="deliverytype_name"
                                    item-value="deliverytype_id"
                                    label="Jenis Pengiriman"
                                ></v-select>
                            </v-flex>
                            <v-flex xs12>
                                <v-select
                                    :items="staffs"
                                    v-model="selected_staff"
                                    return-object
                                    item-text="staff_name"
                                    item-value="staff_id"
                                    label="Petugas Pengirim"
                                    v-if="selected_deliverytype&&selected_deliverytype.deliverytype_code=='DLV.TYPE.SEND.STAFF'"
                                ></v-select>
                            </v-flex>
                            <v-flex xs12>
                                <v-text-field
                                    label="Nama Pengirim"
                                    v-model="delivery_send_note"
                                    v-show="selected_deliverytype&&selected_deliverytype.deliverytype_code=='DLV.TYPE.SEND.DRIVER'"
                                ></v-text-field>

                                <v-text-field
                                    label="Catatan"
                                    v-model="delivery_send_note"
                                    v-show="selected_deliverytype&&selected_deliverytype.deliverytype_code=='DLV.TYPE.PICKUP'"
                                ></v-text-field>
                            </v-flex>
                            <v-flex xs12>
                                <v-textarea
                                    label="Memo untuk bagian Keuangan"
                                    rows="2"
                                    v-model="delivery_memo"
                                    outline
                                ></v-textarea>
                            </v-flex>

                            <v-flex xs12>
                                <v-textarea
                                    label="Catatan"
                                    rows="2"
                                    v-model="delivery_note"
                                    outline

                                ></v-textarea>
                            </v-flex>                     
                        </v-layout>
                    </v-flex>

                    <v-flex xs9 pl-3>
                        <delivery-order-proforma-detail></delivery-order-proforma-detail>
                    </v-flex>
                </v-layout>
            </v-card-text>

            <v-card-actions>
                <v-btn color="primary" flat @click="dialog=!dialog" v-show="!view">Batal</v-btn>
                <v-spacer></v-spacer>
                <!-- <v-btn color="primary" @click="add_detail">Add</v-btn>                 -->
                <!-- <v-btn color="green" @click="save_confirm" :disabled="!btn_save_enabled" :dark="btn_save_enabled" v-show="!view">Simpan & Konfirmasi</v-btn> -->
                <v-btn color="primary" @click="save" v-show="!view">Simpan</v-btn>
                <!-- <v-btn color="primary" @click="dialog=!dialog">Tutup</v-btn> -->
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<style>
.input-dense .v-input__control {
    min-height: 36px !important;
}

.dialog-new .v-input__prepend-outer {
    margin: 0px !important;
}

.dialog-new .v-text-field.v-text-field--solo .v-input__control {
    min-height: 36px;
    padding: 0;
}
</style>
<script>
module.exports = {
    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        'delivery-order-proforma-detail' : httpVueLoader('./delivery-order-proforma-detail.vue')
    },

    data () {
        return {
            
        }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.delivery_new.dialog_proforma },
            set (v) { this.$store.commit('delivery_new/set_common', ['dialog_proforma', v]) }
        },

        edit () { return this.$store.state.delivery_new.edit },
        view () { return false },

        proformas () {
            return this.$store.state.delivery_new.proformas
        },

        selected_proforma : {
            get () { return this.$store.state.delivery_new.selected_proforma },
            set (v) { this.$store.commit('delivery_new/set_selected_proforma', v) }
        },

        warehouses () {
            return this.$store.state.delivery_new.warehouses
        },

        selected_warehouse : {
            get () { return this.$store.state.delivery_new.selected_warehouse },
            set (v) { this.$store.commit('delivery_new/set_selected_warehouse', v) }
        },

        delivery_note : {
            get () { return this.$store.state.delivery_new.delivery_note },
            set (v) { this.$store.commit('delivery_new/set_common', ['delivery_note', v]) }
        },

        delivery_memo : {
            get () { return this.$store.state.delivery_new.delivery_memo },
            set (v) { this.$store.commit('delivery_new/set_common', ['delivery_memo', v]) }
        },

        delivery_send_note : {
            get () { return this.$store.state.delivery_new.delivery_send_note },
            set (v) { this.$store.commit('delivery_new/set_common', ['delivery_send_note', v]) }
        },

        deliverytypes () {
            return this.$store.state.delivery_new.deliverytypes
        },

        selected_deliverytype : {
            get () { return this.$store.state.delivery_new.selected_deliverytype },
            set (v) { 
                this.$store.commit('delivery_new/set_selected_deliverytype', v)
            }
        },

        staffs () {
            return this.$store.state.delivery_new.staffs
        },

        selected_staff : {
            get () { return this.$store.state.delivery_new.selected_staff },
            set (v) { this.$store.commit('delivery_new/set_selected_staff', v) }
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        one_mask_money (x) {
            return window.one_mask_money(x)
        },

        save () {
            this.$store.commit('delivery_new/set_common', ['save_n_confirm', false])
            this.$store.dispatch('delivery_new/save')
        }

        // save_confirm () {
        //     this.$store.commit('delivery_new/set_common', ['save_n_confirm', true])
        //     this.$store.dispatch('delivery_new/save')
        // }
    },

    mounted () {
        this.$store.dispatch('delivery_new/search_proforma')
        // this.$store.dispatch('delivery_new/search_staff')
        // this.$store.dispatch('delivery_new/search_warehouse')
        // this.$store.dispatch('delivery_new/search_deliverytype')
        // this.$store.dispatch('delivery_new/search_item')
    },

    watch : {}
}
</script>