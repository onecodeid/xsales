<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0">
            <v-layout row wrap>
                <v-flex xs7>
                    <h3 class="display-1 font-weight-light zalfa-text-title">{{title}}</h3>
                </v-flex>
                <v-flex xs1 pr-1>
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
                </v-flex>
            </v-layout>
        </v-card-title>
        <v-card-text class="pt-2">
            <v-data-table 
                :headers="headers"
                :items="records"
                :loading="false"
                hide-actions
                class="elevation-1">
                <!-- <template slot="headers" slot-scope="props">
                        
                        <tr>
                            <th v-for="(h, n) in headers" :key="'header-'+n" 
                                role="columnheader" scope="col" :width="h.width" :aria-label="h.text+': Not sorted.'" aria-sort="none" :class="['column', 'text-xs-'+h.align, h.class]"
                                v-show="n<=5" rowspan="2">{{h.text}}</th>
                            <th colspan="5"
                            role="columnheader" scope="col" aria-label="Lewat: Not sorted" aria-sort="none" :class="['column', 'text-xs-center', 'pa-2 zalfa-bg-purple lighten-3 white--text']">LEWAT JATUH TEMPO (dalam HARI)</th>
                        </tr>
                        <tr>
                            <th v-for="(h, n) in headers" :key="'header-'+n" 
                                role="columnheader" scope="col" :width="h.width" :aria-label="h.text+': Not sorted.'" aria-sort="none" :class="['column', 'text-xs-'+h.align, h.class]"
                                v-show="n>5">{{h.text}}</th>
                        </tr>
                        <tr class="v-datatable__progress"><th :colspan="headers.length" class="column"></th></tr>

                </template> -->
                <template slot="items" slot-scope="props">
                    <tr class="cyan lighten-4">
                        <td :colspan="headers.length" class="py-2 px-2 text-xs-left"><b>Arus Kas dari {{ props.item.title }}</b></td>
                        <!-- <td class="pa-2 text-xs-right"><span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.total_unpaid)) }}</b></td>
                        <td class="pa-2 text-xs-right"><span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.total_ongoing)) }}</b></td>
                        <td class="pa-2 text-xs-right"><span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.total_30)) }}</b></td>
                        <td class="pa-2 text-xs-right"><span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.total_60)) }}</b></td>
                        <td class="pa-2 text-xs-right"><span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.total_90)) }}</b></td>
                        <td class="pa-2 text-xs-right"><span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.total_120)) }}</b></td>
                        <td class="pa-2 text-xs-right"><span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.total_rest)) }}</b></td> -->
                    </tr>

                    <tr v-for="(inv, n) in props.item.data">
                        <td class="text-xs-center pa-2" @click="select(props.item)">{{ n+1 }}</td>
                        <td class="text-xs-left pa-2" @click="select(props.item)"><a href="#" @click="select(props.item),detail($event, inv)">{{ inv.label }}</a></td>
                        
                        <td class="text-xs-right pa-2" @click="select(props.item)" v-show="inv.jdebit >= inv.jcredit">{{ one_money(inv.jdebit - inv.jcredit) }}</td>
                        <td class="text-xs-right pa-2 red--text" @click="select(props.item)" v-show="inv.jcredit > inv.jdebit"> ({{ one_money(inv.jcredit - inv.jdebit) }})</td>
                    </tr>
                    <tr class="" v-show="props.item.data.length < 1">
                        <td class="text-xs-center pa-2" @click="select(props.item)">&nbsp;</td>
                        <td :colspan="headers.length-1" class="text-xs-left pa-2 grey--text"><i>( Tidak ada data )</i></td>
                    </tr>

                    <tr class="">
                        <td :colspan="2" class="py-2 px-2 text-xs-left"><b>Kas Bersih yang Diperoleh dari {{ props.item.title }}</b></td>
                        <td class="text-xs-right pa-2" @click="select(props.item)" v-show="props.item.total >= 0"><b>{{ one_money(props.item.total) }}</b></td>
                        <td class="text-xs-right pa-2 red--text" @click="select(props.item)" v-show="props.item.total < 0"><b>({{ one_money(0-props.item.total) }})</b></td>
                    </tr>
                    <tr class="">
                        <td :colspan="headers.length" class="py-4 px-2 text-xs-left"></td>
                    </tr>
                </template>
            </v-data-table>
            <!-- <v-divider></v-divider>
            <v-pagination
                style="margin-top:10px;margin-bottom:10px"
                v-model="curr_page"
                :length="xtotal_page"
                @input="change_page"
            ></v-pagination> -->
        </v-card-text>
        
        <!-- <common-dialog-delete :data="invoice_id" @confirm_del="confirm_del" v-if="dialog_delete"></common-dialog-delete>
        <common-dialog-confirm :data="invoice_id" @confirm="confirm_post" v-if="dialog_confirm" :text="text_post"></common-dialog-confirm>
        <common-dialog-print :report_url="report_url" v-if="dialog_report"></common-dialog-print> -->
        <journal-detail></journal-detail>
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
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        // "common-dialog-print" : httpVueLoader("../../common/components/common-dialog-print-size.vue"),
        "journal-detail" : httpVueLoader("./report-data-fin-008-detail.vue")
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
            return this.$store.state.fin008.records
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
            get () { return this.$store.state.fin008.search },
            set (v) { this.$store.commit('fin008/update_search', v) }
        },

        // query : {
        //     get () { return this.$store.state.fin008.search },
        //     set (v) { this.$store.commit('fin008/set_object', ['search', v]) }
        // },

        // curr_page : {
        //     get () { return this.$store.state.fin008.current_page },
        //     set (v) { this.$store.commit('fin008/set_object', ['current_page', v]) }
        // },

        // xtotal_page () {
        //     return this.$store.state.fin008.total_invoice_page
        // },

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
            this.$store.commit('fin008/set_object', ['selected_invoice', x])
        },

        search () {
            return this.$store.dispatch('fin008/search', {})
        },

        change_page(x) {
            this.curr_page = x
            this.$store.dispatch('fin008/search', {})
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
            this.$store.commit('fin008/set_object', ['dialog_detail', true])
            this.$store.commit('fin008/set_object', ['current_detail_page', 1])
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