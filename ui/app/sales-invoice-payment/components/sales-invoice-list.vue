<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0" v-show="!custom_title">
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
                    <!-- <v-btn color="success" class="ma-0 btn-icon" @click="add">
                        <v-icon>add</v-icon>
                    </v-btn> -->

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

                            <v-btn color="success" class="ma-0 ml-2 btn-icon" @click="add" v-show="!is_sales">
                                <v-icon>add</v-icon>
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
                <template slot="items" slot-scope="props">
                    <td class="text-xs-left pa-1" :class="bg_proforma(props.item)" @click="select(props.item)" style="writing-mode:tb-rl;height:auto;transform:scale(-1)">
                        <span class="orange white--text px-2 py-3 d-block" v-if="props.item.invoice_lunas=='N'&&props.item.invoice_paid>0">LUNAS</span>
                        <span class="success pa-2 py-3 d-block white--text" v-if="props.item.invoice_lunas=='Y'">SEBAGIAN</span>
                        <span class="grey white--text pa-2 py-3 d-block" v-if="props.item.invoice_lunas=='N'&&props.item.invoice_paid==0">BARU</span>
                    </td>
                    <td class="text-xs-left pa-2" :class="bg_proforma(props.item)" @click="select(props.item)">
                        {{ props.item.invoice_date }}
                        <div>{{ props.item.invoice_number }}</div></td>
                    
                    <td class="text-xs-left pa-2" :class="bg_proforma(props.item)" @click="select(props.item)" v-show="!is_sales">
                        {{ props.item.customer_name }}
                        <div v-show="props.item.delivery_memos!=''">
                            <div class="caption blue--text" v-for="(m,n) in props.item.delivery_memos.split(';')" :key="n" v-show="m!=''"><i>— {{m}}</i></div>
                        </div>
                    </td> 
                    <!-- <td class="text-xs-left pa-2" @click="select(props.item)" v-show="!is_sales">
                        -
                    </td>  -->
                    
                    <td class="text-xs-left pa-2" :class="bg_proforma(props.item)" @click="select(props.item)" v-show="!is_sales">
                        {{ props.item.invoice_note }}
                    </td> 
                    <td class="text-xs-right pa-2" :class="bg_proforma(props.item)" @click="select(props.item)" v-show="!is_sales">
                        <span class="grey--text caption">Rp</span> <b>{{ one_money(props.item.invoice_grand_total) }}</b>
                    </td>
                    <td class="text-xs-center pa-2" :class="bg_proforma(props.item)" @click="select(props.item)" v-show="!is_sales">
                        <v-btn color="success btn-icon px-3" large><v-icon>monetization_on</v-icon></v-btn>
                    </td>
                    <!-- <td class="text-xs-left pa-2" :class="bg_proforma(props.item)" @click="select(props.item)" v-show="!is_sales">
                        <v-btn color="success" small class="ma-0" block v-show="props.item.invoice_lunas=='Y'">Lunas</v-btn>
                        <v-btn color="orange" small class="ma-0" block dark v-show="props.item.invoice_lunas=='N'&&props.item.invoice_paid>0">Bayar Sebagian</v-btn>
                        <v-btn color="grey" small class="ma-0" block dark v-show="props.item.invoice_lunas=='N'&&props.item.invoice_paid==0">Belum Dibayar</v-btn>
                    </td> -->
                    <td class="text-xs-center pa-0" :class="bg_proforma(props.item)" @click="select(props.item)">
                        <div class="row">
                            <div class="col-12">
                                <v-btn color="orange" class="btn-icon ma-0" small @click="print_invoice(props.item)" dark title="Cetak invoice"><v-icon>print</v-icon></v-btn>
                                <v-btn color="cyan" class="btn-icon ma-0" small @click="journal(props.item)" dark v-show="props.item.journal_id!=0" title="Jurnal"><v-icon>assignment</v-icon></v-btn>
                            </div>
                            <div class="col-12 mt-1">
                                <v-btn color="primary" class="btn-icon ma-0" small @click="edit(props.item)" title="Ubah invoice"><v-icon>create</v-icon></v-btn>
                                <v-btn color="red" 
                                    class="btn-icon ma-0" small @click="del(props.item)" dark title="Hapus invoice"><v-icon>delete</v-icon></v-btn>
                            </div>
                        </div>
                    </td>
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
    </v-card>
</template>

<style scoped>
.v-text-field.v-text-field--solo .v-input__control {
    min-height: 36px;
}
.v-text-field.v-text-field--solo .v-input__append-outer {
    margin-top: 0px;
    margin-left: 0px;
}
</style>

<script>
module.exports = {
    components : {
        "common-dialog-delete" : httpVueLoader("../../common/components/common-dialog-delete.vue"),
        "common-dialog-confirm" : httpVueLoader("../../common/components/common-dialog-confirm.vue"),
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        "common-dialog-print" : httpVueLoader("../../common/components/common-dialog-print-size.vue")
    },

    data () {
        return {
            headers: [
                {
                    text: "",
                    align: "left",
                    sortable: false,
                    width: "3%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "TANGGAL / NOMOR",
                    align: "left",
                    sortable: false,
                    width: "15%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "VENDOR",
                    align: "left",
                    sortable: false,
                    width: "25%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "CATATAN",
                    align: "left",
                    sortable: false,
                    width: "27%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "TOTAL TAGIHAN",
                    align: "right",
                    sortable: false,
                    width: "15%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "BAYAR",
                    align: "center",
                    sortable: false,
                    width: "5%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "ACTION",
                    align: "center",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                }
            ],

            report_url: ''
        }
    },

    computed : {
        invoices () {
            return this.$store.state.invoice.invoices
        },

        dialog_delete () {
            return this.$store.state.dialog_delete
        },

        dialog_confirm () {
            return this.$store.state.dialog_confirm
        },

        invoice_id () {
            return this.$store.state.invoice.selected_invoice.M_JournalID
        },

        query : {
            get () { return this.$store.state.invoice.search },
            set (v) { this.$store.commit('invoice/update_search', v) }
        },

        curr_page : {
            get () { return this.$store.state.invoice.current_page },
            set (v) { this.$store.commit('invoice/update_current_page', v) }
        },

        xtotal_page () {
            return this.$store.state.invoice.total_invoice_page
        },

        text_post () {
            let j = this.$store.state.invoice.selected_invoice
            return "Apakah anda yakin akan melakukan Posting Jurnal tersebut ?"
        },

        s_date : {
            get () { return this.$store.state.invoice.s_date },
            set (v) { this.$store.commit('invoice/set_common', ['s_date', v]) }
        },

        e_date : {
            get () { return this.$store.state.invoice.e_date },
            set (v) { this.$store.commit('invoice/set_common', ['e_date', v]) }
        },

        title() {
            return "PEMBAYARAN TAGIHAN"
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
            this.$store.commit('invoice_new/set_invoice_dps', [])
            this.$store.commit('invoice_new/set_selected_customer', null)
            // this.$store.commit('invoice_new/set_selected_warehouse', null)
            this.$store.commit('invoice_new/set_selected_term', null)
            this.$store.commit('invoice_new/set_items', [])
            this.$store.commit('invoice_new/set_details', x)
            this.$store.commit('invoice_new/set_common', ['dialog_new', true])
        },

        edit (x) {
            this.select(x)
            let sc = x
            this.$store.commit('invoice_new/set_common', ['edit', true])
            this.$store.commit('invoice_new/set_common', ['invoice_id', sc.invoice_id])
            this.$store.commit('invoice_new/set_common', ['invoice_date', sc.invoice_date])
            this.$store.commit('invoice_new/set_common', ['invoice_due_date', sc.invoice_due_date.split('-').reverse().join('-')])
            this.$store.commit('invoice_new/set_common', ['invoice_note', sc.invoice_note])
            this.$store.commit('invoice_new/set_common', ['invoice_number', sc.invoice_number])
            this.$store.commit('invoice_new/set_common', ['invoice_disc', sc.invoice_disc])
            this.$store.commit('invoice_new/set_common', ['invoice_discrp', sc.invoice_discrp])
            this.$store.commit('invoice_new/set_common', ['invoice_dp', sc.invoice_dp])
            this.$store.commit('invoice_new/set_common', ['invoice_shipping', sc.invoice_shipping])
            this.$store.commit('invoice_new/set_common', ['invoice_proforma', sc.invoice_proforma])
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
            
        },

        del (x) {
            this.select(x)
            this.$store.commit('set_dialog_delete', true)
        },

        confirm_del (x) {
            this.$store.dispatch('invoice/del', {id:x.data})
        },

        post (x) {
            this.select(x)
            this.$store.commit('set_dialog_confirm', true)
        },

        confirm_post (x) {
            this.$store.dispatch('invoice/post', {id:x.data})
        },

        select (x) {
            this.$store.commit('invoice/set_selected_invoice', x)
        },

        search () {
            return this.$store.dispatch('invoice/search', {})
        },

        change_page(x) {
            this.curr_page = x
            this.$store.dispatch('invoice/search', {})
        },

        change_s_date(x) {
            this.$store.commit('invoice/set_common', ['s_date', x.new_date])
            this.$store.dispatch('invoice/search')
        },

        change_e_date(x) {
            this.$store.commit('invoice/set_common', ['e_date', x.new_date])
            this.$store.dispatch('invoice/search')
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
            this.report_url = this.$store.state.invoice.URL+"report/one_sales_002?id="+so.invoice_id
            this.$store.commit('set_dialog_print', true)
        },

        bg_proforma (x) {
            console.log(x)
            if (x.invoice_proforma=='Y')
                return 'amber lighten-4'
            return 'white'
        },

        proforma_edit (z) {

            let sc = z
            this.$store.commit('invoice_new/set_common', ['invoice_proforma', 'Y'])
            this.$store.dispatch('invoice_new/search_item')
            
            this.$store.commit('invoice_new/set_common', ['dialog_proforma', true])
        }
    },

    mounted () {
        console.log(this.is_sales)
        if (this.is_sales)
            this.headers[2].text="NOMOR INVOICE"
    }
}
</script>