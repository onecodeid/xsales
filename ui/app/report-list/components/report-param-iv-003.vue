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
                            :items="warehouses"
                            v-model="selected_warehouse"
                            return-object
                            item-text="warehouse_name"
                            item-value="warehouse_id"
                            label="Gudang"
                        ></v-select>
                    </v-flex>
                </v-layout>
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="success" @click="dialog=!dialog" flat>Tutup</v-btn>
                <v-btn color="success" @click="generate"
                    :disabled="!selected_warehouse">Tampilkan</v-btn>
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
            get () { return this.$store.state.report_param.dialog['iv-003'] },
            set (v) { this.$store.commit('report_param/set_dialog', ['iv-003', v]) }
        },

        report_url () {
            return this.$store.state.report_param.report_url
        },

        params () {
            return ['warehouseid='+this.selected_warehouse.warehouse_id].join('&')
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

        warehouses () {
            return this.$store.state.report_param.warehouses
        },

        selected_warehouse : {
            get () { return this.$store.state.report_param.selected_warehouse },
            set (v) { this.$store.commit('report_param/set_selected_warehouse', v) }
        }
    },

    methods : {
        generate () {
            let urls = this.$store.state.report.URL+'report/'+this.selected_report.report_url+
                        '?'+this.params
            this.$store.commit('report_param/set_common', ['report_url', urls])
            this.$store.commit('set_dialog_print', true)

            this.dialog=!this.dialog
        }
    },

    mounted () {
        this.$store.dispatch('report_param/search_month')
        this.$store.dispatch('report_param/search_warehouse')
    }
}
</script>