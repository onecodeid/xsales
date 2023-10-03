<template>
        <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <h3>
                    <span>DUPLIKAT TRANSAKSI :: PENJUALAN</span>
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
                                        <v-text-field
                                            label="Nomor Ref Pelanggan"
                                            v-model="sales_ref"
                                            :disabled="view"
                                        ></v-text-field>
                                    <!-- </v-flex> -->

                                <v-select
                                    :items="deliverytypes"
                                    v-model="selected_deliverytype"
                                    return-object
                                    item-text="deliverytype_name"
                                    item-value="deliverytype_id"
                                    label="Jenis Pengiriman"
                                ></v-select>
                            </v-flex>
                            <v-flex xs3>
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

                                <v-layout row wrap>
                                    <v-flex xs12>
                                        <v-select
                                            :items="staffs"
                                            v-model="selected_staff"
                                            return-object
                                            item-text="staff_name"
                                            item-value="staff_id"
                                            label="Sales"
                                            :disabled="!!selected_offer"
                                        ></v-select>
                                    </v-flex>
                                </v-layout>

                                <v-select
                                    :items="warehouses"
                                    v-model="selected_warehouse"
                                    return-object
                                    item-text="warehouse_name"
                                    item-value="warehouse_id"
                                    label="Dikirim dari Gudang"
                                    :disabled="false"
                                ></v-select>
                            </v-flex>
                            
                            <v-flex xs7>
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

                                        <div v-if="!!selected_address" class="caption mt-2">
                                            {{selected_address.address_desc}}<br>
                                            Kab / Kota {{selected_address.city_name}} Prop {{selected_address.province_name}}<br>
                                            PIC : <b>{{selected_address.address_pic}}</b><br>
                                            Phone : {{selected_address.address_phone}}
                                        </div>
                                        <!-- END OF DELIVERY ADDRESS -->

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

                                            <template slot="append-outer">
                                                <v-btn color="success" class="ma-0 ml-2 btn-icon" @click="add_exp" v-show="!view" :disabled="!!view">
                                                    <v-icon>add</v-icon>
                                                </v-btn> 
                                            </template>
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

                                            <template slot="append-outer">
                                                <v-btn color="primary" class="ma-0 ml-2 btn-icon" @click="cancel_exp" v-show="!view" :disabled="!!view">
                                                    <v-icon>refresh</v-icon>
                                                </v-btn> 
                                            </template>
                                        </v-text-field>
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
                                            <v-flex xs7>
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
                                            <v-flex xs5>
                                                <v-checkbox label="X" v-model="sales_ppn" true-value="Y" false-value="N"
                                                    :readonly="!!view">
                                                    <template slot="label">
                                                        <span class="caption">
                                                            Termasuk PPN?
                                                        </span>
                                                    </template>
                                                </v-checkbox> 
                                            </v-flex>
                                        </v-layout>
                                        

                                        <v-textarea
                                            label="Memo"
                                            rows="1"
                                            v-model="sales_memo"
                                            outline
                                            :disabled="!!view"
                                            hide-details
                                            class="mb-1"
                                        ></v-textarea>

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
                                <sales-duplicate-detail></sales-duplicate-detail>                               
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

            <sales-order-new-delivery-address></sales-order-new-delivery-address>
            <!-- <master-item-log-purchase></master-item-log-purchase> -->
            <sales-order-item-name></sales-order-item-name>
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
        "sales-order-new-delivery-address" : httpVueLoader("../../sales-order/components/sales-order-new-delivery-address.vue?t="+rnd5),
        "sales-order-new-proforma" : httpVueLoader("./sales-order-new-proforma.vue?t="+rnd5),
        "sales-order-new-affiliate" : httpVueLoader("../../sales-order/components/sales-order-new-affiliate.vue?t="+rnd5),
        "sales-order-item-name" : httpVueLoader("../../sales-order/components/sales-order-item-name.vue?t="+rnd5),
        "sales-duplicate-detail" : httpVueLoader("./sales-duplicate-detail.vue?t="+rnd5)
        // "master-item-log-purchase" : httpVueLoader("../../master-item/components/master-item-log-purchase.vue?t="+rnd5)
    },

    data () {
        return { }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.duplicateNew.dialog_new },
            set (v) { this.$store.commit('duplicateNew/set_common', ['dialog_new', v]) }
        },

        ppn () {
            return this.$store.state.system.conf.ppn
        },

        sales_note : {
            get () { return this.$store.state.duplicateNew.sales_note },
            set (v) { this.$store.commit('duplicateNew/set_common', ['sales_note', v]) }
        },

        sales_memo : {
            get () { return this.$store.state.duplicateNew.sales_memo },
            set (v) { this.$store.commit('duplicateNew/set_common', ['sales_memo', v]) }
        },

        sales_number : {
            get () { return this.$store.state.duplicateNew.sales_number },
            set (v) { this.$store.commit('duplicateNew/set_common', ['sales_number', v]) }
        },

        sales_ref : {
            get () { return this.$store.state.duplicateNew.sales_ref },
            set (v) { this.$store.commit('duplicateNew/set_common', ['sales_ref', v]) }
        },

        sales_ppn : {
            get () { return this.$store.state.duplicateNew.sales_ppn },
            set (v) { 
                this.$store.commit('duplicateNew/set_common', ['sales_ppn', v]) 
                this.retotal(-1)
            }
        },

        details () {
            return this.$store.state.duplicateNew.details
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
            return this.$store.state.duplicateNew.sales_date
        },

        sales_post () {
            let x = this.$store.state
            if (!x.duplicateNew.edit) return false
            if (x.sales.selected_sales.sales_post == 'N') return false
            return true
        },

        items () {
            return this.$store.state.duplicateNew.items
        },

        btn_save_enabled () {
            // let ttl = this.total
            // if (ttl.debit == 0 && ttl.credit == 0) return false
            // if (ttl.debit != ttl.credit) return false
            // if (this.sales_note == '') return false
            // if (!!this.$store.state.duplicateNew.save) return false
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

            // if (this.$store.state.duplicateNew.edit) {
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
            return this.$store.state.duplicateNew.edit
        },

        view () {
            return this.$store.state.duplicateNew.view
        },

        delivery () {
            return this.$store.state.duplicateNew.delivery
        },

        customers () {
            return this.$store.state.duplicateNew.customers
        },

        selected_customer : {
            get () { return this.$store.state.duplicateNew.selected_customer },
            set (v) { 
                this.$store.commit('duplicateNew/set_selected_customer', v)
                this.$store.dispatch('duplicateNew/search_offer')
                this.$store.dispatch('duplicateNew/search_address')
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
            get () { return this.$store.state.duplicateNew.sales_shipping },
            set (v) { this.$store.commit('duplicateNew/set_common', ['sales_shipping', v]) }
        },

        sales_dp : {
            get () { return this.$store.state.duplicateNew.sales_dp },
            set (v) { this.$store.commit('duplicateNew/set_common', ['sales_dp', v]) }
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
            return this.$store.state.duplicateNew.staffs
        },

        selected_staff : {
            get () { return this.$store.state.duplicateNew.selected_staff },
            set (v) { 
                this.$store.commit('duplicateNew/set_selected_staff', v)
                this.$store.dispatch('duplicateNew/search_offer')
            }
        },

        offers () {
            return this.$store.state.duplicateNew.offers
        },

        selected_offer : {
            get () { return this.$store.state.duplicateNew.selected_offer },
            set (v) { 
                this.$store.commit('duplicateNew/set_selected_offer', v)
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
                    
                    this.$store.commit('duplicateNew/set_details', xxx)
                }
            }
        },

        sales_disc : {
            get () { return this.$store.state.duplicateNew.sales_disc },
            set (v) { this.$store.commit('duplicateNew/set_common', ['sales_disc', v]) }
        },

        sales_discrp : {
            get () { return this.$store.state.duplicateNew.sales_discrp },
            set (v) { this.$store.commit('duplicateNew/set_common', ['sales_discrp', v]) }
        },

        sales_disctype : {
            get () { return this.$store.state.duplicateNew.sales_disctype },
            set (v) { this.$store.commit('duplicateNew/set_common', ['sales_disctype', v]) }
        },

        // ppn () { return this.$store.state.duplicateNew.ppn },

        addresses () {
            return this.$store.state.duplicateNew.addresses
        },

        selected_address : {
            get () { return this.$store.state.duplicateNew.selected_address },
            set (v) { 
                this.$store.commit('duplicateNew/set_selected_address', v)
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
            return this.$store.state.duplicateNew.paymentplans
        },

        selected_paymentplan : {
            get () { return this.$store.state.duplicateNew.selected_paymentplan },
            set (v) { this.$store.commit('duplicateNew/set_selected_paymentplan', v) }
        },

        terms () {
            return this.$store.state.duplicateNew.terms
        },

        selected_term : {
            get () { return this.$store.state.duplicateNew.selected_term },
            set (v) { this.$store.commit('duplicateNew/set_selected_term', v) }
        },

        expeditions () {
            return this.$store.state.duplicateNew.expeditions
        },

        selected_expedition : {
            get () { return this.$store.state.duplicateNew.selected_expedition },
            set (v) { this.$store.commit('duplicateNew/set_selected_expedition', v) }
        },

        expedition_name : {
            get () { return this.$store.state.duplicateNew.expedition_name },
            set (v) { this.$store.commit('duplicateNew/set_common', ['expedition_name', v]) }
        },

        expedition_mode : {
            get () { return this.$store.state.duplicateNew.expedition_mode },
            set (v) { this.$store.commit('duplicateNew/set_common', ['expedition_mode', v]) }
        },

        sales_proforma () {
            return this.$store.state.duplicateNew.sales_proforma
        },

        deliverytypes () {
            return this.$store.state.duplicateNew.deliverytypes
        },

        selected_deliverytype : {
            get () { return this.$store.state.duplicateNew.selected_deliverytype },
            set (v) { 
                this.$store.commit('duplicateNew/set_selected_deliverytype', v)
            }
        },

        warehouses () {
            return this.$store.state.duplicateNew.warehouses
        },

        selected_warehouse : {
            get () { return this.$store.state.duplicateNew.selected_warehouse },
            set (v) { 
                this.$store.commit('duplicateNew/set_selected_warehouse', v)
                this.$store.dispatch('duplicateNew/search_item')
            }
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
            if (!!this.$store.state.duplicateNew.proforma)
                this.save_proforma()
            else
                this.$store.dispatch('duplicateNew/save')
        },

        update_amount (idx, side, amount) {
            let d = this.details            
            d[idx][side] = amount
            this.retotal(idx)
        },

        add_detail () {
            let d = this.details
            let dfl = JSON.parse(JSON.stringify(this.$store.state.duplicateNew.detail_default))
            d.push(dfl)
            this.$store.commit('duplicateNew/set_details', d)
        },

        set_details () {
            this.$store.commit('duplicateNew/set_details', this.details)
        },

        del_detail (x) {
            let d = this.details
            d.splice(x, 1)
            this.$store.commit('duplicateNew/set_details', d)
        },

        change_sales_date(x) {
            this.$store.commit('duplicateNew/set_common', ['sales_date', x.new_date])
        },

        update_item(idx, v) {
            let d = this.details
            d[idx].item = v
            d[idx].price = !!v ? v.item_price : 0
            
            this.$store.commit('duplicateNew/set_details', d)
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
            this.$store.dispatch('duplicateNew/save_proforma')
        },

        change_item_name (x) {
            this.$store.commit('duplicateNew/set_object', ['custom_item_name', x.item.item_name])
            this.$store.commit('duplicateNew/set_object', ['selected_item', x])
            this.$store.commit('duplicateNew/set_common', ['dialog_itemname', true])
        }
    },

    mounted () {
        // this.$store.dispatch('duplicateNew/search_customer')
        // this.$store.dispatch('duplicateNew/search_staff')
        // this.$store.dispatch('duplicateNew/search_item')
        // this.$store.dispatch('duplicateNew/search_paymentplan')
        // this.$store.dispatch('duplicateNew/search_term')
        // this.$store.dispatch('duplicateNew/search_expedition')
        // this.$store.dispatch('duplicateNew/search_affiliate')
    },

    watch : {}
}
</script>