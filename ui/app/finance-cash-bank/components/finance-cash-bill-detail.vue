<template>
    <v-layout row wrap>
        <v-flex xs12>
            <v-layout row wrap>
                <v-flex xs12>
                    <v-card>
                        <v-card-title primary-title class="py-2 px-3 cyan white--text">
                            <v-layout row wrap>
                                <v-flex xs5><h3 class="subheading">PO NUMBER</h3></v-flex>
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
                                <v-flex xs2 pr-2>
                                    <v-text-field
                                        solo flat readonly clearable v-show="!!d.type"
                                        :value="d.type?d.type.paymentdetail_name:''"
                                        @click:clear="update_type(n, null)"
                                    ></v-text-field>
                                    <v-select
                                        :items="paymentdetails"
                                        return-object solo v-show="!d.type"
                                        item-value="paymentdetail_id"
                                        item-text="paymentdetail_name"
                                        @change="update_type(n, $event)"
                                        :value="d.type"
                                    ></v-select>
                                </v-flex>

                                <v-flex xs5 pr-3>
                                    <v-text-field
                                        solo
                                        flat
                                        :value="d.bill?d.bill.bill_date+'  '+d.bill.bill_number:''"
                                        readonly
                                        clearable
                                        @click:clear="update_bill(n, null)"
                                        v-show="!!d.bill"
                                        :hint="d.bill?'Rp '+one_money(d.bill.bill_total)+' / Rp '+one_money(d.bill.bill_paid):''"
                                        persistent-hint
                                    ></v-text-field>
                                    <v-select
                                        :items="bills"
                                        :value="d.bill"
                                        return-object
                                        solo
                                        v-show="!d.bill"
                                        :clearable="d.post!='Y'"
                                        :readonly="!!d.bill"
                                        @change="update_bill(n, $event)"
                                        item-text="bill_number"
                                        item-value="bill_id"
                                        placeholder="Pilih..."                                                    
                                        persistent-hint
                                        :hint="d.bill?'Rp '+one_money(d.bill.bill_total)+' / Rp '+one_money(d.bill.bill_paid):''"
                                        :flat=!!d.bill
                                    >
                                        <template slot="item" slot-scope="data">
                                            <div class="v-list__tile__content">
                                                <div class="v-list__tile__title"><span class="blue--text mr-2">{{data.item.bill_date}}</span> {{data.item.bill_number}}</div> 
                                                <div class="v-list__tile__sub-title caption"><span class="red--text">Rp {{one_money(data.item.bill_total)}}</span> / Rp {{one_money(data.item.bill_paid)}}</div>
                                            </div>
                                        </template>

                                        <template slot="selection" slot-scope="data">
                                            <v-layout row wrap>
                                                <div class="v-list__tile__title"><span class="blue--text mr-2">{{data.item.bill_date}}</span> {{data.item.bill_number}}</div>
                                            </v-layout>
                                        </template>
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
                                    </v-select>

                                    <v-text-field
                                        solo
                                        flat
                                        :value="d.dp?d.dp.dp_date+'  '+d.dp.dp_number:''"
                                        readonly
                                        clearable
                                        @click:clear="update_dp(n, null)"
                                        v-show="d.type&&d.type.paymentdetail_code=='PAY.DP'&&!!d.dp"
                                        :hint="d.dp?'Rp '+one_money(d.dp.dp_amount)+' / Rp '+one_money(d.dp.dp_used):''"
                                        persistent-hint
                                    ></v-text-field>

                                    <v-select
                                        :items="dps"
                                        :value="d.dp"
                                        return-object
                                        solo
                                        v-show="d.type&&d.type.paymentdetail_code=='PAY.DP'&&!d.dp"
                                        :clearable="d.post!='Y'"
                                        :readonly="!!d.dp"
                                        @change="update_dp(n, $event)"
                                        item-text="dp_number"
                                        item-value="dp_id"
                                        placeholder="Pilih..."                                                    
                                        persistent-hint
                                        hide-details
                                        class="mt-2"
                                    >
                                        <template slot="item" slot-scope="data">
                                            <div class="v-list__tile__content">
                                                <div class="v-list__tile__title"><span class="orange--text mr-2">{{data.item.dp_date}}</span> {{data.item.dp_number}}</div> 
                                                <div class="v-list__tile__sub-title caption"><span class="red--text">Rp {{one_money(data.item.dp_amount)}}</span> / Rp {{one_money(data.item.dp_used)}}</div>
                                            </div>
                                        </template>
                                        <template slot="selection" slot-scope="data">
                                            <v-layout row wrap>
                                                <div class="v-list__tile__title"><span class="blue--text mr-2">{{data.item.dp_date}}</span> {{data.item.dp_number}}</div>         
                                                
                                            </v-layout>
                                        </template>
                                    </v-select>
                                </v-flex>
                                
                                <!-- <v-flex xs1>
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
                                </v-flex> -->
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
            return this.$store.state.cash_bill.details
        },

        total () {
            let x = 0
            for (let j of this.details) {
                x = x + Math.round(j.amount)
            }
            return {amount:x}
        },

        bills () {
            return this.$store.state.cash_bill.bills
        },

        returs () {
            return this.$store.state.cash_bill.returs
        },

        dps () {
            return this.$store.state.cash_bill.dps
        },

        btn_add_enabled () {
            let d = this.details[this.details.length-1]
            if (!d) return false
            if (!d.bill) return false

            if (this.$store.state.cash_bill.edit) {
                let j = this.$store.state.cash_bill
                if (j.payment_post=='Y') return false
            }
            
            return true
        },

        disc () {
            return this.$store.state.cash_bill.disc
        },

        accounts () {
            return this.$store.state.cash.accounts
        },

        selected_account () {
            return this.$store.state.cash.selected_account
        },

        paymentdetails () {
            return this.$store.state.cash.paymentdetails
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
            // this.$store.commit('cash_bill/set_details', d)
        },

        add_detail () {
            let d = this.details
            let dfl = JSON.parse(JSON.stringify(this.$store.state.cash_bill.detail_default))
            d.push(dfl)
            this.$store.commit('cash_bill/set_details', d)
        },

        set_details () {
            this.$store.commit('cash_bill/set_details', this.details)
        },

        del_detail (x) {
            let d = this.details
            d.splice(x, 1)
            if (d.length < 1)
                this.add_detail()
            this.$store.commit('cash_bill/set_details', d)
        },

        update_bill(idx, v) {
            let d = this.details
            d[idx].bill = v
            this.$store.commit('cash_bill/set_details', d)
        },

        update_retur(idx, v) {
            let d = this.details
            d[idx].retur = v
            this.$store.commit('cash_bill/set_details', d)
        },

        update_dp(idx, v) {
            let d = this.details
            d[idx].dp = v
            this.$store.commit('cash_bill/set_details', d)
        },

        update_type(idx, v) {
            let d = this.details
            d[idx].type = v
            d[idx].is_retur = v.is_retur
            d[idx].disc = v.is_disc
            
            this.$store.commit('cash_bill/set_details', d)
        },

        update_disc (idx, v) {
            let d = this.details            
            d[idx]['disc'] = v
            d[idx]['is_retur'] = "N"
            this.$store.commit('cash_bill/set_details', d)
        },

        update_is_retur (idx, v) {
            let d = this.details            
            d[idx]['is_retur'] = v
            d[idx]['disc'] = "N"
            if (v == "N")
                d[idx]['retur'] = null
            this.$store.commit('cash_bill/set_details', d)
        }
    },

    mounted () {
    }
}
</script>