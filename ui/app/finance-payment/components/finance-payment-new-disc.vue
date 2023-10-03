<template>
    <v-layout row wrap class="mt-2">
        <v-flex xs4 pr-3>
            POTONGAN
        </v-flex>
        <v-flex xs2 pr-2>
            <v-text-field
                label=""
                solo
                hide-details
                :value="disc.cash"
                reverse
                dense
                @change="set_disc"
                @input="update_amount('cash', $event)"
                :mask="one_mask_money(disc.cash)"
                :readonly="disc.post=='Y'"
            >
                <template slot="append"><span class="grey--text">Rp</span></template>
            </v-text-field>
        </v-flex>
        <v-flex xs2 pr-2>
            <v-text-field
                label=""
                solo
                hide-details
                :value="disc.transfer"
                reverse
                dense
                @change="set_disc"
                @input="update_amount('transfer', $event)"
                :mask="one_mask_money(disc.transfer)"
                :readonly="disc.post=='Y'"
            >
                <template slot="append"><span class="grey--text">Rp</span></template>
            </v-text-field>
        </v-flex>
        <v-flex xs2 pr-2>
            <v-text-field
                label=""
                solo
                hide-details
                :value="disc.giro"
                reverse
                dense
                @change="set_disc"
                @input="update_amount('giro', $event)"
                :mask="one_mask_money(disc.giro)"
                :readonly="disc.post=='Y'"
            >
                <template slot="append"><span class="grey--text">Rp</span></template>
            </v-text-field>
        </v-flex>
        <v-flex xs2>
            <v-text-field
                label=""
                solo
                hide-details
                :value="disc.cheque"
                reverse
                dense
                @change="set_disc"
                @input="update_amount('cheque', $event)"
                :mask="one_mask_money(disc.cheque)"
                :readonly="disc.post=='Y'"
            >
                <template slot="append"><span class="grey--text">Rp</span></template>
            </v-text-field>
        </v-flex>
    </v-layout>
</template>

<script>
module.exports = {
    computed : {
        disc () {
            return this.$store.state.payment_new.disc
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        one_mask_money (x) {
            return window.one_mask_money(x)
        },
        
        set_disc () {
            this.$store.commit('payment_new/set_disc', this.disc)
        },

        update_amount (side, amount) {
            let d = this.disc
            d[side] = amount
            // this.$store.commit('payment_new/set_details', d)
        }
    }
}
</script>