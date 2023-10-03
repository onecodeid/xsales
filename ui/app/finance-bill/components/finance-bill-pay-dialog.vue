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
                <finance-bill-pay v-if="dialog"></finance-bill-pay>
            <!-- </v-flex>
        </v-layout> -->
        
    </v-dialog>
</template>

<script>
var t = Math.ceil(Math.random() * 1e10)

module.exports = {
    components : {
        "finance-bill-pay" : httpVueLoader("./finance-bill-pay.vue?t="+t)
    },

    data () {
        return { }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.billPay.dialog_pay },
            set (v) { this.$store.commit('billPay/set_object', ['dialog_pay', v]) }
        }
    }
}
</script>