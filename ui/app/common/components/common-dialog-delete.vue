<template>
    <v-dialog
        v-model="dialog"
        max-width="500px"
        transition="dialog-transition"
    >
        <v-card>
            <v-card-title primary-title class="red white--text">
                <v-icon color="white">error_outline</v-icon> <h3 class="ml-2">Konfirmasi Hapus</h3>
            </v-card-title>

            <v-card-text>
                Apakah anda yakin akan menghapus data tersebut ?
            </v-card-text>

            <v-card-actions>
                <v-btn @click="dialog=false">Batal</v-btn>
                <v-spacer></v-spacer>
                <v-btn color="red" dark @click="del">Hapus</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
module.exports = {
    props : ['data'],
    data () {
        return {}
    },

    computed : {
        dialog : {
            get () { return this.$store.state.dialog_delete },
            set (v) { this.$store.commit('set_dialog_delete', v) }
        }
    },

    methods : {
        del () {
            this.$emit('confirm_del', {data: this.data});
            this.dialog = false
        }
    }
}
</script>