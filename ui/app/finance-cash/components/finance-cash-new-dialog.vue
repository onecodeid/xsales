<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="1000px"
        transition="dialog-transition"
        content-class="dialog-new"
    >
        <!-- <v-layout row wrap>
            <v-flex xs12> -->
                <finance-cash-receive-new v-show="cash_type_code=='CASH.RECEIVE'"></finance-cash-receive-new>
                <finance-cash-pay-new v-show="cash_type_code=='CASH.PAY'"></finance-cash-pay-new>
                <finance-cash-transfer-new v-show="cash_type_code=='CASH.TRANSFER'"></finance-cash-transfer-new>
                <finance-cash-upload v-show="cash_type_code=='CASH.UPLOAD'"></finance-cash-upload>
            <!-- </v-flex>
        </v-layout> -->
        
    </v-dialog>
</template>

<script>
var t = Math.ceil(Math.random() * 1e10)

module.exports = {
    components : {
        "finance-cash-receive-new" : httpVueLoader("./finance-cash-receive-new-3.vue?t="+t),
        "finance-cash-pay-new" : httpVueLoader("./finance-cash-pay-new-3.vue?t="+t),
        "finance-cash-transfer-new" : httpVueLoader("./finance-cash-transfer-new.vue?t="+t),
        "finance-cash-upload" : httpVueLoader("./finance-cash-upload.vue?t="+t)
    },

    data () {
        return { }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.cashNew.dialog_new },
            set (v) { this.$store.commit('cashNew/set_object', ['dialog_new', v]) }
        },

        cash_type_code() {
            return this.$store.state.cashNew.cash_type_code
        }
    }
}
</script>