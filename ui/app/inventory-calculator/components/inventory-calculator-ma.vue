<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-2 primary white--text">
            <v-layout row wrap>
                <v-flex xs9 class="align-center d-flex">
                    <h3 class="d-flex align-center">KALKULATOR MONTH MOVING AVERAGE (MMA)</h3>
                </v-flex>
                <v-flex xs3>
                    <v-text-field
                        v-model="c2_n"
                        type="number"
                        reverse solo
                        min="1" max="12"
                        @change="change_n" @input="change_n"
                        hide-details
                    >
                        <template slot="append"><div class="blue--text font-weight-bold">n</div></template>
                    </v-text-field>
                </v-flex>
            </v-layout>
            
        </v-card-title>
        <v-card-text class="pt-2">
            <v-layout row wrap>
                <v-flex xs4 pr-2>
                    
                </v-flex>
                <v-flex xs8>
                    &nbsp;
                </v-flex>

                <v-flex xs12>
                    <v-layout row wrap>
                        <v-flex xs1 class="text-xs-right pt-3">
                            <b>MA =</b>
                        </v-flex>
                        <v-flex xs11 class="py-2 pl-2">
                            <v-layout row wrap>
                                <v-flex xs12>
                                    <div class="blue lighten-2 pa-2 text-xs-center"><b><span class="red--text">Σ</span> SALES DALAM PERIODE <span class="red--text">n</span></b>
                                        <v-divider class="my-1 white"></v-divider>
                                        <div class="red--text"><b>n</b></div>
                                    </div>
                                </v-flex>
                            </v-layout>
                            
                        </v-flex>
                    </v-layout>
                    <v-layout row wrap>
                        <v-flex xs1 class="text-xs-right pt-2">
                            <b> =</b>
                        </v-flex>
                        <v-flex xs11 pl-2>
                            <v-layout row wrap>
                                <v-flex xs3 pr-2 v-for="(c, n) in c2_columns" :key="n">
                                    <v-text-field
                                        @change="updateAmount(n, $event)"
                                        type="number" reverse solo
                                        :value="c.amount"
                                    >
                                        <template slot="prepend" v-if="n>0"><v-btn color="grey darken-3 white--text" class="btn-icon ma-0">+</v-btn></template>
                                        <!-- <template slot="append"><div class="blue--text font-weight-bold">[{{ n+1 }}]</div></template> -->
                                    </v-text-field>
                                </v-flex>

                                <v-flex xs3 pr-2>
                                    <v-text-field
                                        v-model="c2_n"
                                        type="number" reverse solo disabled
                                    >
                                        <template slot="prepend"><v-btn color="red darken-3 white--text" class="btn-icon ma-0">:</v-btn></template>
                                        <!-- <template slot="append"><div class="blue--text font-weight-bold">RO</div></template> -->
                                    </v-text-field>
                                </v-flex>
                            </v-layout>
                            
                        </v-flex>
                    </v-layout>
                </v-flex>
                
                
                
                

                <v-flex xs12>
                    <v-layout row wrap>
                        <v-flex xs1 class="text-xs-right pt-2">
                            <b>=</b>
                        </v-flex>
                        <v-flex xs11 pl-2>
                            <v-layout row wrap>
                                <v-flex xs3 pr-2>
                                    <v-text-field
                                        v-model="c2_rst"
                                        reverse
                                        solo
                                        disabled
                                    >
                                        <!-- <template slot="prepend"><v-btn color="orange darken-3 white--text" class="btn-icon ma-0">=</v-btn></template> -->
                                        <!-- <template slot="append"><div class="blue--text font-weight-bold">SS</div></template> -->
                                    </v-text-field>
                                </v-flex>
                            </v-layout>
                        </v-flex>
                    </v-layout>
                    
                </v-flex>
            </v-layout>
        </v-card-text>
    </v-card>
    
</template>

<style scoped>
.v-text-field.v-text-field--solo .v-input__control {
    min-height: 36px;
}
.v-text-field.v-text-field--solo .v-input__prepend-outer {
    margin-top: 0px;
    margin-left: 0px;
}
</style>

<script>
module.exports = {
    components : {
    },

    data () {
        return {
            curr_page: 1,
            xtotal_page: 1,

            c1_ddlt: 0,
            c1_ro: 0,
            c1_ss: 0,
        }
    },

    computed : {
        c2_rst () {
            let rst = 0
            for (let c of this.c2_columns)
                rst += parseFloat(c.amount)
            // return rst / this.c2_n
            return this.one_money(rst / this.c2_n)
        },

        c2_n : {
            get () { return this.$store.state.calc.c2_n },
            set (x) { this.$store.commit('calc/set_object', ['c2_n', x]) }
        },

        c2_columns : {
            get () { return this.$store.state.calc.c2_columns },
            set (v) { this.$store.commit('calc/set_object', ['c2_columns', v]) }
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x, "0,000.00")
        },

        change_n (x) {
           let y = []
           for (let n = 0; n < x; n++) {
                y.push({idx:1, amount:(this.c2_columns[n]?this.c2_columns[n].amount:0)})
            }

            this.c2_columns = y
        }, 
        
        updateAmount (n, x) {
            let c = JSON.parse(JSON.stringify(this.c2_columns))
            c[n].amount = x

            this.c2_columns = c
        }
    },

    mounted () {
        let c = JSON.parse(JSON.stringify(this.c2_columns))
        for (let n = 0; n < this.c2_n; n++) {
            c.push({idx:1, amount:0})
        }

        this.c2_columns = c
    }
}
</script>