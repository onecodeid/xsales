<template>
<v-card class="mt-3">
    <v-card-text class="orange lighten-4">
        <v-layout row wrap>
            <v-flex xs2>
                DP :
            </v-flex>
            <v-flex xs10>
                <v-layout row wrap>
                    <v-flex xs12 v-for="(idp, n) in bill_dps" :key="n">
                        <v-select
                            :items="dps"
                            solo
                            item-value="dp_id"
                            item-text="dp_number"
                            return-object
                            hide-details
                            v-if="!idp.dp"
                            @change="set_detail(n, $event)"
                        >
                            <template slot="item" slot-scope="data">
                                <v-layout row wrap>
                                    <v-flex xs12>
                                        <span class="blue--text">{{data.item.dp_number}}</span>
                                        <span class="ml-4">Tgl {{data.item.dp_date}}</span>
                                    </v-flex>
                                    <v-flex xs12 class="py-1">
                                        <v-divider></v-divider>    
                                    </v-flex>
                                    
                                    <v-flex xs12>
                                        <div class="caption red--text">Jumlah : Rp {{one_money(data.item.dp_amount)}}
                                            <span class="ml-4">Tersedia : Rp {{one_money(data.item.dp_unused)}}</span>
                                        </div>
                                    </v-flex>
                                </v-layout>
                            </template>
                        </v-select>

                        <v-layout row wrap v-show="!!idp.dp">
                            <v-flex xs6>
                                <v-text-field
                                    v-if="!!idp.dp"
                                    solo
                                    :value="idp.dp.dp_number"
                                    clearable
                                    readonly
                                    @click:clear="clear_detail(n)"
                                    persistent-hint
                                    :hint="'Rp '+one_money(idp.dp.dp_amount)+' / Rp '+one_money(idp.dp.dp_unused)"
                                    color="red"
                                    :background-color="idp.dp.dp_acc=='Y'?'white':'red'"
                                >
                                    <template slot="hint">anu</template>
                                </v-text-field>
                            </v-flex>
                            <v-flex xs5 pl-3>
                                <v-text-field
                                    solo
                                    reverse    
                                    :value="idp.amount"
                                    suffix="Rp"
                                    @input="set_amount(n, $event)"
                                    :mask="one_mask_money(idp.amount)"
                                ></v-text-field>
                            </v-flex>
                            <v-flex xs1>
                                <v-btn color="red" class="ma-0 mr-1" icon :dark="false" flat @click="del_detail(n)" ><v-icon>delete</v-icon></v-btn>
                            </v-flex>
                        </v-layout>
                        

                        
                    </v-flex>

                    <v-flex xs12 class="text-xs-center">
                        <v-btn color="primary btn-icon" @click="add_detail"><v-icon>add</v-icon></v-btn>
                    </v-flex>        
                </v-layout>
            </v-flex>
        </v-layout>
    </v-card-text>
</v-card>
</template>

<script>
module.exports = {
    computed : {
        dps () {
            return this.$store.state.bill_new.dps
        },

        bill_dps : {
            get () { return this.$store.state.bill_new.bill_dps },
            set (v) { 
                this.$store.commit('bill_new/set_bill_dps', v) 
            }
        }
    },

    methods : {
        add_detail () {
            let x = JSON.parse(JSON.stringify(this.bill_dps))
            x.push(this.$store.state.bill_new.bill_dp_default)

            this.bill_dps = x
        },

        clear_detail (n) {
            let x = JSON.parse(JSON.stringify(this.bill_dps))
            x[n].dp = null

            this.bill_dps = x
        },

        set_detail (n, y) {
            let x = JSON.parse(JSON.stringify(this.bill_dps))
            x[n].dp = y
            x[n].amount = y.dp_unused

            this.bill_dps = x
            this.retotal()
        },

        del_detail (n) {
            let x = JSON.parse(JSON.stringify(this.bill_dps))
            x.splice(n, 1)

            this.bill_dps = x
            this.retotal()
        },

        set_amount (n, y) {
            let x = JSON.parse(JSON.stringify(this.bill_dps))
            x[n].amount = y

            this.bill_dps = x
            this.retotal()
        },

        one_money (x) {
            return window.one_money(x)
        },

        one_mask_money (x) {
            return window.one_mask_money(x)
        },

        retotal () {
            let total = 0
            for (let d of this.bill_dps)
                total += parseFloat(d.amount)

            this.$store.commit('bill_new/set_common', ['bill_dp', total])
        }
    }
}
</script>
