<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0">
            <v-layout row wrap>
                <v-flex xs8>
                    <h3 class="display-1 font-weight-light zalfa-text-title">{{title}}</h3>
                    <h5 class="subheading font-weight-light zalfa-text-title"><b>Per {{ edate }}</b></h5>
                </v-flex>
                <v-flex xs2 pr-1>
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
                <v-flex xs2 pr-1>
                    <v-select
                        :items="endyears"
                        item-text="label2"
                        item-value="edate"
                        return-object
                        v-model="end_month"
                        label="Sampai Bulan"
                    >
                        <template v-slot:append-outer>
                            <v-btn color="orange" :dark="!!start_month&&!!end_month" class="ma-0 ml-2 btn-icon" @click="printMe" :disabled="!start_month||!end_month">
                                <v-icon>print</v-icon>
                            </v-btn>  
                        </template>
                    </v-select>
                </v-flex>
            </v-layout>
        </v-card-title>
        <v-card-text class="pt-2">
            FIN-010
        </v-card-text>
    </v-card>
</template>

<style scoped>
table.v-table tbody td {
    height: auto !important;
}
.v-text-field.v-text-field--solo .v-input__control {
    min-height: 36px;
}
.v-text-field.v-text-field--solo .v-input__append-outer {
    margin-top: 0px;
    margin-left: 0px;
}

table.v-table thead tr {
    height: 30px;
}
</style>

<script>
module.exports = {
    components : {
        // "common-dialog-delete" : httpVueLoader("../../common/components/common-dialog-delete.vue"),
        // "common-dialog-confirm" : httpVueLoader("../../common/components/common-dialog-confirm.vue"),

        // "common-dialog-print" : httpVueLoader("../../common/components/common-dialog-print-size.vue"),

    },

    data () {
        return {
            headers: [
                {
                    text: "NO",
                    align: "center",
                    sortable: false,
                    width: "3%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "AKUN / REKENING",
                    align: "left",
                    sortable: false,
                    width: "77%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "NOMINAL TRANSAKSI",
                    align: "right",
                    sortable: false,
                    width: "20%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                }
            ],

            report_url: ''
        }
    },

    computed : {
        records () {
            return this.$store.state.fin010.records
        },

        // dialog_delete () {
        //     return this.$store.state.dialog_delete
        // },

        // dialog_confirm () {
        //     return this.$store.state.dialog_confirm
        // },

        // invoice_id () {
        //     return this.$store.state.fin010.selected_fin010.M_JournalID
        // },

        query : {
            get () { return this.$store.state.fin010.search },
            set (v) { this.$store.commit('fin010/update_search', v) }
        },

        s_date : {
            get () { return this.$store.state.fin010.s_date },
            set (v) { this.$store.commit('fin010/set_common', ['s_date', v]) }
        },

        e_date : {
            get () { return this.$store.state.fin010.e_date },
            set (v) { this.$store.commit('fin010/set_common', ['e_date', v]) }
        },

        title() {
            return "LAPORAN PERUBAHAN MODAL"
        },

        endyears () {
            return this.$store.state.report_param.months2
        },

        selected_endyear : {
            get () { return this.$store.state.report_param.selected_month2 },
            set (v) { 
                this.$store.commit('report_param/set_object', ['selected_month2', v])
                this.search()
            }
        },

        start_month : {
            get () { return this.$store.state.fin010.start_month },
            set (v) { this.$store.commit('fin010/set_object', ['start_month', v]) }
        },

        end_month : {
            get () { return this.$store.state.fin010.end_month },
            set (v) { this.$store.commit('fin010/set_object', ['end_month', v]) }
        },

        sdate () {
            return this.selected_endyear ? moment(this.selected_endyear.sdate).locale("id").format('LL') : ''
        },

        edate () {
            return this.selected_endyear ? moment(this.selected_endyear.edate).locale("id").format('LL') : ''
        },

        // is_sales() {
        //     if (this.$store.state.filter.indexOf("J.03")>-1)
        //         return true
        //     return false
        // },

        // custom_title () {
        //     return this.$store.state.custom_title?this.$store.state.custom_title:false
        // },

        // dialog_report : {
        //     get () { return this.$store.state.dialog_print },
        //     set (v) { this.$store.commit('set_dialog_print', v) }
        // }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        select (x) {
            this.$store.commit('fin010/set_object', ['selected_invoice', x])
        },

        search () {
            return this.$store.dispatch('fin010/search', {})
        },

        change_page(x) {
            this.curr_page = x
            this.$store.dispatch('fin010/search', {})
        },

        change_s_date(x) {
            this.$store.commit('fin010/set_common', ['s_date', x.new_date])
            this.$store.dispatch('fin010/search')
        },

        change_e_date(x) {
            this.$store.commit('fin010/set_common', ['e_date', x.new_date])
            this.$store.dispatch('fin010/search')
        },

        print_invoice (x) {
            this.select(x)
            let so = x
            this.report_url = this.$store.state.fin010.URL+"report/one_sales_002?id="+so.invoice_id
            this.$store.commit('set_dialog_print', true)
        },

        bg_proforma (x) {
            if (x.invoice_proforma=='Y')
                return 'amber lighten-4'
            return 'white'
        },

        proforma_edit (z) {

            let sc = z
            this.$store.commit('invoice_new/set_common', ['invoice_proforma', 'Y'])
            this.$store.dispatch('invoice_new/search_item')
            
            this.$store.commit('invoice_new/set_common', ['dialog_proforma', true])
        },

        printMe () {
            this.$store.dispatch("fin010/collect").then((x) => {
                window.open(this.$store.state.fin010.URL + 'report/one_fin_010/excel?' + x)
            })
        }
    },

    mounted () {
        // if (this.endyears.length < 1)
        //     this.$store.dispatch('report_param/search_endyear')
        this.$store.dispatch("report_param/search_months").then((x) => {
            this.$store.commit("report_param/set_object", ["selected_month2", x[0]])
            this.$store.dispatch('fin010/search', {})
        })
        
        this.$store.dispatch('fin010/search', {})
    }
}
</script>