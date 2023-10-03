<template>
    <v-dialog
        v-model="dialog"
        scrollable
        
        max-width="500px"
        transition="dialog-transition"
    >
        <v-card>
            <v-card-title class="primary white--text">
                <h3 class="headline">Histori Pengiriman</h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <template v-for="(m, i) in manifests">
                        <v-flex xs3 :key="i+'-1'" class="text-xs-right pr-2 blue--text mb-2">
                            <div>{{ m.manifest_date }}</div>
                            <div>{{ m.manifest_time }}</div>
                        </v-flex>
                        <v-flex xs8 :key="i+'2'" class="mb-2">
                            {{ m.manifest_description }}
                        </v-flex>
                    </template>
                </v-layout>
            </v-card-text>
        </v-card>
    </v-dialog>
</template>

<script>
module.exports = {
    props : ['manifest'],
    computed : {
        dialog : {
            get () { return this.$store.state.dialog_manifest },
            set (v) { this.$store.commit('set_dialog_manifest', v) }
        },

        manifests () {
            return this.manifest
        }
    }
}
</script>