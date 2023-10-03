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
                    <span v-show="!edit">TAGIHAN [PROFORMA] BARU</span>
                    <span v-show="!!edit">UBAH DATA TAGIHAN [PROFORMA]</span>
                </h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                        <v-layout row wrap>

                            <v-flex xs2>
                                <common-datepicker
                                    label="Tanggal Tagihan"
                                    :date="invoice_date"
                                    data="0"
                                    @change="change_invoice_date"
                                    classs=""
                                    hints=" "
                                    :details="true"
                                    :solo="false"
                                    v-if="dialog"
                                ></common-datepicker>

                                
                                
                                <!-- <v-text-field
                                    label="Tanggal Transaksi"
                                    :value="invoice_date"
                                    v-show="!!invoice_post||!!is_sales||!!view"
                                    readonly
                                    :disabled="view"
                                ></v-text-field> -->

                                <v-text-field
                                    label="Nomor Tagihan"
                                    v-model="invoice_number"
                                    :readonly="true"
                                    :disabled="view"
                                    placeholder="( kosongkan saja )"
                                ></v-text-field>
                            </v-flex>
                            <v-flex xs5 pl-5>
                                <v-layout row wrap>
                                    
                                    <v-flex xs6>
                                        <v-select
                                            :items="terms"
                                            v-model="selected_term"
                                            return-object
                                            item-text="term_name"
                                            item-value="term_id"
                                            label="Termin Pembayaran"
                                        ></v-select>
                                    </v-flex>
                                    <!-- <v-flex xs1>
                                        <v-checkbox 
                                        v-model="invoice_due_date_check"
                                        true-value="Y"
                                        false-value="N"
                                        ></v-checkbox>
                                    </v-flex> -->

                                    <!-- <v-flex xs4 v-show="dialog && tempo">
                                        <common-datepicker
                                            label="Tanggal Jatuh + Tempo"
                                            :date="invoice_due_date"
                                            data="0"
                                            @change="change_invoice_due_date"
                                            classs=""
                                            hints=" "
                                            :details="true"
                                            :solo="false"
                                            v-if="dialog && tempo"
                                        ></common-datepicker>
                                    </v-flex> -->

                                    <v-flex xs4 pl-3>
                                        <v-text-field
                                            label="Tanggal Jatuh Tempo"
                                            v-model="invoice_due_date"
                                            readonly
                                        ></v-text-field>
                                    </v-flex>
                                    
                                </v-layout>

                                <v-autocomplete
                                    :items="customers"
                                    v-model="selected_customer"
                                    return-object
                                    item-text="customer_name"
                                    item-value="customer_id"
                                    label="Customer / Supplier"
                                    :disabled="details.length>0 && !!details[0].delivery"
                                ></v-autocomplete>

                                

                                <!-- <v-layout row wrap>
                                    <v-flex xs6>
                                        <v-checkbox label="Harga termasuk PPN?" v-model="invoice_ppn" true-value="Y" false-value="N"></v-checkbox>        
                                    </v-flex>
                                </v-layout> -->
                                
                            </v-flex>
                            <v-flex xs3 offset-xs-2 offset-lg2 offset-md2>
                                <v-textarea
                                    label="Catatan"
                                    rows="4"
                                    v-model="invoice_note"
                                    outline
                                ></v-textarea>
                            </v-flex>
                            


                            <v-flex xs12>
                                <v-card>
                                    <v-card-title primary-title class="py-2 px-3 cyan white--text">
                                        <v-layout row wrap>
                                            <v-flex xs4><h3 class="subheading text-xs-center">SALES ORDER</h3></v-flex>
                                            <v-flex xs8>
                                                <v-layout row wrap>
                                                    <v-flex xs5 pr-2>
                                                        <h3 class="subheading text-xs-center">NAMA ITEM</h3>
                                                    </v-flex>
                                                    <v-flex xs1 pr-2>
                                                        <h3 class="subheading text-xs-right">QTY</h3>
                                                    </v-flex>
                                                    <v-flex xs2 pr-2>
                                                        <h3 class="subheading text-xs-right">HARGA / DISC</h3>
                                                    </v-flex>
                                                    <v-flex xs2 pr-2>
                                                        <h3 class="subheading text-xs-right">PPN</h3>
                                                    </v-flex>
                                                    <v-flex xs2 pr-2>
                                                        <h3 class="subheading text-xs-right">SUBTOTAL</h3>
                                                    </v-flex>
                                                </v-layout>
                                            </v-flex>
                                        </v-layout>
                                    </v-card-title>
                                    <v-card-text class="px-3 py-2">
                                        <v-layout row wrap v-for="(d, n) in details" :key="n" :class="{'mt-2':n>0}">
                                            <v-flex xs4 pr-3>
                                                <v-autocomplete
                                                    :items="items"
                                                    :value="d.delivery"
                                                    return-object
                                                    solo
                                                    hide-details
                                                    clearable
                                                    :readonly="!!d.delivery"
                                                    @change="update_item(n, $event)"
                                                    item-text="item_name"
                                                    item-value="item_id"
                                                    placeholder="Pilih..."
                                                    v-show="!d.delivery"
                                                >
                                                    <template slot="item" slot-scope="data">
                                                        <v-layout row wrap>
                                                            <v-flex xs12>{{data.item.sales_number}} &nbsp; ({{data.item.sales_date}})</v-flex>
                                                            <v-flex xs12 class="caption blue--text">
                                                                {{data.item.customer_name}} - <span class="red--text">{{data.item.warehouse_name}}</span>
                                                            </v-flex>
                                                        </v-layout> 
                                                    </template>

                                                    <template slot="selection" slot-scope="data">
                                                        <span class="blue--text">{{data.item.purchase_number}}</span> &nbsp; {{data.item.item_name}}
                                                    </template>

                                                    <!-- <template slot="prepend" v-if="!is_sales">
                                                        <v-btn color="red" class="ma-0 mr-1" icon :dark="false" flat @click="del_detail(n)" disabled ><v-icon>delete</v-icon></v-btn>
                                                    </template> -->
                                                </v-autocomplete>

                                                <v-layout row wrap v-if="!!d.delivery">
                                                    <v-flex xs12>
                                                        <v-text-field
                                                            solo
                                                            :value="d.delivery.sales_number + '   (' + d.delivery.sales_date + ')'"
                                                            hide-details
                                                            :clearable="!edit"
                                                            readonly
                                                            @click:clear="update_item(n, null)"
                                                            >
                                                            <!-- <template slot="prepend" v-if="!is_sales">
                                                                <v-btn color="red" class="ma-0 mr-1" icon :dark="false" flat @click="del_detail(n)" disabled><v-icon>delete</v-icon></v-btn>
                                                            </template> -->
                                                        </v-text-field>
                                                    </v-flex>    
                                                    <v-flex xs12 class="caption blue--text pl-5 mt-2">
                                                        {{d.delivery.customer_name}} - <span class="red--text">{{d.delivery.warehouse_name}}</span>
                                                    </v-flex>
                                                    <v-flex xs12 class="caption pl-5 mt-2" v-show="d.delivery.delivery_memo!=''">
                                                        <i>Memo : {{d.delivery.delivery_memo}}</i>
                                                    </v-flex>
                                                    
                                                </v-layout>
                                            </v-flex>
                                            <v-flex xs8>
                                                <v-layout row wrap v-for="(item, m) in d.items" :key="m">
                                                    <v-flex xs5 pa-2>
                                                        {{m+1}}. {{item.item_name}}
                                                    </v-flex>
                                                    <v-flex xs1 class="text-xs-right" pa-2>
                                                        {{item.qty}}
                                                    </v-flex>
                                                    <v-flex xs2 class="text-xs-right" pa-2>
                                                        <span class="caption">Rp</span> <b>{{one_money(item.price)}}</b><br>
                                                        <span class="red--text text-xs-right" v-show="Math.round(item.discrp)==0">disc : {{item.disc}}%</span>
                                                        <span class="red--text text-xs-right" v-show="Math.round(item.discrp)!=0">disc : <span class="caption">Rp</span> {{one_money(item.discrp)}}</span>
                                                    </v-flex>
                                                    <v-flex xs2 class="text-xs-right" pa-2>
                                                        <span v-show="item.ppn=='Y'" class="green--text">PPN</span>
                                                        <span v-show="item.ppn=='N'" class="red--text">Tanpa PPN</span>
                                                        <div v-show="item.ppn=='Y'&&item.include_ppn=='Y'" class="caption">Termasuk PPN</div>
                                                    </v-flex>
                                                    <v-flex xs2 class="text-xs-right" pa-2>
                                                        <span class="caption">Rp</span> <b>{{one_money(item.subtotal)}}</b>
                                                    </v-flex>
                                                    <v-flex xs12>
                                                        <v-divider></v-divider>
                                                    </v-flex>
                                                    
                                                </v-layout>    
                                            </v-flex>
                                            
                                        </v-layout>

                                        <v-layout row wrap v-show="!view">
                                            <v-flex xs5>
                                                <sales-invoice-dp></sales-invoice-dp>
                                            </v-flex>
                                            <v-flex xs2 class="text-xs-center" pt-2>
                                                <!-- <v-btn color="primary btn-icon" @click="add_detail" :disabled="!btn_add_enabled"><v-icon>add</v-icon></v-btn> -->
                                                &nbsp;
                                            </v-flex>
                                            <v-flex xs5 pt-2>
                                                <v-layout row wrap>
                                                    <v-flex xs4 pa-2 pt-3 pr-4 offset-xs4>
                                                        <span class="subheading">SUBTOTAL</span>
                                                    </v-flex>
                                                    <v-flex xs4 pa-2 pt-3 class="text-xs-right">
                                                        <span class="caption">Rp</span> <span class="subheading">{{one_money(invoice_subtotal)}}</span>
                                                    </v-flex>
                                                </v-layout>
                                                <v-layout row wrap v-show="ppn_total>0">
                                                    <v-flex xs4 pl-2 pb-2 pt-0 pr-4 offset-xs4>
                                                        <span class="subheading">PPN</span>
                                                    </v-flex>
                                                    <v-flex xs4 px-2 pb-2 pt-0 class="text-xs-right">
                                                        <span class="caption">Rp</span> <span class="subheading">{{one_money(ppn_total)}}</span>
                                                    </v-flex>
                                                </v-layout>
                                                <v-layout row wrap v-show="ppn_total>0">
                                                    <v-flex xs4 pl-2 pb-2 pt-0 pr-4 offset-xs4>
                                                        <span class="subheading">TOTAL</span>
                                                    </v-flex>
                                                    <v-flex xs4 px-2 pb-2 pt-0 class="text-xs-right">
                                                        <span class="caption">Rp</span> <span class="subheading">{{one_money(ppn_total+invoice_subtotal)}}</span>
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
                                                            v-model="invoice_disc"
                                                            v-show="invoice_disctype=='P'"
                                                            dense
                                                            :mask="one_mask_money(invoice_disc)"
                                                            :disabled="selected_invoice.invoice_paid>0"
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
                                                            v-model="invoice_discrp"
                                                            v-show="invoice_disctype=='R'"
                                                            dense
                                                            :mask="one_mask_money(invoice_discrp)"
                                                            :disabled="selected_invoice.invoice_paid>0"
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
                                                            v-model="invoice_shipping"
                                                            dense
                                                            :mask="one_mask_money(invoice_shipping)"
                                                            :disabled="selected_invoice.invoice_paid>0"
                                                            depressed
                                                            hide-details
                                                        >
                                                        </v-text-field>
                                                    </v-flex>
                                                </v-layout>
                                                <!-- END OF SHIPPING -->

                                                <v-layout row wrap>
                                                    <v-flex xs4 pa-2 pt-3 pr-4 offset-xs4>
                                                        <span class="subheading">Total DP</span>
                                                    </v-flex>
                                                    <v-flex xs4 pa-2 pt-3 class="text-xs-right">
                                                        <span class="caption">Rp</span> <span class="subheading">{{one_money(invoice_dp)}}</span>
                                                    </v-flex>
                                                </v-layout>

                                                <v-layout row wrap>
                                                    <v-flex xs5 pa-2 pt-3 pr-4 offset-xs3>
                                                        <span class="title">TOTAL TAGIHAN</span>
                                                    </v-flex>
                                                    <v-flex xs4 pa-2 pt-3 class="text-xs-right">
                                                        <span class="caption">Rp</span> <span class="title">{{one_money(invoice_total)}}</span>
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

/* .dialog-new .v-input__append-outer {
    margin: 0px !important;
} */

.dialog-new .v-input__prepend-outer button {
    min-height: 36px;
}
</style>
<script>
module.exports = {
    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        'sales-invoice-dp' : httpVueLoader('./sales-invoice-dp.vue')
    },

    data () {
        return {
            tempo: true
         }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.invoice_new.dialog_proforma },
            set (v) { this.$store.commit('invoice_new/set_common', ['dialog_proforma', v]) }
        },

        invoice_note : {
            get () { return this.$store.state.invoice_new.invoice_note },
            set (v) { this.$store.commit('invoice_new/set_common', ['invoice_note', v]) }
        },

        invoice_number : {
            get () { return this.$store.state.invoice_new.invoice_number },
            set (v) { this.$store.commit('invoice_new/set_common', ['invoice_number', v]) }
        },

        invoice_ppn : {
            get () { return this.$store.state.invoice_new.invoice_ppn },
            set (v) { 
                this.$store.commit('invoice_new/set_common', ['invoice_ppn', v]) 
                this.retotal(0)
            }
        },

        invoice_dp () {
            return this.$store.state.invoice_new.invoice_dp
        },

        invoice_shipping : {
            get () { return this.$store.state.invoice_new.invoice_shipping },
            set (v) { this.$store.commit('invoice_new/set_common', ['invoice_shipping', v]) }
        },

        details () {
            return this.$store.state.invoice_new.details
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

        invoice_date () {
            return this.$store.state.invoice_new.invoice_date
        },

        // invoice_due_date () {
        //     return this.$store.state.invoice_new.invoice_due_date
        // },

        invoice_due_date : {
            get () { return this.$store.state.invoice_new.invoice_due_date },
            set (v) { this.$store.commit('invoice_new/set_common', ['invoice_due_date', v]) }
        },

        invoice_due_date_check : {
            get () { return this.$store.state.invoice_new.invoice_due_date_check },
            set (v) { 
                this.$store.commit('invoice_new/set_common', ['invoice_due_date_check', v])
            }
        },

        invoice_post () {
            let x = this.$store.state
            if (!x.invoice_new.edit) return false
            if (x.invoice.selected_invoice.invoice_post == 'N') return false
            return true
        },

        items () {
            return this.$store.state.invoice_new.items
        },

        btn_save_enabled () {
            if (this.selected_invoice.invoice_paid>0)
                return false
            let d = this.details[this.details.length-1]
            if (!d) return false
            if (!d.delivery) return false
            if (this.view) return false
            return true
        },

        btn_add_enabled () {
            return false
            // let d = this.details[this.details.length-1]
            
            // if (!d) return false
            // if (!d.delivery) return false
            
            // if (this.is_sales) return false
            // if (this.view) return false
            
            // return true
        },

        is_sales() {
            if (this.$store.state.filter.indexOf("J.03")>-1)
                return true
            return false
        },

        edit() {
            return this.$store.state.invoice_new.edit
        },

        view () {
            return this.$store.state.invoice_new.view
        },

        customers () {
            return this.$store.state.invoice_new.customers
        },

        selected_customer : {
            get () { return this.$store.state.invoice_new.selected_customer },
            set (v) { 
                this.$store.commit('invoice_new/set_selected_customer', v)
                this.$store.dispatch('invoice_new/search_item')
                this.$store.dispatch('invoice_new/search_dp')
            }
        },

        ppn_total () {
            let total = 0
            for (let x of this.details)
                for (let y of x.items)
                    total = total + Math.round(y.ppn_amount)
                    
            return total
        },

        invoice_subtotal () {
            let total = 0
            for (let x of this.details)
                for (let y of x.items)
                    total = total + Math.round(y.subtotal)
            return total
        },

        invoice_total () {
            return ((parseFloat(this.invoice_subtotal) * parseFloat(100-this.invoice_disc) / 100 ) + parseFloat(this.ppn_total)) - parseFloat(this.invoice_discrp) - parseFloat(this.invoice_dp) + parseFloat(this.invoice_shipping)
        },

        warehouses () {
            return this.$store.state.invoice_new.warehouses
        },

        selected_warehouse : {
            get () { return this.$store.state.invoice_new.selected_warehouse },
            set (v) { 
                this.$store.commit('invoice_new/set_selected_warehouse', v)
                this.$store.dispatch('invoice_new/search_item')
            }
        },

        terms () {
            return this.$store.state.invoice_new.terms
        },

        selected_term : {
            get () { return this.$store.state.invoice_new.selected_term },
            set (v) { 
                this.$store.commit('invoice_new/set_selected_term', v)                 
                this.invoice_due_date = moment(this.invoice_date, "YYYY-MM-DD").add(Math.round(v.term_duration), 'days').format('DD-MM-YYYY')
                }
        },

        invoice_disc : {
            get () { return this.$store.state.invoice_new.invoice_disc },
            set (v) { this.$store.commit('invoice_new/set_common', ['invoice_disc', v]) }
        },

        invoice_discrp : {
            get () { return this.$store.state.invoice_new.invoice_discrp },
            set (v) { this.$store.commit('invoice_new/set_common', ['invoice_discrp', v]) }
        },

        invoice_disctype : {
            get () { return this.$store.state.invoice_new.invoice_disctype },
            set (v) { this.$store.commit('invoice_new/set_common', ['invoice_disctype', v]) }
        },

        selected_invoice () {
            if (this.$store.state.invoice)
                return this.$store.state.invoice.selected_invoice
            return {}
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
            this.$store.dispatch('invoice_new/save_proforma')
        },

        update_amount (idx, side, amount) {
            let d = this.details            
            d[idx][side] = amount
            this.retotal(idx)
            // this.$store.commit('invoice_new/set_details', d)
        },

        add_detail () {
            let d = this.details
            let dfl = JSON.parse(JSON.stringify(this.$store.state.invoice_new.detail_default))
            d.push(dfl)
            this.$store.commit('invoice_new/set_details', d)
        },

        set_details () {
            this.$store.commit('invoice_new/set_details', this.details)
        },

        del_detail (x) {
            let d = this.details
            d.splice(x, 1)
            this.$store.commit('invoice_new/set_details', d)
        },

        change_invoice_date(x) {
            this.$store.commit('invoice_new/set_common', ['invoice_date', x.new_date])
        },

        change_invoice_due_date(x) {
            this.$store.commit('invoice_new/set_common', ['invoice_due_date', x.new_date])
        },

        update_item(idx, v) {
            let d = this.details
            d[idx].delivery = v
            d[idx].items = []
            if (v)
                if (v.items)
                    d[idx].items = v.items
            
            this.$store.commit('invoice_new/set_details', d)
        },

        update_items(items) {
            let d = []
            let dfl = JSON.parse(JSON.stringify(this.$store.state.invoice_new.detail_default))
            for (let i in items) {
                d.push(dfl)
                this.update_item(i, items[i])
            }
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
                    d[idxn].total = d[idxn].price * d[idxn].qty * (100 - d[idxn].disc) * (d[idxn].ppn == "Y" && this.invoice_ppn == "N"?1.1:1) / 100
                }
            } else {
                d[idx].total = d[idx].price * d[idx].qty * (100 - d[idx].disc) * (d[idx].ppn == "Y" && this.invoice_ppn == "N"?1.1:1) / 100
                console.log(d[idx].price)
                console.log(d[idx].qty)
                console.log(d[idx].disc)
                console.log(d[idx].ppn)
                
            }
        },

        set_disc (type) {
            this.invoice_disctype = type
            if (type=='R') this.invoice_disc = 0
            if (type=='P') this.invoice_discrp = 0
        }
    },

    mounted () {
        this.$store.dispatch('invoice_new/search_customer')
        this.$store.dispatch('invoice_new/search_warehouse')
        this.$store.dispatch('invoice_new/search_term')
    },

    watch : {
        invoice_due_date_check (n, o) {
            this.tempo = n == "Y" ? true : false
        }
    }
}
</script>