<template>
    <v-card class="orange white--text">
        <v-card-text>
            <v-layout row wrap>
                <v-flex xs8 pr-2>
                    <h1 class="display-4">{{cust_current_pad}}</h1>
                    <div><span class="font-weight-bold">PENAMBAHAN</span> customer baru pekan Ini</div>
                </v-flex>
                <v-flex xs4 class="text-xs-center">
                    <v-icon x-large>people</v-icon>
                    <v-divider class="mb-3"></v-divider>
                    <div>
                        <h3 class="display-2">+{{cust_prev_pad}}</h3>
                        Pekan Kemarin
                    </div>
                </v-flex>
                <!-- <v-flex xs12>
                    <v-divider class="mb-1"></v-divider>
                    <span class="blue--text font-weight-bold">Penambahan</span> customer Baru Pekan Ini
                </v-flex> -->
            </v-layout>
        </v-card-text>
    </v-card>
</template>

<style scoped>
.v-icon {
    font-size: 72px !important;
}
</style>

<script>
module.exports = {
    computed : {
        cust_current () {
            return this.$store.state.dashboard.cust_current
        },

        cust_prev () {
            return this.$store.state.dashboard.cust_prev
        },

        cust_current_pad () {
            try {
                return this.cust_current.padStart(2, '0')
            } catch (error) {
                return this.cust_current
            }
        },

        cust_prev_pad () {
            try {
                return this.cust_prev.padStart(2, '0')
            } catch (error) {
                return this.cust_prev
            }
        }
    },

    mounted () {
        this.$store.dispatch('dashboard/get_customer_total_new')
    }
}
</script>