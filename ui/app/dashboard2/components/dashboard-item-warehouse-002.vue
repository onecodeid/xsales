<template>
    <v-card>
        <v-card-title primary-title class="py-2 px-3 lime white--text title">
            STOK BARANG PER KLASIFIKASI STOK PER GUDANG
        </v-card-title>
        <v-card-text class="pa-0">
            <v-layout row wrap>
                <!-- <v-flex xs12>
                    <v-layout row wrap>
                        <v-flex xs3>Belum dibayar</v-flex>
                        <v-flex xs3 class="text-xs-right">{{ one_money(finance003.summary.total_unpaid) }}</v-flex>    
                    </v-layout>
                    <v-layout row wrap>
                        <v-flex xs3>Jatuh / lewat tempo</v-flex>
                        <v-flex xs3 class="text-xs-right">{{ one_money(finance003.summary.total_due) }}</v-flex>    
                    </v-layout>
                </v-flex> -->
                <v-flex py-2 px-1 v-for="(w, n) in warehouse002" :key="n">
                    <v-layout row wrap>
                        <v-flex xs1 style="writing-mode:tb-rl;height:auto;transform:scale(-1)">
                            <div class="white--text lime darken-2 px-2 py-3 d-flex justify-center font-weight-bold" style="letter-spacing:.3em">{{ w.warehouse_short_name.toUpperCase() }}</div>
                        </v-flex>
                        <v-flex xs11>
                            <iFrame :src="'../components/warehouse002.php?t=1234&data='+JSON.stringify(w.data)" scrolling="no"></iFrame>
                            <!-- <object :data="'../components/warehouse002.php?t=1234&data='+JSON.stringify(w.data)" width="100%" height="100%"></object>         -->
                        </v-flex>
                    </v-layout>
                    
                </v-flex>
                <!-- <v-flex xs7 py-3 px-2 class="caption">
                    <v-layout row wrap v-for="(f, n) in sales002" :key="n" class="pb-1">
                        <v-flex xs7>
                            {{ f.category_name }}
                        </v-flex>
                        <v-flex xs5 class="text-xs-right">
                            <span class="grey--text">Rp</span> {{ one_money(f.omzet_nominal) }}
                        </v-flex>
                    </v-layout>
                    <v-divider class="my-1"></v-divider>
                    <v-layout row wrap class="pb-1 cyan--text">
                        <v-flex xs7>
                            <b>Total</b>
                        </v-flex>
                        <v-flex xs5 class="text-xs-right">
                            <span class="grey--text">Rp</span> <b>{{ one_money(total) }}</b>
                        </v-flex>
                    </v-layout>
                </v-flex> -->
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

        warehouse002 () {
            return this.__s.warehouse002
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