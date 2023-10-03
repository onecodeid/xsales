<template>
    <v-card height="272px">
        <v-card-title primary-title class="py-2 px-3 cyan white--text title">
            Hutang Belum Terbayar
        </v-card-title>
        <v-card-text class="pa-0">
            <v-layout row wrap>
                <v-flex xs12 px-3 py-2 class="cyan--text">
                    <v-layout row wrap>
                        <v-flex xs6>
                            <v-layout row wrap mb-1>
                                <v-flex xs6>Belum dibayar</v-flex>
                                <v-flex xs6 class="text-xs-right"><span class="grey--text">Rp</span> <b>{{ one_money(finance003.summary?finance003.summary.total_unpaid:0) }}</b></v-flex>    
                                
                            </v-layout>
                            <v-layout row wrap>
                                <v-flex xs6>Jatuh / lewat tempo</v-flex>
                                <v-flex xs6 class="text-xs-right"><span class="grey--text">Rp</span> <b>{{ one_money(finance003.summary?finance003.summary.total_due:0) }}</b></v-flex>
                            </v-layout>
                        </v-flex>
                        <v-flex xs6>
                            <v-layout row wrap>
                                <v-flex xs5 offset-xs2 pr-1>
                                    <common-datepicker
                                        label="Dari Tanggal"
                                        :date="sdate"
                                        data="0"
                                        @change="change_sdate"
                                        classs=""
                                        hints=" "
                                        :details="false"
                                        :solo="false"
                                    ></common-datepicker>
                                </v-flex>
                                <v-flex xs5 pl-1 pr-3>
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
                            </v-layout>
                        </v-flex>
                    </v-layout>
                    
                </v-flex>
                <v-flex xs12 pb-2 px-1>
                    <iFrame :src="'./components/finance003.php?data='+JSON.stringify(finance003.datas)" scrolling="no"></iFrame>
                    <!-- <object :data="'./components/finance003.php?data='+JSON.stringify(finance003.datas)" width="100%" height="100%" v-if="!!finance003.state"></object> -->
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
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue')
    },

    computed : {
        finance003 () {
            return this.$store.state.dashboard.finance003
        },

        target_current () {
            return this.finance003.grand_total
        },

        target_monthly () {
            return this.finance003.grand_target
        },

        target_percentage () {
            return Math.round(this.target_current * 100/ this.target_monthly)
        },

        sdate () {
            return this.$store.state.dashboard.finance003_sdate
        },

        edate () {
            return this.$store.state.dashboard.finance003_edate
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        change_sdate(x) {
            this.$store.commit('dashboard/set_object', ['finance003_sdate', x.new_date])
            this.$store.dispatch('dashboard/search_finance003')
        },

        change_edate(x) {
            this.$store.commit('dashboard/set_object', ['finance003_edate', x.new_date])
            this.$store.dispatch('dashboard/search_finance003')
        }
    },

    mounted () {
        this.$store.dispatch('dashboard/search_finance003')
    }
}
</script>