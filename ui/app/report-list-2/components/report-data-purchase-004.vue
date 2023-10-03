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
                :items="items"
                :loading="false"
                hide-actions
                class="elevation-1">
                <template slot="items" slot-scope="props">
                    <tr class="cyan lighten-4">
                        <td>&nbsp;</td>
                        <td :colspan="5" class="py-2 px-2 text-xs-left"><b>{{ props.item.vendor_name }}</b></td>
                        <!-- <td class="pa-2 text-xs-right"><span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.bill_subtotal)) }}</b></td> -->
                        <td class="pa-2 text-xs-right"><span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.total)) }}</b></td>
                        <!-- <td class="pa-2 text-xs-right"><span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.bill_ppn)) }}</b></td>
                        <td class="pa-2 text-xs-right"><span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.bill_shipping)) }}</b></td>
                        <td class="pa-2 text-xs-right"><span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.bill_grandtotal)) }}</b></td> -->
                        <!-- <td colspan="2">&nbsp;</td> -->
                    </tr>

                    <tr v-for="(inv, n) in props.item.details" :key="'C'+props.item.vendor_id+'I'+inv.receive_id">
                        <td class="text-xs-left pa-2" @click="select(props.item)">{{ inv.receive_date }}</td>
                        <td class="text-xs-left pa-2" @click="select(props.item)">{{ inv.item_code }}</td>
                        <td class="text-xs-left pa-2" @click="select(props.item)"><a href="#" @click="select(props.item),detail($event, inv)">{{ inv.item_name }}</a></td>
                        <td class="text-xs-left pa-2" @click="select(props.item)">{{ inv.sales_name }}</td>
                        <td class="text-xs-left pa-2" @click="select(props.item)">{{ inv.warehouse_name }}</td>
                        <td class="text-xs-right pa-2" @click="select(props.item)" v-show="!is_sales">
                            <b>{{ one_money(Math.round(inv.detail_qty)) }}</b> <span class="grey--text caption">{{ inv.unit_name }}</span>
                        </td>
                        <td class="text-xs-right pa-2" @click="select(props.item)" v-show="!is_sales">
                            <span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(inv.detail_total)) }}</b>
                        </td>

                        <!-- <td class="text-xs-right pa-2" @click="select(props.item)">
                            <div v-show="inv.bill_ppnvalue>0"><span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(inv.bill_ppn)) }}</b></div>
                            <div v-show="inv.bill_ppnvalue==0"><span class="red--text">Tanpa PPN</span></div>
                        </td>
                        

                        <td class="text-xs-right pa-2" @click="select(props.item)" v-show="!is_sales">
                            <span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(inv.bill_shipping)) }}</b>
                        </td>
                        <td class="text-xs-right pa-2" @click="select(props.item)" v-show="!is_sales">
                            <span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(inv.bill_grand_total)) }}</b>
                        </td>
                        <td class="text-xs-left pa-1" @click="select(props.item)">
                            <span class="orange white--text pa-1 d-block text-xs-center" v-if="inv.bill_unpaid==0">LUNAS</span>
                            <span class="success pa-1 d-block white--text text-xs-center" v-if="inv.bill_unpaid>0&&inv.bill_paid>0">SEBAGIAN</span>
                            <span class="grey white--text pa-1 d-block text-xs-center" v-if="inv.bill_paid==0">BARU</span>
                        </td>
                        <td class="text-xs-center pa-0" @click="select(props.item)">
                            
                        </td> -->
                    </tr>
                    <tr class="orange lighten-3">
                        <td :colspan="headers.length" class="pt-1"></td>
                    </tr>
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
        
        <common-dialog-delete :data="bill_id" @confirm_del="confirm_del" v-if="dialog_delete"></common-dialog-delete>
        <common-dialog-confirm :data="bill_id" @confirm="confirm_post" v-if="dialog_confirm" :text="text_post"></common-dialog-confirm>
        <common-dialog-print :report_url="report_url" v-if="dialog_report"></common-dialog-print>
        <receive-detail></receive-detail>
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
</style>

<script>
module.exports = {
    components : {
        "common-dialog-delete" : httpVueLoader("../../common/components/common-dialog-delete.vue"),
        "common-dialog-confirm" : httpVueLoader("../../common/components/common-dialog-confirm.vue"),
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        "common-dialog-print" : httpVueLoader("../../common/components/common-dialog-print-size.vue"),
        "receive-detail" : httpVueLoader("./report-data-purchase-004-detail.vue")
    },

    data () {
        return {
            headers: [
                {
                    text: "TANGGAL",
                    align: "left",
                    sortable: false,
                    width: "8%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "KODE ITEM",
                    align: "left",
                    sortable: false,
                    width: "8%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "NAMA ITEM",
                    align: "left",
                    sortable: false,
                    width: "40%",
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
                    text: "GUDANG TUJUAN",
                    align: "left",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "TOTAL QTY",
                    align: "right",
                    sortable: false,
                    width: "12%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "TOTAL NOMINAL",
                    align: "right",
                    sortable: false,
                    width: "12%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                // {
                //     text: "PPN",
                //     align: "right",
                //     sortable: false,
                //     width: "12%",
                //     class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                // },
                // {
                //     text: "ONGKOS KIRIM",
                //     align: "right",
                //     sortable: false,
                //     width: "12%",
                //     class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                // },
                // {
                //     text: "TOTAL TAGIHAN",
                //     align: "right",
                //     sortable: false,
                //     width: "12%",
                //     class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                // },
                // {
                //     text: "STATUS",
                //     align: "center",
                //     sortable: false,
                //     width: "8%",
                //     class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                // },
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
        items () {
            return this.$store.state.purchase004.items
        },

        dialog_delete () {
            return this.$store.state.dialog_delete
        },

        dialog_confirm () {
            return this.$store.state.dialog_confirm
        },

        bill_id () {
            return this.$store.state.purchase004.selected_purchase004.M_JournalID
        },

        query : {
            get () { return this.$store.state.purchase004.search },
            set (v) { this.$store.commit('purchase004/set_object', ['search', v]) }
        },

        curr_page : {
            get () { return this.$store.state.purchase004.current_page },
            set (v) { this.$store.commit('purchase004/set_object', ['current_page', v]) }
        },

        xtotal_page () {
            return this.$store.state.purchase004.total_item_page
        },

        text_post () {
            let j = this.$store.state.purchase004.selected_bill
            return "Apakah anda yakin akan melakukan Posting Jurnal tersebut ?"
        },

        s_date : {
            get () { return this.$store.state.purchase004.s_date },
            set (v) { this.$store.commit('purchase004/set_common', ['s_date', v]) }
        },

        e_date : {
            get () { return this.$store.state.purchase004.e_date },
            set (v) { this.$store.commit('purchase004/set_common', ['e_date', v]) }
        },

        title() {
            return "DAFTAR PENERIMAAN BARANG"
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
            x.push(JSON.parse(JSON.stringify(this.$store.state.bill_new.detail_default)))
            this.$store.commit('bill_new/set_common', ['edit', false])
            this.$store.commit('bill_new/set_common', ['bill_date', this.$store.state.bill_new.current_date])
            this.$store.commit('bill_new/set_common', ['bill_due_date', this.$store.state.bill_new.current_date])
            this.$store.commit('bill_new/set_common', ['bill_note', ''])
            this.$store.commit('bill_new/set_common', ['bill_number', ''])
            this.$store.commit('bill_new/set_common', ['bill_disc', 0])
            this.$store.commit('bill_new/set_common', ['bill_discrp', 0])
            this.$store.commit('bill_new/set_common', ['bill_disctype', 'P'])
            this.$store.commit('bill_new/set_common', ['bill_shipping', 0])
            this.$store.commit('bill_new/set_common', ['bill_memo', ''])
            this.$store.commit('bill_new/set_common', ['bill_address', ''])
            this.$store.commit('bill_new/set_bill_dps', [])
            this.$store.commit('bill_new/set_selected_vendor', null)
            // this.$store.commit('bill_new/set_selected_warehouse', null)
            this.$store.commit('bill_new/set_selected_term', null)
            this.$store.commit('bill_new/set_items', [])
            this.$store.commit('bill_new/set_details', x)
            this.$store.commit('bill_new/set_common', ['dialog_delivery', true])
        },

        edit (x) {
            if (this.$store.state.bill_new.vendors.length < 1) {
                this.$store.commit('set_dialog_progress', true)
                this.$store.dispatch('bill_new/search_vendor').then(() => {
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
            this.$store.commit('bill_new/set_common', ['edit', true])
            this.$store.commit('bill_new/set_common', ['bill_id', sc.bill_id])
            this.$store.commit('bill_new/set_common', ['bill_date', sc.bill_date])
            // this.$store.commit('bill_new/set_common', ['bill_due_date', 
            //     moment(sc.bill_date, "YYYY-MM-DD").add(Math.round(sc.bill_term), 'days').format('DD-MM-YYYY')])
            this.$store.commit('bill_new/set_common', ['bill_due_date', sc.bill_due_date.split('-').reverse().join('-')])
            this.$store.commit('bill_new/set_common', ['bill_note', sc.bill_note])
            this.$store.commit('bill_new/set_common', ['bill_memo', sc.bill_memo])
            this.$store.commit('bill_new/set_common', ['bill_number', sc.bill_number])
            this.$store.commit('bill_new/set_common', ['bill_disc', sc.bill_disc])
            this.$store.commit('bill_new/set_common', ['bill_discrp', sc.bill_discrp])
            this.$store.commit('bill_new/set_common', ['bill_dp', sc.bill_dp])
            this.$store.commit('bill_new/set_common', ['bill_shipping', sc.bill_shipping])
            this.$store.commit('bill_new/set_common', ['bill_proforma', sc.bill_proforma])
            this.$store.commit('bill_new/set_common', ['sales_name', sc.sales.staff_short])
            this.$store.commit('bill_new/set_bill_dps', sc.bill_dps) 

            this.$store.commit('bill_new/set_common', ['bill_disctype', 
                Math.round(sc.bill_discrp)>0?'R':'P'])

            this.$store.commit('bill_new/set_selected_vendor', null)
            // this.$store.commit('bill_new/set_selected_warehouse', null)
            for (let v of this.$store.state.bill_new.vendors)
                if (v.vendor_id == sc.vendor_id)
                    this.$store.commit('bill_new/set_selected_vendor', v)
            
            for (let v of this.$store.state.bill_new.terms)
                if (v.term_id == sc.bill_term)
                    this.$store.commit('bill_new/set_selected_term', v)

            let details = sc.details
            let dfl = JSON.parse(JSON.stringify(this.$store.state.bill_new.detail_default))
            this.$store.dispatch('bill_new/search_dp')

            if (sc.bill_proforma == 'Y') {
                let dx = []
                for (let d in details) {
                    dx.push(dfl)
                    dx[d].delivery = details[d].sales
                    dx[d].items = details[d].items
                }
                this.$store.commit('bill_new/set_details', dx)
                // this.$store.dispatch('bill_new/search_item')
                this.$store.commit('bill_new/set_common', ['dialog_proforma', true])
            }
            else {
                
                // let acc = this.$store.state.bill_new.accounts
                for (let d of details)
                    d.delivery.items = d.items
                //     for (let a of acc)
                //         if (a.account_id == d.account)
                //             d.account = a

                this.$store.commit('bill_new/set_details', details)
                this.$store.commit('bill_new/set_common', ['dialog_new', true])
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
            this.$store.commit('bill_new/set_common', ['bill_address', address])
            
        },

        del (x) {
            this.select(x)
            this.$store.commit('set_dialog_delete', true)
        },

        confirm_del (x) {
            this.$store.dispatch('purchase004/del', {id:x.data})
        },

        post (x) {
            this.select(x)
            this.$store.commit('set_dialog_confirm', true)
        },

        confirm_post (x) {
            this.$store.dispatch('purchase004/post', {id:x.data})
        },

        select (x) {
            this.$store.commit('purchase004/set_object', ['selected_bill', x])
        },

        search () {
            return this.$store.dispatch('purchase004/search', {})
        },

        change_page(x) {
            this.curr_page = x
            this.$store.dispatch('purchase004/search', {})
        },

        change_s_date(x) {
            this.$store.commit('purchase004/set_common', ['s_date', x.new_date])
            this.$store.dispatch('purchase004/search')
        },

        change_e_date(x) {
            this.$store.commit('purchase004/set_common', ['e_date', x.new_date])
            this.$store.dispatch('purchase004/search')
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

        print_bill (x) {
            this.select(x)
            let so = x
            this.report_url = this.$store.state.purchase004.URL+"report/one_sales_002?id="+so.bill_id
            this.$store.commit('set_dialog_print', true)
        },

        bg_proforma (x) {
            if (x.bill_proforma=='Y')
                return 'amber lighten-4'
            return 'white'
        },

        proforma_edit (z) {

            let sc = z
            this.$store.commit('bill_new/set_common', ['bill_proforma', 'Y'])
            this.$store.dispatch('bill_new/search_item')
            
            this.$store.commit('bill_new/set_common', ['dialog_proforma', true])
        },

        detail (x, y) {
            // this.select(x)
            // let sc = x
            // this.$store.commit('bill_new/set_common', ['edit', true])
            // this.$store.commit('bill_new/set_common', ['bill_id', sc.bill_id])
            // this.$store.commit('bill_new/set_common', ['bill_date', sc.bill_date])
            // this.$store.commit('bill_new/set_common', ['bill_note', sc.bill_note])
            // this.$store.commit('bill_new/set_common', ['bill_number', sc.bill_number])
            // this.$store.commit('bill_new/set_common', ['bill_disc', sc.bill_disc])
            // this.$store.commit('bill_new/set_common', ['bill_discrp', sc.bill_discrp])
            // this.$store.commit('bill_new/set_common', ['bill_dp', sc.bill_dp])
            // this.$store.commit('bill_new/set_bill_dps', sc.bill_dps)

            // this.$store.commit('bill_new/set_common', ['bill_disctype', 
                // Math.round(sc.bill_discrp)>0?'R':'P'])

            // this.$store.commit('bill_new/set_selected_vendor', null)
            // this.$store.commit('bill_new/set_selected_warehouse', null)
            // for (let v of this.$store.state.bill_new.vendors)
                // if (v.vendor_id == sc.vendor_id)
                    // this.$store.commit('bill_new/set_selected_vendor', v)
            // for (let v of this.$store.state.bill_new.warehouses)
            //     if (v.warehouse_id == sc.warehouse_id)
            //         this.$store.commit('bill_new/set_selected_warehouse', v)

            let details = sc.details
            // let acc = this.$store.state.bill_new.accounts
            for (let d of details)
                d.receive.items = d.items
            //     for (let a of acc)
            //         if (a.account_id == d.account)
            //             d.account = a
            this.$store.commit('bill_new/set_details', details)
            this.$store.commit('bill_new/set_common', ['dialog_new', true])
        },

        detail (x, y) {
            x.preventDefault()

            this.$store.commit('purchase004/set_object', ['selected_item', y])
            this.$store.commit('purchase004/set_object', ['dialog_detail', true])
            // let sc = y
            // this.$store.commit('bill_new/set_common', ['edit', true])
            // this.$store.commit('bill_new/set_common', ['view', true])
            // this.$store.commit('bill_new/set_common', ['bill_id', sc.bill_id])
            // this.$store.commit('bill_new/set_common', ['bill_date', sc.bill_date])

            // this.$store.commit('bill_new/set_common', ['bill_due_date', sc.bill_due_date.split('-').reverse().join('-')])
            // this.$store.commit('bill_new/set_common', ['bill_note', sc.bill_note])
            // this.$store.commit('bill_new/set_common', ['bill_memo', sc.bill_memo])
            // this.$store.commit('bill_new/set_common', ['bill_number', sc.bill_number])
            // this.$store.commit('bill_new/set_common', ['bill_disc', sc.bill_disc])
            // this.$store.commit('bill_new/set_common', ['bill_discrp', sc.bill_discrp])
            // this.$store.commit('bill_new/set_common', ['bill_dp', sc.bill_dp])
            // this.$store.commit('bill_new/set_common', ['bill_shipping', sc.bill_shipping])

            // this.$store.commit('bill_new/set_bill_dps', sc.bill_dps) 

            // this.$store.commit('bill_new/set_common', ['bill_disctype', 
            //     Math.round(sc.bill_discrp)>0?'R':'P'])

            // let scv = this.$store.state.purchase004.selected_bill
            // this.$store.commit('bill_new/set_selected_vendor', {
            //     vendor_id: scv.vendor_id, vendor_name: scv.vendor_name
            // })

            // this.$store.commit('bill_new/set_object', ['selected_term', null])
            // for (let t of this.$store.state.bill_new.terms) {
            //     if (t.term_id == sc.term_id)
            //     this.$store.commit('bill_new/set_object', ['selected_term', t])
            // }

            // let details = sc.details
            // let dfl = JSON.parse(JSON.stringify(this.$store.state.bill_new.detail_default))
            // this.$store.dispatch('bill_new/search_dp')
            // this.$store.commit('bill_new/set_details', details)

            // this.$store.commit('bill_new/set_common', ['dialog_new', true])
        },

        printMe () {
            this.$store.dispatch("purchase004/collect").then((x) => {
                window.open(this.$store.state.purchase004.URL + 'report/one_purchase_004/excel?' + x)
            })
        }
    },

    mounted () {
        if (this.is_sales)
            this.headers[2].text="NOMOR INVOICE"

        this.$store.dispatch('purchase004/search', {})
    }
}
</script>