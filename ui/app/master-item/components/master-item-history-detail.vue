<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="1100px"
        transition="dialog-transition"
        content-class="dialog-history-detail"
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <h3>DETAIL HISTORI TRANSAKSI :: </h3>
            </v-card-title>
            <v-card-text>
                <v-card flat>
                    <v-layout row wrap>
                        <v-flex xs4 v-show="data.customer_name != ''">
                            
                            <div class="caption blue--text">Nama Customer</div>
                            <div class="body-2 mb-2">{{data.customer_name}}</div>

                            <div class="caption blue--text">Info Pengiriman</div>
                            <div class="body-2">XYZ</div>
                            <v-divider class="cyan"></v-divider>
                            <div class="caption cyan--text">— Jl. Ambudarul / by Sales : Rusmana</div>
                        </v-flex>

                        <v-flex xs4>
                            
                        </v-flex>
                    </v-layout>
                    <v-card-title primary-title class="pa-2 zalfa-bg-purple lighten-3 white--text">
                        <v-layout row wrap>
                            <v-flex v-for="(h, n) in headers" :key="n" :class="'xs'+h[1]">{{ h[0]}}</v-flex>
                            <v-flex xs8>
                                <v-layout row wrap>
                                    <v-flex v-for="(h, n) in headers_2" :key="n" :class="'xs'+h[1]" class="text-xs-right">{{ h[0]}}</v-flex>
                                </v-layout>
                            </v-flex>
                        </v-layout>
                    </v-card-title>
                    <v-card-text class="pa-2">
                        <v-layout row wrap v-for="(item, n) in data.items" :key="n">
                            <v-flex xs1>{{ n + 1}}</v-flex>
                            <v-flex xs3>{{ item.item_name }}</v-flex>
                            <v-flex xs8>
                                <v-layout row wrap>
                                    <v-flex xs2 class="text-xs-right">{{ item.item_qty }}</v-flex>
                                    <v-flex xs3 class="text-xs-right">{{ data.currency }} {{ item.item_price }}</v-flex>
                                    <v-flex xs2 class="text-xs-right">{{ data.currency }} {{ item.item_disc }}</v-flex>
                                    <v-flex xs2 class="text-xs-right">{{ data.currency }} {{ item.item_ppn }}</v-flex>
                                    <v-flex xs3 class="text-xs-right">{{ data.currency }} {{ item.item_subtotal }}</v-flex>
                                </v-layout>
                            </v-flex>
                        </v-layout>
                    </v-card-text>
                </v-card>
            </v-card-text>

            <v-card-actions>
                <v-btn color="primary" @click="dialog=!dialog" >Tutup</v-btn>                
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<style>
.input-dense .v-input__control {
    min-height: 36px !important;
}

.dialog-history-detail .v-divider {
    background-color: transparent !important;
    border-top: dashed 1px cyan;
}
</style>
<script>
var t = Math.ceil(Math.random() * 1e10)
module.exports = {
    components : {
    },

    data () {
        return {
            headers : [["NO", 1], ["NAMA ITEM", 3]],
            headers_2 : [["QTY", 2], ["HARGA", 3], ["POTONGAN", 2], ["PPN", 2], ["SUBTOTAL", 3]]
        }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.item.dialog_history_detail },
            set (v) { this.$store.commit('item/set_common', ['dialog_history_detail', v]) }
        },

        data () {
            return this.$store.state.item.history_data
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        }
    },

    mounted () {

    },

    watch : {
       
    }
}
</script>