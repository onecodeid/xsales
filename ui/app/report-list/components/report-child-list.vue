<template>
    <v-card>
        <v-card-text class="pa-2">
            <v-layout row wrap>
                <v-flex xs4 v-for="(r, n) in reports" v-bind:key="n" pr-2 :class="{'mt-2':n>2}">
                    <v-card @click="select(r)">
                        <v-card-text class="pa-1">
                            <v-layout row wrap>
                                <v-flex xs2>
                                    <v-btn color="success" large class="ma-0 btn-icon-report"><v-icon large>assessment</v-icon></v-btn>
                                </v-flex>
                                <v-flex xs10 pl-2>
                                    <h3 class="subheading">{{ r.report_name }}</h3>
                                    <div class="caption">{{ r.report_code }}</div>
                                </v-flex>
                            </v-layout>
                            
                            
                        </v-card-text>
                    </v-card>
                </v-flex>
            </v-layout>   
        </v-card-text>
        
        <!-- PARAMS -->
        <!-- <report-param-vp-001 v-if="dialog['vp-001']"></report-param-vp-001>
        <report-param-vp-002 v-if="dialog['vp-002']"></report-param-vp-002> -->
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
        <report-param-pur-002 v-if="dialog['pur-002']"></report-param-pur-002>
    </v-card>
</template>

<style scoped>
    .btn-icon-report {
        padding: 5px !important;
        min-width: 0px !important;
    }
</style>

<script>
module.exports = {
    components :{
        // "report-param-vp-001" : httpVueLoader("./report-param-vp-001.vue?abcde"),
        // "report-param-vp-002" : httpVueLoader("./report-param-vp-002.vue?abcde"),
        "report-param-sales-003" : httpVueLoader("./report-param-sales-003.vue?a"),
        "report-param-sales-008" : httpVueLoader("./report-param-sales-008.vue?a"),
        "report-param-sales-009" : httpVueLoader("./report-param-sales-009.vue?a"),
        "report-param-sales-011" : httpVueLoader("./report-param-sales-011.vue?a"),
        "report-param-sales-012" : httpVueLoader("./report-param-sales-012.vue?abc"),
        "report-param-sales-013" : httpVueLoader("./report-param-sales-013.vue?a"),
        "report-param-sales-014" : httpVueLoader("./report-param-sales-014.vue?a"),
        "report-param-iv-001" : httpVueLoader("./report-param-iv-001.vue?a"),
        "report-param-iv-003" : httpVueLoader("./report-param-iv-003.vue?a"),
        "report-param-iv-004" : httpVueLoader("./report-param-iv-004.vue?a"),
        "report-param-fin-001" : httpVueLoader("./report-param-fin-001.vue?a"),
        "report-param-fin-002" : httpVueLoader("./report-param-fin-002.vue?a"),
        "report-param-fin-003" : httpVueLoader("./report-param-fin-003.vue?a"),
        "report-param-fin-005" : httpVueLoader("./report-param-fin-005.vue?a"),
        "report-param-fin-006" : httpVueLoader("./report-param-fin-006.vue?a"),
        "report-param-fin-007" : httpVueLoader("./report-param-fin-007.vue?a"),
        "report-param-pur-002" : httpVueLoader("./report-param-pur-002.vue?a")
    },

    computed : {
        reports () {
            if (this.$store.state.report.selected_group)
                return this.$store.state.report.selected_group.childs
            return []
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
            //open
            this.dialog = true
        }
    }
}
</script>