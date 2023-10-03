<template>
    <v-card>
        <v-card-text>
            <v-layout row wrap>
                <v-flex xs12 sm4 md3 :class="{'pr-4':$vuetify.breakpoint.smAndUp}">
                    <v-text-field
                        label="Nominal PPN"
                        v-model="ppn"
                        type="number"
                        reverse
                        prefix="%"
                    ></v-text-field>
                </v-flex>
            </v-layout>        
        </v-card-text>
        <v-card-actions>
            <v-spacer v-if="$vuetify.breakpoint.smAndUp"></v-spacer>
            <v-btn color="primary" :block="$vuetify.breakpoint.xsOnly" @click="save_conf">Simpan</v-btn>
            <v-spacer v-if="$vuetify.breakpoint.smOnly"></v-spacer>
        </v-card-actions>
    </v-card>
    
</template>

<script>
module.exports = {
    computed : {
        ppn : {
            get () { return this.$store.state.setting.ppn },
            set (v) { this.$store.commit('setting/set_common', ['ppn', v]) }
        }
    },

    methods : {
        save_conf () {
            this.$store.dispatch('setting/save_conf')
        }
    },

    mounted () {

    }
}
</script>
