<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="300px"
        transition="dialog-transition"
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <h3>
                    <span>GENERATE TAGIHAN</span>
                </h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs6 pr-2>
                        <v-text-field
                            label="Tanggal Transaksi"
                            :value="order_date"
                            readonly
                        ></v-text-field>
                    </v-flex>

                    <v-flex xs6 pl-2>
                        <v-text-field
                            label="Nomor Transaksi"
                            :value="order_number"
                            readonly
                        ></v-text-field>
                    </v-flex>

                    <v-flex xs12>
                        <v-text-field
                            label="Nama Vendor"
                            :value="vendor_name"
                            readonly
                        ></v-text-field>
                    </v-flex>

                    <v-flex xs12>
                        <v-textarea
                            label="Catatan"
                            placeholder="Tuliskan catatan anda disini"
                            v-model="bill_note"
                        ></v-textarea>
                    </v-flex>
                </v-layout>
            </v-card-text>

            <v-card-actions>
                <v-btn color="primary" flat @click="dialog=!dialog" v-show="!view">Batal</v-btn>
                <v-spacer></v-spacer>
                <!-- <v-btn color="primary" @click="add_detail">Add</v-btn>                 -->
                <v-btn color="primary" @click="save" :disabled="!btn_save_enabled" :dark="btn_save_enabled" v-show="!view">Generate</v-btn>
            </v-card-actions>

        </v-card>
    </v-dialog>
</template>

<script>
module.exports = {
    computed : {
        dialog : {
            get () { return this.$store.state.receive.dialog_bill },
            set (v) { this.$store.commit('receive/set_common', ['dialog_bill', v]) }
        },

        order_date () {
            return this.$store.state.receive.selected_receive.receive_date
        },

        order_number () {
            return this.$store.state.receive.selected_receive.receive_number
        },

        vendor_name () {
            return this.$store.state.receive.selected_receive.vendor_name
        },

        bill_note : {
            get () { return this.$store.state.receive.bill_note },
            set (v) { this.$store.commit('receive/set_common', ['bill_note', v]) }
        },

        btn_save_enabled () {
            return !this.$store.state.receive.bill_save
        },

        view () {
            return false
        }
    },

    methods : {
        save () {
            return this.$store.dispatch('receive/bill')
        }
    }
}
</script>