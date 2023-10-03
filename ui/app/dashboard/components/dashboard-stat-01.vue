<template>
    <v-layout row wrap>
        <v-flex xs12 sm12 md12 mb-2>
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
            
        </v-flex>
        <v-flex xs12 sm4 md3 mb-2 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
            <v-card>
                <v-card-text>
                    <v-layout row wrap>
                        <v-flex xs4>
                            <v-card color="zalfa-icon-box" flat>
                                <v-card-text class="pa-0">
                                    <v-icon size="60" color="blue" light>swap_horiz</v-icon>        
                                </v-card-text>
                            </v-card>
                            
                        </v-flex>
                        <v-flex xs8 class="text-xs-right">
                            <div class="subheading font-weight-thin">Transaksi Hari Ini</div>
                            <h4 class="headline">34</h4>
                        </v-flex>
                        <v-flex xs12>
                            <v-divider class="mt-2 mb-2"></v-divider>
                            <div class="body-1 font-weight-light"><v-icon size="20">history</v-icon> Last updated : 2020-04-01 09:00</div>
                        </v-flex>
                    </v-layout>
                    
                    
                </v-card-text>
            </v-card>
        </v-flex>

        <v-flex xs12 sm4 md3 mb-2 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
            <v-card>
                <v-card-text class="pb-2">
                    <v-layout row wrap>
                        <v-flex xs4>
                            <v-card color="zalfa-icon-box" flat>
                                <v-card-text class="pa-0">
                                    <v-icon size="60" color="green" light>attach_money</v-icon>        
                                </v-card-text>
                            </v-card>
                            
                        </v-flex>
                        <v-flex xs8 class="text-xs-right">
                            <div class="subheading font-weight-thin">Komisi Bulan Ini</div>
                            <h4 class="headline">Rp {{one_money(admin_total_fee)}}</h4>
                        </v-flex>
                        <v-flex xs12>
                            <v-divider class="mt-2 mb-2"></v-divider>
                            <v-layout row wrap>
                                <v-flex xs9>
                                    <div class="body-1 font-weight-light"><v-icon size="20">history</v-icon> {{admin_fee_sdate}} s/d {{admin_fee_edate}}</div>
                                </v-flex>
                                <v-flex xs3 class="text-xs-right">
                                    <v-btn color="success" small class="ma-0 btn-icon" @click="admin_fee_print">Detail</v-btn>
                                </v-flex>
                            </v-layout>
                            
                        </v-flex>
                    </v-layout>
                    
                    
                </v-card-text>
            </v-card>
        </v-flex>

        <v-flex xs12 sm4 md3 mb-2 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
            <v-card>
                <v-card-text>
                    <v-layout row wrap>
                        <v-flex xs4>
                            <v-card color="zalfa-icon-box" flat>
                                <v-card-text class="pa-0">
                                    <v-icon size="60" color="orange" light>account_balance</v-icon>        
                                </v-card-text>
                            </v-card>
                            
                        </v-flex>
                        <v-flex xs8 class="text-xs-right">
                            <div class="subheading font-weight-thin">Saldo iPaymu</div>
                            <h4 class="headline">Rp {{ one_money(ipaymu_balance) }}</h4>
                        </v-flex>
                        <v-flex xs12>
                            <v-divider class="mt-2 mb-2"></v-divider>
                            <div class="body-1 font-weight-light"><v-icon size="20">history</v-icon> Last updated : 2020-04-01 09:00</div>
                        </v-flex>
                    </v-layout>
                    
                    
                </v-card-text>
            </v-card>
        </v-flex>

        <v-flex xs12 sm4 md3 mb-2 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
            <v-card>
                <v-card-text>
                    <v-layout row wrap>
                        <v-flex xs4>
                            <v-card color="zalfa-icon-box" flat>
                                <v-card-text class="pa-0">
                                    <v-icon size="60" color="purple" light>local_shipping</v-icon>        
                                </v-card-text>
                            </v-card>
                            
                        </v-flex>
                        <v-flex xs8 class="text-xs-right">
                            <div class="subheading font-weight-thin">Order Belum Terkirim</div>
                            <h4 class="headline">14</h4>
                        </v-flex>
                        <v-flex xs12>
                            <v-divider class="mt-2 mb-2"></v-divider>
                            <div class="body-1 font-weight-light"><v-icon size="20">history</v-icon> Last updated : 2020-04-01 09:00</div>
                        </v-flex>
                    </v-layout>
                </v-card-text>
            </v-card>
        </v-flex>

        <v-flex xs12 sm4 md3 mb-2 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
            <v-card>
                <v-card-text>
                    <v-layout row wrap>
                        
                        <v-flex xs12>
                            <object data="../objects/dashboard-chart-customer/"></object>
                        </v-flex>
                    </v-layout>
                    
                    
                </v-card-text>
            </v-card>
        </v-flex>
    </v-layout>
</template>

<style scoped>
    /* .zalfa-icon-box {
        margin-top: -30px
    } */
</style>

<script>
module.exports = {
    
    computed : {
        ipaymu_balance () {
            return this.$store.state.dashboard.ipaymu_balance
        },

        admin_total_fee () {
            return this.$store.state.dashboard.admin_total_fee
        },

        admin_fee_sdate () {
            return this.$store.state.dashboard.admin_fee_sdate
        },

        admin_fee_edate () {
            return this.$store.state.dashboard.admin_fee_edate
        },

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

        admin_fee_print () {
            let x = this.$store.state
            this.$store.commit('set_report_url', x.dashboard.URL+`report/one_fin_001?uid=${x.dashboard.user.user_id}&sdate=${this.admin_fee_sdate}&edate=${this.admin_fee_edate}`)
            this.$store.commit('set_dialog_print', true)
        },

        omzet_next () {
            window.location.replace('../stat-omzet-per-product/')
        }
    }
}
</script>