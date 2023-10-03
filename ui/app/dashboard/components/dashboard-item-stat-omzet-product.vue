<template>
    <v-card>
        <v-card-text>
            <v-layout row wrap>
                <v-flex xs12 md2 class="hidden-sm-only">
                    <h3 class="headline">OMZET PENJUALAN</h3>
                    <div class="subheading hidden-sm-and-up">Periode {{omzet_sdate.split('-').reverse().join('/')}} - {{omzet_edate.split('-').reverse().join('/')}}</div>
                    <div class="subheading hidden-sm-and-down">Periode <br>{{omzet_sdate.split('-').reverse().join('/')}} - <br>{{omzet_edate.split('-').reverse().join('/')}}</div>
                    <div class="pr-2">
                        <v-btn color="primary" class="mx-0 hidden-xs-only" @click="omzet_next" block>Selengkapnya</v-btn>
                    </div>
                    <v-divider class="my-2 hidden-md-and-up"></v-divider>
                </v-flex>

                <v-flex sm12 class="hidden-xs-only hidden-md-and-up">
                    <h3 class="headline">OMZET PENJUALAN <span class="subheading ml-3">Periode {{omzet_sdate.split('-').reverse().join('/')}} - {{omzet_edate.split('-').reverse().join('/')}}</span>
                    
                        <v-btn color="primary" class="ma-0" @click="omzet_next" style="float:right">Selengkapnya</v-btn>
                    </h3>
                    <v-divider class="my-2 hidden-md-and-up"></v-divider>
                </v-flex>

                <v-flex md5 sm6 xs12 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
                    <!-- <object data="../objects/dashboard-chart-omzet-per-product/" width="100%" height="300"></object> -->
                    <div v-for="(o, n) in omzets" v-bind:key="n">
                        <v-layout row wrap v-if="n<5">
                            <v-flex xs9 class="caption">{{o.item_name}}</v-flex>
                            <v-flex xs3 class="caption text-xs-right">{{one_money(o.omzet)}}</v-flex>
                        </v-layout>
                        
                        <v-progress-linear
                            color="error"
                            height="15"
                            :value="o.scale"
                            class="mt-0 mb-2"
                            v-if="n<5"
                        ></v-progress-linear>
                    </div>

                    <div><v-btn color="primary" class="mx-0 mb-2" block v-show="omzet_more&&$vuetify.breakpoint.xsOnly" @click="omzet_more=!omzet_more">Tampilkan Lagi ...</v-btn></div>
                </v-flex>
                <v-flex md5 sm6 xs12 :class="{'pl-2':$vuetify.breakpoint.smAndUp}" v-show="$vuetify.breakpoint.smAndUp||($vuetify.breakpoint.xsOnly&&!omzet_more)">
                    <!-- <object data="../objects/dashboard-chart-omzet-per-product/" width="100%" height="300"></object> -->
                    <div v-for="(o, n) in omzets" v-bind:key="n">
                        <v-layout row wrap v-if="n>=5&&n<10">
                            <v-flex xs9 class="caption">{{o.item_name}}</v-flex>
                            <v-flex xs3 class="caption text-xs-right">{{one_money(o.omzet)}}</v-flex>
                        </v-layout>
                        
                        <v-progress-linear
                            color="error"
                            height="15"
                            :value="o.scale"
                            class="mt-0 mb-2"
                            v-if="n>=5&&n<10"
                        ></v-progress-linear>
                    </div>
                    <div><v-btn color="primary" class="mx-0 mb-2" block v-show="!omzet_more&&$vuetify.breakpoint.xsOnly" @click="omzet_next">Selengkapnya</v-btn></div>
                </v-flex>
            </v-layout>
        </v-card-text>
    </v-card>
</template>

<script>
module.exports = {
    computed : {
        omzets () {
            return this.$store.state.dashboard.omzets
        },

        omzet_high () {
            return this.$store.state.dashboard.omzet_high
        },

        omzet_sdate () {
            return this.$store.state.dashboard.omzet_sdate
        },

        omzet_edate () {
            return this.$store.state.dashboard.omzet_edate
        },

        omzet_more : {
            get () { return this.$store.state.dashboard.omzet_more },
            set (v) { this.$store.commit('dashboard/set_common', ['omzet_more', v]) }
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        omzet_next () {
            window.location.replace('../stat-omzet-per-product/')
        }
    }
}
</script>