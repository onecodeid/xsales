<template>
    <v-dialog
        v-model="dialog"
        persistent
        max-width="1000px"
        transition="dialog-transition"
        content-class="fin008-detail"
        scrollable
    >
        <v-card>
            <v-card-title primary-title class="pt-2 pb-0">
                <v-layout row wrap>
                    <v-flex xs10>
                        <h3 class="display-1 font-weight-light zalfa-text-title">ARUS KAS » <b>{{ selected_account.label.toUpperCase() }}</b></h3>
                    </v-flex>

                    <v-flex xs2>
                        <v-text-field
                            solo
                            hide-details
                            placeholder="Pencarian" v-model="query"
                            @change="searchOne"
                        >
                            <template v-slot:append-outer>
                                <v-btn color="primary" class="ma-0 btn-icon" @click="searchOne">
                                    <v-icon>search</v-icon>
                                </v-btn>
                            </template>
                        </v-text-field>
                    </v-flex>
                    <!-- <v-flex xs1 pr-1>
                        <common-datepicker
                            label="Dari Tanggal"
                            :date="s_date"
                            data="0"
                            @change="change_s_date"
                            classs=""
                            hints=" "
                            :details="false"
                            :solo="true"
                        ></common-datepicker>
                    </v-flex>
                    <v-flex xs1 pl-1>
                        <common-datepicker
                            label="Dari Tanggal"
                            :date="e_date"
                            data="0"
                            @change="change_e_date"
                            classs=""
                            hints=" "
                            :details="false"
                            :solo="true"
                        ></common-datepicker>
                    </v-flex>
                    <v-flex xs3 class="text-xs-right" pl-2>
                        <v-text-field
                            solo
                            hide-details
                            placeholder="Pencarian" v-model="query"
                            @change="search"
                            readonly
                        >
                            <template v-slot:append-outer>
                                <v-btn color="primary" class="ma-0 btn-icon" @click="search">
                                    <v-icon>search</v-icon>
                                </v-btn>      

                                <v-btn color="orange" dark class="ma-0 ml-2 btn-icon" @click="printMe">
                                    <v-icon>print</v-icon>
                                </v-btn>  
                            </template>
                        </v-text-field>
                    </v-flex> -->
                </v-layout>
            </v-card-title>
            <v-card-text class="pt-2">
                <v-data-table 
                    :headers="headers"
                    :items="details"
                    :loading="false"
                    hide-actions
                    class="elevation-1">
                
                    <template slot="items" slot-scope="props">
                        <tr class="" @click="select(props.item),detail($event, props.item)">
                            <td class="py-2 px-2 text-xs-center">{{ props.item.jdate }}</td>
                            <td class="text-xs-left pa-2">{{ props.item.jnumber }}</td>
                            <td class="text-xs-left pa-2 ">{{ props.item.account_name }}</td>
                            <td class="text-xs-left pa-2 ">{{ props.item.jnote }}</td>
                            <td class="text-xs-right pa-2 "><span class="grey--text caption">Rp</span> {{ one_money(props.item.jdebit) }}</td>
                            <td class="text-xs-right pa-2 "><span class="grey--text caption">Rp</span> {{ one_money(props.item.jcredit) }}</td>
                        </tr>
                    </template>
                </v-data-table>
                <v-divider></v-divider>
                <v-pagination
                    style="margin-top:10px;margin-bottom:10px"
                    v-model="curr_page"
                    :length="total_page"
                    total-visible="10"
                    @input="change_page"
                ></v-pagination>
            </v-card-text>
            
            <v-card-actions class="px-3">
                <v-spacer></v-spacer>
                <v-btn color="red" dark @click="dialog=!dialog">Tutup</v-btn>
            </v-card-actions>
            <!-- <common-dialog-delete :data="invoice_id" @confirm_del="confirm_del" v-if="dialog_delete"></common-dialog-delete>
            <common-dialog-confirm :data="invoice_id" @confirm="confirm_post" v-if="dialog_confirm" :text="text_post"></common-dialog-confirm>
            <common-dialog-print :report_url="report_url" v-if="dialog_report"></common-dialog-print> -->
            <!-- <invoice-detail></invoice-detail> -->
        </v-card>
    </v-dialog>
</template>

<style>
.fin008-detail table.v-table tbody td {
    height: auto !important;
}
.fin008-detail .v-text-field.v-text-field--solo .v-input__control {
    min-height: 36px;
}
.fin008-detail .v-text-field.v-text-field--solo .v-input__append-outer {
    margin-top: 0px;
    margin-left: 0px;
}

.fin008-detail table.v-table thead tr {
    height: 30px;
}
</style>

<script>
module.exports = {
    components : {
        "trans-journal-view" : httpVueLoader("../../trans-journal/components/trans-journal-new.vue" + t)
        // "common-dialog-delete" : httpVueLoader("../../common/components/common-dialog-delete.vue"),
        // "common-dialog-confirm" : httpVueLoader("../../common/components/common-dialog-confirm.vue"),
        // 'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        // "common-dialog-print" : httpVueLoader("../../common/components/common-dialog-print-size.vue"),
        // "invoice-detail" : httpVueLoader("../../sales-invoice/components/sales-invoice-new.vue")
    },

    data () {
        return {
            headers: [
                {
                    text: "TANGGAL",
                    align: "center",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "NOMOR JURNAL",
                    align: "left",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "AKUN / REKENING",
                    align: "left",
                    sortable: false,
                    width: "20%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "DESKRIPSI",
                    align: "left",
                    sortable: false,
                    width: "40%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "DEBIT",
                    align: "right",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "KREDIT",
                    align: "right",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                }
            ],

            report_url: ''
        }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.fin008.dialog_detail },
            set (v) { this.$store.commit('fin008/set_object', ['dialog_detail', v]) }
        },

        details () {
            return this.$store.state.fin008.details
        },

        selected_account () {
            return !!this.$store.state.fin008.selected_account ?
                this.$store.state.fin008.selected_account : {}
        },

        // dialog_delete () {
        //     return this.$store.state.dialog_delete
        // },

        // dialog_confirm () {
        //     return this.$store.state.dialog_confirm
        // },

        // invoice_id () {
        //     return this.$store.state.fin008.selected_fin008.M_JournalID
        // },

        query : {
            get () { return this.$store.state.fin008.search_detail },
            set (v) { this.$store.commit('fin008/set_object', ['search_detail', v]) }
        },

        // query : {
        //     get () { return this.$store.state.fin008.search },
        //     set (v) { this.$store.commit('fin008/set_object', ['search', v]) }
        // },

        curr_page : {
            get () { return this.$store.state.fin008.current_detail_page },
            set (v) { this.$store.commit('fin008/set_object', ['current_detail_page', v]) }
        },

        total_page () {
            return this.$store.state.fin008.total_detail_page
        },

        // text_post () {
        //     let j = this.$store.state.fin008.selected_invoice
        //     return "Apakah anda yakin akan melakukan Posting Jurnal tersebut ?"
        // },

        s_date : {
            get () { return this.$store.state.fin008.s_date },
            set (v) { this.$store.commit('fin008/set_common', ['s_date', v]) }
        },

        e_date : {
            get () { return this.$store.state.fin008.e_date },
            set (v) { this.$store.commit('fin008/set_common', ['e_date', v]) }
        },

        title() {
            return "LAPORAN ARUS KAS"
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

        add () {
            let x = []
            x.push(JSON.parse(JSON.stringify(this.$store.state.invoice_new.detail_default)))
            this.$store.commit('invoice_new/set_common', ['edit', false])
            this.$store.commit('invoice_new/set_common', ['invoice_date', this.$store.state.invoice_new.current_date])
            this.$store.commit('invoice_new/set_common', ['invoice_due_date', this.$store.state.invoice_new.current_date])
            this.$store.commit('invoice_new/set_common', ['invoice_note', ''])
            this.$store.commit('invoice_new/set_common', ['invoice_number', ''])
            this.$store.commit('invoice_new/set_common', ['invoice_disc', 0])
            this.$store.commit('invoice_new/set_common', ['invoice_discrp', 0])
            this.$store.commit('invoice_new/set_common', ['invoice_disctype', 'P'])
            this.$store.commit('invoice_new/set_common', ['invoice_shipping', 0])
            this.$store.commit('invoice_new/set_common', ['invoice_memo', ''])
            this.$store.commit('invoice_new/set_common', ['invoice_address', ''])
            this.$store.commit('invoice_new/set_invoice_dps', [])
            this.$store.commit('invoice_new/set_selected_customer', null)
            this.$store.commit('invoice_new/set_selected_term', null)
            this.$store.commit('invoice_new/set_items', [])
            this.$store.commit('invoice_new/set_details', x)
            this.$store.commit('invoice_new/set_common', ['dialog_delivery', true])
        },

        edit (x) {
            if (this.$store.state.invoice_new.customers.length < 1) {
                this.$store.commit('set_dialog_progress', true)
                this.$store.dispatch('invoice_new/search_customer').then(() => {
                    this.$store.commit('set_dialog_progress', false)
                    this.do_edit(x)
                })
            } else {
                this.do_edit(x)
            }
        },

        do_edit (x) {
            this.select(x)
            let sc = x
            this.$store.commit('invoice_new/set_common', ['edit', true])
            this.$store.commit('invoice_new/set_common', ['invoice_id', sc.invoice_id])
            this.$store.commit('invoice_new/set_common', ['invoice_date', sc.invoice_date])
            this.$store.commit('invoice_new/set_common', ['invoice_due_date', sc.invoice_due_date.split('-').reverse().join('-')])
            this.$store.commit('invoice_new/set_common', ['invoice_note', sc.invoice_note])
            this.$store.commit('invoice_new/set_common', ['invoice_memo', sc.invoice_memo])
            this.$store.commit('invoice_new/set_common', ['invoice_number', sc.invoice_number])
            this.$store.commit('invoice_new/set_common', ['invoice_disc', sc.invoice_disc])
            this.$store.commit('invoice_new/set_common', ['invoice_discrp', sc.invoice_discrp])
            this.$store.commit('invoice_new/set_common', ['invoice_dp', sc.invoice_dp])
            this.$store.commit('invoice_new/set_common', ['invoice_shipping', sc.invoice_shipping])
            this.$store.commit('invoice_new/set_common', ['invoice_proforma', sc.invoice_proforma])
            this.$store.commit('invoice_new/set_common', ['sales_name', sc.sales.staff_short])
            this.$store.commit('invoice_new/set_invoice_dps', sc.invoice_dps) 

            this.$store.commit('invoice_new/set_common', ['invoice_disctype', 
                Math.round(sc.invoice_discrp)>0?'R':'P'])

            this.$store.commit('invoice_new/set_selected_customer', null)
            for (let v of this.$store.state.invoice_new.customers)
                if (v.customer_id == sc.customer_id)
                    this.$store.commit('invoice_new/set_selected_customer', v)
            
            for (let v of this.$store.state.invoice_new.terms)
                if (v.term_id == sc.invoice_term)
                    this.$store.commit('invoice_new/set_selected_term', v)

            let details = sc.details
            let dfl = JSON.parse(JSON.stringify(this.$store.state.invoice_new.detail_default))
            this.$store.dispatch('invoice_new/search_dp')

            if (sc.invoice_proforma == 'Y') {
                let dx = []
                for (let d in details) {
                    dx.push(dfl)
                    dx[d].delivery = details[d].sales
                    dx[d].items = details[d].items
                }
                this.$store.commit('invoice_new/set_details', dx)
                this.$store.commit('invoice_new/set_common', ['dialog_proforma', true])
            }
            else {
                
                // let acc = this.$store.state.invoice_new.accounts
                for (let d of details)
                    d.delivery.items = d.items
                //     for (let a of acc)
                //         if (a.account_id == d.account)
                //             d.account = a

                this.$store.commit('invoice_new/set_details', details)
                this.$store.commit('invoice_new/set_common', ['dialog_new', true])
            }

            // address
            let scd = sc.main_address
            let phones = []
            for (let p of scd.phones)
                phones.push(window.phone_format(p.no))

            let address = scd.address_desc + "<br />" +
                (scd.village_name!=''?scd.village_name+', ':'') +
                (scd.district_name!=''?scd.district_name+', ':'') +
                (scd.city_name!=''?scd.city_name+' - ':'') +
                (scd.province_name!=''?scd.province_name:'') + "<br />Phone : " + phones.join(", ")
            this.$store.commit('invoice_new/set_common', ['invoice_address', address])
            
        },

        del (x) {
            this.select(x)
            this.$store.commit('set_dialog_delete', true)
        },

        confirm_del (x) {
            this.$store.dispatch('fin008/del', {id:x.data})
        },

        post (x) {
            this.select(x)
            this.$store.commit('set_dialog_confirm', true)
        },

        confirm_post (x) {
            this.$store.dispatch('fin008/post', {id:x.data})
        },

        select (x) {
            this.$store.commit('fin008/set_object', ['selected_detail', x])
        },

        search () {
            return this.$store.dispatch('fin008/searchDetail')
        },

        searchOne () {
            this.curr_page = 1, this.search()
        },

        change_page(x) {
            this.curr_page = x
            this.$store.dispatch('fin008/searchDetail')
        },

        change_s_date(x) {
            this.$store.commit('fin008/set_common', ['s_date', x.new_date])
            this.$store.dispatch('fin008/search')
        },

        change_e_date(x) {
            this.$store.commit('fin008/set_common', ['e_date', x.new_date])
            this.$store.dispatch('fin008/search')
        },

        journal(x) {
            this.select(x)
            // this.$store.commit('journal/set_selected_journal', x)
            this.$store.commit('journal_new/set_common', ['dialog_new', true])
            this.$store.commit('journal_new/set_common', ['view', true])
            this.$store.commit('journal_new/set_common', ['journal_note', x.journal_note])
            this.$store.commit('journal_new/set_common', ['journal_receipt', x.journal_receipt])
            this.$store.commit('journal_new/set_common', ['journal_date', x.journal_date])

            let accs = []
            for (let d of x.accounts)
                if (d.account.account_id != null)
                    accs.push(d.account)
            this.$store.commit('journal_new/set_accounts', accs)

            // let details = []
            // for (let d of x.details) {
            //     for (let a of this.$store.state.cash_delivery.accounts) {
            //         if (a.account_id == d.account)
            //             d.account = a
            //     }
            //     details.push(d)
            // }
            this.$store.commit('journal_new/set_details', x.accounts)
        },

        print_invoice (x) {
            this.select(x)
            let so = x
            this.report_url = this.$store.state.fin008.URL+"report/one_sales_002?id="+so.invoice_id
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

        detail (x, y) {
            x.preventDefault()

            let sc = y
            this.$store.commit('fin008/set_object', ['selected_account', y])
            this.$store.dispatch('fin008/searchDetail')
            // this.$store.commit('invoice_new/set_common', ['edit', true])
            // this.$store.commit('invoice_new/set_common', ['view', true])
            // this.$store.commit('invoice_new/set_common', ['invoice_id', sc.invoice_id])
            // this.$store.commit('invoice_new/set_common', ['invoice_date', sc.invoice_date])
            // // this.$store.commit('invoice_new/set_common', ['invoice_due_date', 
            // //     moment(sc.invoice_date, "YYYY-MM-DD").add(Math.round(sc.invoice_term), 'days').format('DD-MM-YYYY')])
            // this.$store.commit('invoice_new/set_common', ['invoice_due_date', sc.invoice_due_date.split('-').reverse().join('-')])
            // this.$store.commit('invoice_new/set_common', ['invoice_note', sc.invoice_note])
            // this.$store.commit('invoice_new/set_common', ['invoice_memo', sc.invoice_memo])
            // this.$store.commit('invoice_new/set_common', ['invoice_number', sc.invoice_number])
            // this.$store.commit('invoice_new/set_common', ['invoice_disc', sc.invoice_disc])
            // this.$store.commit('invoice_new/set_common', ['invoice_discrp', sc.invoice_discrp])
            // this.$store.commit('invoice_new/set_common', ['invoice_dp', sc.invoice_dp])
            // this.$store.commit('invoice_new/set_common', ['invoice_shipping', sc.invoice_shipping])
            // this.$store.commit('invoice_new/set_common', ['invoice_proforma', sc.invoice_proforma])
            // this.$store.commit('invoice_new/set_common', ['sales_name', sc.sales.staff_short])
            // this.$store.commit('invoice_new/set_invoice_dps', sc.invoice_dps) 

            // this.$store.commit('invoice_new/set_common', ['invoice_disctype', 
            //     Math.round(sc.invoice_discrp)>0?'R':'P'])

            // let scv = this.$store.state.fin008.selected_invoice
            // this.$store.commit('invoice_new/set_selected_customer', {
            //     customer_id: scv.customer_id, customer_name: scv.customer_name
            // })
            
            // for (let v of this.$store.state.invoice_new.terms)
            //     if (v.term_id == sc.invoice_term)
            //         this.$store.commit('invoice_new/set_selected_term', v)

            // let details = sc.details
            // let dfl = JSON.parse(JSON.stringify(this.$store.state.invoice_new.detail_default))
            // this.$store.dispatch('invoice_new/search_dp')
            // this.$store.commit('invoice_new/set_details', details)

            // this.$store.commit('invoice_new/set_common', ['dialog_new', true])
        },

        printMe () {
            this.$store.dispatch("fin008/collect").then((x) => {
                window.open(this.$store.state.fin008.URL + 'report/one_fin_006/excel?' + x)
            })
        }
    },

    mounted () {
        // if (this.is_sales)
        //     this.headers[2].text="NOMOR INVOICE"
        
        this.$store.dispatch('fin008/search', {})
    }
}
</script>