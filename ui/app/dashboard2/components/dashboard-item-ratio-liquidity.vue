<template>
    <v-card>
        <v-card-title primary-title class="py-2 px-3 cyan white--text title">
            RASIO LIKUIDITAS
        </v-card-title>
        <v-card-text class="pa-0 pt-1">
            <v-layout row wrap>
            <v-flex xs3>
                <common-datepicker
                    label="Per Tanggal"
                    :date="edate"
                    data="0"
                    @change="change_edate"
                    classs=""
                    hints=" "
                    :details="false"
                    :solo="true"
                ></common-datepicker>
            </v-flex>
            <v-flex xs9>
                <v-tabs
                    v-model="tab"
                    color="orange lighten-3"
                    grow
                    >
                    <v-tabs-slider color="yellow"></v-tabs-slider>
                    <v-tab
                        v-for="(item, n) in ratiosx"
                        :key="n"
                    >
                        {{ item.label }}
                    </v-tab>
                    </v-tabs>
            </v-flex>
        </v-layout>
        

    <v-tabs-items v-model="tab">
      <v-tab-item
        v-for="item in ratiosx"
        :key="item.id"
      >
        <v-card flat>
            <v-card-text class="pa-2">
                <table width="100%" class="text-xs-center">
                    <tr>
                        <td rowspan="2" width="20%" class="title orange white--text pa-2">RASIO {{ item.label.toUpperCase() }}</td>
                        <td width="60%" class="pa-2 orange lighten-2">{{ item.formula }}</td>
                        <td rowspan="2" width="20%" class="align-content-center display-2 pa-2 orange white--text">{{ one_money(ratios[item.id]?ratios[item.id].rst:0) }}%</td>
                    </tr>
                    <tr>
                        <td class="pa-2 orange lighten-2">( Rp <b>{{ one_money(ratios[item.id]?ratios[item.id].activa:0) }}</b> / Rp <b>{{ one_money(ratios[item.id]?ratios[item.id].liability:0) }}</b> ) x 100%</td>
                    </tr>
                </table>
            </v-card-text>
        </v-card>
      </v-tab-item>
    </v-tabs-items>
        </v-card-text>
        </v-card>

    
</template>

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
            return this.$store.state.dashboard.ratio_liquidity
        },

        edate () {
            return this.$store.state.dashboard.ratio_liquidity_edate
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        change_edate(x) {
            this.$store.commit('dashboard/set_object', ['ratio_liquidity_edate', x.new_date])
            this.$store.dispatch('dashboard/search_ratio_liquidity')
        }
    },

    mounted () {
        this.$store.dispatch('dashboard/search_ratio_liquidity')
    }
}
</script>