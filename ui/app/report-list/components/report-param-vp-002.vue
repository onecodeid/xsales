<template>
    <v-dialog
        v-model="dialog" 
        max-width="500px"
        transition="dialog-transition"
    >
        <v-card>
            <v-card-title primary-title class="green white--text pt-2 pb-1">
                <h3 class="headline">{{ selected_report.report_name }}</h3>    
            </v-card-title>

            <v-card-text>
                <v-layout row wrap mb-3>
                    <v-flex xs6 pr-2>
                        <common-datepicker label="Tanggal Awal" data="1" :solo="false" @change="change_sdate" :date="sdate"></common-datepicker>
                    </v-flex>
                    <v-flex xs6 pl-2>
                        <common-datepicker label="Tanggal Akhir" data="1" :solo="false" @change="change_edate" :date="edate"></common-datepicker>
                    </v-flex>
                </v-layout>

                <v-layout row wrap mb-3>
                    
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

                <v-layout row wrap>
                    <v-flex xs12>
                        <v-checkbox label="Semua Invoice" v-model="invoice_all" :value="false" :input-value="false"
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
            get () { return this.$store.state.report_param.dialog['vp-002'] },
            set (v) { this.$store.commit('report_param/set_dialog', ['vp-002', v]) }
        },

        report_url () {
            return this.$store.state.report_param.report_url
        },

        params () {
            return ['sdate='+this.sdate, 'edate='+this.edate, 'customerid='+(this.selected_customer?this.selected_customer.customer_id:''), 'all='+(this.invoice_all?'Y':'N')].join('&')
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
        },

        invoice_all : {
            get () { return this.$store.state.report_param.invoice_all },
            set (v) { this.$store.commit('report_param/set_common', ['invoice_all', v]) }
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
        },

        thr_search: _.debounce( function () {
            this.$store.dispatch("report_param/search_customer")
        }, 700)
    },

    mounted () {
        this.$store.dispatch('report_param/search_region')
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