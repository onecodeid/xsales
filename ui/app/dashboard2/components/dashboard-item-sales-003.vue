<template>
    <v-card height="auto">
        <v-card-title primary-title class="py-2 px-3 lime white--text title">
            CAPAIAN PER SALES
        </v-card-title>
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
                        <v-flex xs6 class="caption blue--text">
                            {{one_money(target_monthly)}}
                        </v-flex>
                        <v-flex xs6 class="caption pink--text">
                            {{one_money(target_current)}}
                        </v-flex>
                        <v-flex xs12 class="caption">
                            <v-divider class="my-1"></v-divider>
                            <v-layout row wrap>
                                <v-flex><span class="blue py-1 pl-2 pr-1 white--text d-block">Target</span></v-flex>
                                <v-flex><span class="pink py-1 pl-1 pr-2 white--text d-block">capaian</span></v-flex>
                            </v-layout>
                            <v-divider class="mt-1"></v-divider>
                        </v-flex>
                    </v-layout>
                </v-flex>
                <v-flex xs9 py-2 px-1>
                    <iFrame :src="'../components/snippet.php?data='+JSON.stringify(sales001.omzets)" scrolling="no"></iFrame>
                    <!-- <object :data="'../components/snippet.php?data='+JSON.stringify(sales001.omzets)" width="100%" height="100%" v-if="!!sales001.state"></object> -->
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
    computed : {
        sales001 () {
            return this.$store.state.dashboard.sales001
        },

        target_current () {
            return this.sales001.grand_total
        },

        target_monthly () {
            return this.sales001.grand_target
        },

        target_percentage () {
            return Math.round(this.target_current * 100/ this.target_monthly)
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        }
    },

    mounted () {
        this.$store.dispatch('dashboard/search_sales001')
    }
}
</script>