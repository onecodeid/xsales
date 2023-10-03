<template>
    <v-card>
        <v-card-title primary-title class="py-2 px-3 lime white--text title">
            Ringkasan Beban Operasional
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
                <v-flex xs3 pa-2>
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

                <v-flex xs4 py-2 px-1>
                    <iFrame :src="'./components/finance004.php?data='+JSON.stringify(finance004)" scrolling="no"></iFrame>
                    <!-- <object :data="'./components/finance004.php?t=1234&data='+JSON.stringify(finance004)" width="100%" height="100%"></object> -->
                </v-flex>
                <v-flex xs5 py-3 px-2 class="caption">
                    <v-layout row wrap v-for="(f, n) in finance004" :key="n" class="pb-1">
                        <v-flex xs7>
                            {{ f.account_name }}
                        </v-flex>
                        <v-flex xs5 class="text-xs-right">
                            <span class="grey--text">Rp</span> {{ one_money(f.jdebit) }}
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
        finance004 () {
            return this.$store.state.dashboard.finance004
        },

        total () {
            let total = 0
            for (let f of this.finance004) total += (parseFloat(f.jdebit) - parseFloat(f.jcredit))
            
            return total
        },

        sdate () {
            return this.$store.state.dashboard.finance004_sdate
        },

        edate () {
            return this.$store.state.dashboard.finance004_edate
        }

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
        },

        change_sdate(x) {
            this.$store.commit('dashboard/set_object', ['finance004_sdate', x.new_date])
            this.$store.dispatch('dashboard/search_finance004')
        },

        change_edate(x) {
            this.$store.commit('dashboard/set_object', ['finance004_edate', x.new_date])
            this.$store.dispatch('dashboard/search_finance004')
        }
    },

    mounted () {
        this.$store.dispatch('dashboard/search_finance004')
    }
}
</script>