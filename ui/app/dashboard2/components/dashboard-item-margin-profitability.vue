<template>
    <v-card :class="{'fill-height':$vuetify.breakpoint.mdAndUp}">
        <v-card-title primary-title class="py-2 px-3 cyan white--text title">
            RASIO PROFITABILITAS
        </v-card-title>
        <v-card-text class="pa-1 pt-1">
            <v-layout row wrap>
                <v-flex xs3 md2 pr-2>
                    <common-datepicker
                        label="Dari Tanggal"
                        :date="sdate"
                        data="0"
                        @change="change_sdate"
                        classs=""
                        hints=" "
                        :details="true"
                        :solo="false"
                    ></common-datepicker>
                    <common-datepicker
                        label="Sampai Tanggal"
                        :date="edate"
                        data="0"
                        @change="change_edate"
                        classs=""
                        hints=" "
                        :details="false"
                        :solo="false"
                    ></common-datepicker>
                </v-flex>
                <v-flex xs9 md10>
                    <v-tabs
                        v-model="tab"
                        color="orange lighten-3"
                        grow
                        class="pb-1"
                        >
                        <v-tabs-slider color="yellow"></v-tabs-slider>
                        <v-tab
                            v-for="(item, n) in ratios"
                            :key="n"
                        >
                            {{ item.title.replace(/(MARGIN)/, '') }}
                        </v-tab>
                    </v-tabs>

                    <v-tabs-items v-model="tab">
                        <v-tab-item
                            v-for="(item, rn) in ratios"
                            :key="rn"
                        >
                            <v-card flat>
                                <v-card-text class="pa-0">
                                    <table width="100%" class="text-xs-center">
                                        <tr>
                                            <!-- <td rowspan="2" width="20%" class="title orange white--text pa-2">{{ item.title.toUpperCase() }}</td> -->
                                            <td width="60%" class="pa-2 orange lighten-2 caption">( {{ item.formula.top }} / {{ item.formula.bottom }} ) x 100%</td>
                                            <td rowspan="2" width="20%" class="align-content-center display-2 pa-2 orange white--text">{{ one_money(item.amount.top * 100 / item.amount.bottom) }}%</td>
                                        </tr>
                                        <tr class="pt-1">
                                            <td class="pa-2 orange lighten-2">( Rp <b>{{ one_money(item.amount.top) }}</b> / Rp <b>{{ one_money(item.amount.bottom) }}</b> ) x 100%</td>
                                        </tr>
                                    </table>
                                </v-card-text>
                            </v-card>
                        </v-tab-item>
                    </v-tabs-items>
                </v-flex>
            </v-layout>
        

    
        </v-card-text>
        </v-card>

    
</template>

<style scoped>
table { border-spacing: 0px; }
</style>

<script>
module.exports = {
    data () {
      return {
        tab: null,
        items: [
          'web', 'shopping', 'videos', 'images', 'news'
        ],
        text: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',

        ratiosx: [
            {id:'current',label:'Lancar',formula:'( Aktiva Lancar / Hutang Lancar ) x 100%'},
            {id:'quick',label:'Cepat',formula:'(( Kas & Bank + Piutang ) / Hutang Lancar ) x 100%'},
            {id:'cash',label:'Kas',formula:'( Kas & Bank / Hutang Lancar ) x 100%'}]
      }
    },

    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue')
    },

    computed : {
        ratios () {
            return this.$store.state.dashboard.margin_profitability
        },

        sdate () {
            return this.$store.state.dashboard.margin_profitability_sdate
        },

        edate () {
            return this.$store.state.dashboard.margin_profitability_edate
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        change_sdate(x) {
            this.$store.commit('dashboard/set_object', ['margin_profitability_sdate', x.new_date])
            this.$store.dispatch('dashboard/search_margin_profitability')
        },

        change_edate(x) {
            this.$store.commit('dashboard/set_object', ['margin_profitability_edate', x.new_date])
            this.$store.dispatch('dashboard/search_margin_profitability')
        }
    },

    mounted () {
        this.$store.dispatch('dashboard/search_margin_profitability')
    }
}
</script>