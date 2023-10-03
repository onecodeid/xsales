<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="500px"
        transition="dialog-transition"
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <h3>INFORMASI STOK</h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12 class="red--text">
                        <b>{{ item.item_name }}</b>
                        <v-divider class="mt-1 mb-3"></v-divider>
                    </v-flex>
                </v-layout>
                <v-layout row wrap v-for="(stock, n) in stocks" :key="n" class="py-1">
                    <v-flex xs1>
                        {{ n+1 }}
                    </v-flex>

                    <v-flex xs6>
                        {{ stock.warehouse_name }}
                    </v-flex>
                    <v-flex xs5 class="text-xs-right">
                        <b>{{ one_money(stock.stock_qty) }}</b> {{ unit }}
                        <v-btn color="orange" dark class="ma-0 ml-2 btn-icon" small @click="print_stock_card(stock)">
                            <v-icon>print</v-icon>
                        </v-btn>
                        
                    </v-flex>
                    <v-flex xs11 offset-xs1>
                        <v-checkbox hide-details :input-value="stock.item_slow" :value="stock.item_slow" true-value="Y" false-value="N" class="ma-0 pa-0" @change="setSlow(stock.warehouse_id, $event)">
                            <template slot="label"><span class="caption">Slow Moving</span></template>
                        </v-checkbox>
                    </v-flex>
                </v-layout>
                <v-divider class="mt-3 mb-1"></v-divider>
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>                
                <v-btn color="primary" flat @click="dialog=!dialog">Tutup</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<style>
.input-dense .v-input__control {
    min-height: 36px !important;
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
        dialog : {
            get () { return this.$store.state.item_new.dialog_stock },
            set (v) { this.$store.commit('item_new/set_common', ['dialog_stock', v]) }
        },

        stocks () {
            return this.$store.state.item_new.stocks
        },

        unit () {
            return this.$store.state.item.selected_item.unit_name
        },

        item () {
            return this.$store.state.item.selected_item
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        print_stock_card(x) {
            this.$store.commit('item_new/set_selected_stock', x)
            this.$store.commit('item/set_common', ['dialog_stock_card', true])
        },

        setSlow(x, y) {
            this.$store.dispatch('item/setSlow', {warehouse_id:x, value:y})
        }
    },

    mounted () {
    },

    watch : {
        
    }
}
</script>