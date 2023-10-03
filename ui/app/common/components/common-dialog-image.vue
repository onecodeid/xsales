<template>
    <v-dialog
        v-model="dialog"
        max-width="500px"
        :height="height"
        transition="dialog-transition"
        content-class="zalfa-dialog-print"
    >
        <v-card class="fill-height" :height="height" style="display:flex;flex-direction:column">
            <v-card-title class="cyan white--text pb-2 pt-2" style="flex:0">
                <h3 class="ml-2">{{ title }}</h3>
                <v-spacer></v-spacer>
                <v-btn color="red" dark @click="dialog=!dialog" class="ma-0" small style="min-width:0px">
                    <v-icon>clear</v-icon>
                </v-btn>
            </v-card-title>

            <v-card-text class="grow pa-1" grow style="flex:1">
                <!-- <object  style="overflow: hidden;" width="100%" :height="xheight" :data="xurl"></object> -->
                <v-img width="100%" height="100%" :src="init_image_url"></v-img>
            </v-card-text>

            <!-- <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="red" dark @click="dialog=!dialog">Tutup</v-btn>
            </v-card-actions> -->
        </v-card>
    </v-dialog>
</template>

<style>
.zalfa-dialog-print {
    margin: 12px !important;
    max-height: 95% !important;
}
</style>

<script>
module.exports = {
    props : ['title', 'image_url'],
    data () {
        return {
            init_image_url: this.image_url ? this.image_url : ''
        }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.dialog_image },
            set (v) { this.$store.commit('set_dialog_image', v) }
        },

        height () {
            return window.innerHeight * 0.95
        }
    },

    methods : {
    }
}
</script>