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
                <!-- <v-layout row wrap>
                    <v-flex xs12>
                        <v-select
                            :items="months"
                            v-model="selected_month"
                            label="Bulan Periode"
                            item-value="month_value"
                            item-text="month_name"
                            return-object
                        ></v-select>
                    </v-flex>
                </v-layout>

                <v-layout row wrap mb-3>
                    <v-flex xs6 pr-2>
                        <v-text-field
                            label="Dari Tanggal"
                            type="number"
                            v-model="start_day"
                        ></v-text-field>
                    </v-flex>
                    <v-flex xs6 pl-2>
                        <v-text-field
                            label="Sampai Tanggal"
                            type="number"
                            v-model="end_day"
                        ></v-text-field>
                    </v-flex>
                </v-layout> -->

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

                <v-layout row wrap mb-3>
                    <v-flex xs12>
                        <v-autocomplete
                            label="Customer"
                            v-model="selected_customer"
                            :items="customers"
                            :search-input.sync="search_customer"
                            auto-select-first
                            no-filter
                            return-object
                            :clearable="true"
                            item-text="customer_name"
                            :loading="false"
                            no-data-text="Pilih Customer"
                            
                            >
                            <template
                                slot="item"
                                slot-scope="{ item }"
                                >
                                <v-list-tile-content>
                                    <v-list-tile-title v-text="item.customer_name"></v-list-tile-title>
                                </v-list-tile-content>
                            </template>

                        </v-autocomplete>
                    </v-flex>
                </v-layout>

                <!-- <v-layout row wrap>
                    <v-flex xs12>
                        <v-select
                            :items="staffs"
                            v-model="selected_staff"
                            return-object
                            item-text="staff_name"
                            item-value="staff_id"
                            label="Sales"
                        ></v-select>
                    </v-flex>
                </v-layout> -->
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="success" @click="dialog=!dialog" flat>Tutup</v-btn>
                <v-btn color="success" @click="generate">Tampilkan</v-btn>
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
            get () { return this.$store.state.report_param.dialog['sales-013'] },
            set (v) { this.$store.commit('report_param/set_dialog', ['sales-013', v]) }
        },

        report_url () {
            return this.$store.state.report_param.report_url
        },

        params () {
            return ['sdate='+this.sdate, 'edate='+this.edate, 'customer='+(this.selected_customer?this.selected_customer.customer_id:0)].join('&')
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

        months () {
            return this.$store.state.report_param.months
        },

        selected_month : {
            get () { return this.$store.state.report.selected_report },
            set (v) { this.$store.commit('report_param/set_selected_month', v) }
        },

        staffs () {
            return this.$store.state.report_param.staffs
        },

        selected_staff : {
            get () { return this.$store.state.report_param.selected_staff },
            set (v) { this.$store.commit('report_param/set_selected_staff', v) }
        },

        customers () {
            return this.$store.state.report_param.customers
        },

        selected_customer : {
            get () { return this.$store.state.report_param.selected_customer },
            set (v) { this.$store.commit('report_param/set_selected_customer', v) }
        },

        search_customer : {
            get () { return this.$store.state.report_param.search_customer },
            set (v) { this.$store.commit('report_param/set_common', ['search_customer', v]) }
        }
    },

    methods : {
        generate () {
            // if ( (this.sdate.split('-'))[1] != (this.edate.split('-'))[1] ) {
            //     alert('Laporan harus pada bulan yang sama :(')
            //     return
            // }
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
        },

        thr_search: _.debounce( function () {
            this.$store.dispatch("report_param/search_customer")
        }, 700)
    },

    mounted () {
        this.$store.dispatch('report_param/search_month')
        this.$store.dispatch('report_param/search_staff')
    },

    watch : {
        search_customer(val, old) {
            if (val == null || typeof val == 'undefined') val = ""
            if (val == old ) return
            if (this.$store.state.report_param.search_status == 1 ) return  
            this.$store.commit("report_param/set_common", ['search_customer', val])
            this.thr_search()
        }
    }
}
</script>