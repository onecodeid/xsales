<template>
    <v-card>
        <v-card-title primary-title class="pt-3 pb-3 green white--text">
            <h3 class="d-flex align-center">KALKULATOR KAPAN ORDER (ROP)</h3>
        </v-card-title>
        <v-card-text class="pt-2">
            <v-layout row wrap>
                <v-flex xs1 class="text-xs-right pt-3">
                    <b>ROP =</b>
                </v-flex>
                <v-flex xs11 class="py-2 pl-2">
                    <div class="green lighten-2 pa-2"><b>(LEAD TIME <span class="red--text">X</span> RATA RATA TERJUAL PER HARI) <span class="red--text">+</span> SAFETY STOCK</b></div>
                </v-flex>
            </v-layout>
            <v-layout row wrap>
                <v-flex xs1 class="text-xs-right pt-2">
                    <b>=</b>
                </v-flex>
                <v-flex xs11 pl-2>
                    <v-layout row wrap>
                        <v-flex xs4 pr-2>
                            <v-text-field
                                label="DDLT"
                                v-model="c1_ddlt"
                                type="number"
                                reverse
                                solo
                            >
                                <template slot="append"><div class="blue--text font-weight-bold">LT</div></template>
                            </v-text-field>
                        </v-flex>
                        <v-flex xs4 pr-2>
                            <v-text-field
                                label="RO"
                                v-model="c1_ro"
                                type="number"
                                reverse
                                solo
                            >
                                <template slot="prepend"><v-btn color="grey darken-3 white--text" class="btn-icon ma-0">X</v-btn></template>
                                <template slot="append"><div class="blue--text font-weight-bold">RATA</div></template>
                            </v-text-field>
                        </v-flex>
                        <v-flex xs4>
                            <v-text-field
                                label="SS"
                                v-model="c1_ss"
                                type="number"
                                reverse
                                solo
                            >
                                <template slot="prepend"><v-btn color="grey darken-3 white--text" class="btn-icon ma-0">+</v-btn></template>
                                <template slot="append"><div class="blue--text font-weight-bold">SS</div></template>
                            </v-text-field>
                        </v-flex>
                        <!-- <v-flex xs6>
                            &nbsp;
                        </v-flex> -->
                        
                    </v-layout>
                </v-flex>
            </v-layout>
            <v-layout row wrap>
                <v-flex xs1 class="text-xs-right pt-2">
                    <b>=</b>
                </v-flex>
                <v-flex xs11 pl-2>
                    <v-flex xs4 pr-2>
                        <v-text-field
                            label="DDLT"
                            v-model="c1_rst"
                            reverse
                            solo
                            readonly
                        >
                            <template slot="append"><div class="blue--text font-weight-bold"></div></template>
                        </v-text-field>
                    </v-flex>
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
            c1_ss: 0
        }
    },

    computed : {
        c1_rst () {
            return this.one_money((this.c1_ddlt * this.c1_ro) + parseFloat(this.c1_ss))
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x, "0,000.00")
        }
    },

    mounted () {
        
    }
}
</script>