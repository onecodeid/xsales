<template>
    <v-layout row wrap>
        <v-flex xs12>
            
            <v-layout row wrap>
                <v-flex xs12>
                    <v-card>
                        <v-card-title primary-title class="py-2 px-3 cyan white--text">
                            <v-layout row wrap>
                                <v-flex xs7><h3 class="subheading">PERKIRAAN</h3></v-flex>
                                <v-flex xs4><h3 class="subheading text-xs-right">NOMINAL</h3></v-flex>
                                <v-flex xs1 class="text-xs-center">
                                    <v-icon>delete</v-icon>
                                </v-flex>
                            </v-layout>
                        </v-card-title>
                        <v-card-text class="px-3 py-2">
                            <v-layout row wrap v-for="(d, n) in details" :key="n" :class="{'mt-2':n>0}">
                                <v-flex xs7 pr-3>
                                    <v-text-field
                                        solo
                                        flat
                                        :value="d.account?d.account.account_code+'  '+d.account.account_name:''"
                                        readonly
                                        clearable
                                        @click:clear="update_account(n, null)"
                                        v-show="!!d.account"
                                        hide-details
                                    ></v-text-field>
                                    <v-autocomplete
                                        :items="accounts"
                                        :value="d.account"
                                        return-object
                                        solo
                                        v-show="!d.account"
                                        :clearable="d.post!='Y'"
                                        :readonly="!!d.account"
                                        @change="update_account(n, $event)"
                                        item-text="account_name"
                                        item-value="account_id"
                                        placeholder="Pilih..."
                                        item-disabled="parent"
                                        hide-details
                                        :flat=!!d.account
                                    >
                                        <template slot="item" slot-scope="data">
                                            <div class="v-list__tile__content">
                                                <div class="v-list__tile__title"><span class="blue--text mr-2">{{data.item.account_code}}</span> {{data.item.account_name}}</div> 
                                            </div>
                                            
                                        </template>

                                        <template slot="selection" slot-scope="data">
                                            <v-layout row wrap>
                                                <div class="v-list__tile__title"><span class="blue--text mr-2">{{data.item.account_code}}</span> {{data.item.account_name}}</div>
                                            </v-layout>
                                            
                                            
                                        </template>

                                        <!-- <template slot="prepend">
                                            <v-btn color="red" class="ma-0 mr-1" icon :dark="false" flat @click="del_detail(n)" :disabled="d.post=='Y'"><v-icon>delete</v-icon></v-btn>
                                        </template> -->
                                    </v-autocomplete>
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

<script>
module.exports = {
    computed : {
        details () {
            return this.$store.state.cash_receive.details
        },

        total () {
            let x = 0
            for (let j of this.details) {
                x = x + Math.round(j.amount)
            }
            return {amount:x}
        },

        accounts () {
            return this.$store.state.cash_receive.accounts
        },

        btn_add_enabled () {
            let d = this.details[this.details.length-1]
            if (!d) return false
            if (!d.account) return false

            if (this.$store.state.cash_receive.edit) {
                let j = this.$store.state.cash_receive
                if (!!j.payment_post) return false
            }
            
            return true
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        one_mask_money (x) {
            return window.one_mask_money(x)
        },

        update_account(idx, v) {
            let d = this.details
            d[idx].account = v
            this.set_details()
        },

        update_amount (idx, side, amount) {
            let d = this.details            
            d[idx][side] = amount
            // this.$store.commit('cash_invoice/set_details', d)
        },

        add_detail () {
            let d = this.details
            let dfl = JSON.parse(JSON.stringify(this.$store.state.cash_receive.detail_default))
            d.push(dfl)
            this.set_details()
        },

        del_detail (x) {
            let d = this.details
            d.splice(x, 1)
            if (d.length < 1)
                this.add_detail()
            this.set_details()
        },

        set_details () {
            this.$store.commit('cash_receive/set_details', this.details)
        }
    }
}
</script>