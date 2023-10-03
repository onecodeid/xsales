<template>
    <v-layout row wrap class="mb-2">
        <v-flex xs3 pr-2>
            <v-card>
                <v-card-title primary-title class="green lighten-2 py-2 px-3 white--text">
                    <h3>Piutang Jatuh Tempo dalam 30 Hari</h3>
                </v-card-title>
                <v-card-text class="py-2">
                    <v-layout row wrap class="headline green--text">
                        <v-flex xs2>Rp</v-flex>
                        <v-flex xs10 class="text-xs-right font-weight-bold">{{one_money(payable_info.payable)}}</v-flex>
                    </v-layout>
                </v-card-text>
            </v-card>
        </v-flex>
        <v-flex xs3 pr-2>
            <v-card>
                <v-card-title primary-title class="red lighten-2 py-2 px-3 white--text">
                    <h3>Hutang Jatuh Tempo dalam 30 Hari</h3>
                </v-card-title>
                <v-card-text class="py-2">
                    <v-layout row wrap class="headline red--text">
                        <v-flex xs2>Rp</v-flex>
                        <v-flex xs10 class="text-xs-right font-weight-bold">{{one_money(payable_info.debt)}}</v-flex>
                    </v-layout>
                </v-card-text>
            </v-card>
        </v-flex>
        <v-flex xs3 pr-2>
            <v-card>
                <v-card-title primary-title class="primary lighten-2 py-2 px-3 white--text">
                    <h3>&nbsp;</h3>
                </v-card-title>
                <v-card-text>
                    &nbsp;
                </v-card-text>
            </v-card>
        </v-flex>
        <v-flex xs3>
            <v-card>
                <v-card-title primary-title class="primary lighten-2 py-2 px-3 white--text">
                    <h3>&nbsp;</h3>
                </v-card-title>
                <v-card-text>
                    &nbsp;
                </v-card-text>
            </v-card>
        </v-flex>
    </v-layout>
</template>

<script>
module.exports = {
    computed : {
        payable_info () {
            return this.$store.state.cash.payable_info
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        }
    },

    mounted () {
        this.$store.dispatch('cash/search_payable_info');
    }
}
</script>