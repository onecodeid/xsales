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
                    <span v-show="!edit">PENJUALAN BARU</span>
                    <span v-show="!!edit"><span v-show="!view">UBAH </span>DATA PENJUALAN</span>
                </h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                        <v-layout row wrap>
                            <v-flex xs2 pr-4>
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

                                <!-- <v-flex xs6 pr-4> -->
                                        <!-- <v-text-field
                                            label="Nomor Ref Pelanggan"
                                            v-model="sales_ref"
                                            :disabled="view"
                                        ></v-text-field> -->
                                    <!-- </v-flex> -->
                            </v-flex>
                            <v-flex xs4>
                                <v-autocomplete
                                    :items="customers"
                                    v-model="selected_customer"
                                    return-object
                                    item-text="customer_name"
                                    item-value="customer_id"
                                    label="Customer"
                                    :disabled="(details.length>0 && !!details[0].item) || !!selected_offer"
                                    :loading="customers.length<1"
                                >
                                    <template slot="append-outer">
                                        <v-btn color="success" class="ma-0 ml-2 btn-icon" @click="add_customer" v-show="!view">
                                            <v-icon>add</v-icon>
                                        </v-btn> 
                                    </template>
                                </v-autocomplete>

                                <!-- <v-select
                                    :items="staffs"
                                    v-model="selected_staff"
                                    return-object
                                    item-text="staff_name"
                                    item-value="staff_id"
                                    label="Sales"
                                    :disabled="!!selected_offer"
                                ></v-select> -->

                                <v-select
                                    :items="offers"
                                    v-model="selected_offer"
                                    label="Nomor Penawaran"
                                    return-object
                                    item-text="sales_number"
                                    item-value="sales_id"
                                    clearable
                                    v-show="!selected_offer"
                                >
                                    <template slot="item" slot-scope="data">
                                        <div>
                                            {{data.item.sales_date}} / {{data.item.sales_number}}
                                        </div>
                                    </template>
                                </v-select>

                                <v-text-field
                                    label="Nomor Penawaran"
                                    :value="selected_offer.sales_date + ' / ' + selected_offer.sales_number"
                                    v-if="!!selected_offer"
                                    :clearable="!view"
                                    @click:clear="selected_offer=null"
                                    readonly
                                    persistent-hint
                                    
                                >
                                    <!-- <template slot="hint">anu</template> -->
                                </v-text-field>
                                
                            </v-flex>
                            
                            <v-flex xs6>
                                <!-- <v-layout row wrap>
                                    <v-flex xs12 pl-4>
                                        
                                    </v-flex>    
                                </v-layout> -->
                                <v-layout row wrap>
                                    <v-flex xs6 pl-4>

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
                                            :error="!selected_address"
                                            :error-count="!selected_address?1:0"
                                            :error-messages="!selected_address?['Harus dipilih']:[]"
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
                                            <template slot="append-outer">
                                                <v-btn color="success" class="ma-0 ml-2 btn-icon" @click="add_address" v-show="!view">
                                                    <v-icon>add</v-icon>
                                                </v-btn> 
                                            </template>
                                        </v-select>

                                        <div v-if="!!selected_address" class="caption mt-2 mb-2">
                                            {{selected_address.address_desc}}<br>
                                            Kab / Kota {{selected_address.city_name}} Prop {{selected_address.province_name}}<br>
                                            PIC : <b>{{selected_address.address_pic}}</b><br>
                                            Phone : {{selected_address.address_phone}}
                                        </div>
                                        <!-- END OF DELIVERY ADDRESS -->

                                        <!-- EXPEDITION -->
                                        <!-- <v-select
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

                                            <template slot="append-outer">
                                                <v-btn color="success" class="ma-0 ml-2 btn-icon" @click="add_exp" v-show="!view" :disabled="!!view">
                                                    <v-icon>add</v-icon>
                                                </v-btn> 
                                            </template>
                                        </v-select> -->

                                        <!-- <v-text-field
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

                                            <template slot="append-outer">
                                                <v-btn color="primary" class="ma-0 ml-2 btn-icon" @click="cancel_exp" v-show="!view" :disabled="!!view">
                                                    <v-icon>refresh</v-icon>
                                                </v-btn> 
                                            </template>
                                        </v-text-field> -->
                                        <!-- END OF EXPEDITION -->

                                        <!-- <v-textarea
                                            label="Catatan"
                                            rows="4"
                                            v-model="sales_note"
                                            outline
                                        ></v-textarea> -->
                                        <!-- <v-layout row wrap>
                                            <v-flex xs12>
                                                <v-checkbox label="Harga termasuk PPN?" v-model="sales_ppn" true-value="Y" false-value="N"
                                                    :readonly="!!view"></v-checkbox>        
                                            </v-flex>
                                        </v-layout> -->
                                        
                                    </v-flex>

                                    <v-flex xs6 pl-4>
                                        <v-layout row wrap>
                                            <v-flex xs12>
                                                <v-select
                                                    :items="terms"
                                                    v-model="selected_term"
                                                    return-object
                                                    item-text="term_name"
                                                    item-value="term_id"
                                                    label="Metode Pembayaran"
                                                    :disabled="!!view"
                                                    :error="!selected_term"
                                                    :error-count="!selected_term?1:0"
                                                    :error-messages="!selected_term?['Harus dipilih']:[]"
                                                    
                                                >
                                                    <template slot="selection" slot-scope="data">
                                                        <span style="font-size:0.9em">{{data.item.term_name}}</span>
                                                    </template>
                                                </v-select>
                                            </v-flex>
                                            <!-- <v-flex xs4>
                                                <v-checkbox label="X" v-model="sales_ppn" true-value="Y" false-value="N"
                                                    :readonly="!!view">
                                                    <template slot="label">
                                                        <span class="caption">
                                                            Termasuk PPN?
                                                        </span>
                                                    </template>
                                                </v-checkbox> 
                                            </v-flex> -->
                                        </v-layout>
                                        

                                        <!-- <v-textarea
                                            label="Memo"
                                            rows="1"
                                            v-model="sales_memo"
                                            outline
                                            :disabled="!!view"
                                            hide-details
                                            class="mb-1"
                                        ></v-textarea> -->

                                        <v-textarea
                                            label="Catatan"
                                            rows="1"
                                            v-model="sales_note"
                                            outline
                                            :disabled="!!view"
                                            hide-details
                                            class="mb-1"
                                        ></v-textarea>
                                    </v-flex>        
                                </v-layout>
                                
                            </v-flex>
                            
                            


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
                                            <v-flex xs5 pr-3>
                                                <v-autocomplete
                                                    :items="items"
                                                    :value="d.item"
                                                    return-object
                                                    solo
                                                    hide-details
                                                    clearable
                                                    :readonly="!!d.item"
                                                    @change="update_item(n, $event)"
                                                    item-text="item_code_name"
                                                    item-value="item_id"
                                                    placeholder="Pilih..."
                                                    v-show="!d.item"
                                                >
                                                    <template slot="item" slot-scope="data">
                                                        {{data.item.item_code}} - {{data.item.item_name}}
                                                    </template>

                                                    <template slot="selection" slot-scope="data">
                                                        {{data.item.item_code}} - {{data.item.item_name}}
                                                    </template>

                                                    <template slot="prepend" v-if="!is_sales">
                                                        <v-btn color="red" class="ma-0 mr-1" icon :dark="false" flat @click="del_detail(n)" ><v-icon>delete</v-icon></v-btn>
                                                    </template>
                                                </v-autocomplete>

                                                <v-layout row wrap v-if="!!d.item">
                                                    <v-flex xs12>
                                                        <v-text-field
                                                            solo
                                                            :value="d.item.item_code + ' - ' + d.item.item_name"
                                                            hide-details
                                                            :clearable="!view"
                                                            readonly
                                                            @click:clear="update_item(n, null)"
                                                            @dblclick="change_item_name(d)"
                                                            >
                                                            <template slot="prepend" v-if="!is_sales">
                                                                <v-btn color="red" class="ma-0 mr-1" icon :dark="false" flat @click="del_detail(n)" :disabled="!!view" ><v-icon>delete</v-icon></v-btn>
                                                            </template>
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
                                                        ></v-text-field>
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
                                                        style="margin-top:0px !important"
                                                        :disabled="!!view"></v-checkbox>
                                                    </v-flex> -->
                                                    <v-flex xs4 pr-2>
                                                        <v-text-field
                                                            label=""
                                                            solo
                                                            hide-details
                                                            :value="Math.round(d.subtotal)"
                                                            reverse
                                                            dense
                                                            :mask="one_mask_money(Math.round(d.subtotal))"
                                                            suffix="Rp"
                                                            readonly
                                                        ></v-text-field>
                                                    </v-flex>
                                                </v-layout>    
                                            </v-flex>
                                            
                                        </v-layout>

                                        <v-layout row wrap v-show="!view">
                                            <v-flex xs3 pt-5>
                                                <!-- <sales-order-new-affiliate></sales-order-new-affiliate> -->
                                                <sales-order-new-proforma v-if="sales_proforma=='Y'"></sales-order-new-proforma>
                                            </v-flex>
                                            <v-flex xs2 class="text-xs-center" pt-2 offset-md2>
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

                                                <!-- PPN -->
                                                <!-- <v-layout row wrap v-show="ppn_total>-1">
                                                    <v-flex xs4 pl-2 pb-2 pt-0 pr-4 offset-xs4>
                                                        <span class="subheading">PPN</span>
                                                    </v-flex>
                                                    <v-flex xs4 px-2 pb-2 pt-0 class="text-xs-right">
                                                        <span class="caption">Rp</span> <span class="subheading">{{one_money(ppn_total)}}</span>
                                                    </v-flex>
                                                </v-layout> -->
                                                <!-- <v-layout row wrap v-show="ppn_total>0">
                                                    <v-flex xs4 pl-2 pb-2 pt-0 pr-4 offset-xs4>
                                                        <span class="subheading">TOTAL</span>
                                                    </v-flex>
                                                    <v-flex xs4 px-2 pb-2 pt-0 class="text-xs-right">
                                                        <span class="caption">Rp</span> <span class="subheading">{{one_money(ppn_total+invoice_subtotal)}}</span>
                                                    </v-flex>
                                                </v-layout> -->

                                                <!-- DISCOUNT -->
                                                <v-layout row wrap>
                                                    <v-flex xs4 pa-2 pt-1 pr-4 offset-xs4>
                                                        <span class="subheading">Potongan</span>
                                                    </v-flex>
                                                    
                                                    <v-flex xs4>
                                                        <v-text-field
                                                            solo
                                                            reverse
                                                            v-model="sales_disc"
                                                            v-show="sales_disctype=='P'"
                                                            dense

                                                            depressed
                                                            hide-details
                                                        >
                                                            <template slot="prepend">
                                                                <v-btn small class="orange ma-0 btn-icon" @click="set_disc('R')" depressed dark>%</v-btn>
                                                            </template>
                                                        </v-text-field>
                                                        <v-text-field
                                                            solo
                                                            reverse
                                                            v-model="sales_discrp"
                                                            v-show="sales_disctype=='R'"
                                                            dense
                                                            :mask="one_mask_money(sales_discrp)"
                                                            depressed
                                                            hide-details
                                                        >
                                                            <template slot="prepend">
                                                                <v-btn small class="orange ma-0 btn-icon" @click="set_disc('P')" depressed dark>Rp</v-btn>
                                                            </template>
                                                        </v-text-field>
                                                    </v-flex>
                                                </v-layout>
                                                <!-- END OF DISCOUNT -->

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
                                                            <template slot="prepend">
                                                                <v-btn small class="grey ma-0 btn-icon" depressed :dark="false" disabled>Rp</v-btn>
                                                            </template>
                                                        </v-text-field>
                                                    </v-flex>
                                                </v-layout>
                                                <!-- END OF SHIPPING -->

                                                <!-- DP -->
                                                <!-- <v-layout row wrap>
                                                    <v-flex xs4 pa-2 pt-1 pr-4 offset-xs4>
                                                        <span class="subheading">DP</span>
                                                    </v-flex>
                                                    
                                                    <v-flex xs4>
                                                        <v-text-field
                                                            solo
                                                            reverse
                                                            v-model="sales_dp"
                                                            dense
                                                            :mask="one_mask_money(sales_dp)"
                                                            depressed
                                                            hide-details
                                                        >
                                                            <template slot="prepend">
                                                                <v-btn small class="grey ma-0 btn-icon" depressed :dark="false" disabled>Rp</v-btn>
                                                            </template>
                                                        </v-text-field>
                                                    </v-flex>
                                                </v-layout> -->
                                                <!-- END OF DP -->

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
                <v-btn color="orange" @click="deliver" v-show="delivery" dark>Buat Pengiriman</v-btn>
            </v-card-actions>
        </v-card>

        <sales-order-new-delivery-address></sales-order-new-delivery-address>
        <master-item-log-purchase></master-item-log-purchase>
        <sales-order-item-name></sales-order-item-name>
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
var rnd5 = Math.floor(Math.random() * 1000000)
module.exports = {
    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        "sales-order-new-delivery-address" : httpVueLoader("./sales-order-new-delivery-address.vue?t="+rnd5),
        "sales-order-new-proforma" : httpVueLoader("./sales-order-new-proforma.vue?t="+rnd5),
        "sales-order-new-affiliate" : httpVueLoader("./sales-order-new-affiliate.vue?t="+rnd5),
        "sales-order-item-name" : httpVueLoader("./sales-order-item-name.vue?t="+rnd5),
        "master-item-log-purchase" : httpVueLoader("../../master-item/components/master-item-log-purchase.vue?t="+rnd5)
    },

    data () {
        return { }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.sales_new.dialog_new },
            set (v) { this.$store.commit('sales_new/set_common', ['dialog_new', v]) }
        },

        ppn () {
            return this.$store.state.system.conf.ppn
        },

        sales_note : {
            get () { return this.$store.state.sales_new.sales_note },
            set (v) { this.$store.commit('sales_new/set_common', ['sales_note', v]) }
        },

        sales_memo : {
            get () { return this.$store.state.sales_new.sales_memo },
            set (v) { this.$store.commit('sales_new/set_common', ['sales_memo', v]) }
        },

        sales_number : {
            get () { return this.$store.state.sales_new.sales_number },
            set (v) { this.$store.commit('sales_new/set_common', ['sales_number', v]) }
        },

        sales_ref : {
            get () { return this.$store.state.sales_new.sales_ref },
            set (v) { this.$store.commit('sales_new/set_common', ['sales_ref', v]) }
        },

        sales_ppn : {
            get () { return this.$store.state.sales_new.sales_ppn },
            set (v) { 
                this.$store.commit('sales_new/set_common', ['sales_ppn', v]) 
                this.retotal(-1)
            }
        },

        details () {
            return this.$store.state.sales_new.details
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

        ppn_total () {
            let ppn = false
            for (let d of this.details)
                if (d.ppn=="Y") ppn = true
            // new algorithm
            let total = ((parseFloat(this.sales_total) * parseFloat(100-this.sales_disc) / 100 )) 
                        - parseFloat(this.sales_discrp)
            total = total * (!!ppn?(this.ppn/100):0)

            return total

            // let total = 0
            // for (let x of this.details)
            //     total = total + Math.round(x.ppn_amount)
                    
            
            // return total
        },

        sales_date () {
            return this.$store.state.sales_new.sales_date
        },

        sales_post () {
            let x = this.$store.state
            if (!x.sales_new.edit) return false
            if (x.sales.selected_sales.sales_post == 'N') return false
            return true
        },

        items () {
            return this.$store.state.sales_new.items
        },

        btn_save_enabled () {
            // let ttl = this.total
            // if (ttl.debit == 0 && ttl.credit == 0) return false
            // if (ttl.debit != ttl.credit) return false
            // if (this.sales_note == '') return false
            // if (!!this.$store.state.sales_new.save) return false
            // if (!!this.sales_post) return false
            // if (this.view) return false

            // for (let d of this.details)
            //     if (!d.account && (Math.round(d.credit) > 0 || Math.round(d.debit) > 0))
            //         return false
            if (!this.selected_term) return false
            if (!this.selected_address) return false
            if (!this.selected_customer) return false
            if (!this.selected_staff) return false
            if (!this.selected_offer) return false

            return true
        },

        btn_add_enabled () {
            // let d = this.details[this.details.length-1]
            // if (!d) return false
            // if (!d.account) return false
            // if (this.is_sales) return false
            // if (this.view) return false

            // if (this.$store.state.sales_new.edit) {
            //     let j = this.$store.state.sales.selected_sales
            //     if (j.sales_post=='Y') return false
            // }

            
            
            return true
        },

        is_sales() {
            if (this.$store.state.filter.indexOf("J.03")>-1)
                return true
            return false
        },

        edit() {
            return this.$store.state.sales_new.edit
        },

        view () {
            return this.$store.state.sales_new.view
        },

        delivery () {
            return this.$store.state.sales_new.delivery
        },

        customers () {
            return this.$store.state.sales_new.customers
        },

        selected_customer : {
            get () { return this.$store.state.sales_new.selected_customer },
            set (v) { 
                this.$store.commit('sales_new/set_selected_customer', v)
                this.$store.dispatch('sales_new/search_offer')
                this.$store.dispatch('sales_new/search_address')
            }
        },

        sales_total () {
            let total = 0
            for (let x of this.details)
                // new algorithm
                total = total + Math.round(x.netto)
                // total = total + Math.round(x.subtotal)
            return total
        },

        sales_shipping : {
            get () { return this.$store.state.sales_new.sales_shipping },
            set (v) { this.$store.commit('sales_new/set_common', ['sales_shipping', v]) }
        },

        sales_dp : {
            get () { return this.$store.state.sales_new.sales_dp },
            set (v) { this.$store.commit('sales_new/set_common', ['sales_dp', v]) }
        },

        sales_grandtotal () {
            // new algorithm
            let ppn = false
            for (let d of this.details)
                if (d.ppn=="Y") ppn = true
            // new algorithm
            let total = ((parseFloat(this.sales_total) * parseFloat(100-this.sales_disc) / 100 )) 
                        - parseFloat(this.sales_discrp)
            total = total + (!!ppn?(this.ppn_total):0)
                        + parseFloat(this.sales_shipping)
                        - parseFloat(this.sales_dp)

            return total

            // return ((parseFloat(this.sales_total) * parseFloat(100-this.sales_disc) / 100 )) 
            //         - parseFloat(this.sales_discrp) 
            //         + parseFloat(this.sales_shipping)
            //         + parseFloat(this.ppn_total)
            //         - parseFloat(this.sales_dp)

            // return ((parseFloat(this.sales_total) * parseFloat(100-this.sales_disc) / 100 ) + parseFloat(this.ppn_total)) - parseFloat(this.invoice_discrp) - parseFloat(this.invoice_dp) + parseFloat(this.invoice_shipping)
            // return parseFloat(this.sales_total) + parseFloat(this.sales_shipping)
        },

        staffs () {
            return this.$store.state.sales_new.staffs
        },

        selected_staff : {
            get () { return this.$store.state.sales_new.selected_staff },
            set (v) { 
                this.$store.commit('sales_new/set_selected_staff', v)
                this.$store.dispatch('sales_new/search_offer')
            }
        },

        offers () {
            return this.$store.state.sales_new.offers
        },

        selected_offer : {
            get () { return this.$store.state.sales_new.selected_offer },
            set (v) { 
                this.$store.commit('sales_new/set_selected_offer', v)
                if (!this.edit) {
                    this.sales_shipping = v.sales_shipping
                    let xxx = []
                    for (let soff of v.details) {
                        xxx.push(
                            {
                            "ppn": soff.ppn,
                            "ppn_amount": soff.ppn_amount,
                            "qty": soff.qty,
                            "disc": soff.disc,
                            "item": soff.item,
                            "price": soff.price,
                            "total": soff.total,
                            "subtotal": soff.subtotal,
                            "discrp": soff.discrp,
                            "disctype": soff.disctype
                            }
                        )
                        // {ppn_amount:0,total:0,subtotal:0,disctype:'P'}
                    }
                    
                    this.$store.commit('sales_new/set_details', xxx)
                }
            }
        },

        sales_disc : {
            get () { return this.$store.state.sales_new.sales_disc },
            set (v) { this.$store.commit('sales_new/set_common', ['sales_disc', v]) }
        },

        sales_discrp : {
            get () { return this.$store.state.sales_new.sales_discrp },
            set (v) { this.$store.commit('sales_new/set_common', ['sales_discrp', v]) }
        },

        sales_disctype : {
            get () { return this.$store.state.sales_new.sales_disctype },
            set (v) { this.$store.commit('sales_new/set_common', ['sales_disctype', v]) }
        },

        // ppn () { return this.$store.state.sales_new.ppn },

        addresses () {
            return this.$store.state.sales_new.addresses
        },

        selected_address : {
            get () { return this.$store.state.sales_new.selected_address },
            set (v) { 
                this.$store.commit('sales_new/set_selected_address', v)
            }
        },

        address_desc () {
            let add = this.selected_address
            if (!add) return ''
            return add.address_desc +
                " Kota " + add.city_name +
                " Prop " + add.province_name
        },

        paymentplans () {
            return this.$store.state.sales_new.paymentplans
        },

        selected_paymentplan : {
            get () { return this.$store.state.sales_new.selected_paymentplan },
            set (v) { this.$store.commit('sales_new/set_selected_paymentplan', v) }
        },

        terms () {
            return this.$store.state.sales_new.terms
        },

        selected_term : {
            get () { return this.$store.state.sales_new.selected_term },
            set (v) { this.$store.commit('sales_new/set_selected_term', v) }
        },

        expeditions () {
            return this.$store.state.sales_new.expeditions
        },

        selected_expedition : {
            get () { return this.$store.state.sales_new.selected_expedition },
            set (v) { this.$store.commit('sales_new/set_selected_expedition', v) }
        },

        expedition_name : {
            get () { return this.$store.state.sales_new.expedition_name },
            set (v) { this.$store.commit('sales_new/set_common', ['expedition_name', v]) }
        },

        expedition_mode : {
            get () { return this.$store.state.sales_new.expedition_mode },
            set (v) { this.$store.commit('sales_new/set_common', ['expedition_mode', v]) }
        },

        sales_proforma () {
            return this.$store.state.sales_new.sales_proforma
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
            if (!!this.$store.state.sales_new.proforma)
                this.save_proforma()
            else
                this.$store.dispatch('sales_new/save')
        },

        update_amount (idx, side, amount) {
            let d = this.details            
            d[idx][side] = amount
            this.retotal(idx)
        },

        add_detail () {
            let d = this.details
            let dfl = JSON.parse(JSON.stringify(this.$store.state.sales_new.detail_default))
            d.push(dfl)
            this.$store.commit('sales_new/set_details', d)
        },

        set_details () {
            this.$store.commit('sales_new/set_details', this.details)
        },

        del_detail (x) {
            let d = this.details
            d.splice(x, 1)
            this.$store.commit('sales_new/set_details', d)
        },

        change_sales_date(x) {
            this.$store.commit('sales_new/set_common', ['sales_date', x.new_date])
        },

        update_item(idx, v) {
            let d = this.details
            d[idx].item = v
            d[idx].price = v.item_price
            
            this.$store.commit('sales_new/set_details', d)
        },

        change_ppn(idx, v) {
            let d = this.details

            // patch, one ppn all ppn
            for (let i in d) {
                d[i].ppn = v
                this.retotal(i)    
            }

            // d[idx].ppn = v
            // this.retotal(idx)
        },

        retotal(idx) {
            let d = this.details
            let ppn = (parseInt(this.ppn)) / 100

            if (idx == -1) {
                
                for (let idxn in d) {
                    if (d[idxn].disctype=='P') {
                        d[idxn].total = d[idxn].price * d[idxn].qty * (100 - d[idxn].disc) * (d[idxn].ppn == "Y" && this.sales_ppn == "N"? (ppn+1) :1) / 100
                        d[idxn].subtotal = d[idxn].price * d[idxn].qty * (100 - d[idxn].disc) / 100

                        // new algorithm
                        d[idxn].netto = (
                            (d[idxn].price * d[idxn].qty * (100 - d[idxn].disc) / 100) 
                                - d[idxn].discrp) / (d[idxn].ppn == "Y" && this.sales_ppn == "Y"? (ppn+1) :1)
                    }
                    else {
                        d[idxn].total = (d[idxn].price - d[idxn].discrp) * d[idxn].qty * (d[idxn].ppn == "Y" && this.sales_ppn == "N"? (ppn+1) :1)
                        d[idxn].subtotal = (d[idxn].price - d[idxn].discrp) * d[idxn].qty

                        // new algorithm
                        d[idxn].netto = (
                            (d[idxn].price * d[idxn].qty * (100 - d[idxn].disc) / 100) 
                                - d[idxn].discrp) / (d[idxn].ppn == "Y" && this.sales_ppn == "Y"? (ppn+1) :1)
                    }
                        
                    d[idxn].ppn_amount = d[idxn].subtotal * (d[idxn].ppn == "Y" && this.sales_ppn == "N"? ppn : 0)
                    d[idxn].total = Math.round(d[idxn].total)
                }
            } else {
                if (d[idx].disctype=='P') {
                    d[idx].total = d[idx].price * d[idx].qty * (100 - d[idx].disc) * (d[idx].ppn == "Y" && this.sales_ppn == "N"? (ppn+1) :1) / 100
                    d[idx].subtotal = d[idx].price * d[idx].qty * (100 - d[idx].disc) / 100

                    // new algorithm
                        d[idx].netto = (
                            (d[idx].price * d[idx].qty * (100 - d[idx].disc) / 100) 
                                - d[idx].discrp) / (d[idx].ppn == "Y" && this.sales_ppn == "Y"? (ppn+1) :1)
                }
                else {
                    d[idx].total = (d[idx].price - d[idx].discrp) * d[idx].qty * (d[idx].ppn == "Y" && this.sales_ppn == "N"? (ppn+1) :1)
                    d[idx].subtotal = (d[idx].price - d[idx].discrp) * d[idx].qty

                    // new algorithm
                        d[idx].netto = (
                            (d[idx].price * d[idx].qty * (100 - d[idx].disc) / 100) 
                                - d[idx].discrp) / (d[idx].ppn == "Y" && this.sales_ppn == "Y"? (ppn+1) :1)
                }
                    
                d[idx].ppn_amount = d[idx].subtotal * (d[idx].ppn == "Y" && this.sales_ppn == "N"? ppn : 0)
                d[idx].total = Math.round(d[idx].total)
            }
        },

        set_disc (idx, type) {
            if (isNaN(idx)) {
                this.sales_disctype = idx
                if (idx=='R') this.sales_disc = 0
                if (idx=='P') this.sales_discrp = 0
            }
                
            else {
                let d = this.details            
                d[idx]['disctype'] = type
                this.retotal(-1)
            }
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
            this.$store.commit('customer_new/set_common', ['customer_prospect', 'N'])
            this.$store.commit('customer_new/set_phones', [])        
            
            this.$store.commit('customer_new/set_selected_province', null)
            this.$store.commit('customer_new/set_selected_city', null)
            this.$store.commit('customer_new/set_selected_district', null)
            this.$store.commit('customer_new/set_selected_village', null)
            this.$store.commit('customer_new/set_selected_customer_type', 
                this.$store.state.customer_new.customer_types[0])
            this.$store.commit('customer_new/set_dialog_new', true)
        },

        add_exp () {
            this.expedition_mode = "N"
            this.selected_expedition = null
            // this.$store.commit('expedition_new/set_dialog_new', true)
        },

        cancel_exp () {
            this.expedition_mode = "E"
            this.expedition_name = ''
            // this.$store.commit('expedition_new/set_dialog_new', true)
        },

        add_address () {
            this.$store.commit('address/set_common', ['edit', false])
            this.$store.commit('address/set_common', ['address_id', 0])
            this.$store.commit('address/set_common', ['address_name', ''])
            this.$store.commit('address/set_common', ['address_desc', ''])
            this.$store.commit('address/set_common', ['address_pic_name', ''])
            this.$store.commit('address/set_common', ['address_postcode', ''])
            this.$store.commit('address/set_common', ['address_phone', ''])
            this.$store.commit('address/set_selected_province', null)
            this.$store.commit('address/set_selected_city', null)
            this.$store.commit('address/set_selected_district', null)
            this.$store.commit('address/set_selected_village', null)
            this.$store.commit('address/set_phones', [])

            this.$store.commit('address/set_common', ['dialog_new', true])
        },

        deliver () {
            this.$emit('deliver')
        },

        save_proforma () {
            this.$store.dispatch('sales_new/save_proforma')
        },

        change_item_name (x) {
            this.$store.commit('sales_new/set_object', ['custom_item_name', x.item.item_name])
            this.$store.commit('sales_new/set_object', ['selected_item', x])
            this.$store.commit('sales_new/set_common', ['dialog_itemname', true])
        }
    },

    mounted () {
        this.$store.dispatch('sales_new/search_customer')
        this.$store.dispatch('sales_new/search_staff')
        this.$store.dispatch('sales_new/search_item')
        this.$store.dispatch('sales_new/search_paymentplan')
        this.$store.dispatch('sales_new/search_term')
        this.$store.dispatch('sales_new/search_expedition')
        this.$store.dispatch('sales_new/search_affiliate')
    },

    watch : {}
}
</script>