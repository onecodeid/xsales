<template>
    <v-card>
        <v-card-title primary-title class="cyan white--text pt-3">
            <h3>
                <span v-show="!edit">INPUT DATA PENGIRIMAN BARANG</span>
                <span v-show="!!edit">UBAH DATA PENGIRIMAN BARANG</span>
            </h3>
        </v-card-title>
        <v-card-text>
            <v-layout row wrap>
                <v-flex xs12>
                    <v-layout row wrap>

                        <v-flex xs2>
                            <v-layout row wrap>
                                <v-flex xs6 pr-2>
                                    <common-datepicker
                                        label="Tanggal"
                                        :date="delivery_date.split('-').reverse().join('-')"
                                        data="0"
                                        @change="change_delivery_date"
                                        classs=""
                                        hints=" "
                                        :details="true"
                                        :solo="false"
                                        v-if="dialog||!!sa"
                                        :disabled="view"
                                    ></common-datepicker>
                                </v-flex>
                                <v-flex xs6>
                                    <v-text-field
                                        label="Nomor Faktur"
                                        v-model="delivery_number"
                                        :readonly="delivery_post||is_sales||view"
                                        
                                    ></v-text-field>
                                </v-flex>
                            </v-layout>
                            
                            <v-select
                                :items="deliverytypes"
                                v-model="selected_deliverytype"
                                return-object
                                item-text="deliverytype_name"
                                item-value="deliverytype_id"
                                label="Jenis Pengiriman"
                                :disabled="!!view"
                            ></v-select>

                            <v-layout row wrap>
                                <v-flex xs12>
                                    <v-select
                                        :items="staffs"
                                        v-model="selected_staff"
                                        return-object
                                        item-text="staff_name"
                                        item-value="staff_id"
                                        label="Petugas Pengirim"
                                        v-if="selected_deliverytype&&selected_deliverytype.deliverytype_code=='DLV.TYPE.SEND.STAFF'"
                                        :disabled="!!view"
                                    ></v-select>

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
                            </v-layout>
                            <!-- <v-text-field
                                label="Nomor Referensi Customer"
                                v-model="delivery_ref_number"
                                :readonly="delivery_post||is_sales||view"
                                
                            ></v-text-field> -->
                            <!-- <v-text-field
                                label="Tanggal Transaksi"
                                :value="delivery_date"
                                v-show="!!delivery_post||!!is_sales||!!view"
                                readonly
                                :disabled="view"
                            ></v-text-field> -->

                            
                        </v-flex>
                        <v-flex xs6 pl-4>
                            <v-layout row wrap>
                                <v-flex xs6>
                                    <v-layout row wrap>
                                        <v-flex xs12>
                                            <v-autocomplete
                                                :items="customers"
                                                v-model="selected_customer"
                                                return-object
                                                item-text="customer_name"
                                                item-value="customer_id"
                                                label="Customer"
                                                :disabled="details.length>0 && !!details[0].item"
                                            ></v-autocomplete>
                                        </v-flex>
                                        <v-flex xs12>
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
                                                        <!-- <v-flex xs12>
                                                            <span class="d-block caption">{{data.item.address_desc}}</span>        
                                                        </v-flex> -->
                                                    </v-layout>
                                                </template>
                                                <!-- <template slot="append-outer">
                                                    <v-btn color="success" class="ma-0 ml-2 btn-icon" @click="add_address" v-show="!view">
                                                        <v-icon>add</v-icon>
                                                    </v-btn> 
                                                </template> -->
                                            </v-select>

                                            <div v-if="!!selected_address" class="caption mt-2 mb-2">
                                                {{selected_address.address_desc}}<br>
                                                Kab / Kota {{selected_address.city_name}} Prop {{selected_address.province_name}}<br>
                                                PIC : <b>{{selected_address.address_pic}}</b><br>
                                                Phone : {{selected_address.address_phone}}
                                            </div>
                                            <!-- END OF DELIVERY ADDRESS -->
                                        </v-flex>
                                    </v-layout>
                                    
                                </v-flex>
                                <v-flex xs6 pl-4>
                                    <v-select
                                        :items="warehouses"
                                        v-model="selected_warehouse"
                                        return-object
                                        item-text="warehouse_name"
                                        item-value="warehouse_id"
                                        label="Dikirim dari Gudang"
                                        :disabled="!single && !!edit && details.length>0 && !!details[0].item"
                                    ></v-select>

                                    <v-text-field
                                        label="Sales"
                                        readonly
                                        :value="sales_name"
                                    ></v-text-field>

                                    <!-- EXPEDITION -->
                                    <v-select
                                        :items="expeditions"
                                        v-model="selected_expedition"
                                        return-object
                                        item-text="expedition_name"
                                        item-value="expedition_id"
                                        clearable
                                        v-show="!selected_expedition&&expedition_mode=='E'"
                                        class="mt-3"
                                        :disabled="!!view"
                                    >
                                        <template slot="label">
                                            <span class="orange--text">Kirim via ekspedisi? Pilih ...</span>
                                        </template>

                                        <!-- <template slot="append-outer">
                                            <v-btn color="success" class="ma-0 ml-2 btn-icon" @click="add_exp" v-show="!view" :disabled="!!view">
                                                <v-icon>add</v-icon>
                                            </v-btn> 
                                        </template> -->
                                    </v-select>

                                    <v-text-field
                                        :value="selected_expedition.expedition_name"
                                        v-if="!!selected_expedition"
                                        :clearable="!view"
                                        @click:clear="selected_expedition=null"
                                        readonly
                                        persistent-hint
                                        class="mt-3"
                                    >
                                        <template slot="label">
                                            <span class="orange--text">Ekspedisi</span>
                                        </template>
                                    </v-text-field>

                                    <v-text-field
                                        v-model="expedition_name"
                                        v-if="expedition_mode=='N'"
                                        :clearable="!view"
                                        :readonly="!!view"
                                        class="mt-3"
                                    >
                                        <template slot="label">
                                            <span class="orange--text">Tuliskan Ekspedisi anda disini</span>
                                        </template>

                                        <!-- <template slot="append-outer">
                                            <v-btn color="primary" class="ma-0 ml-2 btn-icon" @click="cancel_exp" v-show="!view" :disabled="!!view">
                                                <v-icon>refresh</v-icon>
                                            </v-btn> 
                                        </template> -->
                                    </v-text-field>
                                    <!-- END OF EXPEDITION -->

                                </v-flex>
                            </v-layout>
                            
                            <!-- <v-layout row wrap>
                                <v-flex xs6>
                                    <v-checkbox label="Harga termasuk PPN?" v-model="delivery_ppn" true-value="Y" false-value="N"></v-checkbox>        
                                </v-flex>
                            </v-layout> -->
                            
                        </v-flex>

                        <v-flex xs4 pl-4>
                            <v-layout row wrap>
                                <v-flex xs6>
                                    <v-textarea
                                        label="Memo untuk bagian Keuangan"
                                        rows="4"
                                        v-model="delivery_memo"
                                        outline
                                        :readonly="!!view"
                                    ></v-textarea>
                                </v-flex>

                                <v-flex xs6 pl-2>
                                    <v-textarea
                                        label="Catatan"
                                        rows="4"
                                        v-model="delivery_note"
                                        outline
                                        :readonly="!!view"
                                    ></v-textarea>
                                </v-flex>

                                <v-flex xs12>
                                    <v-card>
                                        <v-card-title primary-title class="py-1 px-2 orange white--text">INVOICE</v-card-title>
                                        <v-card-text class="py-1 px-2">
                                            <div v-for="(inv, n) in invoices" :key="n"><a :href="'../sales-invoice/view/'+inv.invoice_id" target="_blank">{{ inv.invoice_date }} | {{ inv.invoice_number }}</a></div>
                                        </v-card-text>
                                    </v-card>
                                </v-flex>
                            </v-layout>
                        </v-flex>
                        
                        
                        


                        <v-flex xs12>
                            <v-card>
                                <v-card-title primary-title class="py-2 px-3 cyan white--text">
                                    <v-layout row wrap>
                                        <v-flex xs6><h3 class="subheading text-xs-center">PRODUK</h3></v-flex>
                                        <v-flex xs6>
                                            <v-layout row wrap>
                                                <v-flex xs2 pr-2>
                                                    <h3 class="subheading text-xs-center">QTY</h3>
                                                </v-flex>
                                                <v-flex xs2 pr-2>
                                                    <h3 class="subheading text-xs-center">UNIT</h3>
                                                </v-flex>
                                                <v-flex xs8 pr-2>
                                                    <h3 class="subheading text-xs-center">CATATAN</h3>
                                                </v-flex>
                                            </v-layout>
                                        </v-flex>
                                    </v-layout>
                                </v-card-title>
                                <v-card-text class="px-3 py-2" v-show="!!selected_customer&&!!selected_warehouse">
                                    <v-layout row wrap v-for="(d, n) in details" :key="d.detail_id" :class="{'mt-2':n>0}">
                                        <v-flex xs6 pr-3>
                                            <v-select
                                                :items="items"
                                                :value="d.item"
                                                return-object
                                                solo
                                                hide-details
                                                clearable
                                                :readonly="!!d.item"
                                                @change="update_item(n, $event)"
                                                item-text="item_name"
                                                item-value="detail_id"
                                                placeholder="Pilih..."
                                                v-show="!d.item"
                                            >
                                                <template slot="item" slot-scope="data">
                                                    <v-layout row wrap>
                                                        <v-flex xs12>{{data.item.item_name}} ({{data.item.detail_qty}}/{{data.item.detail_sent}})</v-flex>
                                                        <v-flex xs12 class="caption blue--text">
                                                            {{data.item.sales_number}} - {{data.item.customer_name}}
                                                        </v-flex>
                                                    </v-layout> 
                                                </template>

                                                <template slot="selection" slot-scope="data">
                                                    <span class="blue--text">{{data.item.sales_number}}</span> &nbsp; {{data.item.item_name}}
                                                </template>

                                                <template slot="prepend" v-if="!is_sales && !single">
                                                    <v-btn color="red" class="ma-0 mr-1" icon :dark="false" flat @click="del_detail(n)" :disabled="!!view"><v-icon>delete</v-icon></v-btn>
                                                </template>
                                            </v-select>

                                            <v-layout row wrap v-if="!!d.item">
                                                <v-flex xs12>
                                                    <v-text-field
                                                        solo
                                                        :value="(!!d.item.item_custom_name?d.item.item_custom_name:d.item.item_name) + '   (' + d.item.detail_qty + '/' + d.item.detail_sent + ')'"
                                                        hide-details
                                                        :clearable="!view"
                                                        readonly
                                                        @click:clear="update_item(n, null)"
                                                        @dblclick="change_item_name(d)"
                                                        >
                                                        <template slot="prepend" v-if="!is_sales && !single">
                                                            <v-btn color="red" class="ma-0 mr-1" icon :dark="false" flat @click="del_detail(n)"  :disabled="!!view"><v-icon>delete</v-icon></v-btn>
                                                        </template>
                                                    </v-text-field>
                                                </v-flex>    
                                                <v-flex xs12 class="caption blue--text pl-5 mt-2">
                                                    <span class="red--text">{{d.item.item_code}} • </span>
                                                    <a :href="HOST+'ui/app/sales-order/view/'+d.item.sales_id" @click="" target="_blank">{{d.item.sales_number}}</a> - <span class="red--text">{{d.item.customer_name}}</span>
                                                </v-flex>
                                                
                                            </v-layout>
                                        </v-flex>


                                        <v-flex xs6>
                                            <v-layout row wrap>
                                                <v-flex xs2 pr-2>
                                                    <v-text-field
                                                        label=""
                                                        solo
                                                        hide-details
                                                        :value="d.qty"
                                                        reverse
                                                        dense
                                                        @change="set_details"
                                                        @input="update_amount(n, 'qty', $event)"

                                                        suffix=""
                                                        :readonly="!!view"
                                                    ></v-text-field>
                                                    <span class="caption d-block text-xs-right pr-2"><i>stok : <b>{{one_money(d.item?d.item.stock:0)}}</b></i></span>
                                                </v-flex>
                                                <v-flex xs2 pr-2>
                                                    <v-text-field
                                                        label=""
                                                        solo
                                                        hide-details
                                                        :value="d.item?d.item.item_unit:''"
                                                        dense
                                                        readonly
                                                        flat
                                                    ></v-text-field>
                                                </v-flex>
                                                <v-flex xs8 pr-2>
                                                    <v-text-field
                                                        label=""
                                                        solo
                                                        hide-details
                                                        :value="d.note"
                                                        dense
                                                        :readonly="!!view"
                                                    ></v-text-field>
                                                    <div v-if="!!d.item" v-show="d.item.sales_memo!=''" class="caption mt-2 blue--text">
                                                        <!-- <v-divider class="my-2"></v-divider> -->
                                                        <b>Memo purchasing</b> : <i><u>{{d.item.sales_memo}}</u></i>
                                                    </div>
                                                </v-flex>
                                            </v-layout>    
                                        </v-flex>
                                        
                                    </v-layout>

                                    <v-layout row wrap v-show="!view">
                                        <v-flex xs2 class="text-xs-center" pt-2 offset-md5>
                                            <v-btn color="primary btn-icon" @click="add_detail" :disabled="!btn_add_enabled"><v-icon>add</v-icon></v-btn>
                                        </v-flex>
                                        <v-flex xs5>
                                            <!-- <v-layout row wrap>
                                                <v-flex xs2 offset-md5 pt-2>
                                                    TOTAL
                                                </v-flex>
                                                <v-flex xs5 pt-2>
                                                    {{one_money(delivery_total)}}
                                                </v-flex>
                                            </v-layout> -->
                                        </v-flex>
                                    </v-layout>
                                    
                                </v-card-text>

                                <v-card-text class="px-3 py-2" v-show="!selected_customer||!selected_warehouse">
                                    <v-layout row wrap>
                                        <v-flex xs12 class="text-xs-center red--text pa-2">
                                            Pilih dulu customer dan gudangnya ...
                                        </v-flex>
                                    </v-layout>
                                </v-card-text>
                            </v-card>

                            
                            <!-- <v-layout row wrap>
                                <v-flex xs12 px-3 pt-3>
                                    <v-layout row wrap>
                                        <v-flex xs7 py-3>
                                            <h3 class="title">TOTAL</h3>
                                        </v-flex>
                                        <v-flex xs5>
                                            <v-layout row wrap>
                                                <v-flex xs6 pr-2>
                                                    <v-text-field
                                                        label=""
                                                        solo
                                                        hide-details
                                                        :value="one_money(total.debit)"
                                                        reverse
                                                        dense
                                                        flat
                                                        suffix="Rp"
                                                        readonly
                                                    ></v-text-field>
                                                </v-flex>
                                                <v-flex xs6 pl-2>
                                                    <v-text-field
                                                        label=""
                                                        solo
                                                        hide-details
                                                        :value="one_money(total.credit)"
                                                        reverse
                                                        dense
                                                        flat
                                                        suffix="Rp"
                                                        readonly
                                                    ></v-text-field>
                                                </v-flex>
                                            </v-layout>
                                        </v-flex>    
                                    </v-layout>
                                </v-flex>
                                
                            </v-layout> -->
                            
                        </v-flex>
                    </v-layout>
                </v-flex>
            </v-layout>
        </v-card-text>

        <v-card-actions>
            <v-btn color="primary" flat @click="dialog=!dialog" v-show="!view">Batal</v-btn>
            <v-spacer></v-spacer>
            <!-- <v-btn color="primary" @click="add_detail">Add</v-btn>                 -->
            <!-- <v-btn color="green" @click="save_confirm" :disabled="!btn_save_enabled" :dark="btn_save_enabled" v-show="!view">Simpan & Konfirmasi</v-btn> -->
            <v-btn color="primary" @click="save_confirm" :disabled="!btn_save_enabled" :dark="btn_save_enabled" v-show="!view">Simpan</v-btn>
            <v-btn color="primary" @click="dialog=!dialog" v-show="view">Tutup</v-btn>
        </v-card-actions>

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
    </v-card>
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
            get () { return this.$store.state.delivery_new.dialog_new },
            set (v) { this.$store.commit('delivery_new/set_common', ['dialog_new', v]) }
        },

        HOST () {
            return this.$store.state.delivery.HOST
        },

        sa () {
            return this.$store.state.delivery_new.sa
        },

        snackbar : {
            get () { return this.$store.state.delivery_new.snackbar },
            set (v) { this.$store.commit('delivery_new/set_common', ['snackbar', v]) }
        },

        snackbar_text : {
            get () { return this.$store.state.delivery_new.snackbar_text },
            set (v) { this.$store.commit('delivery_new/set_common', ['snackbar_text', v]) }
        },

        delivery_note : {
            get () { return this.$store.state.delivery_new.delivery_note },
            set (v) { this.$store.commit('delivery_new/set_common', ['delivery_note', v]) }
        },

        delivery_memo : {
            get () { return this.$store.state.delivery_new.delivery_memo },
            set (v) { this.$store.commit('delivery_new/set_common', ['delivery_memo', v]) }
        },

        delivery_number : {
            get () { return this.$store.state.delivery_new.delivery_number },
            set (v) { this.$store.commit('delivery_new/set_common', ['delivery_number', v]) }
        },

        delivery_ref_number : {
            get () { return this.$store.state.delivery_new.delivery_ref_number },
            set (v) { this.$store.commit('delivery_new/set_common', ['delivery_ref_number', v]) }
        },

        delivery_ppn : {
            get () { return this.$store.state.delivery_new.delivery_ppn },
            set (v) { 
                this.$store.commit('delivery_new/set_common', ['delivery_ppn', v]) 
                this.retotal(0)
            }
        },

        delivery_send_note : {
            get () { return this.$store.state.delivery_new.delivery_send_note },
            set (v) { this.$store.commit('delivery_new/set_common', ['delivery_send_note', v]) }
        },

        details () {
            return this.$store.state.delivery_new.details
        },

        total () {
            let x = 0
            let y = 0
            for (let j of this.details) {
                x = x + Math.round(j.debit)
                y = y + Math.round(j.credit)
            }
            return {debit:x,credit:y}
        },

        delivery_date () {
            return this.$store.state.delivery_new.delivery_date
        },

        delivery_post () {
            let x = this.$store.state
            if (!x.delivery_new.edit) return false
            if (x.delivery.selected_delivery.delivery_confirm == 'N') return false
            return true
        },

        items () {
            return this.$store.state.delivery_new.items
        },

        btn_save_enabled () {
            // let ttl = this.total
            // if (ttl.debit == 0 && ttl.credit == 0) return false
            // if (ttl.debit != ttl.credit) return false
            // if (this.delivery_note == '') return false
            // if (!!this.$store.state.delivery_new.save) return false
            // if (!!this.delivery_post) return false
            // if (this.view) return false

            // for (let d of this.details)
            //     if (!d.account && (Math.round(d.credit) > 0 || Math.round(d.debit) > 0))
            //         return false

            return true
        },

        btn_add_enabled () {
            if (!!this.single)
                return false
            // let d = this.details[this.details.length-1]
            // if (!d) return false
            // if (!d.account) return false
            // if (this.is_sales) return false
            // if (this.view) return false

            // if (this.$store.state.delivery_new.edit) {
            //     let j = this.$store.state.delivery.selected_delivery
            //     if (j.delivery_post=='Y') return false
            // }
            
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
            if (r.delivery_invoice!=0) return true
            // if (r.delivery_invoice!=0 || r.delivery_confirm=='Y') return true
            // return false
            return this.$store.state.delivery_new.view
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

        delivery_total () {
            let total = 0
            for (let x of this.details)
                total = total + Math.round(x.total)
            return total
        },

        warehouses () {
            return this.$store.state.delivery_new.warehouses
        },

        selected_warehouse : {
            get () { return this.$store.state.delivery_new.selected_warehouse },
            set (v) { 
                this.$store.commit('delivery_new/set_selected_warehouse', v)
                this.$store.dispatch('delivery_new/search_item')
            }
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

        single () {
            return this.$store.state.delivery_new.single
            // return this.$store.state.delivery_new.view
        },

        sales_name () {
            if (!!this.single && !this.edit
                && this.$store.state.sales_new.selected_staff)
                return this.$store.state.sales_new.selected_staff.staff_name

            if (this.details.length > 0 && this.details[0].item)
                return this.details[0].item.staff_name

            return ''
        },

        expeditions () {
            return this.$store.state.sales_new.expeditions
        },

        selected_expedition : {
            get () { return this.$store.state.delivery_new.selected_expedition },
            set (v) { this.$store.commit('delivery_new/set_selected_expedition', v) }
        },

        expedition_name : {
            get () { return this.$store.state.delivery_new.expedition_name },
            set (v) { this.$store.commit('delivery_new/set_common', ['expedition_name', v]) }
        },

        expedition_mode : {
            get () { return this.$store.state.delivery_new.expedition_mode },
            set (v) { this.$store.commit('delivery_new/set_common', ['expedition_mode', v]) }
        },

        invoices () {
            return this.$store.state.delivery_new.invoices
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

        update_amount (idx, side, amount) {
            let d = this.details            
            d[idx][side] = amount
            this.retotal(idx)
            // this.$store.commit('delivery_new/set_details', d)
        },

        add_detail () {
            let d = this.details
            let dfl = JSON.parse(JSON.stringify(this.$store.state.delivery_new.detail_default))
            d.push(dfl)
            this.$store.commit('delivery_new/set_details', d)
        },

        set_details () {
            this.$store.commit('delivery_new/set_details', this.details)
        },

        del_detail (x) {
            let d = this.details
            d.splice(x, 1)
            this.$store.commit('delivery_new/set_details', d)
        },

        change_delivery_date(x) {
            this.$store.commit('delivery_new/set_common', ['delivery_date', x.new_date])
        },

        update_item(idx, v) {
            let d = this.details
            d[idx].item = v

            if (idx == 0) {
                for (let add of this.addresses) {
                    if (add.address_id == v.address_id)
                        this.selected_address = v
                }
            }
            
            this.$store.commit('delivery_new/set_details', d)
        },

        change_ppn(idx, v) {
            let d = this.details
            d[idx].ppn = v
            this.retotal(idx)
        },

        retotal(idx) {
            let d = this.details
            if (idx == 0) {
                for (let idxn in d) {
                    d[idxn].total = d[idxn].price * d[idxn].qty * (100 - d[idxn].disc) * (d[idxn].ppn == "Y" && this.delivery_ppn == "N"?1.1:1) / 100
                }
            } else {
                d[idx].total = d[idx].price * d[idx].qty * (100 - d[idx].disc) * (d[idx].ppn == "Y" && this.delivery_ppn == "N"?1.1:1) / 100
                // console.log(d[idx].price)
                // console.log(d[idx].qty)
                // console.log(d[idx].disc)
                // console.log(d[idx].ppn)
                
            }
        },

        change_item_name (x) {
            this.$store.commit('delivery_new/set_object', ['custom_item_name', x.item.item_name])
            this.$store.commit('delivery_new/set_object', ['selected_item', x])
            this.$store.commit('delivery_new/set_common', ['dialog_itemname', true])
        }
    },

    mounted () {
        this.$store.dispatch('delivery_new/search_customer')
        this.$store.dispatch('delivery_new/search_staff')
        this.$store.dispatch('delivery_new/search_warehouse')
        this.$store.dispatch('delivery_new/search_deliverytype')
        // this.$store.dispatch('delivery_new/search_item')
    },

    watch : {
        // details (v, o) {
        //     this.$store.dispatch('delivery_new/search_stock')
        // }
    }
}
</script>