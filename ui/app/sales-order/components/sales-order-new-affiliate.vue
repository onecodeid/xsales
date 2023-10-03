<template>
    <v-card>
        <v-card-text>
            <v-layout row wrap>
                <v-flex xs12>
                    <v-select
                        :items="affiliates"
                        v-model="selected_affiliate"
                        return-object
                        item-text="affiliate_name"
                        item-value="affiliate_id"
                        label="Affiliate"
                        clearable
                        placeholder="Kosongkan jika tidak ada"
                    ></v-select>
                </v-flex>

                <v-flex xs12 v-show="!!selected_affiliate">
                    <v-layout row wrap>
                        <v-flex xs6 py-2>
                            <span class="subheading">Komisi</span>
                        </v-flex>

                        <v-flex xs6>
                            <v-text-field
                                solo
                                reverse
                                v-model="affiliate_fee"
                                dense
                                depressed
                                hide-details
                            >
                                <template slot="append-outer">
                                    <v-btn small class="orange ma-0 btn-icon" depressed dark>%</v-btn>
                                </template>
                            </v-text-field>
                        </v-flex>
                    </v-layout>
                </v-flex>
                
                
            </v-layout>
        </v-card-text>
    </v-card>
</template>

<style scoped>
.input-dense .v-input__control {
    min-height: 36px !important;
}

.v-input__prepend-outer {
    margin: 0px !important;
}

.v-text-field.v-text-field--solo .v-input__control {
    min-height: 36px;
    padding: 0;
}

.v-input__append-outer {
    margin: 0px !important;
}

.v-input__append-outer button {
    min-height: 36px;
}
</style>
<script>
module.exports = {
    components : {
    },

    data () {
        return { }
    },

    computed : {
        affiliates () {
            return this.$store.state.sales_new.affiliates
        },

        selected_affiliate : {
            get () { return this.$store.state.sales_new.selected_affiliate },
            set (v) { 
                this.$store.commit('sales_new/set_selected_affiliate', v) 
                if (!v) {
                    this.affiliate_fee = 0
                }
            }
        },

        affiliate_fee : {
            get () { return this.$store.state.sales_new.affiliate_fee },
            set (v) { this.$store.commit('sales_new/set_common', ['affiliate_fee', v]) }
        }
    }
}