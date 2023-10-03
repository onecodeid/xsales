<template>
    <v-dialog
        v-model="dialog"
        max-width="800px"
        transition="dialog-transition"
    >
        <v-card>
            <v-card-title primary-title class="red white--text">
                <v-icon color="white">error_outline</v-icon> <h3 class="ml-2">HISTORY PEMBAYARAN</h3>
            </v-card-title>

            <v-card-text>
                <v-layout row wrap class="orange white--text">
                    <v-flex xs1 class="pa-2">NO</v-flex>
                    <v-flex xs2 class="pa-2">TANGGAL</v-flex>
                    <v-flex xs5 class="pa-2">REKENING TUJUAN</v-flex>
                    <v-flex xs2 class="text-xs-right pa-2">NOMINAL</v-flex>
                    <v-flex xs2 class="text-xs-right pa-2">POTONGAN</v-flex>
                </v-layout>
                <v-layout row wrap v-for="(h,n) in histories">
                    <v-flex xs1 class="pa-2">{{ n+1 }}</v-flex>
                    <v-flex xs2 class="pa-2">{{ h.pay_date }}</v-flex>
                    <v-flex xs5 class="pa-2">{{ h.account_name }}</v-flex>
                    <v-flex xs2 class="text-xs-right pa-2"><span class="grey--text">Rp</span> <b>{{ one_money(h.pay_amount) }}</b></v-flex>
                    <v-flex xs2 class="text-xs-right pa-2"><span class="grey--text">Rp</span> <b>{{ one_money(h.pay_disc_amount) }}</b></v-flex>
                </v-layout>
            </v-card-text>

            <v-card-actions>
                <v-btn color="red" dark @click="dialog=false">TUTUP</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
module.exports = {
    data () {
        return {
            
        }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.sales015.dialog_history },
            set (v) { this.$store.commit('sales015/set_object', ['dialog_history', v]) }
        },

        histories () {
            return this.$store.state.sales015.payment_histories
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        }
    }
}
</script>