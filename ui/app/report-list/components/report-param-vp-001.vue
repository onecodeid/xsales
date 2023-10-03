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
                        <common-datepicker label="Tanggal Awal" data="1" :solo="false" @change="change_sdate" :date="sdate"></common-datepicker>
                    </v-flex>
                </v-layout>

                <v-layout row wrap mb-3>
                    <v-flex xs12>
                        <common-datepicker label="Tanggal Akhir" data="1" :solo="false" @change="change_edate" :date="edate"></common-datepicker>
                    </v-flex>
                </v-layout>

                <v-layout row wrap>
                    <v-flex xs12>
                        <v-autocomplete
                            :items="regions"
                            item-text="region_name"
                            item-value="region_id"
                            return-object
                            v-model="selected_region"
                            label="Pilih Region"
                        ></v-autocomplete>
                    </v-flex>
                </v-layout>

                <v-layout row wrap>
                    <v-flex xs12>
                        <v-autocomplete
                            :items="staffs"
                            item-text="staff_name"
                            item-value="staff_id"
                            return-object
                            v-model="selected_staff"
                            label="Pilih Sales"
                        ></v-autocomplete>
                    </v-flex>
                </v-layout>

                <v-layout row wrap>
                    <v-flex xs12>
                        <v-checkbox label="Form Kosong" v-model="blank_sheet" :value="false" :input-value="false"
                            :true-value="true"
                            :false-value="false"></v-checkbox>
                    </v-flex>
                </v-layout>

                <!-- <v-layout row wrap>
                    <v-flex xs12>
                        <v-select
                            :items="admins"
                            item-text="user_full_name"
                            item-value="user_id"
                            return-object
                            v-model="selected_admin"
                            label="Admin"
                        ></v-select>
                    </v-flex>
                </v-layout> -->
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="success" @click="dialog=!dialog" flat>Tutup</v-btn>
                <v-btn color="success" @click="generate"
                    :disabled="false">Tampilkan</v-btn>
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
            get () { return this.$store.state.report_param.dialog['vp-001'] },
            set (v) { this.$store.commit('report_param/set_dialog', ['vp-001', v]) }
        },

        report_url () {
            return this.$store.state.report_param.report_url
        },

        params () {
            return ['sdate='+this.sdate, 'edate='+this.edate, 'regionid='+(this.selected_region?this.selected_region.region_id:0), 'salesid='+(this.selected_staff?this.selected_staff.staff_id:0),this.blank_sheet?'blank=true':''].join('&')
        },

        edate : {
            get () { return this.$store.state.report_param.edate },
            set (v) { this.$store.commit('report_param/set_common', ['edate', v]) }
        },

        sdate : {
            get () { return this.$store.state.report_param.sdate },
            set (v) { this.$store.commit('report_param/set_common', ['sdate', v]) }
        },

        selected_report () {
            return this.$store.state.report.selected_report
        },

        staffs () {
            return this.$store.state.report_param.staffs
        },

        selected_staff : {
            get () { return this.$store.state.report_param.selected_staff },
            set (v) { this.$store.commit('report_param/set_selected_staff', v) }
        },

        regions () {
            return this.$store.state.report_param.regions
        },

        selected_region : {
            get () { return this.$store.state.report_param.selected_region },
            set (v) { this.$store.commit('report_param/set_selected_region', v) }
        },

        blank_sheet : {
            get () { return this.$store.state.report_param.blank_sheet },
            set (v) { this.$store.commit('report_param/set_common', ['blank_sheet', v]) }
        }
    },

    methods : {
        generate () {
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
        this.$store.dispatch('report_param/search_region')
        this.$store.dispatch('report_param/search_staff')
    }
}
</script>