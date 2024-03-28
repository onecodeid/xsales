<template>
    <v-layout row wrap>

        <v-flex xs12 sm6 md6 mb-2 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
            <item-sales-002></item-sales-002>
        </v-flex>

        <!-- <v-flex xs12 sm6 md6 mb-2 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
            <item-sales-001></item-sales-001>
        </v-flex> -->

        <v-flex xs12 sm6 md6 mb-2 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
            <item-sales-003></item-sales-003>
        </v-flex>
        <v-flex xs0 sm12 md12>
            <item-sales-customer-001></item-sales-customer-001>
        </v-flex>
<!-- 
        <v-flex xs12>
            <v-layout row wrap>
                <v-flex xs12 sm6 md6 mb-2 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
                    <item-ratio-liquidity></item-ratio-liquidity>
                </v-flex>
                <v-flex xs12 sm6 md6 mb-2 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
                    <item-margin-profitability></item-margin-profitability>
                </v-flex>
            </v-layout>
        </v-flex>
        
        <v-flex xs12 sm6 md6 mb-2 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
            <item-finance-002></item-finance-002>
        </v-flex>
        <v-flex xs12 sm6 md6 mb-2 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
            <item-finance-003></item-finance-003>
        </v-flex> -->
        
        
    </v-layout>
</template>

<script>
let t = '?t='+Math.random()
module.exports = {
    components : {
        "item-sales-customer-001": httpVueLoader("./dashboard-item-sales-customer-001-main.vue"+t),
        "item-sales-002": httpVueLoader("./dashboard-item-sales-002.vue"+t),
        // "item-sales-001": httpVueLoader("./dashboard-item-sales-001.vue"),
        "item-sales-003": httpVueLoader("./dashboard-item-sales-003.vue"+t),
        // "item-ratio-liquidity": httpVueLoader("./dashboard-item-ratio-liquidity.vue"),
        // "item-margin-profitability": httpVueLoader("./dashboard-item-margin-profitability.vue"),
        // "item-finance-002": httpVueLoader("./dashboard-item-finance-002.vue"),
        // "item-finance-003": httpVueLoader("./dashboard-item-finance-003.vue"),
        // "item-finance-004": httpVueLoader("./dashboard-item-finance-004.vue")
    },

    mounted () {
        this.$store.dispatch("dashboardSales/search_months").then((x) => {
            this.$store.commit("dashboardSales/set_object", ["selected_month2", x[x.length-1]])
            this.$store.dispatch("dashboardSales/search_staff").then((y) => {
                this.$store.dispatch("dashboardSales/searchSalesCustomer001").then((z) => {
                    this.$store.dispatch('dashboardSales/searchSales002').then((a) => {
                        this.$store.dispatch('dashboardSales/searchSales003')
                    })
                })
            })
        })
        
    }
}
</script>