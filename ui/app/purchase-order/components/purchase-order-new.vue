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
            <v-card-title primary-title class="cyan white--text pt-3">
                <h3>
                    <span v-show="!edit">PURCHASING BARU</span>
                    <span v-show="!!edit">UBAH DATA PURCHASING</span>
                </h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                        <v-layout row wrap>

                            <v-flex xs2 pr-4>
                                <common-datepicker
                                    label="Tanggal Transaksi"
                                    :date="purchase_date.split('-').reverse().join('-')"
                                    data="0"
                                    @change="change_purchase_date"
                                    classs=""
                                    hints=" "
                                    :details="true"
                                    :solo="false"
                                    v-if="dialog"
                                ></common-datepicker>
                                <!-- <v-text-field
                                    label="Tanggal Transaksi"
                                    :value="purchase_date"
                                    v-show="!!purchase_post||!!is_sales||!!view"
                                    readonly
                                    :disabled="view"
                                ></v-text-field> -->

                                <v-text-field
                                    label="Nomor Faktur"
                                    v-model="purchase_number"
                                    :readonly="false"
                                    :disabled="view"
                                    placeholder="Nomor Faktur"
                                ></v-text-field>
                            </v-flex>
                            <v-flex xs4 pl-4>
                                <v-autocomplete
                                    :items="vendors"
                                    v-model="selected_vendor"
                                    return-object
                                    item-text="vendor_name"
                                    item-value="vendor_id"
                                    label="Vendor / Supplier"
                                    :disabled="details.length>0 && !!details[0].item && !!purchase_done"
                                ></v-autocomplete>

                                <!-- <v-layout row wrap>
                                    <v-flex xs6 pr-2>
                                        <v-select
                                            :items="staffs"
                                            v-model="selected_staff"
                                            return-object
                                            item-text="staff_name"
                                            item-value="staff_id"
                                            label="Sales"
                                            :disabled="!!view"
                                        ></v-select>
                                    </v-flex>
                                    <v-flex xs6 pl-1>
                                        <v-checkbox label="Harga termasuk PPN?" v-model="purchase_ppn" true-value="Y" false-value="N" :disabled="!!view">
                                            <template slot="label"><div class="body-1">Harga termasuk PPN?</div></template>
                                            </v-checkbox>        
                                    </v-flex>
                                </v-layout> -->
                                
                            </v-flex>
                            
                            <v-flex xs3 pl-4>
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
                                
                                <!-- <v-select
                                    :items="paymentplans"
                                    v-model="selected_paymentplan"
                                    return-object
                                    item-text="paymentplan_name"
                                    item-value="paymentplan_id"
                                    label="Metode Pembayaran"
                                ></v-select> -->

                                <!-- <v-text-field
                                    label="Memo"
                                    v-model="purchase_memo"
                                    outline
                                    :readonly="!!view"
                                ></v-text-field>                                 -->
                            </v-flex>

                            <v-flex xs3 pl-4>
                                <v-textarea
                                    label="Catatan"
                                    rows="4"
                                    v-model="purchase_note"
                                    outline
                                    :readonly="!!view"
                                ></v-textarea>
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
                                    <v-card-text class="px-3 py-2" v-show="!!selected_vendor">
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
                                                        <v-btn color="red" class="ma-0 mr-1" icon :dark="false" flat @click="del_detail(n)" :disabled="!!view" ><v-icon>delete</v-icon></v-btn>
                                                    </template>
                                                </v-autocomplete>

                                                <v-layout row wrap v-if="!!d.item">
                                                    <v-flex xs12>
                                                        <v-text-field
                                                            solo
                                                            :value="d.item.item_name"
                                                            hide-details
                                                            :clearable="!view"
                                                            readonly
                                                            @click:clear="update_item(n, null)"
                                                            >
                                                            <template slot="prepend" v-if="!is_sales">
                                                                <v-btn color="red" class="ma-0 mr-1" icon :dark="false" flat @click="del_detail(n)"  :disabled="!!view"><v-icon>delete</v-icon></v-btn>
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
                                                            :mask="one_mask_money(d.discrp)"
                                                            
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
                                                        :input-value="d.ppn"
                                                        hide-details
                                                        @change="change_ppn(n, $event)"
                                                        style="margin-top:0px !important"
                                                        :disabled="!!view"></v-checkbox>
                                                    </v-flex> -->
                                                    <v-flex xs4 pr-2>
                                                        <v-text-field
                                                            label=""
                                                            solo
                                                            hide-details
                                                            :value="one_money(d.itemtotal)"
                                                            reverse
                                                            dense
                                                            suffix="Rp"
                                                            readonly
                                                        ></v-text-field>
                                                    </v-flex>
                                                </v-layout>    
                                            </v-flex>
                                            
                                        </v-layout>

                                        <v-layout row wrap>
                                            <v-flex xs2 class="text-xs-center" pt-2 offset-md5>
                                                <v-btn color="primary btn-icon" @click="add_detail" :disabled="!btn_add_enabled"><v-icon>add</v-icon></v-btn>
                                            </v-flex>
                                            <v-flex xs5 pt-2>
                                                <v-layout row wrap>
                                                    <v-flex xs4 pa-2 pt-3 pr-4 offset-xs4>
                                                        <span class="subheading">SUBTOTAL</span>
                                                    </v-flex>
                                                    <v-flex xs4 pa-2 pt-3 class="text-xs-right">
                                                        <span class="caption">Rp</span> <span class="subheading">{{one_money(purchase_subtotal)}}</span>
                                                    </v-flex>
                                                </v-layout>

                                                <!-- DISCOUNT -->
                                                <v-layout row wrap>
                                                    <v-flex xs4 pa-2 pt-1 pr-4 offset-xs4>
                                                        <span class="subheading">Potongan</span>
                                                    </v-flex>
                                                    
                                                    <v-flex xs4>
                                                        <v-text-field
                                                            solo
                                                            reverse
                                                            v-model="purchase_disc"
                                                            v-show="purchase_disctype=='P'"
                                                            dense
                                                            :readonly="!!purchase_done"
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
                                                            v-model="purchase_discrp"
                                                            v-show="purchase_disctype=='R'"
                                                            dense
                                                            :mask="one_mask_money(purchase_discrp)"
                                                            depressed
                                                            hide-details
                                                            :readonly="!!purchase_done"
                                                        >
                                                            <template slot="prepend">
                                                                <v-btn small class="orange ma-0 btn-icon" @click="set_disc('P')" depressed dark>Rp</v-btn>
                                                            </template>
                                                        </v-text-field>
                                                    </v-flex>
                                                </v-layout>
                                                <!-- END OF DISCOUNT -->
                                                
                                                <v-layout row wrap v-show="ppn_total>0">
                                                    <v-flex xs4 pl-2 pb-2 pt-0 pr-4 offset-xs4>
                                                        <span class="subheading">PPN</span>
                                                    </v-flex>
                                                    <v-flex xs4 px-2 pb-2 pt-0 class="text-xs-right">
                                                        <span class="caption">Rp</span> <span class="subheading">{{one_money(ppn_total)}}</span>
                                                    </v-flex>
                                                </v-layout>

                                                <!-- SHIPPING / ONGKOS KIRIM -->
                                                <v-layout row wrap>
                                                    <v-flex xs4 pa-2 pt-1 pr-4 offset-xs4>
                                                        <span class="subheading">Biaya Pengiriman</span>
                                                    </v-flex>
                                                    
                                                    <v-flex xs4>
                                                        <v-text-field
                                                            solo
                                                            reverse
                                                            v-model="purchase_shipping"
                                                            dense
                                                            :mask="one_mask_money(purchase_shipping)"
                                                            depressed
                                                            hide-details
                                                            :readonly="!!purchase_done"
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
                                                            v-model="purchase_dp"
                                                            dense
                                                            :mask="one_mask_money(purchase_dp)"
                                                            depressed
                                                            hide-details
                                                            :readonly="!!purchase_done"
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
                                                        <span class="caption">Rpx</span> <span class="title">{{one_money(purchase_grandtotal)}}</span>
                                                    </v-flex>
                                                </v-layout>

                                            </v-flex>
                                        </v-layout>
                                        
                                    </v-card-text>
                                    <v-card-text class="px-3 py-2" v-show="!selected_vendor">
                                        <v-layout row wrap>
                                            <v-flex xs12 class="text-xs-center">
                                                Silahkan pilih dulu Vendornya
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

.dialog-new .v-input__append-outer button, .dialog-new .v-input__prepend-outer button {
    min-height: 36px;
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
            get () { return this.$store.state.purchase_new.dialog_new },
            set (v) { this.$store.commit('purchase_new/set_common', ['dialog_new', v]) }
        },

        ppn () {
            return this.$store.state.system.conf.ppn
        },

        purchase_note : {
            get () { return this.$store.state.purchase_new.purchase_note },
            set (v) { this.$store.commit('purchase_new/set_common', ['purchase_note', v]) }
        },

        purchase_memo : {
            get () { return this.$store.state.purchase_new.purchase_memo },
            set (v) { this.$store.commit('purchase_new/set_common', ['purchase_memo', v]) }
        },

        purchase_number : {
            get () { return this.$store.state.purchase_new.purchase_number },
            set (v) { this.$store.commit('purchase_new/set_common', ['purchase_number', v]) }
        },

        purchase_ppn : {
            get () { return this.$store.state.purchase_new.purchase_ppn },
            set (v) { 
                this.$store.commit('purchase_new/set_common', ['purchase_ppn', v]) 
                this.retotal(-1)
            }
        },

        details () {
            return this.$store.state.purchase_new.details
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

        purchase_date () {
            return this.$store.state.purchase_new.purchase_date
        },

        purchase_post () {
            let x = this.$store.state
            if (!x.purchase_new.edit) return false
            if (x.purchase.selected_purchase.purchase_post == 'N') return false
            return true
        },

        items () {
            return this.$store.state.purchase_new.items
        },

        btn_save_enabled () {
            if (!this.selected_vendor)
                return false
            if (this.details.length < 1)
                return false

            if (!!this.purchase_done)
                return false
            // let ttl = this.total
            // if (ttl.debit == 0 && ttl.credit == 0) return false
            // if (ttl.debit != ttl.credit) return false
            // if (this.purchase_note == '') return false
            // if (!!this.$store.state.purchase_new.save) return false
            // if (!!this.purchase_post) return false
            // if (this.view) return false

            // for (let d of this.details)
            //     if (!d.account && (Math.round(d.credit) > 0 || Math.round(d.debit) > 0))
            //         return false

            return true
        },

        btn_add_enabled () {
            if (!this.selected_vendor)
                return false

            if (!!this.purchase_done)
                return false
            // let d = this.details[this.details.length-1]
            // if (!d) return false
            // if (!d.account) return false
            // if (this.is_sales) return false
            // if (this.view) return false

            // if (this.$store.state.purchase_new.edit) {
            //     let j = this.$store.state.purchase.selected_purchase
            //     if (j.purchase_post=='Y') return false
            // }
            
            return true
        },

        is_sales() {
            if (this.$store.state.filter.indexOf("J.03")>-1)
                return true
            return false
        },

        edit() {
            return this.$store.state.purchase_new.edit
        },

        view () {
            if (!!this.purchase_done) return true
            return this.$store.state.purchase_new.view
        },

        vendors () {
            return this.$store.state.purchase_new.vendors
        },

        selected_vendor : {
            get () { return this.$store.state.purchase_new.selected_vendor },
            set (v) { this.$store.commit('purchase_new/set_selected_vendor', v) }
        },

        purchase_total () {
            let total = 0
            for (let x of this.details)
                total = total + Math.round(x.total)
            return total
        },

        ppn_total () {
            // new algorithm
            let ppn = false
            for (let d of this.details)
                if (d.ppn=="Y") ppn = true

            let total = ((parseFloat(this.purchase_subtotal) * parseFloat(100-this.purchase_disc) / 100 )) 
                        - parseFloat(this.purchase_discrp)
            total = total * (!!ppn?(this.ppn/100):0)

            return total

            // let total = 0
            // for (let x of this.details)
                // for (let y of x.items)
                    // total = total + Math.round(x.ppn_amount)
                    
            // return total
        },

        purchase_subtotal () {
            let total = 0
            for (let x of this.details)
                // for (let y of x.items)
                    total = total + Math.round(x.subtotal)
            return total
        },

        staffs () {
            return this.$store.state.purchase_new.staffs
        },

        selected_staff : {
            get () { return this.$store.state.purchase_new.selected_staff },
            set (v) { this.$store.commit('purchase_new/set_selected_staff', v) }
        },

        paymentplans () {
            return this.$store.state.purchase_new.paymentplans
        },

        selected_paymentplan : {
            get () { return this.$store.state.purchase_new.selected_paymentplan },
            set (v) { this.$store.commit('purchase_new/set_selected_paymentplan', v) }
        },

        purchase_done () {
            if (!!this.edit) return (this.$store.state.purchase.selected_purchase.purchase_done=="N"?false:true)
            return false
        },

        purchase_disc : {
            get () { return this.$store.state.purchase_new.purchase_disc },
            set (v) { this.$store.commit('purchase_new/set_common', ['purchase_disc', v]) }
        },

        purchase_discrp : {
            get () { return this.$store.state.purchase_new.purchase_discrp },
            set (v) { this.$store.commit('purchase_new/set_common', ['purchase_discrp', v]) }
        },

        purchase_disctype : {
            get () { return this.$store.state.purchase_new.purchase_disctype },
            set (v) { this.$store.commit('purchase_new/set_common', ['purchase_disctype', v]) }
        },

        purchase_shipping : {
            get () { return this.$store.state.purchase_new.purchase_shipping },
            set (v) { this.$store.commit('purchase_new/set_common', ['purchase_shipping', v]) }
        },

        purchase_grandtotal () {
            // new algorithm
            let ppn = false
            for (let d of this.details)
                if (d.ppn=="Y") ppn = true
            // new algorithm
            let total = ((parseFloat(this.purchase_subtotal) * parseFloat(100-this.purchase_disc) / 100 )) 
                        - parseFloat(this.purchase_discrp)

            total = total + parseFloat((!!ppn?(this.ppn_total):0))
                        + parseFloat(this.purchase_shipping)
                        - parseFloat(this.purchase_dp)

            return total
                        // - parseFloat(this.sales_dp)

            // return ((parseFloat(this.purchase_total) * parseFloat(100-this.purchase_disc) / 100 )) 
            //         - parseFloat(this.purchase_discrp) 
            //         + parseFloat(this.purchase_shipping)
            //         + parseFloat(this.ppn_total)

                    // - parseFloat(this.purchase_dp)
            // return ((parseFloat(this.purchase_total) * parseFloat(100-this.purchase_disc) / 100 ) + parseFloat(this.ppn_total)) - parseFloat(this.invoice_discrp) - parseFloat(this.invoice_dp) + parseFloat(this.invoice_shipping)
            // return parseFloat(this.purchase_total) + parseFloat(this.purchase_shipping)
        },

        purchase_dp : {
            get () { return this.$store.state.purchase_new.purchase_dp },
            set (v) { this.$store.commit('purchase_new/set_common', ['purchase_dp', v]) }
        },

        terms () {
            return this.$store.state.purchase_new.terms
        },

        selected_term : {
            get () { return this.$store.state.purchase_new.selected_term },
            set (v) { this.$store.commit('purchase_new/set_object', ['selected_term', v]) }
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
            this.$store.dispatch('purchase_new/save')
        },

        update_amount (idx, side, amount) {
            let d = this.details            
            d[idx][side] = amount
            this.retotal(idx)
            // this.$store.commit('purchase_new/set_details', d)
        },

        add_detail () {
            let d = this.details
            let dfl = JSON.parse(JSON.stringify(this.$store.state.purchase_new.detail_default))
            d.push(dfl)
            this.$store.commit('purchase_new/set_details', d)
        },

        set_details () {
            this.$store.commit('purchase_new/set_details', this.details)
        },

        del_detail (x) {
            let d = this.details
            d.splice(x, 1)
            this.$store.commit('purchase_new/set_details', d)
        },

        change_purchase_date(x) {
            this.$store.commit('purchase_new/set_common', ['purchase_date', x.new_date])
        },

        update_item(idx, v) {
            let d = this.details
            d[idx].item = v
            d[idx].price = !!v?v.item_price:0
            
            this.$store.commit('purchase_new/set_details', d)
        },

        change_ppn(idx, v) {
            let d = this.details
            d[idx].ppn = v
            this.retotal(idx)
        },

        retotal(idx) {
            let ppn = this.ppn / 100
            console.log(ppn)
            let d = this.details
            if (idx == -1) {
                for (let idxn in d) {
                    d[idxn].subtotal = (d[idxn].price * d[idxn].qty * (100 - d[idxn].disc) / 100) - d[idxn].discrp
                    d[idxn].itemtotal = d[idxn].subtotal
                    d[idxn].total = d[idxn].subtotal
                    d[idxn].ppn_amount = 0
                    if (d[idxn].ppn == "Y") {
                        d[idxn].ppn_amount = d[idxn].subtotal * ppn
                        if (this.purchase_ppn == "Y") {
                            d[idxn].subtotal = d[idxn].subtotal / (1+ppn)
                            d[idxn].ppn_amount = d[idxn].total - d[idxn].subtotal
                        } else {
                            d[idxn].total = d[idxn].subtotal + d[idxn].ppn_amount
                        }
                    }
                }
            } else {
                d[idx].subtotal = (d[idx].price * d[idx].qty * (100 - d[idx].disc) / 100) - d[idx].discrp
                d[idx].itemtotal = d[idx].subtotal
                d[idx].total = d[idx].subtotal
                d[idx].ppn_amount = 0
                if (d[idx].ppn == "Y") {
                    d[idx].ppn_amount = d[idx].subtotal * ppn
                    if (this.purchase_ppn == "Y") {
                        d[idx].subtotal = d[idx].subtotal / (1+ppn)
                        d[idx].ppn_amount = d[idx].total - d[idx].subtotal
                    } else {
                        d[idx].total = d[idx].subtotal + d[idx].ppn_amount
                    }
                }
            }
            this.$store.commit('purchase_new/set_details', d)
        },

        set_disc (idx, type) {
            if (isNaN(idx)) {
                this.purchase_disctype = idx
                if (idx=='R') this.purchase_disc = 0
                if (idx=='P') this.purchase_discrp = 0
            }
                
            else {
                let d = this.details            
                d[idx]['disctype'] = type
                this.retotal(-1)
            }
        }
    },

    mounted () {
        this.$store.dispatch('purchase_new/search_vendor')
        this.$store.dispatch('purchase_new/search_item')
    },

    watch : {}
}
</script>