<template>
    <v-dialog
        v-model="dialog" 
        max-width="300px"
        transition="dialog-transition"
    >
        <v-card>
            <v-card-title primary-title class="green white--text pt-2 pb-1">
                <h3 class="headline">{{ selected_report.report_name }}</h3>    
            </v-card-title>

            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                        <v-select
                            :items="endyears"
                            item-text="label"
                            item-value="edate"
                            return-object
                            v-model="selected_endyear"
                            label="Periode"
                            clearable
                        >
                        </v-select>
                    </v-flex>
                </v-layout>

                <!-- <v-layout row wrap mb-3>
                    <v-flex xs12>
                        <common-datepicker label="Tanggal Awal" data="1" :solo="false" @change="change_sdate" :date="sdate"></common-datepicker>
                    </v-flex>
                </v-layout>

                <v-layout row wrap mb-3>
                    <v-flex xs12>
                        <common-datepicker label="Tanggal Akhir" data="1" :solo="false" @change="change_edate" :date="edate"></common-datepicker>
                    </v-flex>
                </v-layout> -->
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="success" @click="dialog=!dialog" flat>Tutup</v-btn>
                <v-btn color="success" @click="generate" :disabled="!selected_endyear">Tampilkan</v-btn>
            </v-card-actions>
        </v-card>
        <!-- <common-dialog-print :report_url="report_url" v-if="dialog_report"></common-dialog-print> -->
    </v-dialog>
</template>

<script>
module.exports = {
    components : {
        'common-datepicker': httpVueLoader('./../../common/components/common-datepicker.vue'),
        // "common-dialog-print" : httpVueLoader("./../../common/components/common-dialog-print.vue")
    },

    computed : {
        dialog : {
            get () { return this.$store.state.report_param.dialog['fin-003'] },
            set (v) { this.$store.commit('report_param/set_dialog', ['fin-003', v]) }
        },

        report_url () {
            return this.$store.state.report_param.report_url
        },

        params () {
            // return ['sdate='+this.sdate, 'edate='+this.edate].join('&')
            return ['sdate='+this.selected_endyear.sdate, 'edate='+this.selected_endyear.edate].join('&')
        },

        selected_report () {
            return this.$store.state.report.selected_report
        },

        edate : {
            get () { return this.$store.state.report_param.edate },
            set (v) { this.$store.commit('report_param/set_common', ['edate', v]) }
        },

        sdate : {
            get () { return this.$store.state.report_param.sdate },
            set (v) { this.$store.commit('report_param/set_common', ['sdate', v]) }
        },

        URL () {
            return this.$store.state.report_param.URL
        },

        endyears () {
            return this.$store.state.report_param.months2
        },

        selected_endyear : {
            get () { return this.$store.state.report_param.selected_month2 },
            set (v) { this.$store.commit('report_param/set_object', ['selected_month2', v]) }
        }
    },

    methods : {
        generate () {
            let sd = new Date(this.sdate)
            let ed = new Date(this.edate)
            if (sd.getFullYear() != ed.getFullYear()) {
                alert("Harus di periode TAHUN yang sama !")
                return
            }

            let urls = this.$store.state.report.URL+'report/'+this.selected_report.report_url+
                        '?'+this.params
            this.$store.commit('report_param/set_common', ['report_url', urls])
            this.$store.commit('set_dialog_print', true)

            this.dialog=!this.dialog
        },

        change_edate(x) {
            this.edate = x.new_date
        },

        change_sdate(x) {
            this.sdate = x.new_date
        }
    },

    mounted () {
        // if (this.endyears.length < 1)
        //     this.$store.dispatch('report_param/search_endyear')
        if (this.endyears.length < 1)
            this.$store.dispatch('report_param/search_months')
    }
}
</script>