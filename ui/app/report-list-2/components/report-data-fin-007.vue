<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0" v-show="!custom_title">
            <v-layout row wrap>
                <v-flex xs6>
                    <h3 class="display-1 font-weight-light zalfa-text-title">{{title}}</h3>
                </v-flex>
                <v-flex xs2 pr-2>
                    <v-select
                        :items="staffs"
                        v-model="selected_staff"
                        solo
                        dense
                        label="Semua Sales"
                        item-value="staff_id"
                        item-text="staff_name"
                        return-object
                        hide-details
                        clearable
                    >
                        <template slot="selection" slot-scope="data">
                            <span class="body-2">{{ data.item.staff_name }}</span>
                        </template>
                        <template slot="item" slot-scope="data">
                            <span class="body-2">{{ data.item.staff_name }}</span>
                        </template>
                    </v-select>
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
                <v-flex xs2 class="text-xs-right" pl-2>
                    <v-text-field
                        solo
                        hide-details
                        placeholder="Pencarian" v-model="query"
                        @change="search"
                    >
                        <template v-slot:append-outer>
                            <v-btn color="primary" class="ma-0 btn-icon" @click="search">
                                <v-icon>search</v-icon>
                            </v-btn>      

                            <v-btn color="orange" dark class="ma-0 ml-2 btn-icon" @click="printMe" v-show="!is_sales">
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
                :items="invoices"
                :loading="false"
                hide-actions
                class="elevation-1">
                <template slot="headers" slot-scope="props">
                        
                        <tr>
                            <th v-for="(h, n) in headers" :key="'header-'+n" 
                                role="columnheader" scope="col" :width="h.width" :aria-label="h.text+': Not sorted.'" aria-sort="none" :class="['column', 'text-xs-'+h.align, h.class]">{{h.text}}</th>
                        </tr>
                        
                        <tr class="v-datatable__progress"><th :colspan="headers.length" class="column"></th></tr>

                </template>
                <template slot="items" slot-scope="props">
                    <tr class="cyan lighten-4">
                        <td :colspan="9" class="py-2 px-2 text-xs-left"><b>{{ props.item.customer_name }}</b></td>
                        <!-- <td class="pa-2 text-xs-right"></td> -->

                        <!-- <td :colspan="2" class="pa-2 text-xs-left orange lighten-3 pl-3"><b>SUB TOTAL</b></td>
                        <td class="pa-2 text-xs-right orange lighten-3"><span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.total_grandtotal)) }}</b></td>
                        <td class="pa-2 text-xs-right orange lighten-3"><span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.total_paid)) }}</b></td>
                        <td class="pa-2 text-xs-right orange lighten-3"><span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.total_unpaid)) }}</b></td> -->
                    </tr>
                    <tr v-for="(inv, n) in props.item.invoices" :key="'invoice-'+inv.invoice_id">
                        <td class="text-xs-left pa-2" @click="select(props.item)"><a href="#" @click="select(props.item),detail($event, inv)">{{ n + 1 }}</a></td>
                        <td class="text-xs-center pa-2" @click="select(props.item)">{{ inv.invoice_date }}</td>
                        <!-- <td class="text-xs-left pa-2" @click="select(props.item)"></td> -->
                        <td class="text-xs-left pa-2" @click="select(props.item)">{{ inv.invoice_number }}</td>
                        <td class="text-xs-left pa-2" @click="select(props.item)">{{ inv.sales_name }}</td>
                        <td class="text-xs-center pa-2" @click="select(props.item)">{{ inv.invoice_duedate }}</td>
                        <!-- <td class="text-xs-right pa-2" @click="select(props.item)" v-show="!is_sales">
                            <span class="grey--text caption">Rp</span> 
                                <b>{{ one_money(Math.round(inv.invoice_unpaid)) }}</b>
                        </td> -->
                        <!-- <td class="text-xs-right pa-2" @click="select(props.item)" v-show="!is_sales">
                            <span class="grey--text caption">Rp</span> 
                                <b>{{ inv.invoice_diff_date <= 0 ? one_money(Math.round(inv.invoice_unpaid)) : 0 }}</b>
                        </td> -->

                        
                        <td class="text-xs-right pa-2" @click="select(props.item)">
                            <b>{{ inv.term_duration }}</b> <span class="grey--text">hari</span>
                        </td>

                        <td class="text-xs-right pa-2" @click="select(props.item)" v-show="!is_sales">
                            <span class="grey--text caption">Rp</span> 
                                <b>{{ one_money(Math.round(inv.invoice_grandtotal)) }}</b>
                        </td>
                        <td class="text-xs-right pa-2" @click="select(props.item)" v-show="!is_sales">
                            <div class="payment-box" v-for="(p, m) in inv.payments" :key="'payment-'+inv.invoice_id+'-'+m" href="#" >
                                <a href="#">Rp <b>{{ one_money(p.pay_total) }}</b></a>
                            </div>
                            <!-- <span class="grey--text caption">Rp</span> 
                                <b>{{ one_money(Math.round(inv.invoice_paid)) }}</b> -->
                        </td>
                        
                        <td class="text-xs-right pa-2" @click="select(props.item)" v-show="!is_sales">
                            <span class="grey--text caption">Rp</span> 
                                <b>{{ one_money(Math.round(inv.invoice_unpaid)) }}</b>
                        </td>
                        <!-- <td class="text-xs-left pa-1" @click="select(props.item)">
                            <span class="orange white--text pa-1 d-block text-xs-center" v-if="inv.invoice_unpaid==0">LUNAS</span>
                            <span class="success pa-1 d-block white--text text-xs-center" v-if="inv.invoice_unpaid>0&&inv.invoice_paid>0">SEBAGIAN</span>
                            <span class="grey white--text pa-1 d-block text-xs-center" v-if="inv.invoice_paid==0">BARU</span>
                        </td> -->
                        
                    </tr>

                    <tr class="">
                        <td :colspan="4" class="py-2 px-2 text-xs-left"></td>
                        <!-- <td class="pa-2 text-xs-right"></td> -->

                        <td :colspan="2" class="pa-2 text-xs-left orange lighten-3 pl-3"><b>SUB TOTAL</b></td>
                        <td class="pa-2 text-xs-right orange lighten-3"><span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.total_grandtotal)) }}</b></td>
                        <td class="pa-2 text-xs-right orange lighten-3"><span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.total_paid)) }}</b></td>
                        <td class="pa-2 text-xs-right orange lighten-3"><span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.total_unpaid)) }}</b></td>
                    </tr>
                    <!-- <tr class="orange lighten-3"><td :colspan="headers.length" class="pt-1"></td></tr> -->
                </template>
                <template slot="footer" slot-scope="props">
                        
                        <tr class="orange white--text font-weight-bold">
                            <td :colspan="6">GRAND TOTAL</td>
                            <td class="text-xs-right px-2">{{ one_money(grandTotal.total) }}</td>
                            <td class="text-xs-right px-2">{{ one_money(grandTotal.paid) }}</td>
                            <td class="text-xs-right px-2">{{ one_money(grandTotal.unpaid) }}</td>
                            <!-- <th v-for="(h, n) in headers" :key="'header-'+n" 
                                role="columnheader" scope="col" :width="h.width" :aria-label="h.text+': Not sorted.'" aria-sort="none" :class="['column', 'text-xs-'+h.align, h.class]">{{h.text}}</th> -->
                        </tr>
                        
                        <tr class="v-datatable__progress"><th :colspan="headers.length" class="column"></th></tr>

                </template>
            </v-data-table>
            <v-divider></v-divider>
            <v-pagination
                style="margin-top:10px;margin-bottom:10px"
                v-model="curr_page"
                :length="xtotal_page"
                @input="change_page"
            ></v-pagination>
        </v-card-text>
        
        <common-dialog-delete :data="invoice_id" @confirm_del="confirm_del" v-if="dialog_delete"></common-dialog-delete>
        <common-dialog-confirm :data="invoice_id" @confirm="confirm_post" v-if="dialog_confirm" :text="text_post"></common-dialog-confirm>
        <common-dialog-print :report_url="report_url" v-if="dialog_report"></common-dialog-print>
        <invoice-detail></invoice-detail>
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

.payment-box:not(:last-child) {
    border-bottom: solid 1px #DDD;
    padding-bottom: 3px;
    margin-bottom: 3px;
}
</style>

<script>
module.exports = {
    components : {
        "common-dialog-delete" : httpVueLoader("../../common/components/common-dialog-delete.vue"),
        "common-dialog-confirm" : httpVueLoader("../../common/components/common-dialog-confirm.vue"),
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        "common-dialog-print" : httpVueLoader("../../common/components/common-dialog-print-size.vue"),
        "invoice-detail" : httpVueLoader("../../sales-invoice/components/sales-invoice-new.vue")
    },

    data () {
        return {
            headers: [
                {
                    text: "NO",
                    align: "left",
                    sortable: false,
                    width: "4%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "TANGGAL",
                    align: "center",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "NOMOR INVOICE",
                    align: "left",
                    sortable: false,
                    width: "16%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "SALES",
                    align: "left",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "JATUH TEMPO",
                    align: "center",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "TERM",
                    align: "right",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "TOTAL PIUTANG",
                    align: "right",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "PEMBAYARAN",
                    align: "right",
                    sortable: false,
                    width: "20%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "SISA PIUTANG",
                    align: "right",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                }
                // {
                //     text: "ACTION",
                //     align: "center",
                //     sortable: false,
                //     width: "10%",
                //     class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                // }
            ],

            report_url: ''
        }
    },

    computed : {
        invoices () {
            return this.$store.state.fin007.invoices
        },

        dialog_delete () {
            return this.$store.state.dialog_delete
        },

        dialog_confirm () {
            return this.$store.state.dialog_confirm
        },

        invoice_id () {
            return this.$store.state.fin007.selected_fin007.M_JournalID
        },

        query : {
            get () { return this.$store.state.fin007.search },
            set (v) { this.$store.commit('fin007/update_search', v) }
        },

        query : {
            get () { return this.$store.state.fin007.search },
            set (v) { this.$store.commit('fin007/set_object', ['search', v]) }
        },

        curr_page : {
            get () { return this.$store.state.fin007.current_page },
            set (v) { this.$store.commit('fin007/set_object', ['current_page', v]) }
        },

        xtotal_page () {
            return this.$store.state.fin007.total_invoice_page
        },

        text_post () {
            let j = this.$store.state.fin007.selected_invoice
            return "Apakah anda yakin akan melakukan Posting Jurnal tersebut ?"
        },

        s_date : {
            get () { return this.$store.state.fin007.s_date },
            set (v) { this.$store.commit('fin007/set_common', ['s_date', v]) }
        },

        e_date : {
            get () { return this.$store.state.fin007.e_date },
            set (v) { this.$store.commit('fin007/set_common', ['e_date', v]) }
        },

        title() {
            return "LAPORAN PIUTANG PELANGGAN"
        },

        is_sales() {
            if (this.$store.state.filter.indexOf("J.03")>-1)
                return true
            return false
        },

        custom_title () {
            return this.$store.state.custom_title?this.$store.state.custom_title:false
        },

        dialog_report : {
            get () { return this.$store.state.dialog_print },
            set (v) { this.$store.commit('set_dialog_print', v) }
        },

        grandTotal () {
            let gt = {total:0,paid:0,unpaid:0}
            for (let iv of this.invoices) {
                gt.total += parseFloat(iv.total_grandtotal)
                gt.unpaid += parseFloat(iv.total_unpaid)
                gt.paid += parseFloat(iv.total_paid)
            }

            return gt
        },

        staffs () {
            return this.$store.state.report_param.staffs
        },

        selected_staff : {
            get () { return this.$store.state.fin007.selected_staff },
            set (v) { 
                this.$store.commit('fin007/set_object', ['selected_staff', v])
                this.search()
            }
        }
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
            // this.$store.commit('invoice_new/set_selected_warehouse', null)
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
            // this.$store.commit('invoice_new/set_common', ['invoice_due_date', 
            //     moment(sc.invoice_date, "YYYY-MM-DD").add(Math.round(sc.invoice_term), 'days').format('DD-MM-YYYY')])
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
            // this.$store.commit('invoice_new/set_selected_warehouse', null)
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
                // this.$store.dispatch('invoice_new/search_item')
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
            this.$store.dispatch('fin007/del', {id:x.data})
        },

        post (x) {
            this.select(x)
            this.$store.commit('set_dialog_confirm', true)
        },

        confirm_post (x) {
            this.$store.dispatch('fin007/post', {id:x.data})
        },

        select (x) {
            this.$store.commit('fin007/set_object', ['selected_invoice', x])
        },

        search () {
            return this.$store.dispatch('fin007/search', {})
        },

        change_page(x) {
            this.curr_page = x
            this.$store.dispatch('fin007/search', {})
        },

        change_s_date(x) {
            this.$store.commit('fin007/set_common', ['s_date', x.new_date])
            this.$store.dispatch('fin007/search')
        },

        change_e_date(x) {
            this.$store.commit('fin007/set_common', ['e_date', x.new_date])
            this.$store.dispatch('fin007/search')
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
            this.report_url = this.$store.state.fin007.URL+"report/one_sales_002?id="+so.invoice_id
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
            this.$store.commit('invoice_new/set_common', ['edit', true])
            this.$store.commit('invoice_new/set_common', ['view', true])
            this.$store.commit('invoice_new/set_common', ['invoice_id', sc.invoice_id])
            this.$store.commit('invoice_new/set_common', ['invoice_date', sc.invoice_date])
            // this.$store.commit('invoice_new/set_common', ['invoice_due_date', 
            //     moment(sc.invoice_date, "YYYY-MM-DD").add(Math.round(sc.invoice_term), 'days').format('DD-MM-YYYY')])
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

            let scv = this.$store.state.fin007.selected_invoice
            this.$store.commit('invoice_new/set_selected_customer', {
                customer_id: scv.customer_id, customer_name: scv.customer_name
            })
            
            for (let v of this.$store.state.invoice_new.terms)
                if (v.term_id == sc.invoice_term)
                    this.$store.commit('invoice_new/set_selected_term', v)

            let details = sc.details
            let dfl = JSON.parse(JSON.stringify(this.$store.state.invoice_new.detail_default))
            this.$store.dispatch('invoice_new/search_dp')
            this.$store.commit('invoice_new/set_details', details)

            this.$store.commit('invoice_new/set_common', ['dialog_new', true])
        },

        printMe () {
            this.$store.dispatch("fin007/collect").then((x) => {
                window.open(this.$store.state.fin007.URL + 'report/one_fin_007/excel?' + x)
            })
        }
    },

    mounted () {
        if (this.is_sales)
            this.headers[2].text="NOMOR INVOICE"
        
        this.$store.dispatch("report_param/search_staff").then((x) => {
            this.$store.dispatch('fin007/search')
        })
    }
}
</script>