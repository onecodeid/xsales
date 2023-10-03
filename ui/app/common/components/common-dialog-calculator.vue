<template>
    <v-dialog
        v-model="dialog"
        persistent overlay
        max-width="500px"
        transition="dialog-transition"
    >
        <v-card>
            <v-card-text>
                <object data="../../common/components/calculator.html" width="100%" height="470px"></object>
            </v-card-text>
            <v-card-actions class="text-xs-center justify-center">
                <v-btn @click="dialog=false" color="red" class="white--text">Tutup</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
module.exports = {
    props : ['text'],
    computed : {
        dialog : {
            get () { return this.$store.state.dialog_calculator },
            set (v) { this.$store.commit('set_dialog_calculator', v) }
        }
    }
}
</script>