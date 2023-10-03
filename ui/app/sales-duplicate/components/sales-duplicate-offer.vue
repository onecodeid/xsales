<template>
    <v-card>
        <v-card-title primary-title class="cyan white--text pt-3">
            <h3>
                <span>DUPLIKAT TRANSAKSI :: PENAWARAN</span>
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
                                        :loading="customers.length<1"
                                    >
                                        <template slot="append-outer">
                                            <v-btn color="success" class="ma-0 ml-2 btn-icon" @click="add_customer" :disabled="sales_used=='Y'">
                                                <v-icon>add</v-icon>
                                            </v-btn> 
                                        </template>
                                    </v-autocomplete>

                                    <v-layout row wrap>
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
                                    </v-layout>
                                </v-flex>
                                <v-flex xs6 pr-1>
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
                                </v-flex>
                            </v-layout>
                        </v-flex>
                                
                        <v-flex xs5 pl-5>
                            <v-layout row wrap>
                                <v-flex xs4 pr-2>
                                    <v-select
                                        :items="leadtypes"
                                        v-model="selected_leadtype"
                                        return-object
                                        item-text="leadtype_name"
                                        item-value="leadtype_id"
                                        label="Tipe Prospek"
                                    ></v-select>
                                </v-flex>
                                <v-flex xs8>
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
                            

                            <v-text-field
                                label="Franco"
                                v-model="sales_franco"
                                :disabled="view"
                            ></v-text-field>

                            <v-text-field
                                label="Rencana Pengiriman"
                                v-model="sales_delivery"
                                :disabled="view"
                            ></v-text-field>
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
        </v-card-actions>
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
        "sales-duplicate-detail" : httpVueLoader("./sales-duplicate-detail.vue?t="+rnd5)
    },

    data () {
        return { }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.duplicateNew.dialog_new },
            set (v) { this.$store.commit('duplicateNew/set_common', ['dialog_new', v]) }
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

        sales_ppn : {
            get () { return this.$store.state.duplicateNew.sales_ppn },
            set (v) { 
                this.$store.commit('duplicateNew/set_common', ['sales_ppn', v]) 
                this.retotal(-1)
            }
        },

        sales_franco : {
            get () { return this.$store.state.duplicateNew.sales_franco },
            set (v) { this.$store.commit('duplicateNew/set_common', ['sales_franco', v]) }
        },

        sales_delivery : {
            get () { return this.$store.state.duplicateNew.sales_delivery },
            set (v) { this.$store.commit('duplicateNew/set_common', ['sales_delivery', v]) }
        },

        sales_validity : {
            get () { return this.$store.state.duplicateNew.sales_validity },
            set (v) { this.$store.commit('duplicateNew/set_common', ['sales_validity', v]) }
        },

        sales_used () {
            return this.$store.state.duplicateNew.sales_used
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

        units () {
            return this.$store.state.duplicateNew.units
        },

        packs () {
            return this.$store.state.duplicateNew.packs
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
            return this.$store.state.duplicateNew.edit
        },

        view () {
            return this.$store.state.duplicateNew.view
        },

        customers () {
            return this.$store.state.duplicateNew.customers
        },

        selected_customer : {
            get () { return this.$store.state.duplicateNew.selected_customer },
            set (v) { this.$store.commit('duplicateNew/set_selected_customer', v) }
        },

        sales_total () {
            let total = 0
            for (let x of this.details)
                total = total + Math.round(x.total)
            return total
        },

        sales_shipping : {
            get () { return this.$store.state.duplicateNew.sales_shipping },
            set (v) { this.$store.commit('duplicateNew/set_common', ['sales_shipping', v]) }
        },

        sales_grandtotal () {
            return parseFloat(this.sales_total) + parseFloat(this.sales_shipping)
        },

        staffs () {
            return this.$store.state.duplicateNew.staffs
        },

        selected_staff : {
            get () { return this.$store.state.duplicateNew.selected_staff },
            set (v) { this.$store.commit('duplicateNew/set_selected_staff', v) }
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

        leadtypes () {
            return this.$store.state.duplicateNew.leadtypes
        },

        selected_leadtype : {
            get () { return this.$store.state.duplicateNew.selected_leadtype },
            set (v) { this.$store.commit('duplicateNew/set_selected_leadtype', v) }
        },

        source_id () {
            return this.$store.state.duplicateNew.source_id
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        one_mask_money (x) {
            return window.one_mask_money(x)
        },

        setObject (x, y) {
            this.$store.commit('duplicateNew/set_object', [x, y])
        },

        save () {
            this.$store.dispatch('duplicateNew/save')
        },

        update_amount (idx, side, amount) {
            let d = this.details            
            d[idx][side] = amount
            this.retotal(idx)
            // this.$store.commit('duplicateNew/set_details', d)
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
            d[idx].price = v.item_price
            
            this.$store.commit('duplicateNew/set_details', d)
        },

        update_pack(idx, v) {
            let d = this.details
            d[idx].pack = v            
            this.$store.commit('duplicateNew/set_details', d)
        },

        update_unit(idx, v) {
            let d = this.details
            d[idx].unit = v
            this.$store.commit('duplicateNew/set_details', d)
        },

        change_ppn(idx, v) {
            let d = this.details
            d[idx].ppn = v
            this.retotal(idx)
        },

        retotal(idx) {
            let d = this.details
            if (idx == -1) {
                for (let idxn in d) {
                    if (d[idxn].disctype=='P')
                        d[idxn].total = d[idxn].price * d[idxn].qty * (100 - d[idxn].disc) * (d[idxn].ppn == "Y" && this.sales_ppn == "N"?1.1:1) / 100
                    else
                        d[idxn].total = (d[idxn].price - d[idxn].discrp) * d[idxn].qty * (d[idxn].ppn == "Y" && this.sales_ppn == "N"?1.1:1)
                    d[idxn].total = Math.round(d[idxn].total)
                }
            } else {
                if (d[idx].disctype=='P')
                    d[idx].total = d[idx].price * d[idx].qty * (100 - d[idx].disc) * (d[idx].ppn == "Y" && this.sales_ppn == "N"?1.1:1) / 100
                else
                    d[idx].total = (d[idx].price - d[idx].discrp) * d[idx].qty * (d[idx].ppn == "Y" && this.sales_ppn == "N"?1.1:1)
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
            // this.$store.commit('duplicateNew/set_details', d)
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
        }
    },

    mounted () {

        this.$store.dispatch('duplicateNew/search').then((y) => {

            // set customer
            this.$store.dispatch('duplicateNew/search_customer').then((x) => {
                for (let c of this.customers)
                    if (c.customer_id == y.x_customer) this.selected_customer = c

                // Delivery Address
                if (y.x_deliveryaddress != 0)
                this.$store.dispatch('duplicateNew/search_address').then((x) => {
                    for (let a of x.records)
                        if (a.address_id == y.x_deliveryaddress) this.setObject('selected_address', a)
                })
            })
                    
            // set staff
            this.$store.dispatch('duplicateNew/search_staff').then((x) => {
                for (let s of this.staffs)
                    if (s.staff_id == y.x_staff) this.selected_staff = s
            })  
            
            // set staff
            this.$store.dispatch('duplicateNew/search_leadtype').then((x) => {
                for (let l of this.leadtypes)
                    if (l.leadtype_id == y.offer_lead) this.selected_leadtype = l
            })  

            this.$store.dispatch('duplicateNew/search_term').then((x) => {
                for (let t of this.terms)
                    if (t.term_id == y.x_term) this.selected_term = t
            })

            this.setObject('sales_franco', y.offer_franco)
            this.setObject('sales_delivery', y.offer_delivery)
            this.setObject('sales_note', y.offer_note)
            this.setObject('sales_memo', y.offer_memo)
            this.setObject('sales_ppn', y.x_include_ppn)
            // this.setObject('sales_total', y.sales_total)

            // Delivery Warehouse
            if (y.delivery_warehouse != 0)
            this.$store.dispatch('duplicateNew/search_warehouse').then((x) => {
                for (let w of x.records)
                    if (w.warehouse_id == y.delivery_warehouse) this.setObject('selected_warehouse', w)
            })

            // Delivery Type
            if (y.delivery_type != 0)
            this.$store.dispatch('duplicateNew/search_deliverytype').then((x) => {
                for (let d of x.records)
                    if (d.deliverytype_id == y.delivery_type) this.setObject('selected_deliverytype', d)
            })

            this.$store.dispatch('duplicateNew/search_item').then((x) => {
                this.setObject('details', y.details)
            })
        })
        
        // 
        // this.$store.dispatch('duplicateNew/search_item')
        // this.$store.dispatch('duplicateNew/search_paymentplan')
        // 
        // this.$store.dispatch('duplicateNew/search_pack')
        // this.$store.dispatch('duplicateNew/search_unit')
    },

    watch : {}
}
</script>