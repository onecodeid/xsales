<template>
    <v-card>
        <v-card-title primary-title class="py-2 px-3 cyan white--text">
            <v-layout row wrap>
                <v-flex xs5><h3 class="subheading">PRODUK</h3></v-flex>
                <v-flex xs7>
                    <v-layout row wrap>
                        <v-flex v-for="(h, n) in [['HARGA','right','xs3 pr-2'], 
                        ['QTY','right','xs2 pr-2'],
                        ['POTONGAN','right','xs2 pr-2'],
                        ['PPN','center','xs1 px-0'],
                        ['SUB TOTAL','right','xs4 pr-2']]" :key="n" :class="h[2]">
                            <h3 :class="'subheading text-xs-'+h[1]">{{ h[0] }}</h3>
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
                            <v-btn color="red" class="ma-0 mr-1" icon :dark="false" flat @click="del_detail(n)" ><v-icon>delete</v-icon></v-btn>
                        </template>
                    </v-autocomplete>

                    <v-layout row wrap v-if="!!d.item">
                        <v-flex xs12>
                            <v-text-field
                            v-if="!!d.item"
                                solo
                                :value="d.item.item_name"
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
                        <v-flex xs3 pr-2>
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
                        <v-flex xs1 pl-2 pt-1>
                            <v-checkbox 
                            true-value="Y"
                            false-value="N" 
                            :value="d.ppn"
                            :input-value="d.ppn" hide-details
                            @change="change_ppn(n, $event)"
                            style="margin-top:0px !important"
                            :disabled="!!view"></v-checkbox>
                        </v-flex>
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
                    <sales-order-new-affiliate></sales-order-new-affiliate>
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
                    <v-layout row wrap v-show="ppn_total>-1">
                        <v-flex xs4 pl-2 pb-2 pt-0 pr-4 offset-xs4>
                            <span class="subheading">PPN</span>
                        </v-flex>
                        <v-flex xs4 px-2 pb-2 pt-0 class="text-xs-right">
                            <span class="caption">Rp</span> <span class="subheading">{{one_money(ppn_total)}}</span>
                        </v-flex>
                    </v-layout>
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
                    <v-layout row wrap>
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
                    </v-layout>
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
    },

    offers () {
        return this.$store.state.duplicateNew.offers
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
    }
},

methods : {
    one_money (x) {
        return window.one_money(x)
    },

    one_mask_money (x) {
        return window.one_mask_money(x)
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

    change_item_name (x) {
        this.$store.commit('duplicateNew/set_object', ['custom_item_name', x.item.item_name])
        this.$store.commit('duplicateNew/set_object', ['selected_item', x])
        this.$store.commit('duplicateNew/set_common', ['dialog_itemname', true])
    }
},

mounted () {
},

watch : {}
}
</script>