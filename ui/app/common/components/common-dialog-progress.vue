<template>
    <v-dialog
        v-model="dialog"
        persistent overlay
        max-width="500px"
        transition="dialog-transition"
    >
        <v-card>
            <v-card-text>
                <v-progress-linear :indeterminate="true"></v-progress-linear>
                <div class="justify-center subheading text-xs-center">{{ textme }}</div>
            </v-card-text>
        </v-card>
    </v-dialog>
</template>

<script>
module.exports = {
    props : ['text'],
    computed : {
        dialog : {
            get () { return this.$store.state.dialog_progress },
            set (v) { this.$store.commit('set_dialog_progress', v) }
        },

        textme () {
            return this.text ? this.text : 'Sedang menyiapkan... mohon ditunggu...'
        }
    }
}
</script>