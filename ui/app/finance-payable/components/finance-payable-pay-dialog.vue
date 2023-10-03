<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="1200px"
        transition="dialog-transition"
        content-class="dialog-new"
    >
        <!-- <v-layout row wrap>
            <v-flex xs12> -->
                <finance-payable-pay></finance-payable-pay>
            <!-- </v-flex>
        </v-layout> -->
        
    </v-dialog>
</template>

<script>
var t = Math.ceil(Math.random() * 1e10)

module.exports = {
    components : {
        "finance-payable-pay" : httpVueLoader("./finance-payable-pay.vue?t="+t)
    },

    data () {
        return { }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.payablePay.dialog_pay },
            set (v) { this.$store.commit('payablePay/set_object', ['dialog_pay', v]) }
        }
    }
}
</script>