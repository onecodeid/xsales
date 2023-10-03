<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="1000px"
        transition="dialog-transition"
        content-class="dialog-new"
        
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <h3>
                    <span v-show="!edit">INPUT DATA PENGIRIMAN BARANG</span>
                    <span v-show="!!edit">UBAH DATA PENGIRIMAN BARANG</span>
                </h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs4>
                        <common-datepicker
                            label="Tanggal"
                            :date="delivery_date.split('-').reverse().join('-')"
                            data="0"
                            @change="change_delivery_date"
                            classs=""
                            hints=" "
                            :details="true"
                            :solo="false"
                            v-if="dialog"
                            :disabled="view"
                        ></common-datepicker>

                        <v-layout row wrap>
                            <v-flex xs12>
                                <v-autocomplete
                                    :items="customers"
                                    v-model="selected_customer"
                                    return-object
                                    item-text="customer_name"
                                    item-value="customer_id"
                                    label="Customer"
                                ></v-autocomplete>
                            </v-flex>
                            <v-flex xs6>
                                <!-- DELIVERY ADDRESS -->
                                <v-select
                                    :items="addresses"
                                    v-model="selected_address"
                                    return-object
                                    item-text="address_name"
                                    item-value="address_id"
                                    label="Alamat Pengiriman"
                                    :disabled="!selected_customer||!!view"
                                    hide-details
                                >
                                    <template slot="item" slot-scope="data">
                                        <v-layout row wrap>
                                            <v-flex xs12 class="blue--text">
                                                {{data.item.address_name}}        
                                            </v-flex>
                                            <v-flex xs12>
                                                <span class="d-block caption">{{data.item.address_desc}}</span>        
                                            </v-flex>
                                        </v-layout>
                                    </template>
                                    <template slot="selection" slot-scope="data">
                                        <v-layout row wrap py-2>
                                            <v-flex xs12 class="blue--text">
                                                {{data.item.address_name}}        
                                            </v-flex>
                                        </v-layout>
                                    </template>
                                </v-select>

                                <div v-if="!!selected_address" class="caption mt-2">
                                    {{selected_address.address_desc}}<br>
                                    Kab / Kota {{selected_address.city_name}} Prop {{selected_address.province_name}}<br>
                                    PIC : <b>{{selected_address.address_pic}}</b><br>
                                    Phone : {{selected_address.address_phone}}
                                </div>
                                <!-- END OF DELIVERY ADDRESS -->
                            </v-flex>
                        </v-layout>
                    </v-flex>
                    <v-flex xs8>
                        <v-card>
                            <v-card-title primary-title>
                                <v-layout row wrap>
                                    <v-flex xs3>
                                        <h3>TANGGAL</h3>
                                    </v-flex>
                                    <v-flex xs3>
                                        <h3>NO SO</h3>
                                    </v-flex>
                                    <v-flex xs6>
                                        <h3>DETAIL</h3>
                                    </v-flex>
                                </v-layout>
                            </v-card-title>
                            <v-card-text>
                                <v-layout row wrap>
                                    <v-flex xs6>
                                        <v-layout row wrap v-for="(sales, n) in saless" :key="n" @click="selected_sales=sales">
                                            <v-flex xs6>
                                                {{sales.sales_date}}
                                            </v-flex>
                                            <v-flex xs6>
                                                {{sales.sales_number}}
                                            </v-flex>
                                        </v-layout>
                                    </v-flex>
                                    <v-flex xs6>
                                        <v-layout row wrap v-for="(detail, nn) in details" :key="nn">
                                            
                                            <v-flex xs10>
                                                {{detail.item.item_name}}
                                            </v-flex>
                                            <v-flex xs2 class="text-xs-right">
                                                {{one_money(detail.qty)}}
                                            </v-flex>
                                        </v-layout>
                                        
                                    </v-flex>
                                </v-layout>
                            </v-card-text>
                        </v-card>
                    </v-flex>

                </v-layout>
            </v-card-text>

            <v-card-actions>
                <v-btn color="primary" flat @click="dialog=!dialog" v-show="!view">Batal</v-btn>
                <v-spacer></v-spacer>
                <v-btn color="green" @click="save_confirm" :disabled="!btn_save_enabled" :dark="btn_save_enabled" v-show="!view">Simpan & Konfirmasi</v-btn>
                <v-btn color="primary" @click="save" :disabled="!btn_save_enabled" :dark="btn_save_enabled" v-show="!view">Simpan</v-btn>
                <v-btn color="primary" @click="dialog=!dialog" v-show="view">Tutup</v-btn>
            </v-card-actions>
        </v-card>

        <v-snackbar
            v-model="snackbar"
            multi-line
            :timeout="3000"
            top
            vertical
            >
            {{ snackbar_text }}
            <v-btn
                color="pink"
                flat
                @click="snackbar = false"
            >
                Tutup
            </v-btn>
        </v-snackbar>

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
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue')
    },

    data () {
        return { }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.delivery_new.dialog_sales_new },
            set (v) { this.$store.commit('delivery_new/set_common', ['dialog_sales_new', v]) }
        },

        snackbar : {
            get () { return this.$store.state.delivery_new.snackbar },
            set (v) { this.$store.commit('delivery_new/set_common', ['snackbar', v]) }
        },

        snackbar_text : {
            get () { return this.$store.state.delivery_new.snackbar_text },
            set (v) { this.$store.commit('delivery_new/set_common', ['snackbar_text', v]) }
        },

        delivery_date () {
            return this.$store.state.delivery_new.delivery_date
        },

        btn_save_enabled () {

            return true
        },

        is_sales() {
            if (this.$store.state.filter.indexOf("J.03")>-1)
                return true
            return false
        },

        edit() {
            return this.$store.state.delivery_new.edit
        },

        view () {
            let r = this.$store.state.delivery.selected_delivery
            if (!this.edit) return false
            if (r.delivery_invoice!=0 || r.delivery_confirm=='Y') return true
            return false
            // return this.$store.state.delivery_new.view
        },

        customers () {
            return this.$store.state.delivery_new.customers
        },

        selected_customer : {
            get () { return this.$store.state.delivery_new.selected_customer },
            set (v) { 
                this.$store.commit('delivery_new/set_selected_customer', v)
                this.$store.dispatch('delivery_new/search_item')
                this.$store.dispatch('delivery_new/search_address')
                this.$store.dispatch('delivery_new/search_sales')
            }
        },

        addresses () {
            return this.$store.state.delivery_new.addresses
        },

        selected_address : {
            get () { return this.$store.state.delivery_new.selected_address },
            set (v) { 
                this.$store.commit('delivery_new/set_selected_address', v)
            }
        },

        saless () {
            return this.$store.state.delivery_new.saless
        },

        selected_sales : {
            get () { return this.$store.state.delivery_new.selected_sales },
            set (v) { 
                this.$store.commit('delivery_new/set_selected_sales', v)

                for (let add of this.addresses)
                    if (add.address_id == v.address_id)
                        this.selected_address = add
            }
        },

        details () {
            if (!this.selected_sales) return []
            return this.selected_sales.details
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
        },

        save_confirm () {
            this.$store.commit('delivery_new/set_common', ['save_n_confirm', true])
            this.$store.dispatch('delivery_new/save')
        },

        change_delivery_date(x) {
            this.$store.commit('delivery_new/set_common', ['delivery_date', x.new_date])
        }
    },

    mounted () {
        this.$store.dispatch('delivery_new/search_customer')
        this.$store.dispatch('delivery_new/search_staff')
        this.$store.dispatch('delivery_new/search_warehouse')
        this.$store.dispatch('delivery_new/search_deliverytype')
    },

    watch : {
    }
}
</script>