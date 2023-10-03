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
                        <v-autocomplete
                            label="Propinsi"
                            v-model="selected_province"
                            :items="provinces"
                            auto-select-first
                            return-object
                            clearable
                            item-text="M_ProvinceName"
                            item-value="M_ProvinceID"
                            placeholder="Pilih Propinsi"
                            hint="Kosongkan untuk memilih semua propinsi"
                            persistent-hint
                            >
                            <template
                                slot="item"
                                slot-scope="{ item }"
                                >
                                <v-list-tile-content>
                                <v-list-tile-title v-text="item.M_ProvinceName"></v-list-tile-title>
                                <!-- <v-list-tile-sub-title v-text="getAddress(item)"></v-list-tile-sub-title> -->
                                </v-list-tile-content>
                            </template>

                        </v-autocomplete>
                    </v-flex>
                </v-layout>

                <v-layout row wrap mb-3>
                    <v-flex xs12>
                        <v-autocomplete
                            label="Kota"
                            v-model="selected_city"
                            :items="cities"
                            auto-select-first
                            return-object
                            clearable
                            item-text="M_CityName"
                            item-value="M_CityID"
                            placeholder="Pilih Kota"
                            :disabled="selected_province == null"
                            hint="Kosongkan untuk memilih semua kota"
                            persistent-hint
                            >
                            <template
                                slot="item"
                                slot-scope="{ item }"
                                >
                                <v-list-tile-content>
                                <v-list-tile-title v-text="item.M_CityName"></v-list-tile-title>
                                <!-- <v-list-tile-sub-title v-text="getAddress(item)"></v-list-tile-sub-title> -->
                                </v-list-tile-content>
                            </template>

                        </v-autocomplete>
                    </v-flex>
                </v-layout>

                <v-layout row wrap mb-3>
                    <v-flex xs12>
                        <v-autocomplete
                            label="Kota"
                            v-model="selected_customer_level"
                            :items="customer_levels"
                            auto-select-first
                            return-object
                            clearable
                            item-text="M_CustomerLevelName"
                            item-value="M_CustomerLevelID"
                            placeholder="Pilih Level / Jenjang"
                            hint="Kosongkan untuk memilih semua level"
                            persistent-hint
                            >
                            <template
                                slot="item"
                                slot-scope="{ item }"
                                >
                                <v-list-tile-content>
                                <v-list-tile-title v-text="item.M_CustomerLevelName"></v-list-tile-title>
                                <!-- <v-list-tile-sub-title v-text="getAddress(item)"></v-list-tile-sub-title> -->
                                </v-list-tile-content>
                            </template>

                        </v-autocomplete>
                    </v-flex>
                </v-layout>

                
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
            get () { return this.$store.state.report_param.dialog['master-001'] },
            set (v) { this.$store.commit('report_param/set_dialog', ['master-001', v]) }
        },

        report_url () {
            return this.$store.state.report_param.report_url
        },

        params () {
            return ['province_id='+(this.selected_province?this.selected_province.M_ProvinceID:0), 
                    'city_id='+(this.selected_city?this.selected_city.M_CityID:0),
                    'level_id='+(this.selected_customer_level?this.selected_customer_level.M_CustomerLevelID:0),
                    'token='+this.$store.state.report_param.token].join('&')
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

        admins () {
            return this.$store.state.report_param.admins
        },

        selected_admin : {
            get () { return this.$store.state.report_param.selected_admin },
            set (v) { this.$store.commit('report_param/set_selected_admin', v) }
        },

        selected_city : {
            get () { return this.$store.state.report_param.selected_city },
            set (v) { 
                this.$store.commit('report_param/set_selected_city', v)
            }
        },

        cities () {
            return this.$store.state.report_param.cities
        },

        provinces () {
            return this.$store.state.report_param.provinces
        },

        selected_province : {
            get () { return this.$store.state.report_param.selected_province },
            set (v) { 
                this.$store.commit('report_param/set_selected_province', v)
                this.$store.dispatch('report_param/search_city')
            }
        },

        customer_levels () {
            return this.$store.state.report_param.customer_levels
        },

        selected_customer_level : {
            get () { return this.$store.state.report_param.selected_customer_level },
            set (v) { 
                this.$store.commit('report_param/set_selected_customer_level', v)
            }
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
        this.$store.dispatch('report_param/search_province')
        this.$store.dispatch('report_param/search_customer_level')
    }
}
</script>