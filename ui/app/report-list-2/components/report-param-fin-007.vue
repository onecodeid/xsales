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

    data () {
        return {
            x : 0
        }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.report_param.dialog['fin-007'] },
            set (v) { this.$store.commit('report_param/set_dialog', ['fin-007', v]) }
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

        endyears () {
            return this.$store.state.report_param.endyears
        },

        selected_endyear : {
            get () { return this.$store.state.report_param.selected_endyear },
            set (v) { this.$store.commit('report_param/set_object', ['selected_endyear', v]) }
        },

        URL () {
            return this.$store.state.report_param.URL
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
            let urls = this.$store.state.report.URL+'report/'+this.selected_report.report_url+
                        '?'+this.params
            console.log(urls)
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
        })
    },

    beforeMount () {
        // this.generate()
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