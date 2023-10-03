<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0" v-show="!custom_title">
            <v-layout row wrap>
                <v-flex xs7>
                    <h3 class="display-1 font-weight-light zalfa-text-title">{{title}}</h3>
                </v-flex>
                <!-- <v-flex xs3 pr-2>
                    <v-select
                        :items="warehouses"
                        v-model="selected_warehouse"
                        item-value="warehouse_id"
                        item-text="warehouse_name"
                        return-object
                        solo
                    ></v-select>
                </v-flex> -->
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
                </v-flex> -->
                <v-flex xs1 pl-1>
                    <common-datepicker
                        label="Dari Tanggal"
                        :date="e_date"
                        data="0"
                        @change="change_e_date"
                        classs=""
                        hints="Per Tanggal"
                        :details="true"
                        :solo="true"
                    ></common-datepicker>
                </v-flex>
                <v-flex xs2 pl-2>
                    <v-select
                        :items="categories"
                        v-model="selected_category"
                        item-value="category_id"
                        item-text="category_name"
                        return-object
                        label="Kategori Produk"
                        hide-details
                        solo
                        clearable
                    ></v-select>
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
            <template>
                <v-tabs v-model="activeTab" class="d-flex mb-1">
                    <v-tab v-for="(w, n) in warehouses" :key="'tab-'+n"
                        class="cyan lighten-5 grow" active-class="cyan white--text" >{{ w.warehouse_name }}</v-tab>
                    <!-- <v-tab-item v-for="(w, n) in warehouses" :key="'tab-item-'+n">
                        {{ w.details }}
                    </v-tab-item> -->
                </v-tabs>
            </template>

            <v-data-table 
                :headers="headers"
                :items="items"
                :loading="false"
                hide-actions
                class="elevation-1">
                <template slot="items" slot-scope="props">
                    <tr class="lighten-5">
                        <td class="py-2 px-2 text-xs-left cyan lighten-5">
                            {{ props.item.category_name }}</td>
                        <td class="py-2 px-2 text-xs-left">
                            {{ props.item.item_code }}</td>
                        <td :colspan="2" class="py-2 px-2 text-xs-left">
                            <b class="primary--text">{{ props.item.item_name }}</b></td>
                        <td class="py-2 px-2 text-xs-right"><b>{{ one_money(Math.round(props.item.log_a4_qty)) }}</b> <span class="grey--text caption">{{ props.item.unit_name }}</span></td>
                        <td class="py-2 px-2 text-xs-right"><span class="grey--text caption">Rp</span> <b>{{ one_money(props.item.item_hpp) }}</b></td>
                        <!-- <td class="py-3 px-2 text-xs-right"><b>{{ one_money(Math.round(props.item.stock_out_qty)) }}</b> <span class="grey--text caption">{{ props.item.unit_name }}</span></td> -->
                        <td class="py-2 px-2 text-xs-right"><span class="grey--text caption">Rp</span> <b>{{ one_money(props.item.item_hpp * props.item.log_a4_qty) }}</b></td>
                        <!-- <td class="pa-2 text-xs-right"><span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.log_subtotal)) }}</b></td> -->
                        <!-- <td class="pa-2 text-xs-right"><span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.total)) }}</b></td> -->
                        <!-- <td class="pa-2 text-xs-right"><span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.log_ppn)) }}</b></td>
                        <td class="pa-2 text-xs-right"><span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.log_shipping)) }}</b></td>
                        <td class="pa-2 text-xs-right"><span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.log_grandtotal)) }}</b></td> -->
                        <!-- <td colspan="2">&nbsp;</td> -->
                    </tr>

                    <!-- <tr v-for="(inv, n) in props.item.logs" :key="'C'+props.item.item_id+'I'+inv.stock_id">
                        <td class="text-xs-left pa-2" @click="select(props.item)">{{ inv.stock_date }}</td>
                        <td class="text-xs-left pa-2" @click="select(props.item)">{{ inv.type_text }}</td>
                        <td class="text-xs-left pa-2" @click="select(props.item)">{{ inv.ref_number }}</td>
                        <td class="text-xs-left pa-2" @click="select(props.item)">{{ inv.stock_before_qty }} <span class="grey--text caption">{{ inv.unit_name }}</span></td>
                        
                        <td class="text-xs-right pa-2" @click="select(props.item)" v-show="!is_sales && inv.stock_qty > 0">
                            <b>{{ one_money(Math.round(inv.stock_qty)) }}</b> <span class="grey--text caption">{{ inv.unit_name }}</span>
                        </td>
                        <td class="text-xs-right pa-2" @click="select(props.item)" v-show="!is_sales && inv.stock_qty <= 0">-</td>

                        <td class="text-xs-right pa-2" @click="select(props.item)" v-show="!is_sales && inv.stock_qty < 0">
                            <b>{{ one_money(Math.round(inv.stock_qty)) }}</b> <span class="grey--text caption">{{ inv.unit_name }}</span>
                        </td>
                        <td class="text-xs-right pa-2" @click="select(props.item)" v-show="!is_sales && inv.stock_qty >= 0">-</td>

                        <td class="text-xs-right pa-2" @click="select(props.item)" v-show="!is_sales">
                            <b>{{ one_money(Math.round(inv.stock_after_qty)) }} <span class="grey--text caption">{{ inv.unit_name }}</span></b>
                        </td>
                    </tr> -->
                    <!-- <tr class="orange lighten-3"><td :colspan="headers.length" class="pt-1"></td></tr> -->
                </template>
                <template v-slot:footer>
                    <tr>
                        <td colspan="2"></td>
                        <td class="px-2" colspan="4"><b>Total Nominal</b></td>
                        <td class="px-2 text-xs-right"><span class="grey--text">Rp</span> <b>{{ one_money(total) }}</b></td>
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
        
        <common-dialog-delete :data="log_id" @confirm_del="confirm_del" v-if="dialog_delete"></common-dialog-delete>
        <common-dialog-confirm :data="log_id" @confirm="confirm_post" v-if="dialog_confirm" :text="text_post"></common-dialog-confirm>
        <common-dialog-print :report_url="report_url" v-if="dialog_report"></common-dialog-print>
        <item-detail></item-detail>
    </v-card>
</template>

<style scoped>
.v-tabs__container {
    display: flex;
}
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
        "item-detail" : httpVueLoader("./report-data-iv-008-detail.vue")
    },

    data () {
        return {
            headers: [
                {
                    text: "KATEGORI",
                    align: "left",
                    sortable: false,
                    width: "12%",
                    class: "pa-2 cyan white--text"
                },
                {
                    text: "KODE ITEM",
                    align: "left",
                    sortable: false,
                    width: "12%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "NAMA ITEM",
                    align: "left",
                    sortable: false,
                    width: "23%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "",
                    align: "left",
                    sortable: false,
                    width: "15%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "QTY",
                    align: "right",
                    sortable: false,
                    width: "12%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "HPP RATA2",
                    align: "right",
                    sortable: false,
                    width: "12%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "NOMINAL",
                    align: "right",
                    sortable: false,
                    width: "14%",
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
        activeTab : {
            get () { return this.$store.state.iv008.current_tab },
            set (v) { 
                this.$store.commit('iv008/set_object', ['current_tab', v]) 
                let y = this.warehouses[v]
                this.$store.commit('iv008/set_object', ['items', y.details])
            }
        },

        items () {
            return this.$store.state.iv008.items
        },

        dialog_delete () {
            return this.$store.state.dialog_delete
        },

        dialog_confirm () {
            return this.$store.state.dialog_confirm
        },

        log_id () {
            return this.$store.state.iv008.selected_iv008.M_JournalID
        },

        query : {
            get () { return this.$store.state.iv008.search },
            set (v) { this.$store.commit('iv008/set_object', ['search', v]) }
        },

        curr_page : {
            get () { return this.$store.state.iv008.current_page },
            set (v) { this.$store.commit('iv008/set_object', ['current_page', v]) }
        },

        xtotal_page () {
            return this.$store.state.iv008.total_item_page
        },

        text_post () {
            let j = this.$store.state.iv008.selected_log
            return "Apakah anda yakin akan melakukan Posting Jurnal tersebut ?"
        },

        s_date : {
            get () { return this.$store.state.iv008.s_date },
            set (v) { this.$store.commit('iv008/set_common', ['s_date', v]) }
        },

        e_date : {
            get () { return this.$store.state.iv008.e_date },
            set (v) { this.$store.commit('iv008/set_common', ['e_date', v]) }
        },

        title() {
            return "LAPORAN NILAI STOK GUDANG"
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

        warehouses () {
            return this.$store.state.report_param.warehouses
        },

        selected_warehouse : {
            get () { return this.$store.state.iv008.selected_warehouse },
            set (v) { this.$store.commit('iv008/set_object', ['selected_warehouse', v]) }
        },

        categories () {
            return this.$store.state.iv008.categories
        },

        selected_category : {
            get () { return this.$store.state.iv008.selected_category },
            set (v) { 
                this.$store.commit('iv008/set_object', ['selected_category', v]) 
                this.search()
            }
        },

        warehouses () {
            return this.$store.state.iv008.warehouses
        },

        total () {
            let total = 0
            for (let i of this.items) total += parseFloat(i.item_hpp*i.log_a4_qty)
            return total
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        add () {
            let x = []
            x.push(JSON.parse(JSON.stringify(this.$store.state.log_new.detail_default)))
            this.$store.commit('log_new/set_common', ['edit', false])
            this.$store.commit('log_new/set_common', ['log_date', this.$store.state.log_new.current_date])
            this.$store.commit('log_new/set_common', ['log_due_date', this.$store.state.log_new.current_date])
            this.$store.commit('log_new/set_common', ['log_note', ''])
            this.$store.commit('log_new/set_common', ['log_number', ''])
            this.$store.commit('log_new/set_common', ['log_disc', 0])
            this.$store.commit('log_new/set_common', ['log_discrp', 0])
            this.$store.commit('log_new/set_common', ['log_disctype', 'P'])
            this.$store.commit('log_new/set_common', ['log_shipping', 0])
            this.$store.commit('log_new/set_common', ['log_memo', ''])
            this.$store.commit('log_new/set_common', ['log_address', ''])
            this.$store.commit('log_new/set_log_dps', [])
            this.$store.commit('log_new/set_selected_vendor', null)
            // this.$store.commit('log_new/set_selected_warehouse', null)
            this.$store.commit('log_new/set_selected_term', null)
            this.$store.commit('log_new/set_items', [])
            this.$store.commit('log_new/set_details', x)
            this.$store.commit('log_new/set_common', ['dialog_delivery', true])
        },

        edit (x) {
            if (this.$store.state.log_new.vendors.length < 1) {
                this.$store.commit('set_dialog_progress', true)
                this.$store.dispatch('log_new/search_vendor').then(() => {
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
            this.$store.commit('log_new/set_common', ['edit', true])
            this.$store.commit('log_new/set_common', ['log_id', sc.log_id])
            this.$store.commit('log_new/set_common', ['log_date', sc.log_date])
            // this.$store.commit('log_new/set_common', ['log_due_date', 
            //     moment(sc.log_date, "YYYY-MM-DD").add(Math.round(sc.log_term), 'days').format('DD-MM-YYYY')])
            this.$store.commit('log_new/set_common', ['log_due_date', sc.log_due_date.split('-').reverse().join('-')])
            this.$store.commit('log_new/set_common', ['log_note', sc.log_note])
            this.$store.commit('log_new/set_common', ['log_memo', sc.log_memo])
            this.$store.commit('log_new/set_common', ['log_number', sc.log_number])
            this.$store.commit('log_new/set_common', ['log_disc', sc.log_disc])
            this.$store.commit('log_new/set_common', ['log_discrp', sc.log_discrp])
            this.$store.commit('log_new/set_common', ['log_dp', sc.log_dp])
            this.$store.commit('log_new/set_common', ['log_shipping', sc.log_shipping])
            this.$store.commit('log_new/set_common', ['log_proforma', sc.log_proforma])
            this.$store.commit('log_new/set_common', ['sales_name', sc.sales.staff_short])
            this.$store.commit('log_new/set_log_dps', sc.log_dps) 

            this.$store.commit('log_new/set_common', ['log_disctype', 
                Math.round(sc.log_discrp)>0?'R':'P'])

            this.$store.commit('log_new/set_selected_vendor', null)
            // this.$store.commit('log_new/set_selected_warehouse', null)
            for (let v of this.$store.state.log_new.vendors)
                if (v.vendor_id == sc.vendor_id)
                    this.$store.commit('log_new/set_selected_vendor', v)
            
            for (let v of this.$store.state.log_new.terms)
                if (v.term_id == sc.log_term)
                    this.$store.commit('log_new/set_selected_term', v)

            let details = sc.details
            let dfl = JSON.parse(JSON.stringify(this.$store.state.log_new.detail_default))
            this.$store.dispatch('log_new/search_dp')

            if (sc.log_proforma == 'Y') {
                let dx = []
                for (let d in details) {
                    dx.push(dfl)
                    dx[d].delivery = details[d].sales
                    dx[d].items = details[d].items
                }
                this.$store.commit('log_new/set_details', dx)
                // this.$store.dispatch('log_new/search_item')
                this.$store.commit('log_new/set_common', ['dialog_proforma', true])
            }
            else {
                
                // let acc = this.$store.state.log_new.accounts
                for (let d of details)
                    d.delivery.items = d.items
                //     for (let a of acc)
                //         if (a.account_id == d.account)
                //             d.account = a

                this.$store.commit('log_new/set_details', details)
                this.$store.commit('log_new/set_common', ['dialog_new', true])
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
            this.$store.commit('log_new/set_common', ['log_address', address])
            
        },

        del (x) {
            this.select(x)
            this.$store.commit('set_dialog_delete', true)
        },

        confirm_del (x) {
            this.$store.dispatch('iv008/del', {id:x.data})
        },

        post (x) {
            this.select(x)
            this.$store.commit('set_dialog_confirm', true)
        },

        confirm_post (x) {
            this.$store.dispatch('iv008/post', {id:x.data})
        },

        select (x) {
            this.$store.commit('iv008/set_object', ['selected_log', x])
        },

        search () {
            return this.$store.dispatch('iv008/search', {})
        },

        change_page(x) {
            this.curr_page = x
            this.$store.dispatch('iv008/search', {})
        },

        change_s_date(x) {
            this.$store.commit('iv008/set_common', ['s_date', x.new_date])
            this.$store.dispatch('iv008/search')
        },

        change_e_date(x) {
            this.$store.commit('iv008/set_common', ['e_date', x.new_date])
            this.$store.dispatch('iv008/search')
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

        print_log (x) {
            this.select(x)
            let so = x
            this.report_url = this.$store.state.iv008.URL+"report/one_sales_002?id="+so.log_id
            this.$store.commit('set_dialog_print', true)
        },

        bg_proforma (x) {
            if (x.log_proforma=='Y')
                return 'amber lighten-4'
            return 'white'
        },

        proforma_edit (z) {

            let sc = z
            this.$store.commit('log_new/set_common', ['log_proforma', 'Y'])
            this.$store.dispatch('log_new/search_item')
            
            this.$store.commit('log_new/set_common', ['dialog_proforma', true])
        },

        detail (x, y) {
            // this.select(x)
            // let sc = x
            // this.$store.commit('log_new/set_common', ['edit', true])
            // this.$store.commit('log_new/set_common', ['log_id', sc.log_id])
            // this.$store.commit('log_new/set_common', ['log_date', sc.log_date])
            // this.$store.commit('log_new/set_common', ['log_note', sc.log_note])
            // this.$store.commit('log_new/set_common', ['log_number', sc.log_number])
            // this.$store.commit('log_new/set_common', ['log_disc', sc.log_disc])
            // this.$store.commit('log_new/set_common', ['log_discrp', sc.log_discrp])
            // this.$store.commit('log_new/set_common', ['log_dp', sc.log_dp])
            // this.$store.commit('log_new/set_log_dps', sc.log_dps)

            // this.$store.commit('log_new/set_common', ['log_disctype', 
                // Math.round(sc.log_discrp)>0?'R':'P'])

            // this.$store.commit('log_new/set_selected_vendor', null)
            // this.$store.commit('log_new/set_selected_warehouse', null)
            // for (let v of this.$store.state.log_new.vendors)
                // if (v.vendor_id == sc.vendor_id)
                    // this.$store.commit('log_new/set_selected_vendor', v)
            // for (let v of this.$store.state.log_new.warehouses)
            //     if (v.warehouse_id == sc.warehouse_id)
            //         this.$store.commit('log_new/set_selected_warehouse', v)

            let details = sc.details
            // let acc = this.$store.state.log_new.accounts
            for (let d of details)
                d.receive.items = d.items
            //     for (let a of acc)
            //         if (a.account_id == d.account)
            //             d.account = a
            this.$store.commit('log_new/set_details', details)
            this.$store.commit('log_new/set_common', ['dialog_new', true])
        },

        detail (x, y) {
            x.preventDefault()

            this.$store.commit('iv008/set_object', ['selected_item', y])
            this.$store.commit('iv008/set_object', ['dialog_detail', true])
            // let sc = y
            // this.$store.commit('log_new/set_common', ['edit', true])
            // this.$store.commit('log_new/set_common', ['view', true])
            // this.$store.commit('log_new/set_common', ['log_id', sc.log_id])
            // this.$store.commit('log_new/set_common', ['log_date', sc.log_date])

            // this.$store.commit('log_new/set_common', ['log_due_date', sc.log_due_date.split('-').reverse().join('-')])
            // this.$store.commit('log_new/set_common', ['log_note', sc.log_note])
            // this.$store.commit('log_new/set_common', ['log_memo', sc.log_memo])
            // this.$store.commit('log_new/set_common', ['log_number', sc.log_number])
            // this.$store.commit('log_new/set_common', ['log_disc', sc.log_disc])
            // this.$store.commit('log_new/set_common', ['log_discrp', sc.log_discrp])
            // this.$store.commit('log_new/set_common', ['log_dp', sc.log_dp])
            // this.$store.commit('log_new/set_common', ['log_shipping', sc.log_shipping])

            // this.$store.commit('log_new/set_log_dps', sc.log_dps) 

            // this.$store.commit('log_new/set_common', ['log_disctype', 
            //     Math.round(sc.log_discrp)>0?'R':'P'])

            // let scv = this.$store.state.iv008.selected_log
            // this.$store.commit('log_new/set_selected_vendor', {
            //     vendor_id: scv.vendor_id, vendor_name: scv.vendor_name
            // })

            // this.$store.commit('log_new/set_object', ['selected_term', null])
            // for (let t of this.$store.state.log_new.terms) {
            //     if (t.term_id == sc.term_id)
            //     this.$store.commit('log_new/set_object', ['selected_term', t])
            // }

            // let details = sc.details
            // let dfl = JSON.parse(JSON.stringify(this.$store.state.log_new.detail_default))
            // this.$store.dispatch('log_new/search_dp')
            // this.$store.commit('log_new/set_details', details)

            // this.$store.commit('log_new/set_common', ['dialog_new', true])
        },

        printMe () {
            this.$store.dispatch("iv008/collect").then((x) => {
                window.open(this.$store.state.iv008.URL + 'report/one_iv_008/excel?' + x)
            })
        }
    },

    mounted () {
        if (this.is_sales)
            this.headers[2].text="NOMOR INVOICE"

        this.$store.dispatch('iv008/search').then((x) => {
            this.$store.dispatch('iv008/search_category').then((x) => {
                // this.selected_warehouse = x[0]
                // this.search()
            })
        })
    }
}
</script>