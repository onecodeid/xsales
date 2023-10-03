<template>
    <v-layout row wrap>

        <v-flex xs12 sm6 md12 lg12 mb-2 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
            <item-warehouse-001></item-warehouse-001>
        </v-flex>
        <v-flex xs12 sm6 md12 lg12 mb-2 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
            <item-warehouse-002></item-warehouse-002>
        </v-flex>

        <v-flex xs12 sm6 md12 lg12 mb-2 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
            <item-warehouse-003></item-warehouse-003>
        </v-flex>

        <!-- <v-flex xs12 sm6 md6 mb-2 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
            <item-sales-001></item-sales-001>
        </v-flex> -->

        <!-- <v-flex xs12 sm6 md6 mb-2 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
            <item-sales-003></item-sales-003>
        </v-flex>
        <v-flex xs0 sm12 md12>
            <item-sales-customer-001></item-sales-customer-001>
        </v-flex> -->
    </v-layout>
</template>

<script>
let t = '?t='+Math.random()
module.exports = {
    components : {
        "item-warehouse-001": httpVueLoader("./dashboard-item-warehouse-001.vue"+t),
        "item-warehouse-002": httpVueLoader("./dashboard-item-warehouse-002.vue"+t),
        "item-warehouse-003": httpVueLoader("./dashboard-item-warehouse-003.vue"+t),
        // "item-sales-002": httpVueLoader("./dashboard-item-sales-002.vue"),
        // "item-sales-001": httpVueLoader("./dashboard-item-sales-001.vue"),
        // "item-sales-003": httpVueLoader("./dashboard-item-sales-003.vue"),
        // "item-ratio-liquidity": httpVueLoader("./dashboard-item-ratio-liquidity.vue"),
        // "item-margin-profitability": httpVueLoader("./dashboard-item-margin-profitability.vue"),
        // "item-finance-002": httpVueLoader("./dashboard-item-finance-002.vue"),
        // "item-finance-003": httpVueLoader("./dashboard-item-finance-003.vue"),
        // "item-finance-004": httpVueLoader("./dashboard-item-finance-004.vue")
    },

    mounted () {
        this.$store.dispatch("dashboardWarehouse/search_months").then((x) => {
            this.$store.commit("dashboardWarehouse/set_object", ["selected_month2", x[x.length-1]])
            this.$store.dispatch("dashboardWarehouse/search_staff").then((y) => {
                this.$store.dispatch("dashboardWarehouse/searchWarehouse001").then((z) => {
                    this.$store.dispatch("dashboardWarehouse/searchWarehouse002").then((z1) => { 
                        this.$store.dispatch("dashboardWarehouse/searchWarehouse003").then((z2) => { console.log(z2) })  
                    })
                    // this.$store.dispatch('dashboardWarehouse/searchSales002').then((a) => {
                        // this.$store.dispatch('dashboardWarehouse/searchSales003')
                    // })
                })
            })
        })
        
    }
}
</script>