<template>
    <v-card :height="h">
        <v-card-text class="pa-0 fill-height">
            <v-layout row wrap class="fill-height">
                <v-flex xs6 class="pa-1 blue lighten-2 white--text text-xs-center">
                    <h3 class="headline">{{one_money(omzet_admin.omzet_yest)}}</h3>
                    <div class="caption">PEKAN LALU</div>
                    <v-divider class="my-1"></v-divider>
                    <h3 class="">OMZET PEKANAN</h3>
                    <h3 class="title">ANDA</h3>
                </v-flex>
                <v-flex xs6 class="pa-1 lighten-2 white--text text-xs-center"
                    :class="omzet_admin.percentage<75?'red':(omzet_admin.percentage<100?'orange':'green')">
                    <h3 class="headline">{{one_money(omzet_admin.omzet_current)}}</h3>
                    <div class="caption">PEKAN INI</div>
                    <v-divider class="my-1"></v-divider>
                    
                    <v-layout row wrap>
                        <v-flex xs4>
                            <h3 class="headline">{{Math.round(omzet_admin.percentage)}}<span class="body-2">%</span></h3>
                            <div class="caption">{{omzet_admin.percentage<100?'turun':'naik'}}</div>
                        </v-flex>
                        <v-flex xs8 class="cyan lighten-1">
                            <h3 class="subheading">{{one_money(omzet_admin.omzet_month)}}</h3>
                            <div class="caption">total omzet bulan ini</div>
                        </v-flex>
                    </v-layout>
                </v-flex>
            </v-layout>
        </v-card-text>
    </v-card>
</template>

<script>
module.exports = {
    props : ['height'],

    computed : {
        h () {
            if (this.height)
                return this.height
            return 133
        },

        omzet_admin () {
            return this.$store.state.dashboard.omzet_admin
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        }
    },

    mounted () {
        this.$store.dispatch('dashboard/get_omzet_admin')
    }
}
</script>