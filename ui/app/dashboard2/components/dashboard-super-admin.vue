<template>
    <v-layout row wrap>
        
       <!-- <v-flex xs12 sm12 md12 mb-2>
            <item-stat-omzet-product></item-stat-omzet-product>
        </v-flex> -->

        <v-flex xs12 sm6 md6 mb-2 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
            <item-sales-001></item-sales-001>
        </v-flex>
        <v-flex xs0 sm6 md6 v-show="$vuetify.breakpoint.mdAndUp">&nbsp;</v-flex>

        <v-flex xs12>
            <v-layout row wrap>
                <v-flex xs12 sm6 md6 mb-2 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
                    <item-ratio-liquidity></item-ratio-liquidity>
                </v-flex>
                <v-flex xs12 sm6 md6 mb-2 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
                    <item-margin-profitability></item-margin-profitability>
                </v-flex>
                <v-flex xs12 sm6 md6 mb-2 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
                    <item-ratio-activity></item-ratio-activity>
                </v-flex>
            </v-layout>
        </v-flex>
        
        <v-flex xs12 sm6 md6 mb-2 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
            <item-finance-002></item-finance-002>
        </v-flex>
        <v-flex xs12 sm6 md6 mb-2 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
            <item-finance-003></item-finance-003>
        </v-flex>
        <v-flex xs12 sm6 md6 mb-2 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
            <item-finance-004></item-finance-004>
        </v-flex>

         <!-- <v-flex xs12 sm4 md3 mb-2 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
            <item-stat-customer-map></item-stat-customer-map>
        </v-flex>

        <v-flex xs12 sm4 md3 mb-2>
            <item-stat-customer></item-stat-customer>
        </v-flex>

        <v-flex xs12 sm4 md3 mb-2 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
            <item-ipaymu-balance></item-ipaymu-balance>
        </v-flex>
        
        <v-flex xs12 sm8 md9 mb-2 class="hidden-xs-only">
            <item-blank height="133"></item-blank>
        </v-flex> -->
        
    </v-layout>
</template>

<script>
let t = '?t='+Math.random()
module.exports = {
    components : {
        // "item-stat-customer": httpVueLoader("./dashboard-item-stat-customer.vue"),
        // "item-stat-customer-map": httpVueLoader("./dashboard-item-stat-customer-map.vue"),
        "item-sales-001": httpVueLoader("./dashboard-item-sales-001.vue"+t),
        "item-ratio-liquidity": httpVueLoader("./dashboard-item-ratio-liquidity.vue"+t),
        "item-ratio-activity": httpVueLoader("./dashboard-item-ratio-activity.vue"+t),
        "item-margin-profitability": httpVueLoader("./dashboard-item-margin-profitability.vue"+t),
        "item-finance-002": httpVueLoader("./dashboard-item-finance-002.vue"+t),
        "item-finance-003": httpVueLoader("./dashboard-item-finance-003.vue"+t),
        "item-finance-004": httpVueLoader("./dashboard-item-finance-004.vue"+t),
        // "item-stat-omzet-product": httpVueLoader("./dashboard-item-stat-omzet-product.vue"),
        // "item-ipaymu-balance": httpVueLoader("./dashboard-item-ipaymu-balance.vue"),
        // "item-blank": httpVueLoader("./dashboard-item-blank.vue")
    },

    mounted () {
        this.$store.dispatch("dashboard/search_sales001")
    }
}
</script>