<template>
    <v-card>
        <v-card-title primary-title class="py-2 px-3 amber lighten-1 white--text">
            <v-layout row wrap>
                <v-flex xs6><h3 class="subheading text-xs-center">PRODUK</h3></v-flex>
                <v-flex xs6>
                    <v-layout row wrap>
                        <v-flex xs2 pr-2>
                            <h3 class="subheading text-xs-center">QTY</h3>
                        </v-flex>
                        <v-flex xs2 pr-2>
                            <h3 class="subheading text-xs-center">UNIT</h3>
                        </v-flex>
                        <v-flex xs8 pr-2>
                            <h3 class="subheading text-xs-center">CATATAN</h3>
                        </v-flex>
                    </v-layout>
                </v-flex>
            </v-layout>
        </v-card-title>
        <v-card-text class="px-3 py-2" v-show="!!selected_proforma">
            <v-layout row wrap v-for="(d, n) in details" :key="d.item.detail_id" :class="{'mt-2':n>0}">
                <v-flex xs6 pr-3>
                    <v-layout row wrap v-if="!!d.item">
                        <v-flex xs12>
                            <v-text-field
                                solo
                                :value="d.item.item_name + '   (' + d.item.detail_qty + '/' + d.item.detail_sent + ')'"
                                hide-details
                                readonly
                                >
                            </v-text-field>
                        </v-flex>    
                        <v-flex xs6 class="caption blue--text pl-2 mt-2">
                            No SO : {{d.item.sales_number}}
                        </v-flex>
                        
                        
                    </v-layout>
                </v-flex>


                <v-flex xs6>
                    <v-layout row wrap>
                        <v-flex xs2 pr-2>
                            <v-text-field
                                label=""
                                solo
                                hide-details
                                :value="d.qty"
                                reverse
                                dense
                                readonly
                                :mask="one_mask_money(d.qty)"
                                suffix=""
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs2 pr-2>
                            <v-text-field
                                label=""
                                solo
                                hide-details
                                :value="d.item?d.item.item_unit:''"
                                dense
                                readonly
                                flat
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs8 pr-2>
                            <v-text-field
                                label=""
                                solo
                                hide-details
                                :value="d.note"
                                dense
                            ></v-text-field>
                            <div v-if="!!d.item" v-show="d.item.sales_memo!=''" class="caption mt-2 blue--text">
                                <!-- <v-divider class="my-2"></v-divider> -->
                                <b>Memo purchasing</b> : <i><u>{{d.item.sales_memo}}</u></i>
                            </div>
                        </v-flex>
                    </v-layout>    
                </v-flex>
                
            </v-layout>

            
            
        </v-card-text>

        <v-card-text class="px-3 py-2" v-show="!selected_proforma">
            <v-layout row wrap>
                <v-flex xs12 class="text-xs-center red--text pa-2">
                    Pilih dulu SOnya
                </v-flex>
            </v-layout>
        </v-card-text>
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
</style>
<script>
module.exports = {
    components : {
    },

    data () {
        return {
            
        }
    },

    computed : {
        proformas () {
            return this.$store.state.delivery_new.proformas
        },

        selected_proforma () {
            return this.$store.state.delivery_new.selected_proforma
        },

        details () {
            if (!this.selected_proforma) return []
            return this.selected_proforma.items
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        one_mask_money (x) {
            return window.one_mask_money(x)
        }
    },

    mounted () {
    },

    watch : {}
}
</script>
