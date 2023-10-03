<template>
        <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <h3>
                    <span>DUPLIKAT TRANSAKSI :: TAGIHAN</span>
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
                                    <v-flex xs8>
                                        <!-- CUSTOMER -->
                                        <v-autocomplete
                                            :items="customers"
                                            v-model="selected_customer"
                                            return-object
                                            item-text="customer_name"
                                            item-value="customer_id"
                                            label="Customer"
                                            :disabled="details.length>0 && !!details[0].delivery"
                                            :loading="customers.length<1"
                                        >
                                            <template slot="prepend-inner">
                                                <v-icon :class="!!selected_customer?'blue--text':'red--text'">people</v-icon>
                                            </template>

                                            <template slot="selection" slot-scope="data">
                                                <span class="blue--text">{{data.item.customer_name}}</span>
                                            </template>
                                        </v-autocomplete>
                                        <!-- END OF CUSTOMER -->
                                    </v-flex>
                                    <v-flex xs4 pl-2>
                                        <v-text-field
                                            label="Sales"
                                            v-model="sales_name"
                                            readonly
                                        ></v-text-field>
                                    </v-flex>
                                </v-layout>
                                
                                
                                <v-layout row wrap>
                                    
                                    <v-flex xs6>
                                        <v-select
                                            :items="terms"
                                            v-model="selected_term"
                                            return-object
                                            item-text="term_name"
                                            item-value="term_id"
                                            label="Termin Pembayaran"
                                            :disabled="!!view"
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

                                

                                

                                <!-- <v-layout row wrap>
                                    <v-flex xs6>
                                        <v-checkbox label="Harga termasuk PPN?" v-model="invoice_ppn" true-value="Y" false-value="N"></v-checkbox>        
                                    </v-flex>
                                </v-layout> -->
                                
                            </v-flex>
                            <v-flex xs5>
                                <v-layout row wrap>
                                    <v-flex xs6 pl-2 pr-1>
                                        <v-textarea
                                            label="Memo"
                                            rows="4"
                                            v-model="invoice_memo"
                                            outline
                                            :readonly="!!view"
                                        ></v-textarea>
                                    </v-flex>
                                    <v-flex xs6 pl-1>
                                        <v-textarea
                                            label="Pesan"
                                            rows="4"
                                            v-model="invoice_note"
                                            outline
                                            :readonly="!!view"
                                        ></v-textarea>
                                    </v-flex>
                                </v-layout>
                            </v-flex>
                            <v-flex xs3 offset-xs-2 offset-lg2 offset-md2>
                                
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

/* .dialog-new .v-input__append-outer {
    margin: 0px !important;
} */

.dialog-new .v-input__prepend-outer button {
    min-height: 36px;
}
</style>
<script>
var rnd5 = Math.floor(Math.random() * 1000000)
module.exports = {
    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        'sales-invoice-dp' : httpVueLoader('./sales-invoice-dp.vue'),
        "sales-duplicate-detail" : httpVueLoader("./sales-duplicate-detail.vue?t="+rnd5)
    },

    data () {
        return {
            tempo: true
         }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.duplicateNew.dialog_new },
            set (v) { this.$store.commit('duplicateNew/set_common', ['dialog_new', v]) }
        },

        ppn () {
            return this.$store.state.system.conf.ppn
        },

        invoice_note : {
            get () { return this.$store.state.duplicateNew.invoice_note },
            set (v) { this.$store.commit('duplicateNew/set_common', ['invoice_note', v]) }
        },

        invoice_memo : {
            get () { return this.$store.state.duplicateNew.invoice_memo },
            set (v) { this.$store.commit('duplicateNew/set_common', ['invoice_memo', v]) }
        },

        invoice_number : {
            get () { return this.$store.state.duplicateNew.invoice_number },
            set (v) { this.$store.commit('duplicateNew/set_common', ['invoice_number', v]) }
        },

        invoice_ppn : {
            get () { return this.$store.state.duplicateNew.invoice_ppn },
            set (v) { 
                this.$store.commit('duplicateNew/set_common', ['invoice_ppn', v]) 
                this.retotal(0)
            }
        },

        invoice_dp () {
            return this.$store.state.duplicateNew.invoice_dp
        },

        invoice_shipping : {
            get () { return this.$store.state.duplicateNew.invoice_shipping },
            set (v) { this.$store.commit('duplicateNew/set_common', ['invoice_shipping', v]) }
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

        invoice_date () {
            return this.$store.state.duplicateNew.invoice_date
        },

        // invoice_due_date () {
        //     return this.$store.state.duplicateNew.invoice_due_date
        // },

        invoice_due_date : {
            get () { return this.$store.state.duplicateNew.invoice_due_date },
            set (v) { this.$store.commit('duplicateNew/set_common', ['invoice_due_date', v]) }
        },

        invoice_due_date_check : {
            get () { return this.$store.state.duplicateNew.invoice_due_date_check },
            set (v) { 
                this.$store.commit('duplicateNew/set_common', ['invoice_due_date_check', v])
            }
        },

        invoice_post () {
            let x = this.$store.state
            if (!x.duplicateNew.edit) return false
            if (x.invoice.selected_invoice.invoice_post == 'N') return false
            return true
        },

        items () {
            return this.$store.state.duplicateNew.items
        },

        btn_save_enabled () {
            if (this.selected_invoice.invoice_paid>0)
                return false
            let d = this.details[this.details.length-1]
            if (!d) return false
            if (!d.delivery) return false
            if (this.view) return false

            if (this.invoice_dps.length > 0) {
                for (let dp of this.invoice_dps) {
                    if (dp.dp.dp_acc && dp.dp.dp_acc == 'N')
                        return false
                }
            }

            return true
        },

        btn_add_enabled () {
            let d = this.details[this.details.length-1]
            
            if (!d) return false
            if (!d.delivery) return false
            
            if (this.is_sales) return false
            if (this.view) return false

            // if (this.$store.state.duplicateNew.edit) {
            //     let j = this.$store.state.invoice.selected_invoice
            //     if (j.invoice_post=='Y') return false
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
            if (this.selected_invoice.invoice_paid>0)
                return true
            return this.$store.state.duplicateNew.view
        },

        customers () {
            return this.$store.state.duplicateNew.customers
        },

        selected_customer : {
            get () { return this.$store.state.duplicateNew.selected_customer },
            set (v) { 
                this.$store.commit('duplicateNew/set_selected_customer', v)
                this.$store.dispatch('duplicateNew/search_item')
                this.$store.dispatch('duplicateNew/search_dp')
            }
        },

        ppn_total () {
            //new algorithm
            // return this.invoice_total * (this.ppn) / 100


            let ppn = false
            for (let d of this.details)
                for (let i of d.items)
                    if (i.ppn=="Y") ppn = true
            // new algorithm
            let total = this.invoice_total
            total = total * (!!ppn?(this.ppn/100):0)

            return total

            // let total = 0
            // for (let x of this.details)
            //     for (let y of x.items)
            //         total = total + Math.round(y.ppn_amount)
                    
            // return total
        },

        invoice_subtotal () {
            let total = 0
            for (let x of this.details)
                for (let y of x.items)
                    // new algorithm
                    total = total + y.subtotal
                    // total = total + Math.round(y.subtotal)
            return total
        },

        invoice_total () {
            // new algorithm
            return ((parseFloat(this.invoice_subtotal) * parseFloat(100-this.invoice_disc) / 100 )) - parseFloat(this.invoice_discrp)
            
            // return ((parseFloat(this.invoice_subtotal) * parseFloat(100-this.invoice_disc) / 100 ) + parseFloat(this.ppn_total)) - parseFloat(this.invoice_discrp) - parseFloat(this.invoice_dp) + parseFloat(this.invoice_shipping)
        },

        invoice_grandtotal () {
            // new algorithm
            return parseFloat(this.invoice_total) - parseFloat(this.invoice_dp) + parseFloat(this.invoice_shipping) + parseFloat(this.ppn_total)
            
            // return ((parseFloat(this.invoice_subtotal) * parseFloat(100-this.invoice_disc) / 100 ) + parseFloat(this.ppn_total)) - parseFloat(this.invoice_discrp) - parseFloat(this.invoice_dp) + parseFloat(this.invoice_shipping)
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
        },

        terms () {
            return this.$store.state.duplicateNew.terms
        },

        selected_term : {
            get () { return this.$store.state.duplicateNew.selected_term },
            set (v) { 
                this.$store.commit('duplicateNew/set_selected_term', v)                 
                this.invoice_due_date = moment(this.invoice_date, "YYYY-MM-DD").add(Math.round(v.term_duration), 'days').format('DD-MM-YYYY')
                }
        },

        invoice_disc : {
            get () { return this.$store.state.duplicateNew.invoice_disc },
            set (v) { this.$store.commit('duplicateNew/set_common', ['invoice_disc', v]) }
        },

        invoice_discrp : {
            get () { return this.$store.state.duplicateNew.invoice_discrp },
            set (v) { this.$store.commit('duplicateNew/set_common', ['invoice_discrp', v]) }
        },

        invoice_disctype : {
            get () { return this.$store.state.duplicateNew.invoice_disctype },
            set (v) { this.$store.commit('duplicateNew/set_common', ['invoice_disctype', v]) }
        },

        selected_invoice () {
            if (this.$store.state.invoice)
                return this.$store.state.invoice.selected_invoice
            return {}
        },

        invoice_dps () {
            return this.$store.state.duplicateNew.invoice_dps
        },

        sales_name () {
            return this.$store.state.duplicateNew.sales_name
        },

        invoice_address () {
            return this.$store.state.duplicateNew.invoice_address
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

        change_invoice_date(x) {
            this.$store.commit('duplicateNew/set_common', ['invoice_date', x.new_date])
        },

        change_invoice_due_date(x) {
            this.$store.commit('duplicateNew/set_common', ['invoice_due_date', x.new_date])
        },

        update_item(idx, v) {
            let d = this.details
            d[idx].delivery = v
            d[idx].items = []
            if (v)
                if (v.items)
                    d[idx].items = v.items
            
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
            let ppn = (100 + this.ppn) / 100
            if (idx == 0) {
                for (let idxn in d) {
                    d[idxn].total = d[idxn].price * d[idxn].qty * (100 - d[idxn].disc) * (d[idxn].ppn == "Y" && this.invoice_ppn == "N"?ppn:1) / 100
                    // d[idxn].total = d[idxn].price * d[idxn].qty * (100 - d[idxn].disc) * (d[idxn].ppn == "Y" && this.invoice_ppn == "N"?ppn:1) / 100
                }
            } else {
                d[idx].total = d[idx].price * d[idx].qty * (100 - d[idx].disc) * (d[idx].ppn == "Y" && this.invoice_ppn == "N"?ppn:1) / 100                
            }
        },

        set_disc (type) {
            this.invoice_disctype = type
            if (type=='R') this.invoice_disc = 0
            if (type=='P') this.invoice_discrp = 0
        }
    },

    mounted () {
        // this.$store.dispatch('duplicateNew/search_customer')
        // this.$store.dispatch('duplicateNew/search_warehouse')
        // this.$store.dispatch('duplicateNew/search_term')
    },

    watch : {
        invoice_due_date_check (n, o) {
            this.tempo = n == "Y" ? true : false
        }
    }
}
</script>