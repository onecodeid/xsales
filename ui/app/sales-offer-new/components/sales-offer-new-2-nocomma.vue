<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="1200px"
        transition="dialog-transition"
        content-class="dialog-new"
        persistent
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <h3>
                    <span v-show="!edit">TERIMA ORDER BARU</span>
                    <span v-show="!!edit">UBAH DATA ORDER</span>
                </h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                        <v-layout row wrap>
                            <v-flex xs7>
                                <v-layout row wrap>
                                    <v-flex xs3>
                                        <common-datepicker
                                            label="Tanggal Transaksi"
                                            :date="sales_date.split('-').reverse().join('-')"
                                            data="0"
                                            @change="change_sales_date"
                                            classs=""
                                            hints=" "
                                            :details="true"
                                            :solo="false"
                                            v-if="dialog"
                                        ></common-datepicker>
                                        <!-- <v-text-field
                                            label="Tanggal Transaksi"
                                            :value="sales_date"
                                            v-show="!!sales_post||!!is_sales||!!view"
                                            readonly
                                            :disabled="view"
                                        ></v-text-field> -->

                                        <v-text-field
                                            label="Nomor Faktur"
                                            v-model="sales_number"
                                            :readonly="true"
                                            :disabled="view"
                                            placeholder="( kosongkan saja )"
                                        ></v-text-field>
                                    </v-flex>
                                    <v-flex xs9 pl-5>
                                        <v-autocomplete
                                            :items="customers"
                                            v-model="selected_customer"
                                            return-object
                                            item-text="customer_name"
                                            item-value="customer_id"
                                            label="Customer"
                                            :disabled="sales_used=='Y'"
                                        >
                                            <template slot="append-outer">
                                                <v-btn color="success" class="ma-0 ml-2 btn-icon" @click="add_customer" :disabled="sales_used=='Y'">
                                                    <v-icon>add</v-icon>
                                                </v-btn> 
                                            </template>
                                        </v-autocomplete>

                                        <v-text-field
                                            label="Catatan"
                                            outline
                                            v-model="sales_note"
                                        ></v-text-field>

                                        <!-- <v-layout row wrap>
                                            <v-flex xs6>
                                                <v-select
                                                    :items="staffs"
                                                    v-model="selected_staff"
                                                    return-object
                                                    item-text="staff_name"
                                                    item-value="staff_id"
                                                    label="Sales"
                                                ></v-select>
                                            </v-flex>
                                            <v-flex xs6 pl-4>
                                                <v-checkbox label="Harga termasuk PPN?" v-model="sales_ppn" true-value="Y" false-value="N"></v-checkbox>        
                                            </v-flex>
                                        </v-layout> -->
                                    </v-flex>
                                    <!-- <v-flex xs6 pr-1>
                                        <v-text-field
                                            label="Catatan"
                                            outline
                                            v-model="sales_note"
                                        ></v-text-field>
                                    </v-flex>
                                    <v-flex xs6 pl-1>
                                        <v-text-field
                                            label="Validitas"
                                            outline
                                            v-model="sales_validity"
                                        ></v-text-field>
                                    </v-flex> -->
                                </v-layout>
                            </v-flex>
                                    
                            <v-flex xs5 pl-5>
                                <v-layout row wrap>
                                    <!-- <v-flex xs4 pr-2>
                                        <v-select
                                            :items="leadtypes"
                                            v-model="selected_leadtype"
                                            return-object
                                            item-text="leadtype_name"
                                            item-value="leadtype_id"
                                            label="Tipe Prospek"
                                        ></v-select>
                                    </v-flex> -->
                                    <v-flex xs12>
                                        <v-select
                                            :items="terms"
                                            v-model="selected_term"
                                            return-object
                                            item-text="term_name"
                                            item-value="term_id"
                                            label="Metode Pembayaran"
                                        ></v-select>
                                    </v-flex>
                                </v-layout>
                                

                                <!-- <v-layout row wrap>
                                    <v-flex xs4>
                                        <v-text-field
                                            label="Franco"
                                            v-model="sales_franco"
                                            :disabled="view"
                                        ></v-text-field>
                                    </v-flex>
                                    <v-flex xs8 pl-2>
                                        <v-text-field
                                            label="Keterangan Stok"
                                            v-model="sales_stock"
                                            :disabled="view"
                                        ></v-text-field>
                                    </v-flex>
                                </v-layout> -->
                                

                                <v-text-field
                                    label="Delivery Time"
                                    v-model="sales_delivery"
                                    :disabled="view"
                                ></v-text-field>
                            </v-flex>
                                    
                                
                            

                            <!-- <v-flex xs3 pl-4>
                                <v-textarea
                                    label="Catatan"
                                    rows="4"
                                    v-model="sales_note"
                                    outline
                                ></v-textarea>
                            </v-flex> -->
                            


                            <v-flex xs12>
                                <v-card>
                                    <v-card-title primary-title class="py-2 px-3 cyan white--text">
                                        <v-layout row wrap>
                                            <v-flex xs5><h3 class="subheading">PRODUK</h3></v-flex>
                                            <v-flex xs7>
                                                <v-layout row wrap>
                                                    <v-flex xs4 pr-2>
                                                        <h3 class="subheading text-xs-right">HARGA</h3>
                                                    </v-flex>
                                                    <v-flex xs2 pr-2>
                                                        <h3 class="subheading text-xs-right">QTY</h3>
                                                    </v-flex>
                                                    <v-flex xs2 pr-2>
                                                        <h3 class="subheading text-xs-right">POTONGAN</h3>
                                                    </v-flex>
                                                    <!-- <v-flex xs1 px-0>
                                                        <h3 class="subheading text-xs-center">PPN</h3>
                                                    </v-flex> -->
                                                    <v-flex xs4 pr-2>
                                                        <h3 class="subheading text-xs-right">SUB TOTAL</h3>
                                                    </v-flex>
                                                </v-layout>
                                            </v-flex>
                                        </v-layout>
                                    </v-card-title>
                                    <v-card-text class="px-3 py-2">
                                        <v-layout row wrap v-for="(d, n) in details" :key="n" :class="{'mt-2':n>0}">

                                            <v-flex xs5 pr-3 v-show="d.other!='Y'">
                                                <v-autocomplete
                                                    :items="items"
                                                    :value="d.item"
                                                    return-object
                                                    solo
                                                    hide-details
                                                    clearable
                                                    :readonly="!!d.item"
                                                    @change="update_item(n, $event)"
                                                    item-text="item_name"
                                                    item-value="item_id"
                                                    placeholder="Pilih..."
                                                    v-show="!d.item"
                                                >
                                                    <template slot="item" slot-scope="data">
                                                        {{data.item.item_name}}
                                                    </template>

                                                    <template slot="selection" slot-scope="data">
                                                        {{data.item.item_name}}
                                                    </template>

                                                    <template slot="prepend" v-if="!is_sales">
                                                        <v-btn color="red" class="ma-0" icon :dark="false" flat @click="del_detail(n)" ><v-icon>delete</v-icon></v-btn>
                                                        <!-- <v-btn color="blue" class="ma-0 mr-1" icon :dark="false" flat @click="switch_other(n)"><v-icon>autorenew</v-icon></v-btn> -->
                                                    </template>
                                                </v-autocomplete>

                                                <v-layout row wrap v-if="!!d.item">
                                                    <v-flex xs12>
                                                        <v-text-field
                                                            solo
                                                            :value="d.item.item_name"
                                                            hide-details
                                                            clearable
                                                            readonly
                                                            @click:clear="update_item(n, null)"
                                                            >
                                                            <template slot="prepend" v-if="!is_sales">
                                                                <v-btn color="red" class="ma-0 mr-1" icon :dark="false" flat @click="del_detail(n)" ><v-icon>delete</v-icon></v-btn>
                                                            </template>
                                                        </v-text-field>
                                                        <div class="caption text-xs-left pl-5 py-1 mt-1"><a href="#" @click="purchase_history(d.item)"><i>History pembelian</i></a></div>
                                                    </v-flex>
                                                    <!-- <div class="caption text-xs-right cyan lighten-2 px-2 py-1 mt-1"><i>History pembelian</i></div> -->
                                                    
                                                </v-layout>
                                            </v-flex>

                                            <v-flex xs5 pr-3 v-show="d.other=='Y'">
                                                 <v-text-field
                                                    solo
                                                    :value="d.other_name"
                                                    hide-details
                                                    clearable
                                                    @input="update_amount(n, 'other_name', $event)"
                                                    >
                                                    <template slot="prepend" v-if="!is_sales">
                                                        <v-btn color="red" class="ma-0 mr-1" icon :dark="false" flat @click="del_detail(n)" ><v-icon>delete</v-icon></v-btn>
                                                        <!-- <v-btn color="blue" class="ma-0 mr-1" icon :dark="false" flat @click="switch_other(n)" v-show="d.other_name==''||d.other_name==null"><v-icon>autorenew</v-icon></v-btn> -->
                                                    </template>
                                                </v-text-field>   

                                                <v-layout row wrap>
                                                    <v-flex xs7 offset-xs1 pt-1 pr-4>
                                                        <v-select
                                                            :items="packs"
                                                            item-value="pack_id"
                                                            item-text="pack_name"
                                                            return-object
                                                            label="Kemasan"
                                                            hide-details
                                                            @change="update_pack(n, $event)"
                                                            :value="d.pack"
                                                            v-show="!d.pack"
                                                        >
                                                            <template slot="append-outer">
                                                                <v-btn color="success" class="ma-0 ml-2 btn-icon" small @click="add_pack" :disabled="!!view">
                                                                    <v-icon>add</v-icon>
                                                                </v-btn> 
                                                            </template>
                                                        </v-select>
                                                        <v-text-field
                                                            readonly
                                                            clearable
                                                            v-if="!!d.pack"
                                                            :value="d.pack.pack_name"
                                                            label="Kemasan"
                                                            @click:clear="update_pack(n, null)"
                                                        >
                                                        </v-text-field>
                                                    </v-flex>
                                                    <v-flex xs4 pt-1 pl-2>
                                                        <v-select
                                                            :items="units"
                                                            label="Satuan / Unit"
                                                            item-value="unit_id"
                                                            item-text="unit_name"
                                                            return-object
                                                            hide-details
                                                            @change="update_unit(n, $event)"
                                                            :value="d.unit"
                                                            v-show="!d.unit"
                                                        >
                                                            <template slot="append-outer">
                                                                <v-btn color="success" class="ma-0 ml-2 btn-icon" small @click="add_unit" :disabled="!!view">
                                                                    <v-icon>add</v-icon>
                                                                </v-btn> 
                                                            </template>
                                                        </v-select>
                                                        <v-text-field
                                                            readonly
                                                            clearable
                                                            v-if="!!d.unit"
                                                            :value="d.unit.unit_name"
                                                            label="Unit"
                                                            @click:clear="update_unit(n, null)"
                                                        >
                                                        </v-text-field>
                                                    </v-flex>
                                                </v-layout>
                                                
                                            </v-flex>

                                            <v-flex xs7>
                                                <v-layout row wrap>
                                                    <v-flex xs4 pr-2>
                                                        <v-text-field
                                                            label=""
                                                            solo
                                                            hide-details
                                                            :value="d.price"
                                                            reverse
                                                            dense
                                                            @change="set_details"
                                                            @input="update_amount(n, 'price', $event)"
                                                            suffix="Rp"
                                                            :readonly="view"
                                                        ></v-text-field>
                                                    </v-flex>
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
                                                            :readonly="view"
                                                            class="pl-0"
                                                        >
                                                            <!-- <template slot="append">
                                                                <v-tooltip :value="d.tooltip" top v-if="!!d.item">
                                                                    <template v-slot:activator="{ on }">
                                                                        <v-btn icon v-on="on" small class="ma-0">
                                                                        <v-icon color="grey lighten-1" small>shopping_cart</v-icon>
                                                                        </v-btn>
                                                                    </template>
                                                                    <span>
                                                                        <span v-for="(stock, n) in d.item.stocks" :key="n" class="mr-2">
                                                                            {{stock.warehouse_name}} : {{stock.stock_qty}}
                                                                        </span>
                                                                    </span>
                                                                </v-tooltip>
                                                            </template> -->
                                                        </v-text-field>
                                                        <div class="caption text-xs-right cyan lighten-2 px-2 py-1 mt-1" v-if="!!d.item"><i>stock : {{one_money(d.item.stock)}}</i></div>

                                                    </v-flex>
                                                    <v-flex xs2 pr-2>
                                                        <v-text-field
                                                            label=""
                                                            solo
                                                            hide-details
                                                            :value="d.disc"
                                                            reverse
                                                            dense
                                                            @change="set_details"
                                                            @input="update_amount(n, 'disc', $event)"
                                                            
                                                            :readonly="view"
                                                            v-show="d.disctype=='P'"
                                                        >
                                                            <template slot="append-outer">
                                                                <v-btn small class="orange ma-0 btn-icon" @click="set_disc(n, 'R')" depressed dark>%</v-btn>
                                                            </template>
                                                        </v-text-field>
                                                        <v-text-field
                                                            label=""
                                                            solo
                                                            hide-details
                                                            :value="d.discrp"
                                                            reverse
                                                            dense
                                                            @change="set_details"
                                                            @input="update_amount(n, 'discrp', $event)"
                                                            
                                                            :readonly="view"
                                                            v-show="d.disctype=='R'"
                                                        >
                                                            <template slot="append-outer">
                                                                <v-btn small class="orange ma-0 btn-icon" @click="set_disc(n, 'P')" depressed dark>Rp</v-btn>
                                                            </template>
                                                        </v-text-field>
                                                    </v-flex>
                                                    <!-- <v-flex xs1 pl-2 pt-1>
                                                        <v-checkbox 
                                                        true-value="Y"
                                                        false-value="N" 
                                                        :value="d.ppn"
                                                        
                                                        :input-value="d.ppn" hide-details
                                                        @change="change_ppn(n, $event)"
                                                        style="margin-top:0px !important"></v-checkbox>
                                                    </v-flex> -->
                                                    <v-flex xs4 pr-2>
                                                        <v-text-field
                                                            label=""
                                                            solo
                                                            hide-details
                                                            :value="Math.round(d.total)"
                                                            reverse
                                                            dense
                                                            :mask="one_mask_money(Math.round(d.total))"
                                                            suffix="Rp"
                                                            readonly
                                                        ></v-text-field>
                                                    </v-flex>
                                                </v-layout>    
                                            </v-flex>
                                            
                                        </v-layout>

                                        <v-layout row wrap v-show="!view">
                                            <v-flex xs2 class="text-xs-center" pt-2 offset-md5>
                                                <v-btn color="primary btn-icon" @click="add_detail" :disabled="!btn_add_enabled"><v-icon>add</v-icon></v-btn>
                                            </v-flex>
                                            <v-flex xs5 pt-2>
                                                
                                                <v-layout row wrap pt-3>
                                                    <v-flex xs4 pl-2 pb-2 pr-4 offset-xs4>
                                                        <span class="subheading">SUBTOTAL</span>
                                                    </v-flex>
                                                    <v-flex xs4 px-2 pb-2 pt-0 class="text-xs-right">
                                                        <span class="caption">Rp</span> <span class="subheading">{{one_money(sales_total)}}</span>
                                                    </v-flex>
                                                </v-layout>

                                                <!-- SHIPPING / ONGKOS KIRIM -->
                                                <v-layout row wrap>
                                                    <v-flex xs4 pa-2 pt-1 pr-4 offset-xs4>
                                                        <span class="subheading">Ongkos Kirim</span>
                                                    </v-flex>
                                                    
                                                    <v-flex xs4>
                                                        <v-text-field
                                                            solo
                                                            reverse
                                                            v-model="sales_shipping"
                                                            dense
                                                            :mask="one_mask_money(sales_shipping)"
                                                            depressed
                                                            hide-details
                                                        >
                                                        </v-text-field>
                                                    </v-flex>
                                                </v-layout>
                                                <!-- END OF SHIPPING -->

                                                <v-layout row wrap>
                                                    <v-flex xs4 offset-md4 pa-2 pt-3 pr-4 >
                                                        <span class="title">TOTAL</span>
                                                    </v-flex>
                                                    <v-flex xs4 pa-2 pt-3 class="text-xs-right">
                                                        <span class="caption">Rp</span> <span class="title">{{one_money(sales_grandtotal)}}</span>
                                                        
                                                    </v-flex>
                                                </v-layout>

                                                
                                            </v-flex>
                                        </v-layout>

                                        
                                        
                                    </v-card-text>
                                </v-card>                                
                            </v-flex>
                        </v-layout>
                    </v-flex>
                </v-layout>
            </v-card-text>

            <v-card-actions>
                <v-btn color="primary" flat @click="dialog=!dialog" v-show="!view">Batal</v-btn>
                <v-spacer></v-spacer>
                <!-- <v-btn color="primary" @click="add_detail">Add</v-btn>                 -->
                <v-btn color="primary" @click="save" :disabled="!btn_save_enabled" :dark="btn_save_enabled" v-show="!view">Simpan</v-btn>
                <v-btn color="primary" @click="dialog=!dialog" v-show="view">Tutup</v-btn>
            </v-card-actions>
        </v-card>

        <master-item-log-purchase></master-item-log-purchase>
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

.dialog-new .v-input__append-outer {
    margin: 0px !important;
}

.dialog-new .v-input__append-outer button {
    min-height: 36px;
}
</style>
<script>
module.exports = {
    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        "master-item-log-purchase" : httpVueLoader("../../master-item/components/master-item-log-purchase.vue")
    },

    data () {
        return { }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.offer_new.dialog_new },
            set (v) { this.$store.commit('offer_new/set_common', ['dialog_new', v]) }
        },

        ppn () {
            return this.$store.state.system.conf.ppn
        },

        sales_note : {
            get () { return this.$store.state.offer_new.sales_note },
            set (v) { this.$store.commit('offer_new/set_common', ['sales_note', v]) }
        },

        sales_memo : {
            get () { return this.$store.state.offer_new.sales_memo },
            set (v) { this.$store.commit('offer_new/set_common', ['sales_memo', v]) }
        },

        sales_number : {
            get () { return this.$store.state.offer_new.sales_number },
            set (v) { this.$store.commit('offer_new/set_common', ['sales_number', v]) }
        },

        sales_ppn : {
            get () { return this.$store.state.offer_new.sales_ppn },
            set (v) { 
                this.$store.commit('offer_new/set_common', ['sales_ppn', v]) 
                this.retotal(-1)
            }
        },

        sales_franco : {
            get () { return this.$store.state.offer_new.sales_franco },
            set (v) { this.$store.commit('offer_new/set_common', ['sales_franco', v]) }
        },

        sales_delivery : {
            get () { return this.$store.state.offer_new.sales_delivery },
            set (v) { this.$store.commit('offer_new/set_common', ['sales_delivery', v]) }
        },

        sales_validity : {
            get () { return this.$store.state.offer_new.sales_validity },
            set (v) { this.$store.commit('offer_new/set_common', ['sales_validity', v]) }
        },

        sales_used () {
            return this.$store.state.offer_new.sales_used
        },

        details () {
            return this.$store.state.offer_new.details
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

        sales_date () {
            return this.$store.state.offer_new.sales_date
        },

        sales_post () {
            let x = this.$store.state
            if (!x.offer_new.edit) return false
            if (x.offer.selected_sales.sales_post == 'N') return false
            return true
        },

        items () {
            return this.$store.state.offer_new.items
        },

        units () {
            return this.$store.state.offer_new.units
        },

        packs () {
            return this.$store.state.offer_new.packs
        },

        btn_save_enabled () {
            if (!this.selected_customer) return false
            if (!this.selected_staff) return false
            if (!this.selected_leadtype) return false

            return true
        },

        btn_add_enabled () {            
            return true
        },

        is_sales() {
            if (this.$store.state.filter.indexOf("J.03")>-1)
                return true
            return false
        },

        edit() {
            return this.$store.state.offer_new.edit
        },

        view () {
            return this.$store.state.offer_new.view
        },

        customers () {
            return this.$store.state.offer_new.customers
        },

        selected_customer : {
            get () { return this.$store.state.offer_new.selected_customer },
            set (v) { this.$store.commit('offer_new/set_selected_customer', v) }
        },

        sales_total () {
            let total = 0
            for (let x of this.details)
                total = total + Math.round(x.total)
            return total
        },

        sales_shipping : {
            get () { return this.$store.state.offer_new.sales_shipping },
            set (v) { this.$store.commit('offer_new/set_common', ['sales_shipping', v]) }
        },

        sales_stock : {
            get () { return this.$store.state.offer_new.sales_stock },
            set (v) { this.$store.commit('offer_new/set_common', ['sales_stock', v]) }
        },

        sales_grandtotal () {
            return parseFloat(this.sales_total) + parseFloat(this.sales_shipping)
        },

        staffs () {
            return this.$store.state.offer_new.staffs
        },

        selected_staff : {
            get () { return this.$store.state.offer_new.selected_staff },
            set (v) { this.$store.commit('offer_new/set_selected_staff', v) }
        },

        paymentplans () {
            return this.$store.state.offer_new.paymentplans
        },

        selected_paymentplan : {
            get () { return this.$store.state.offer_new.selected_paymentplan },
            set (v) { this.$store.commit('offer_new/set_selected_paymentplan', v) }
        },

        terms () {
            return this.$store.state.offer_new.terms
        },

        selected_term : {
            get () { return this.$store.state.offer_new.selected_term },
            set (v) { this.$store.commit('offer_new/set_selected_term', v) }
        },

        leadtypes () {
            return this.$store.state.offer_new.leadtypes
        },

        selected_leadtype : {
            get () { return this.$store.state.offer_new.selected_leadtype },
            set (v) { this.$store.commit('offer_new/set_selected_leadtype', v) }
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
            this.$store.dispatch('offer_new/save')
        },

        update_amount (idx, side, amount) {
            let d = this.details            
            d[idx][side] = amount
            this.retotal(idx)
            // this.$store.commit('offer_new/set_details', d)
        },

        add_detail () {
            let d = this.details
            let dfl = JSON.parse(JSON.stringify(this.$store.state.offer_new.detail_default))
            d.push(dfl)
            this.$store.commit('offer_new/set_details', d)
        },

        set_details () {
            this.$store.commit('offer_new/set_details', this.details)
        },

        del_detail (x) {
            let d = this.details
            d.splice(x, 1)
            this.$store.commit('offer_new/set_details', d)
        },

        change_sales_date(x) {
            this.$store.commit('offer_new/set_common', ['sales_date', x.new_date])
        },

        update_item(idx, v) {
            let d = this.details
            d[idx].item = v
            // d[idx].price = v.item_price
            
            this.$store.commit('offer_new/set_details', d)
        },

        update_pack(idx, v) {
            let d = this.details
            d[idx].pack = v            
            this.$store.commit('offer_new/set_details', d)
        },

        update_unit(idx, v) {
            let d = this.details
            d[idx].unit = v
            this.$store.commit('offer_new/set_details', d)
        },

        change_ppn(idx, v) {
            let d = this.details
            d[idx].ppn = v
            this.retotal(idx)
        },

        retotal(idx) {
            let d = this.details
            let ppn = (100 + this.ppn) / 100
            if (idx == -1) {
                for (let idxn in d) {
                    if (d[idxn].disctype=='P')
                        d[idxn].total = d[idxn].price * d[idxn].qty * (100 - d[idxn].disc) * (d[idxn].ppn == "Y" && this.sales_ppn == "N"?ppn:1) / 100
                    else
                        d[idxn].total = (d[idxn].price - d[idxn].discrp) * d[idxn].qty * (d[idxn].ppn == "Y" && this.sales_ppn == "N"?ppn:1)
                    d[idxn].total = Math.round(d[idxn].total)
                }
            } else {
                if (d[idx].disctype=='P')
                    d[idx].total = d[idx].price * d[idx].qty * (100 - d[idx].disc) * (d[idx].ppn == "Y" && this.sales_ppn == "N"?ppn:1) / 100
                else
                    d[idx].total = (d[idx].price - d[idx].discrp) * d[idx].qty * (d[idx].ppn == "Y" && this.sales_ppn == "N"?ppn:1)
                d[idx].total = Math.round(d[idx].total)
            }
        },

        set_disc (idx, type) {
            let d = this.details            
            d[idx]['disctype'] = type
            this.retotal(-1)
        },

        add_customer () {
            this.$store.commit('customer_new/set_common', ['edit', false])
            this.$store.commit('customer_new/set_common', ['customer_name', ''])
            this.$store.commit('customer_new/set_common', ['customer_address', ''])
            this.$store.commit('customer_new/set_common', ['customer_phone', ''])
            this.$store.commit('customer_new/set_common', ['customer_note', ''])
            this.$store.commit('customer_new/set_common', ['customer_email', ''])
            this.$store.commit('customer_new/set_common', ['customer_postcode', ''])
            this.$store.commit('customer_new/set_common', ['customer_pic_name', ''])
            this.$store.commit('customer_new/set_common', ['customer_pic_phone', ''])
            this.$store.commit('customer_new/set_common', ['customer_npwp', ''])
            this.$store.commit('customer_new/set_common', ['customer_prospect', 'Y'])
            this.$store.commit('customer_new/set_phones', [])        
            
            this.$store.commit('customer_new/set_selected_province', null)
            this.$store.commit('customer_new/set_selected_city', null)
            this.$store.commit('customer_new/set_selected_district', null)
            this.$store.commit('customer_new/set_selected_village', null)
            this.$store.commit('customer_new/set_selected_customer_type', 
                this.$store.state.customer_new.customer_types[0])
            this.$store.commit('customer_new/set_dialog_new', true)
        },

        switch_other (idx) {
            let d = this.details            
            let o = d[idx].other
            d[idx].other = (o == 'Y' ? 'N' : 'Y')
            // this.$store.commit('offer_new/set_details', d)
        },

        add_unit () {
            this.$store.commit('unit_new/set_common', ['edit', false])
            this.$store.commit('unit_new/set_common', ['unit_name', ''])
            this.$store.commit('unit_new/set_common', ['unit_code', ''])
            this.$store.commit('unit_new/set_dialog_new', true)
        
        },

        add_pack () {        
            this.$store.commit('pack_new/set_common', ['edit', false])
            this.$store.commit('pack_new/set_common', ['pack_name', ''])
            this.$store.commit('pack_new/set_common', ['pack_code', ''])
            this.$store.commit('pack_new/set_dialog_new', true)
            return false
        },

        purchase_history(v) {
            this.$store.commit('item_logpurchase/set_object', ['purchases', []])
            this.$store.commit('item_logpurchase/set_object', ['selected_purchase', null])
            this.$store.commit('item_logpurchase/set_common', ['item_name', '-'])

            for (let i of this.items) {
                if (i.item_id == v.item_id) {
                    this.$store.commit('item_logpurchase/set_object', ['purchases', i.log_purchase])        
                    this.$store.commit('item_logpurchase/set_common', ['item_name', i.item_name])
                }
            }
            this.$store.commit('item_logpurchase/set_common', ['dialog', true])
        }
    },

    mounted () {
        this.$store.dispatch('offer_new/search_customer')
        this.$store.dispatch('offer_new/search_staff')
        this.$store.dispatch('offer_new/search_item')
        this.$store.dispatch('offer_new/search_paymentplan')
        this.$store.dispatch('offer_new/search_term')
        this.$store.dispatch('offer_new/search_pack')
        this.$store.dispatch('offer_new/search_unit')
    },

    watch : {}
}
</script>