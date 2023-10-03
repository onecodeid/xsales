<template>
    <v-layout row wrap>
        <v-flex xs12 sm6 md8 lg9 mb-2 v-show="$vuetify.breakpoint.smAndDown||$vuetify.breakpoint.lgAndUp">
            <item-filter></item-filter>
        </v-flex>
        <v-flex :class="[mainGrid, {'pl-2':$vuetify.breakpoint.smAndUp}]" v-show="$vuetify.breakpoint.smAndDown||$vuetify.breakpoint.lgAndUp">
            <item-sales-customer-001></item-sales-customer-001>
        </v-flex>
        <v-flex :class="[mainGrid]">
            <v-card class="text-xs-center">
                <v-card-title primary-title class="py-2 px-3 cyan white--text title justify-center">
                    OMZET CUSTOMER BARU
                </v-card-title>
                <v-card-text class="py-2 px-2">
                    <v-layout row wrap>
                        <v-flex xs4 class="text-xs-left caption cyan--text">
                            Bulan {{ data.month_name }}<br>{{ data.year_name }}
                        </v-flex>
                        <v-flex xs8>
                            <h3 class="headline text-xs-right"><span class="font-weight-light">Rp</span> {{ one_money(data.cmonth_omzet) }}</h3>        
                        </v-flex>
                    </v-layout>
                    
                    <v-divider class="my-1"></v-divider>
                    <v-layout row wrap class="pt-2">
                        <v-flex xs6 class="subheading"><div>Bulan Sebelumnya</div><span class="font-weight-light">Rp</span> <b>{{ one_money(data.pmonth_omzet) }}</b></v-flex>
                        <v-flex xs6><div>% Rasio</div><b>{{ one_money((data.cmonth_omzet - data.pmonth_omzet) * 100 / data.pmonth_omzet) }} %</b></v-flex>
                    </v-layout>
                    <v-layout row wrap>
                        <v-flex xs12 class="align-center">
                            <v-btn color="primary" dark flat depressed class="ma-0">Trend
                                <v-icon v-show="Math.round(data.cmonth_omzet)>Math.round(data.pmonth_omzet)" class="green--text">arrow_drop_up</v-icon>
                            <v-icon v-show="Math.round(data.cmonth_omzet)<Math.round(data.pmonth_omzet)" class="red--text" large>arrow_drop_down</v-icon>
                            </v-btn>
                        </v-flex>
                    </v-layout>
                </v-card-text>
            </v-card>        
        </v-flex>

        <v-flex :class="[mainGrid, {'pl-2':$vuetify.breakpoint.smAndUp}]">
            <v-card class="text-xs-center">
                <v-card-title primary-title class="py-2 px-3 cyan white--text title justify-center">
                    OMZET REPEAT ORDER
                </v-card-title>
                <v-card-text class="py-2 px-2">
                    <v-layout row wrap>
                        <v-flex xs4 class="text-xs-left caption cyan--text">
                            Bulan {{ data.month_name }}<br>{{ data.year_name }}
                        </v-flex>
                        <v-flex xs8>
                            <h3 class="headline text-xs-right"><span class="font-weight-light">Rp</span> {{ one_money(data.cmonth_omzet_repeat) }}</h3>        
                        </v-flex>
                    </v-layout>
                    
                    <v-divider class="my-1"></v-divider>
                    <v-layout row wrap class="pt-2">
                        <v-flex xs6 class="subheading"><div>Bulan Sebelumnya</div><span class="font-weight-light">Rp</span> <b>{{ one_money(data.pmonth_omzet_repeat) }}</b></v-flex>
                        <v-flex xs6><div>% Rasio</div><b>{{ one_money((data.cmonth_omzet_repeat - data.pmonth_omzet_repeat) * 100 / data.pmonth_omzet_repeat) }} %</b></v-flex>
                    </v-layout>
                    <v-layout row wrap>
                        <v-flex xs12 class="align-center">
                            <v-btn color="primary" dark flat depressed class="ma-0">Trend
                                <v-icon v-show="Math.round(data.cmonth_omzet_repeat)>Math.round(data.pmonth_omzet_repeat)" class="green--text">arrow_drop_up</v-icon>
                            <v-icon v-show="Math.round(data.cmonth_omzet_repeat)<Math.round(data.pmonth_omzet_repeat)" class="red--text" large>arrow_drop_down</v-icon>
                            </v-btn>
                        </v-flex>
                    </v-layout>
                </v-card-text>
            </v-card>        
        </v-flex>

        <v-flex :class="[mainGrid, {'pl-2':$vuetify.breakpoint.mdAndUp}]">
            <v-card class="text-xs-center">
                <v-card-title primary-title class="py-2 px-3 orange lighten-1 white--text title justify-center">
                    PROFIT CUSTOMER BARU
                </v-card-title>
                <v-card-text class="py-2 px-2">
                    <v-layout row wrap>
                        <v-flex xs4 class="text-xs-left caption cyan--text">
                            Bulan {{ data.month_name }}<br>{{ data.year_name }}
                        </v-flex>
                        <v-flex xs8>
                            <h3 class="headline text-xs-right"><span class="font-weight-light">Rp</span> {{ one_money(data.cmonth_profit) }}</h3>        
                        </v-flex>
                    </v-layout>
                    
                    <v-divider class="my-1"></v-divider>
                    <v-layout row wrap class="pt-2">
                        <v-flex xs6 class="subheading"><div>Bulan Sebelumnya</div><span class="font-weight-light">Rp</span> <b>{{ one_money(data.pmonth_profit) }}</b></v-flex>
                        <v-flex xs6><div>% Rasio</div><b>{{ one_money((data.cmonth_profit - data.pmonth_profit) * 100 / data.pmonth_profit) }} %</b></v-flex>
                    </v-layout>
                    <v-layout row wrap>
                        <v-flex xs12 class="align-center">
                            <v-btn color="primary" dark flat depressed class="ma-0">Trend
                                <v-icon v-show="Math.round(data.cmonth_profit)>Math.round(data.pmonth_profit)" class="green--text">arrow_drop_up</v-icon>
                            <v-icon v-show="Math.round(data.cmonth_profit)<Math.round(data.pmonth_profit)" class="red--text" large>arrow_drop_down</v-icon>
                            </v-btn>
                        </v-flex>
                    </v-layout>
                </v-card-text>
            </v-card>        
        </v-flex>

        <v-flex :class="[mainGrid, {'pl-2':($vuetify.breakpoint.lgAndUp||$vuetify.breakpoint.smOnly)}]">
            <v-card class="text-xs-center">
                <v-card-title primary-title class="py-2 px-3 orange lighten-1 white--text title justify-center">
                    PROFIT REPEAT ORDER
                </v-card-title>
                <v-card-text class="py-2 px-2">
                    <v-layout row wrap>
                        <v-flex xs4 class="text-xs-left caption cyan--text">
                            Bulan {{ data.month_name }}<br>{{ data.year_name }}
                        </v-flex>
                        <v-flex xs8>
                            <h3 class="headline text-xs-right"><span class="font-weight-light">Rp</span> {{ one_money(data.cmonth_profit_repeat) }}</h3>        
                        </v-flex>
                    </v-layout>
                    
                    <v-divider class="my-1"></v-divider>
                    <v-layout row wrap class="pt-2">
                        <v-flex xs6 class="subheading"><div>Bulan Sebelumnya</div><span class="font-weight-light">Rp</span> <b>{{ one_money(data.pmonth_profit_repeat) }}</b></v-flex>
                        <v-flex xs6><div>% Rasio</div><b>{{ one_money((data.cmonth_profit_repeat - data.pmonth_profit_repeat) * 100 / data.pmonth_profit_repeat) }} %</b></v-flex>
                    </v-layout>
                    <v-layout row wrap>
                        <v-flex xs12 class="align-center">
                            <v-btn color="primary" dark flat depressed class="ma-0">Trend
                                <v-icon v-show="Math.round(data.cmonth_profit_repeat)>Math.round(data.pmonth_profit_repeat)" class="green--text">arrow_drop_up</v-icon>
                            <v-icon v-show="Math.round(data.cmonth_profit_repeat)<Math.round(data.pmonth_profit_repeat)" class="red--text" large>arrow_drop_down</v-icon>
                            </v-btn>
                        </v-flex>
                    </v-layout>
                </v-card-text>
            </v-card>        
        </v-flex>

        <v-flex md4 pl-2 v-show="$vuetify.breakpoint.mdOnly">
            <item-filter></item-filter>
        </v-flex>
        <v-flex :class="[mainGrid, {'pl-2':$vuetify.breakpoint.smAndUp}]" v-show="$vuetify.breakpoint.mdOnly">
            <item-sales-customer-001></item-sales-customer-001>
        </v-flex>

    </v-layout>
    
</template>

<script>
module.exports = {
    components : {
        "item-sales-customer-001": httpVueLoader("./dashboard-item-sales-customer-001.vue"),
        "item-filter": httpVueLoader("./dashboard-item-sales-customer-filter.vue")
    },

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