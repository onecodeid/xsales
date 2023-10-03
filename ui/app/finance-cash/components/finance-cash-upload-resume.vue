<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="1200px"
        transition="dialog-transition"
        content-class="dialog-new"
    >
        
        <v-card>
            <v-card-text class="pa-1">
                <v-layout row wrap>
                    <v-flex xs12 py-1>
                        <v-alert
                            :value="true"
                            type="success"
                            >
                            Berhasil meng<i>impor</i> sejumlah {{ cash_cnt }} baris.
                        </v-alert>
                    </v-flex>
                    <v-flex xs12>
                        <finance-cash-list></finance-cash-list>
                    </v-flex>
                </v-layout>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="primary" flat @click="dialog=!dialog">Tutup</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
var t = Math.ceil(Math.random() * 1e10)

module.exports = {
    components : {
        "finance-cash-list" : httpVueLoader("./finance-cash-list.vue?t="+t)
    },

    data () {
        return { }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.cashNew.dialogUploadResume },
            set (v) { this.$store.commit('cashNew/set_object', ['dialogUploadResume', v]) }
        },

        cash_type_code() {
            return this.$store.state.cashNew.cash_type_code
        },

        cash_md5() {
            return this.$store.state.cashNew.batch_md5
        },

        cash_cnt() {
            return this.$store.state.cashNew.batch_cnt
        }
    }
}
</script>