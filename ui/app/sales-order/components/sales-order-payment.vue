<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="500px"
        transition="dialog-transition"
        content-class="dialog-new"
        
    >
    <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <h3>
                    <span v-show="!edit">PENERIMAAN PEMBAYARAN</span>
                    <span v-show="!!edit">UBAH DATA PENERIMAAN PEMBAYARAN</span>
                </h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs4>
                        <common-datepicker
                            label="Tanggal Penerimaan"
                            :date="payment_date"
                            data="0"
                            @change="change_payment_date"
                            classs=""
                            hints=" "
                            :details="true"
                            :solo="false"
                            v-if="dialog"
                        ></common-datepicker>
                    </v-flex>
                    <v-flex xs8>
                        
                    </v-flex>
                </v-layout>
            </v-card-text>
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

/* .dialog-new .v-input__append-outer {
    margin: 0px !important;
} */

.dialog-new .v-input__prepend-outer button {
    min-height: 36px;
}
</style>
<script>
module.exports = {
    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
    },

    data () {
        return {
            tempo: true
         }
    },

    computed : {
        __s () { return this.$store.state.payment },

        dialog : {
            get () { return this.__s.dialog },
            set (v) { this.__c("dialog", v) }
        },

        edit () { return this.__s.edit },

        payment_date () {
            return this.__s.payment_date
        }
    },

    methods : {
        __c (a, b) { this.$store.commit("payment/set_object", [a, b]) },

        change_payment_date(x) {
            this.__c('payment_date', x.new_date)
        }
    }
}