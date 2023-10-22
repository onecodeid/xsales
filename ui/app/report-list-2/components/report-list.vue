<template>
    <v-card>
        <v-card-text class="pa-0">
            <v-layout row wrap v-for="(g, n) in groups" v-bind:key="n" mb-2>
                <v-flex xs12>
                    <v-card>
                        <v-card-title primary-title class="primary white--text text-xs-center body-2 py-2">
                            {{  g.report_name.toUpperCase()  }}
                        </v-card-title>
                        <v-card-text>
                            <v-layout row wrap>
                                <v-flex xs12 md6 lg4 v-for="(r, m) in g.childs" v-bind:key="m" pr-2 :class="['mb-2']">
                                    <v-card @click="select(r)" class="report-items">
                                        <v-card-text class="pa-1">
                                            <v-layout row wrap>
                                                <v-flex xs1 class="text-xs-center d-flex align-center">
                                                    <v-icon :color="icon_colors[r.report_type]" large>{{ r.report_icon }}</v-icon>
                                                    <!-- <v-btn color="success" large class="ma-0 btn-icon-report" depressed flat disabled><v-icon large>assessment</v-icon></v-btn> -->
                                                </v-flex>
                                                <v-flex xs11 pl-2>
                                                    <h3 class="subheading">{{ r.report_name }}</h3>
                                                    <div class="caption">{{ r.report_code }}</div>
                                                </v-flex>
                                            </v-layout>
                                        </v-card-text>
                                    </v-card>
                                </v-flex>
                            </v-layout>
                            
                        </v-card-text>
                    </v-card>
                </v-flex>
            </v-layout>
            
        </v-card-text>

        <report-data-dialog></report-data-dialog>
        <report-param-sales-003 v-if="dialog['sales-003']"></report-param-sales-003>
        <report-param-sales-008 v-if="dialog['sales-008']"></report-param-sales-008>
        <report-param-sales-009 v-if="dialog['sales-009']"></report-param-sales-009>
        <report-param-sales-011 v-if="dialog['sales-011']"></report-param-sales-011>
        <report-param-sales-012 v-if="dialog['sales-012']"></report-param-sales-012>
        <report-param-sales-013 v-if="dialog['sales-013']"></report-param-sales-013>
        <report-param-sales-014 v-if="dialog['sales-014']"></report-param-sales-014>
        <report-param-iv-001 v-if="dialog['iv-001']"></report-param-iv-001>
        <report-param-iv-003 v-if="dialog['iv-003']"></report-param-iv-003>
        <report-param-iv-004 v-if="dialog['iv-004']"></report-param-iv-004>
        <report-param-fin-001 v-if="dialog['fin-001']"></report-param-fin-001>
        <report-param-fin-002 v-if="dialog['fin-002']"></report-param-fin-002>
        <report-param-fin-003 v-if="dialog['fin-003']"></report-param-fin-003>
        <report-param-fin-005 v-if="dialog['fin-005']"></report-param-fin-005>
        <report-param-fin-006 v-if="dialog['fin-006']"></report-param-fin-006>
        <report-param-fin-007 v-if="dialog['fin-007']"></report-param-fin-007>
        <report-param-fin-010 v-if="dialog['fin-010']"></report-param-fin-010>
        <report-param-fin-021 v-if="dialog['fin-021']"></report-param-fin-021>
        <report-param-pur-002 v-if="dialog['pur-002']"></report-param-pur-002>

    </v-card>
            
        
</template>

<style scoped>
    .v-list--two-line .v-list__tile {
        height: 64px;
    }

    .btn-icon-report {
        padding: 5px !important;
        min-width: 0px !important;
    }
    .report-items:hover {
        background-color: #DDEDED;
        cursor: pointer
    }

</style>

<script>
let t = "?t=" + Math.round(Math.random() * 1e11)
module.exports = {
    components : {
        "report-data-dialog" : httpVueLoader("./report-data-dialog.vue"+t),
        "report-param-sales-003" : httpVueLoader("./report-param-sales-003.vue"+t),
        "report-param-sales-008" : httpVueLoader("./report-param-sales-008.vue"+t),
        "report-param-sales-009" : httpVueLoader("./report-param-sales-009.vue"+t),
        "report-param-sales-011" : httpVueLoader("./report-param-sales-011.vue"+t),
        "report-param-sales-012" : httpVueLoader("./report-param-sales-012.vue"+t),
        "report-param-sales-013" : httpVueLoader("./report-param-sales-013.vue"+t),
        "report-param-sales-014" : httpVueLoader("./report-param-sales-014.vue"+t),
        "report-param-iv-001" : httpVueLoader("./report-param-iv-001.vue"+t),
        "report-param-iv-003" : httpVueLoader("./report-param-iv-003.vue"+t),
        "report-param-iv-004" : httpVueLoader("./report-param-iv-004.vue"+t),
        "report-param-fin-001" : httpVueLoader("./report-param-fin-001.vue"+t),
        "report-param-fin-002" : httpVueLoader("./report-param-fin-002.vue"+t),
        "report-param-fin-003" : httpVueLoader("./report-param-fin-003-2.vue"+t),
        "report-param-fin-005" : httpVueLoader("./report-param-fin-005.vue"+t),
        "report-param-fin-006" : httpVueLoader("./report-param-fin-006.vue"+t),
        "report-param-fin-007" : httpVueLoader("./report-param-fin-007.vue"+t),
        "report-param-fin-010" : httpVueLoader("./report-param-fin-010.vue"+t),
        "report-param-fin-021" : httpVueLoader("./report-param-fin-021.vue"+t),
        "report-param-pur-002" : httpVueLoader("./report-param-pur-002.vue"+t)
    },

    data () {
        return {
            icon_colors : {
                'PDF' : 'red',
                'DATA' : 'cyan'
            }
        }
    },

    computed : {
        groups () {
            return this.$store.state.report.groups
        },

        selected_group () {
            return this.$store.state.report.selected_group
        },

        selected_report () {
            return this.$store.state.report.selected_report
        },

        dialog : {
            get () { return this.$store.state.report_param.dialog },
            set (v) {
                let x = this.selected_report.report_code.replace(/ONE\-/, '').toLowerCase()
                this.$store.commit('report_param/set_dialog', [x, v]) 
            }
        }
    },

    methods : {
        select (x) {
            this.$store.commit('report/set_selected_report', x)

            if (x.report_type == 'PDF')
                //open
                this.dialog = true
            else if (x.report_type == 'DATA') {
                // let code = x.report_code.replace("ONE-", "").toLowerCase()
                // special
                code = x.report_code.replace("PUR-", "PURCHASE-").toLowerCase()
                window.open('../'+code)
                // this.$store.commit('report_param/set_object', ['dialog_print_data', true])
                // this.$store.commit('report_param/set_data_show', [code, true])
            }
        },

        is_selected (x) {
            if (!this.selected_group)
                return false
            if (x.report_id == this.selected_group.report_id)
                return true
            return false
        }
    },

    mounted () {
        this.$store.dispatch('report/search_groups')
    }
}
</script>