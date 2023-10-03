<template>
    <v-layout row wrap>
        <v-flex xs12>
            <v-layout row wrap>
                <v-flex xs12>
                    <v-card>
                        <v-card-title primary-title class="py-2 px-3 cyan white--text">
                            <v-layout row wrap>
                                <v-flex xs5><h3 class="subheading">INVOICE</h3></v-flex>
                                <v-flex xs1><h3 class="subheading">DISC</h3></v-flex>
                                <v-flex xs1><h3 class="subheading">RETUR</h3></v-flex>
                                <v-flex xs4><h3 class="subheading text-xs-right">NOMINAL</h3></v-flex>
                                <v-flex xs1 class="text-xs-center">
                                    <v-icon>delete</v-icon>
                                </v-flex>
                            </v-layout>
                        </v-card-title>
                        <v-card-text class="px-3 py-2">
                            <v-layout row wrap v-for="(d, n) in details" :key="n" :class="{'mt-2':n>0}">
                                <v-flex xs5 pr-3>
                                    <v-text-field
                                        solo
                                        flat
                                        :value="d.invoice?d.invoice.invoice_date+'  '+d.invoice.invoice_number:''"
                                        readonly
                                        clearable
                                        @click:clear="update_invoice(n, null)"
                                        v-show="!!d.invoice"
                                        :hint="d.invoice?'Rp '+one_money(d.invoice.invoice_total)+' / Rp '+one_money(d.invoice.invoice_paid):''"
                                        persistent-hint
                                    ></v-text-field>

                                    <v-select
                                        :items="invoices"
                                        :value="d.invoice"
                                        return-object
                                        solo
                                        v-show="!d.invoice"
                                        :clearable="d.post!='Y'"
                                        :readonly="!!d.invoice"
                                        @change="update_invoice(n, $event)"
                                        item-text="invoice_number"
                                        item-value="invoice_id"
                                        placeholder="Pilih..."                                                    
                                        persistent-hint
                                        :hint="d.invoice?'Rp '+one_money(d.invoice.invoice_total)+' / Rp '+one_money(d.invoice.invoice_paid):''"
                                        :flat=!!d.invoice
                                    >
                                        <template slot="item" slot-scope="data">
                                            <div class="v-list__tile__content">
                                                <div class="v-list__tile__title"><span class="blue--text mr-2">{{data.item.invoice_date}}</span> {{data.item.invoice_number}}</div> 
                                                <div class="v-list__tile__sub-title caption"><span class="red--text">Rp {{one_money(data.item.invoice_total)}}</span> / Rp {{one_money(data.item.invoice_paid)}}</div>
                                            </div>
                                            
                                        </template>

                                        <template slot="selection" slot-scope="data">
                                            <v-layout row wrap>
                                                <div class="v-list__tile__title"><span class="blue--text mr-2">{{data.item.invoice_date}}</span> {{data.item.invoice_number}}</div>         
                                                
                                            </v-layout>
                                            
                                            
                                        </template>

                                        <!-- <template slot="prepend">
                                            <v-btn color="red" class="ma-0 mr-1" icon :dark="false" flat @click="del_detail(n)" :disabled="d.post=='Y'"><v-icon>delete</v-icon></v-btn>
                                        </template> -->
                                    </v-select>

                                    <v-text-field
                                        solo
                                        flat
                                        :value="d.retur?d.retur.retur_date+'  '+d.retur.retur_number:''"
                                        readonly
                                        clearable
                                        @click:clear="update_retur(n, null)"
                                        v-show="!!d.retur"
                                        :hint="d.retur?'Rp '+one_money(d.retur.retur_total)+' / Rp '+one_money(d.retur.retur_used):''"
                                        persistent-hint
                                        class="retur--text"
                                    ></v-text-field>

                                    <v-select
                                        :items="returs"
                                        :value="d.retur"
                                        return-object
                                        solo
                                        v-show="d.is_retur=='Y'&&!d.retur"
                                        :clearable="d.post!='Y'"
                                        :readonly="!!d.retur"
                                        @change="update_retur(n, $event)"
                                        item-text="retur_number"
                                        item-value="retur_id"
                                        placeholder="Pilih..."                                                    
                                        persistent-hint
                                        :hint="d.retur?'Rp '+one_money(d.retur.retur_total)+' / Rp '+one_money(d.retur.retur_used):''"
                                        :flat=!!d.retur
                                        hide-details
                                    >
                                        <template slot="item" slot-scope="data">
                                            <div class="v-list__tile__content">
                                                <div class="v-list__tile__title"><span class="orange--text mr-2">{{data.item.retur_date}}</span> {{data.item.retur_number}}</div> 
                                                <div class="v-list__tile__sub-title caption"><span class="red--text">Rp {{one_money(data.item.retur_total)}}</span> / Rp {{one_money(data.item.retur_used)}}</div>
                                            </div>
                                            
                                        </template>

                                        <template slot="selection" slot-scope="data">
                                            <v-layout row wrap>
                                                <div class="v-list__tile__title"><span class="blue--text mr-2">{{data.item.retur_date}}</span> {{data.item.retur_number}}</div>         
                                                
                                            </v-layout>
                                            
                                            
                                        </template>

                                        <!-- <template slot="prepend">
                                            <v-btn color="red" class="ma-0 mr-1" icon :dark="false" flat @click="del_detail(n)" :disabled="d.post=='Y'"><v-icon>delete</v-icon></v-btn>
                                        </template> -->
                                    </v-select>
                                </v-flex>

                                <v-flex xs1>
                                    <v-checkbox 
                                    :value="d.disc"
                                    :input-value="d.disc"
                                    true-value="Y"
                                    false-value="N" hide-details
                                    @change="update_disc(n, $event)"></v-checkbox>
                                </v-flex>
                                <v-flex xs1>
                                    <v-checkbox 
                                    :value="d.is_retur"
                                    :input-value="d.is_retur"
                                    true-value="Y"
                                    false-value="N" hide-details
                                    @change="update_is_retur(n, $event)"></v-checkbox>
                                </v-flex>
                                <v-flex xs4>
                                    <v-text-field
                                        label=""
                                        solo
                                        hide-details
                                        :value="d.amount"
                                        reverse
                                        dense
                                        @change="set_details"
                                        @input="update_amount(n, 'amount', $event)"
                                        :mask="one_mask_money(d.amount)"
                                        :readonly="d.post=='Y'"
                                    >
                                        <template slot="append"><span class="grey--text">Rp</span></template>
                                    </v-text-field>
                                </v-flex> 
                                <v-flex xs1 class="text-xs-center">
                                    <v-btn color="red" class="ma-0 mr-1" icon :dark="false" flat @click="del_detail(n)" :disabled="d.post=='Y'"><v-icon>delete</v-icon></v-btn>
                                </v-flex>  

                                <v-flex xs12 pt-1 pb-1>
                                    <v-divider></v-divider>
                                </v-flex>                                         
                            </v-layout>

                            <v-layout row wrap>
                                <v-flex xs12 class="text-xs-center" pt-2>
                                    <v-btn color="primary btn-icon" @click="add_detail" :disabled="!btn_add_enabled"><v-icon>add</v-icon></v-btn>
                                </v-flex>
                            </v-layout>
                            <!-- <finance-payment-new-disc></finance-payment-new-disc> -->
                        </v-card-text>
                    </v-card>

                    
                    <v-layout row wrap>
                        <v-flex xs12 px-3 pt-3>
                            <v-layout row wrap>
                                <v-flex xs7 py-3>
                                    <h3 class="title">TOTAL</h3>
                                </v-flex>
                                <v-flex xs4>
                                    <v-layout row wrap>
                                        <v-flex xs2 py-3>
                                            <h3 class="title text-xs-right font-weight-thin">Rp</h3>
                                        </v-flex>
                                        <v-flex xs10 pr-2 py-3>
                                            <h3 class="title text-xs-right">{{one_money(total.amount)}}</h3>
                                            <!-- <v-text-field
                                                label=""
                                                solo
                                                hide-details
                                                :value="one_money(total.amount)"
                                                reverse
                                                dense
                                                flat
                                                suffix="Rp"
                                                readonly
                                            ></v-text-field> -->
                                        </v-flex>
                                    </v-layout>
                                </v-flex>    
                            </v-layout>
                        </v-flex>
                        
                    </v-layout>
                    
                </v-flex>
            </v-layout>
        </v-flex>
    </v-layout>
</template>

<style scoped>
.v-input--checkbox {
    margin-top: 4px !important;
}

.retur--text input {
    color: #ff9800!important;
    caret-color: #ff9800!important;
}
</style>

<script>
module.exports = {
    data () {
        return { }
    },

    computed : {
        details () {
            return this.$store.state.cash_invoice.details
        },

        total () {
            let x = 0
            for (let j of this.details) {
                x = x + Math.round(j.amount)
            }
            return {amount:x}
        },

        invoices () {
            return this.$store.state.cash_invoice.invoices
        },

        returs () {
            return this.$store.state.cash_invoice.returs
        },

        btn_add_enabled () {
            let d = this.details[this.details.length-1]
            if (!d) return false
            if (!d.invoice) return false

            if (this.$store.state.cash_invoice.edit) {
                let j = this.$store.state.cash_invoice
                if (j.payment_post=='Y') return false
            }
            
            return true
        },

        disc () {
            return this.$store.state.cash_invoice.disc
        },

        accounts () {
            return this.$store.state.cash.accounts
        },

        selected_account () {
            return this.$store.state.cash.selected_account
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
            // this.$store.commit('cash_invoice/set_details', d)
        },

        update_disc (idx, v) {
            let d = this.details            
            d[idx]['disc'] = v
            d[idx]['is_retur'] = "N"
            this.$store.commit('cash_invoice/set_details', d)
        },

        update_is_retur (idx, v) {
            let d = this.details            
            d[idx]['is_retur'] = v
            d[idx]['disc'] = "N"
            if (v == "N")
                d[idx]['retur'] = null
            this.$store.commit('cash_invoice/set_details', d)
        },

        add_detail () {
            let d = this.details
            let dfl = JSON.parse(JSON.stringify(this.$store.state.cash_invoice.detail_default))
            d.push(dfl)
            this.$store.commit('cash_invoice/set_details', d)
        },

        set_details () {
            this.$store.commit('cash_invoice/set_details', this.details)
        },

        del_detail (x) {
            let d = this.details
            d.splice(x, 1)
            if (d.length < 1)
                this.add_detail()
            this.$store.commit('cash_invoice/set_details', d)
        },

        update_invoice(idx, v) {
            let d = this.details
            d[idx].invoice = v
            this.$store.commit('cash_invoice/set_details', d)
        },

        update_retur(idx, v) {
            let d = this.details
            d[idx].retur = v
            this.$store.commit('cash_invoice/set_details', d)
        }
    },

    mounted () {
    }
}
</script>