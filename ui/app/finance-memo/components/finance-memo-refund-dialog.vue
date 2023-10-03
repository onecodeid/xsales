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
                <finance-memo-refund></finance-memo-refund>
            <!-- </v-flex>
        </v-layout> -->
        
    </v-dialog>
</template>

<script>
var t = Math.ceil(Math.random() * 1e10)

module.exports = {
    components : {
        "finance-memo-refund" : httpVueLoader("./finance-memo-refund.vue?t="+t)
    },

    data () {
        return { }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.memoRefund.dialog_new },
            set (v) { this.$store.commit('memoRefund/set_object', ['dialog_new', v]) }
        }
    }
}
</script>