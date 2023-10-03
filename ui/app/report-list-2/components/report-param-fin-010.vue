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
                

                <v-layout row wrap mb-3>
                    <v-flex xs12>
                        <v-select
                            :items="endyears"
                            item-text="label2"
                            item-value="edate"
                            return-object
                            v-model="start_month"
                            label="Dari Bulan"
                        >
                        </v-select>
                    </v-flex>
                </v-layout>

                <v-layout row wrap mb-3>
                    <v-flex xs12>
                        <v-select
                            :items="endyears"
                            item-text="label2"
                            item-value="edate"
                            return-object
                            v-model="end_month"
                            label="Dari Bulan"
                        >
                        </v-select>
                    </v-flex>
                </v-layout>
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="success" @click="dialog=!dialog" flat>Tutup</v-btn>
                <v-btn color="success" @click="generate"
                    :disabled="!start_month||!end_month">Tampilkan</v-btn>
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
            get () { return this.$store.state.report_param.dialog['fin-010'] },
            set (v) { this.$store.commit('report_param/set_dialog', ['fin-010', v]) }
        },

        report_url () {
            return this.$store.state.report_param.report_url
        },

        params () {
            return ['sdate='+this.start_month.sdate, 'edate='+this.end_month.edate].join('&')
        },

        endyears () {
            return this.$store.state.report_param.months2
        },

        start_month : {
            get () { return this.$store.state.fin010.start_month },
            set (v) { this.$store.commit('fin010/set_object', ['start_month', v]) }
        },

        end_month : {
            get () { return this.$store.state.fin010.end_month },
            set (v) { this.$store.commit('fin010/set_object', ['end_month', v]) }
        },

        selected_report () {
            return this.$store.state.report.selected_report
        }
    },

    methods : {
        generate () {
            // if ( (this.sdate.split('-'))[1] != (this.edate.split('-'))[1] ) {
            //     alert('Laporan harus pada bulan yang sama :(')
            //     return
            // }
            let urls = this.$store.state.report.URL+'report/'+this.selected_report.report_url+
                        '/excel?'+this.params
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
        this.$store.dispatch("report_param/search_months").then((x) => {
            this.$store.commit("fin010/set_object", ["start_month", x[0]])
            this.$store.commit("fin010/set_object", ["end_month", x[0]])
        })
        // this.$store.dispatch('report_param/search_month')
        // this.$store.dispatch('report_param/search_item')
    }
}
</script>