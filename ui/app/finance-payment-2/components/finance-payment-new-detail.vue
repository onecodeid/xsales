<template>
    <div>
        <v-layout row wrap v-for="(d, n) in details" :key="n" :class="{'mt-2':n>0}">
            <v-flex xs7 pr-3>
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
        </v-layout>

        <v-layout row wrap>
            <v-flex xs12 class="text-xs-center" pt-2>
                <v-btn color="primary btn-icon" @click="add_detail"><v-icon>add</v-icon></v-btn>
            </v-flex>
        </v-layout>

        <v-layout row wrap>
            <v-flex xs12 pt-1 pb-1>
                <v-divider></v-divider>
            </v-flex>
        </v-layout>
    </div>
</template>

<script>
module.exports = {
    components : {
    },

    data () {
        return {
            tempo: true
         }
    },

    computed : {
        payment_total () {
            let total = 0
            for (let x of this.details)
                for (let y of x.items)
                    total = total + Math.round(y.subtotal)
            return total
        },

        details () {
            return this.$store.state.payment_new.details
        },

        invoices () {
            return this.$store.state.payment_new.invoices
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
            // this.$store.commit('payment_new/set_details', d)
        },

        add_detail () {
            let d = this.details
            let dfl = JSON.parse(JSON.stringify(this.$store.state.payment_new.detail_default))
            d.push(dfl)
            this.$store.commit('payment_new/set_details', d)
        },

        set_details () {
            this.$store.commit('payment_new/set_details', this.details)
        },

        del_detail (x) {
            let d = this.details
            d.splice(x, 1)
            this.$store.commit('payment_new/set_details', d)
        },

        update_invoice(idx, v) {
            let d = this.details
            d[idx].invoice = v
            this.$store.commit('cash_invoice/set_details', d)
        }
    },

    mounted () {
    },

    watch : {
    }
}
</script>