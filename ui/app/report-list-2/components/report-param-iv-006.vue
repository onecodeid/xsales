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
                            :items="categories"
                            v-model="selected_category"
                            item-value="category_id"
                            item-text="category_name"
                            return-object
                            label="Kategori Produk"
                            clearable
                        ></v-select>
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
            get () { return this.$store.state.report_param.dialog['iv-006'] },
            set (v) { this.$store.commit('report_param/set_dialog', ['iv-006', v]) }
        },

        report_url () {
            return this.$store.state.report_param.report_url
        },

        params () {
            return ['category_id='+(this.selected_category?this.selected_category.category_id:0)].join('&')
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
        },

        edate : {
            get () { return this.$store.state.report_param.edate },
            set (v) { this.$store.commit('report_param/set_common', ['edate', v]) }
        },

        sdate : {
            get () { return this.$store.state.report_param.sdate },
            set (v) { this.$store.commit('report_param/set_common', ['sdate', v]) }
        },

        categories () {
            return this.$store.state.iv006.categories
        },

        selected_category : {
            get () { return this.$store.state.iv006.selected_category },
            set (v) { 
                this.$store.commit('iv006/set_object', ['selected_category', v])
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
        this.$store.dispatch('report_param/search_month')
        this.$store.dispatch('report_param/search_warehouse')

        this.$store.dispatch('iv006/search_category').then((x) => {
            this.generate()
            // this.selected_warehouse = x[0]
            // this.search()
        })

    }
}
</script>