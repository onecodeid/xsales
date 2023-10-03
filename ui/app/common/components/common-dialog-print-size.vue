<template>
    <v-dialog
        v-model="dialog"
        max-width="1000px"
        :height="height"
        transition="dialog-transition"
        content-class="zalfa-dialog-print"
    >
        <v-card class="fill-height" :height="height" style="display:flex;flex-direction:column">
            <v-card-title class="cyan white--text pb-2 pt-2" style="flex:0">
                <h3 class="ml-2" :title="init_report_url">Laporan</h3>
                <v-spacer></v-spacer>
                <v-layout row wrap>
                    <v-flex xs6 offset-xs6 pr-2>
                        <v-select
                            :items="page_sizes"
                            item-value="id"
                            item-text="size"
                            hide-details
                            solo
                            return-object
                            v-model="selected_page_size"
                        >
                            <template slot="item" slot-scope="data">
                                {{data.item.size}} {{data.item.orientation_label}}
                            </template>
                            <template slot="selection" slot-scope="data">
                                {{data.item.size}} {{data.item.orientation_label}}
                            </template>
                            <!-- <template slot="prepend">
                                <v-btn color="success" dark @click="dialog=!dialog" class="ma-0 mt-1 mr-1" small style="min-width:0px">
                                    <v-icon>save</v-icon>
                                </v-btn>
                            </template> -->
                        </v-select>
                    </v-flex>
                </v-layout>
                
                <!-- <v-btn color="success" dark @click="dialog=!dialog" class="ma-0 mr-2" small style="min-width:0px" v-if="!!selected_page_size">
                    <v-icon class="mr-1">note</v-icon> {{selected_page_size.size}} {{selected_page_size.orientation_label}}
                </v-btn> -->
                <v-btn color="red" dark @click="dialog=!dialog" class="ma-0" small style="min-width:0px">
                    <v-icon>clear</v-icon>
                </v-btn>
            </v-card-title>

            <v-card-text class="grow pa-1" grow style="flex:1">
                <!-- <object  style="overflow: hidden;" width="100%" :height="xheight" :data="xurl"></object> -->
                <object :data="init_report_url" type="application/pdf" width="100%" height="100%"></object>
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

.input-dense .v-input__control {
    min-height: 30px !important;
}

.zalfa-dialog-print .v-input__prepend-outer {
    margin: 0px !important;
}

.zalfa-dialog-print .v-text-field.v-text-field--solo .v-input__control {
    min-height: 30px;
    padding: 0;
}

.zalfa-dialog-print .v-input__append-outer {
    margin: 0px !important;
}
</style>

<script>
module.exports = {
    props : ['data', 'report_url'],
    data () {
        return {
            init_report_url: this.report_url ? this.report_url : '',
            init_report_url_ori: this.report_url ? this.report_url : ''
        }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.dialog_print },
            set (v) { this.$store.commit('set_dialog_print', v) }
        },

        height () {
            return window.innerHeight * 0.95
        },

        page_sizes() {
            return this.$store.state.system.page_sizes
        },

        selected_page_size : {
            get () { return this.$store.state.selected_page_size },
            set (v) { 
                this.$store.commit('set_selected_page_size', v)
                this.init_report_url = this.init_report_url_ori + "&size=" +
                                        JSON.stringify([v.orientation, v.size])
            }
        }
    },

    methods : {
    },

    mounted () {
        if (!!this.selected_page_size) {
            this.init_report_url = this.init_report_url_ori + "&size=" +
                                        JSON.stringify([v.orientation, v.size])
        }

        let url = this.init_report_url
        if (url.indexOf('excel')>-1) {
            let e = url.split('?')
            let p = e[1].split('&')
            let prm = {url:e[0]}
            for (let px of p) {
                let x = px.split('=')
                prm[x[0]] = x[1]
            }
    
            this.$store.dispatch("system/report_excel", prm)
        }
    }
}
</script>