<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="500px"
        transition="dialog-transition"
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <h3>SALDO AWAL</h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                        <v-layout row wrap>

                            <v-flex xs6 pr-2>
                                <v-text-field
                                    label="Debit"
                                    v-model="balanceDebit"
                                    reverse
                                    hide-details
                                ></v-text-field>

                                <div class="text-xs-right blue--text mt-2"><i>Rp <b>{{ one_money(balanceDebit) }}</b></i></div>
                            </v-flex>

                            <v-flex xs6 pl-2>
                                <v-text-field
                                    label="Credit"
                                    v-model="balanceCredit"
                                    reverse
                                    hide-details
                                ></v-text-field>

                                <div class="text-xs-right blue--text mt-2"><i>Rp <b>{{ one_money(balanceCredit) }}</b></i></div>
                            </v-flex>

                        </v-layout>
                    </v-flex>
                </v-layout>
            </v-card-text>

            <v-card-actions>
                <v-btn color="primary" flat @click="dialog=!dialog">Batal</v-btn>
                <v-spacer></v-spacer>
                <v-btn color="primary" @click="save">Simpan</v-btn>                
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<style>
.input-dense .v-input__control {
    min-height: 36px !important;
}
</style>
<script>
module.exports = {
    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue')
    },

    data () {
        return { }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.account_new.dialog_balance },
            set (v) { this.$store.commit('account_new/set_common', ['dialog_balance', v]) }
        },

        balanceDebit : {
            get () { return this.$store.state.account_new.balanceDebit },
            set (v) { this.$store.commit('account_new/set_common', ['balanceDebit', v]) }
        },

        balanceCredit : {
            get () { return this.$store.state.account_new.balanceCredit },
            set (v) { this.$store.commit('account_new/set_common', ['balanceCredit', v]) }
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        save () {
            this.$store.dispatch('account_new/set_balance')
        }
    }
}
</script>