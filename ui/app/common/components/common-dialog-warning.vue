<template>
    <v-dialog
        v-model="dialog"
        max-width="500px"
        transition="dialog-transition"
    >
        <v-card>
            <v-card-title primary-title class="red white--text">
                <v-icon color="white">error_outline</v-icon> <h3 class="ml-2">Peringatan</h3>
            </v-card-title>

            <v-card-text>
                {{ init_text }}
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="red" dark @click="confirm">{{ btn_text_confirm }}</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
module.exports = {
    props : ['data', 'text', 'btn_cancel', 'btn_confirm'],
    data () {
        return {
            init_text: this.text ? this.text : 'Opps ! Sesuatu telah terjadi...',
            btn_text_cancel: this.btn_cancel ? this.btn_cancel : 'Batal',
            btn_text_confirm: this.btn_confirm ? this.btn_confirm : 'Saya Mengerti'
        }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.dialog_warning },
            set (v) { this.$store.commit('set_dialog_warning', v) }
        }
    },

    methods : {
        confirm () {
            this.dialog = false
        }
    }
}
</script>