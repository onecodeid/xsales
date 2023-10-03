<template>
    <v-card>
        <v-card-text>
            <v-layout row wrap>
                <v-flex xs12 sm3 md2 lg2>
                    <warehouse-filter></warehouse-filter>
                </v-flex>
                
                <v-flex v-for="(d, n) in data" :key="n" xs12 sm3 md2 lg2 class="px-1">
                    <v-card>
                        <v-card-title primary-title class="pa-2 cyan white--text text-xs-center justify-center">
                            <v-icon class="white--text mr-2">local_shipping</v-icon>{{ d.warehouse_short_name.toUpperCase() }}
                        </v-card-title>
                        <v-card-text class="pa-2">
                            <v-layout row wrap>
                                <v-flex xs6 pr-1>
                                    <v-sheet
                                    class="d-flex justify-center align-center fill-height display-1"
                                    color="grey lighten-3" style="flex-direction: column;"><div>{{ d.delivery_cnt }}</div><div class="caption" style="margin-top:-6px">pengiriman</div></v-sheet>
                                </v-flex>
                                <v-flex xs6>
                                    <v-sheet
                                    class="text-xs-center align-center pa-1 mb-1"
                                    color="yellow lighten-3"><div><b>{{ one_money(d.item_cnt) }}</b></div><div class="caption" style="margin-top:-6px">jenis item</div></v-sheet>
                                    <v-sheet
                                    class="text-xs-center align-center pa-1"
                                    color="cyan lighten-3"><div><b>{{ one_money(d.item_qty) }}</b></div><div class="caption" style="margin-top:-6px">qty item</div></v-sheet>
                                </v-flex>
                            </v-layout>
                        </v-card-text>
                    </v-card>
                </v-flex>
            </v-layout>  
        </v-card-text>
    </v-card>
</template>

<style scoped>
iframe { border: none; width:100%; height:100%; overflow: hidden; }
</style>

<script>
module.exports = {
    components : {
        "warehouse-filter": httpVueLoader("./dashboard-item-warehouse-filter.vue"),
        SheetFooter: {
            functional: true,

            render (h, { children }) {
                return h('v-sheet', {
                    staticClass: 'mt-auto align-center justify-center d-flex',
                    props: {
                    color: 'rgba(0, 0, 0, .36)',
                    dark: true,
                    height: 50
                    }
                }, children)
            }
        },

        SheetHeader: {
            functional: true,

            render (h, { children }) {
                return h('v-sheet', {
                    staticClass: 'mt-auto align-center justify-center d-flex',
                    props: {
                    color: 'rgba(0, 0, 0, .36)',
                    dark: true,
                    height: 50
                    }
                }, children)
            }
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        }
    },

    computed : {
        __s () {
            return this.$store.state.dashboardWarehouse
        },

        data () {
            return this.__s.warehouse001
        }
    }
}