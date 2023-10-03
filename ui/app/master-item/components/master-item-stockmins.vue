<template>
    <v-card>
        <v-card-title primary-title class="white--text cyan pa-2">
            Minimal Stock
        </v-card-title>
        <v-card-text class="pt-2 pb-2 pl-2 pr-1">
            <v-layout row wrap>
                <v-flex xs2 v-for="(stock, n) in stocks" :key="n" mr-1>
                    <v-layout column>
                        <v-flex pa-1 class="zalfa-bg-purple lighten-3 white--text text-xs-center">
                            {{stock.warehouse_short}}
                        </v-flex>
                    </v-layout>

                    <v-text-field
                        :label="stock.warehouse_short"
                        :value="stock.stock_min"
                        solo
                        @change="change_minstock(n, $event)"
                        class="input-dense text-xs-center"
                    ></v-text-field>
                </v-flex>
            </v-layout>
        </v-card-text>
    </v-card>
</template>

<style scoped>
.input-dense .v-input__control {
    min-height: 36px !important;
}
</style>

<script>
module.exports = {
    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        "master-item-pack" : httpVueLoader("./master-item-pack.vue?t=123")
    },

    data () {
        return {
            
        }
    },

    computed : {
        stocks () {
            return this.$store.state.item_new.stocks
        },

        selected_stock () {
            return this.$store.state.item_new.selected_stock
        },

        edit () {
            return this.$store.state.item_new.edit
        },

        view () { return this.$store.state.view }
    },

    methods : {

        change_minstock(n, x) {
            let total = 0
            let stocks = JSON.parse(JSON.stringify(this.stocks))
            stocks[n].stock_min = x

            for (let stock of stocks)
                total += parseFloat(stock.stock_min)

            this.$store.commit('item_new/set_stocks', stocks)
            this.$store.commit('item_new/set_common', ['item_min', total])
        }

        // select_itempack (x) {
        //     this.$store.commit('item_new/set_selected_itempack', x)
        // },

        // add_itempack () {
        //     this.$store.commit('item_new/set_common', ['dialog_pack', true])
        //     this.$store.commit('item_new/set_common', ['edit_pack', false])
        //     this.$store.commit('item_new/set_selected_itempack_pack', null)
        //     this.$store.commit('item_new/set_selected_customer', null)
        // },

        // del (x) {
        //     let itempacks = this.$store.state.item_new.itempacks
        //     itempacks.splice(x, 1)
        //     this.$store.commit('item_new/set_itempacks', itempacks)
        // },

        // edit_itempack (x) {
        //     this.select_pack(x)
        //     this.$store.commit('item_new/set_common', ['dialog_pack', true])
        //     this.$store.commit('item_new/set_common', ['edit_pack', true])
            
        //     this.$store.commit('item_new/set_selected_customer', null)
        //     for (let c of this.$store.state.item_new.customers)
        //         if (c.customer_id == x.customer_id)
        //             this.$store.commit('item_new/set_selected_customer', c)

        //     this.$store.commit('item_new/set_selected_itempack_pack', null)
        //     for (let c of this.$store.state.item_new.packs)
        //         if (c.pack_id == x.pack_id)
        //             this.$store.commit('item_new/set_selected_itempack_pack', c)
        // },

        // select_pack (x) {
        //     this.$store.commit('item_new/set_selected_itempack_pack', x)
        // }
    },

    mounted () {
    },

    watch : {
        
    }
}
</script>