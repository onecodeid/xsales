<template>
    <v-card height="192px">
        <v-card-text class="pa-0">
            <v-layout row wrap>
                <v-flex xs3 class="text-xs-center" py-3 px-1>
                    <v-progress-circular
                    :rotate="-90"
                    :size="100"
                    :width="15"
                    :value="target_percentage"
                    color="pink"
                    >
                    <h3 class="headline">{{target_percentage}}%</h3>
                    </v-progress-circular>
                    <v-divider class="my-1"></v-divider>
                    <v-layout row wrap>
                        <v-flex xs6 class="body-2 blue--text">
                            {{one_money(target_weekly)}}
                        </v-flex>
                        <v-flex xs6 class="body-2 pink--text">
                            {{one_money(target_current)}}
                        </v-flex>
                        <v-flex xs12 class="caption">
                            <v-divider class="my-1"></v-divider>
                            <span class="blue py-1 pl-2 pr-1 white--text">Target pekanan</span><span class="pink py-1 pl-1 pr-2 white--text">capaian</span>
                            <v-divider class="mt-1"></v-divider>
                        </v-flex>
                    </v-layout>
                </v-flex>
                <v-flex xs9 py-2 px-1>
                    <object data="../objects/dashboard-chart-capaian/" width="100%" ></object>
                </v-flex>
            </v-layout>
        </v-card-text>
    </v-card>
</template>

<script>
module.exports = {
    computed : {
        target_current () {
            return this.$store.state.dashboard.target_current
        },

        target_weekly () {
            return this.$store.state.dashboard.target_weekly
        },

        target_percentage () {
            return Math.round(this.target_current * 100/ this.target_weekly)
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        }
    },

    mounted () {
        this.$store.dispatch('dashboard/get_target_this_week')
    }
}
</script>