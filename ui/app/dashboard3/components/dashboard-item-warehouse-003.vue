<template>
    <v-card>
        <v-card-title primary-title class="py-2 px-3 orange lighten-2 white--text title">
            STATISTIK JENIS PENGIRIMAN
        </v-card-title>
        <v-card-text class="pa-0">
            <v-layout row wrap>
                
                <v-flex xs5>
                    <iFrame :src="'../components/warehouse003.php?t=1234&data='+JSON.stringify(warehouse003.deliveries)" scrolling="no"></iFrame>
                    <!-- <object :data="'../components/warehouse003.php?t=1234&data='+JSON.stringify(warehouse003.deliveries)" width="100%" height="100%"></object> -->
                </v-flex>

                <v-flex xs5>
                    <iFrame :src="'../components/warehouse003.php?t=1234&data='+JSON.stringify(warehouse003.deliveries2)" scrolling="no"></iFrame>
                    <!-- <object :data="'../components/warehouse003.php?t=1234&data='+JSON.stringify(warehouse003.deliveries2)" width="100%" height="100%"></object> -->
                </v-flex>
                
                <v-flex xs2 class="text-xs-center">
                    <div class="mt-4 mb-1">Jumlah Ekspedisi :</div>
                    <div class="title mb-2">{{ warehouse003.expedition_cnt }}</div>
                    <div class="mb-1">Jumlah Sopir :</div>
                    <div class="title">{{ warehouse003.driver_cnt }}</div>
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
    computed : {
        __s () {
            return this.$store.state.dashboardWarehouse
        },

        warehouse003 () {
            return this.__s.warehouse003
        },

        // total () {
        //     let total = 0
        //     for (let f of this.sales002) total += (parseFloat(f.omzet_nominal))
            
        //     return total
        // }

        // target_current () {
        //     return this.finance003.grand_total
        // },

        // target_monthly () {
        //     return this.finance003.grand_target
        // },

        // target_percentage () {
        //     return Math.round(this.target_current * 100/ this.target_monthly)
        // }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        }
    },

    mounted () {
        
    }
}
</script>