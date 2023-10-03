<template>
    <v-card class="text-xs-center">
        <v-card-title primary-title class="py-2 px-3 lime white--text title justify-center" title="sales-customer-001">
            CUSTOMER BARU
        </v-card-title>
        <v-card-text class="py-2 px-2 lime lighten-5">
            <v-layout row wrap>
                <v-flex xs4 class="text-xs-left caption cyan--text">
                    Bulan {{ data.month_name }}<br>{{ data.year_name }}
                </v-flex>
                <v-flex xs4>
                    <h3 class="headline">{{ one_money(data.cmonth_customer) }}</h3>        
                </v-flex>
            </v-layout>
            
            <v-divider class="my-1"></v-divider>
            <v-layout row wrap class="pt-2">
                <v-flex xs6 class="subheading"><div>Bulan Sebelumnya</div><b>{{ one_money(data.pmonth_customer) }}</b></v-flex>
                <v-flex xs6><div>% Rasio</div><b>{{ one_money((data.cmonth_customer - data.pmonth_customer) * 100 / data.pmonth_customer) }} %</b></v-flex>
            </v-layout>
            <v-layout row wrap>
                <v-flex xs12 class="align-center">
                    <v-btn color="primary" dark flat depressed class="ma-0">Trend
                        <v-icon v-show="Math.round(data.cmonth_customer)>Math.round(data.pmonth_customer)" class="green--text">arrow_drop_up</v-icon>
                    <v-icon v-show="Math.round(data.cmonth_customer)<Math.round(data.pmonth_customer)" class="red--text" large>arrow_drop_down</v-icon>
                    </v-btn>
                </v-flex>
            </v-layout>
        </v-card-text>
    </v-card>        
</template>

<script>
module.exports = {
    computed : {
        data () {
            return this.$store.state.dashboardSales.customer001
        },

        mainGrid () {
            return 'xs12 sm6 md4 lg3 mb-2'
        }
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