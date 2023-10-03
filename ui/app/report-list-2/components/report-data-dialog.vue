<template>
    <v-dialog
        v-model="dialog"
        persistent
        max-width="1300px"
        transition="dialog-transition"
        content-class="zalfa-dialog-print"
        scrollable
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pb-2 pt-2">
                <h3 class="ml-2" :title="init_report_url">
                    Laporan
                </h3>
                <v-spacer></v-spacer>
                <v-btn color="red" dark @click="dialog=!dialog" class="ma-0" small style="min-width:0px">
                    <v-icon>clear</v-icon>
                </v-btn>
            </v-card-title>

            <v-card-text>
                <!-- <report-data-sales-015 v-if="dataShow['sales-015']"></report-data-sales-015>
                <report-data-sales-016 v-if="dataShow['sales-016']"></report-data-sales-016>
                <report-data-sales-017 v-if="dataShow['sales-017']"></report-data-sales-017>
                <report-data-purchase-002 v-if="dataShow['pur-002']"></report-data-purchase-002>
                <report-data-purchase-003 v-if="dataShow['pur-003']"></report-data-purchase-003>
                <report-data-purchase-004 v-if="dataShow['pur-004']"></report-data-purchase-004>
                <report-data-purchase-005 v-if="dataShow['pur-005']"></report-data-purchase-005>
                <report-data-iv-005 v-if="dataShow['iv-005']"></report-data-iv-005>
                <report-data-fin-006 v-if="dataShow['fin-006']"></report-data-fin-006>
                <report-data-fin-002 v-if="dataShow['fin-002']"></report-data-fin-002>
                <report-data-fin-008 v-if="dataShow['fin-008']"></report-data-fin-008>
                <report-data-fin-009 v-if="dataShow['fin-009']"></report-data-fin-009> -->
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="red" dark @click="dialog=!dialog">Tutup</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<style>
.zalfa-dialog-print {
    /* margin: 12px !important;
    max-height: 95% !important; */
}
</style>

<script>
let t = "?t=" + Math.round(Math.random() * 1e11)
module.exports = {
    props : ['data', 'report_url'],
    components: {
        // "report-data-sales-015" : httpVueLoader("./report-data-sales-015.vue" + t),
        // "report-data-sales-016" : httpVueLoader("./report-data-sales-016.vue" + t),
        // "report-data-sales-017" : httpVueLoader("./report-data-sales-017.vue" + t),
        // "report-data-purchase-002" : httpVueLoader("./report-data-purchase-002.vue" + t),
        // "report-data-purchase-003" : httpVueLoader("./report-data-purchase-003.vue" + t),
        // "report-data-purchase-004" : httpVueLoader("./report-data-purchase-004.vue" + t),
        // "report-data-purchase-005" : httpVueLoader("./report-data-purchase-005.vue" + t),
        // "report-data-iv-005" : httpVueLoader("./report-data-iv-005.vue" + t),
        // "report-data-fin-006" : httpVueLoader("./report-data-fin-006.vue" + t),
        // "report-data-fin-002" : httpVueLoader("./report-data-fin-002-2.vue" + t),
        // "report-data-fin-008" : httpVueLoader("./report-data-fin-008.vue" + t),
        // "report-data-fin-009" : httpVueLoader("./report-data-fin-009.vue" + t)
    },

    data () {
        return {
            init_report_url: this.report_url ? this.report_url : ''
        }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.report_param.dialog_print_data },
            set (v) { 
                this.$store.commit('report_param/set_object', ['dialog_print_data', v])
                if (!v) {
                    let x = JSON.parse(JSON.stringify(this.dataShow))
                    for (let y in x) x[y] = false
                    this.dataShow = x
                }
            }
        },

        dataShow : {
            get () { return this.$store.state.report_param.dataShow },
            set (v) { this.$store.commit('report_param/set_object', ['dataShow', v]) }
        },

        height () {
            return window.innerHeight * 0.95
        },

        selected_report () {
            return this.$store.state.report.selected_report
        }
    },

    methods : {
    },

    mounted () {
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