<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="800px"
        transition="dialog-transition"
        content-class="dialog-new"
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <h3>PENERIMAAN UANG MELALUI {{selected_account?selected_account.M_AccountName.toUpperCase():''}}</h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs3>
                        <common-datepicker
                            label="Tanggal Transaksi"
                            :date="payment_date"
                            data="0"
                            @change="change_payment_date"
                            classs=""
                            hints=" "
                            :details="true"
                            :solo="false"
                            v-if="dialog&&!payment_post"
                        ></common-datepicker>
                        <v-text-field
                            label="Tanggal Transaksi"
                            :value="payment_date"
                            v-show="!!payment_post"
                            readonly
                        ></v-text-field>

                        <v-text-field
                            label="Akun"
                            :value="selected_account?selected_account.M_AccountName:''"
                            readonly
                        >
                        </v-text-field>
                    </v-flex>
                    <v-flex xs9 pl-4>
                        <v-textarea
                            label="Catatan"
                            v-model="payment_note"
                            :readonly="payment_post"
                            rows="4"
                            placeholder=" "
                        ></v-textarea>
                    </v-flex>
                </v-layout>
                <finance-cash-receive-detail></finance-cash-receive-detail>
            </v-card-text>
            <v-card-actions>
                <v-btn color="primary" flat @click="dialog=!dialog">Batal</v-btn>
                <v-spacer></v-spacer>
                <v-btn color="primary" @click="save" :disabled="!btn_save_enabled" :dark="btn_save_enabled">Simpan</v-btn>                
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>
<style>
.input-dense .v-input__control {
    min-height: 36px !important;
}

.dialog-new .v-input__prepend-outer {
    margin: 0px !important;
}

.dialog-new .v-text-field.v-text-field--solo .v-input__control {
    min-height: 36px;
    padding: 0;
}

.dialog-new .v-text-field--solo-flat .v-input__slot {
    margin-bottom: -5px;
}

.dialog-new .v-text-field--solo-flat .v-text-field__details {
    margin-bottom: 0px;
}
</style>
<script>
module.exports = {
    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        'finance-cash-receive-detail' : httpVueLoader('./finance-cash-receive-detail.vue')
    },

    computed : {
        dialog : {
            get () { return this.$store.state.cash_receive.dialog_new },
            set (v) { this.$store.commit('cash_receive/set_common', ['dialog_new', v]) }
        },

        btn_save_enabled () {
            let ttl = this.total
            
            if (ttl.amount == 0) return false
            if (this.payment_note == '') return false
            if (!!this.$store.state.cash_receive.save) return false
            if (!!this.payment_post) return false
            for (let d of this.details)
                if (!d.account && (Math.round(d.amount) > 0 ))
                    return false

            return true
        },
        
        // btn_add_enabled () {
        //     let d = this.details[this.details.length-1]
        //     if (!d) return false
        //     // if (!d.invoice) return false

        //     // if (this.$store.state.cash_invoice.edit) {
        //     //     let j = this.$store.state.cash_invoice
        //     //     if (j.payment_post=='Y') return false
        //     // }
            
        //     return true
        // },

        details () {
            return this.$store.state.cash_receive.details
        },

        selected_account () {
            return this.$store.state.cash.selected_account
        },

        payment_date () {
            return this.$store.state.cash_receive.payment_date
        },
        
        payment_post () {
            return this.$store.state.cash_receive.payment_post
        },

        payment_note : {
            get () { return this.$store.state.cash_receive.payment_note },
            set (v) { this.$store.commit('cash_receive/set_common', ['payment_note', v]) }
        },

        total () {
            let x = 0
            for (let j of this.details) {
                x = x + Math.round(j.amount)
            }
            return {amount:x}
        }
    },

    methods : {
        change_payment_date(x) {
            this.$store.commit('cash_receive/set_common', ['payment_date', x.new_date])
        },

        save () {
            this.$store.dispatch('cash_receive/save')
        }
    },

    mounted () {
        this.$store.dispatch('cash_receive/search_account')
    }
}
</script>