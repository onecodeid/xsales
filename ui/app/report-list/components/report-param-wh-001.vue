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
                            label="Customer"
                            v-model="selected_customer"
                            :items="customers"
                            :search-input.sync="search_customer"
                            auto-select-first
                            no-filter
                            return-object
                            :clearable="true"
                            item-text="M_CustomerName"
                            :loading="false"
                            no-data-text="Pilih Customer"
                            >
                            <template
                                slot="item"
                                slot-scope="{ item }"
                                >
                                <v-list-tile-content>
                                    <v-list-tile-title v-text="item.M_CustomerName"></v-list-tile-title>
                                <v-list-tile-sub-title v-text="item.M_CustomerLevelName + ', ' + item.M_CityName"></v-list-tile-sub-title>
                                </v-list-tile-content>
                            </template>

                        </v-autocomplete>
                    </v-flex>
                </v-layout>

                <v-layout row wrap mb-3>
                    <v-flex xs12>
                        <v-select
                            :items="orders"
                            item-text="L_SoNumber"
                            item-value="L_SoID"
                            return-object
                            v-model="selected_order"
                            label="Nomor Order"
                            hint="20 Order terakhir"
                            persistent-hint
                        >
                            <template slot="selection" slot-scope="data">
                                <!-- HTML that describe how select should render selected items -->
                                <v-layout row>
                                    <v-flex xs6 class="blue--text">
                                        {{ data.item.L_SoNumber }}
                                    </v-flex>
                                    <v-flex xs6 class="text-xs-right caption">
                                        {{ data.item.L_SoDate.substr(0,10).split('-').reverse().join('-') }}
                                    </v-flex>
                                </v-layout>
                            </template>
                            <template slot="item" slot-scope="data">
                                <v-layout column>
                                    <v-flex xs1 class="blue--text">
                                        {{ data.item.L_SoNumber }}
                                    </v-flex>
                                    <v-flex xs1 class="caption">
                                        {{ data.item.L_SoDate.substr(0,10).split('-').reverse().join('-') }}
                                    </v-flex>
                                    <v-flex xs1 mt-1>
                                        <v-divider class="blue lighten-3"></v-divider>
                                    </v-flex>
                                </v-layout>
                                
                            </template>
                        </v-select>
                    </v-flex>
                </v-layout>

                <v-layout row wrap>
                    <v-flex xs12>
                        
                    </v-flex>
                </v-layout>
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="success" @click="dialog=!dialog" flat>Tutup</v-btn>
                <v-btn color="success" @click="generate"
                    :disabled="!selected_customer">Tampilkan</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
module.exports = {
    components : {
        'common-datepicker': httpVueLoader('./../../common/components/common-datepicker.vue')
    },

    computed : {
        dialog : {
            get () { return this.$store.state.report_param.dialog['wh-001'] },
            set (v) { this.$store.commit('report_param/set_dialog', ['wh-001', v]) }
        },

        params () {
            return ['soid='+this.selected_order.L_SoID].join('&')
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

        orders () {
            if (this.selected_customer)
                return this.selected_customer.orders
            return []
        },

        selected_order : {
            get () { return this.$store.state.report_param.selected_order },
            set (v) { this.$store.commit('report_param/set_selected_order', v) }
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

        thr_search: _.debounce( function () {
            this.$store.dispatch("report_param/search_customer")
        }, 700)
    },

    mounted () {
        this.$store.dispatch('report_param/search_admins')
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