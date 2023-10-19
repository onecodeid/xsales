<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0" v-show="!custom_title">
            <v-layout row wrap>
                <v-flex xs8>
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
                        :clearables="true"
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
                <!-- <v-flex xs2 pl-2>
                    <v-select
                        :items="staffs"
                        v-model="selected_staff"
                        return-object
                        item-text="staff_name"
                        item-value="staff_id"
                        label="Sales"
                        solo
                        clearable
                    >
                        <template slot="selection" slot-scope="data">
                            <span class="" v-show="data.item.staff_name.length <= 15">{{data.item.staff_name}}</span>
                            <span class="" v-show="data.item.staff_name.length > 15">{{data.item.staff_name.substr(0, 13)+' ..'}}</span>
                        </template>
                    </v-select>
                </v-flex> -->
                <v-flex xs2 class="text-xs-right" pl-2>
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
                :items="saless"
                :loading="false"
                hide-actions
                class="elevation-1">
                <template slot="items" slot-scope="props">
                    <td class="text-xs-left pa-1" :class="bg_proforma(props.item)" @click="select(props.item)" v-show="!is_sales" style="writing-mode:tb-rl;height:auto;transform:scale(-1)">
                        <!-- <v-btn color="success" small v-show="props.item.sales_done == 'Y'" class="ma-0" block>Selesai</v-btn>
                        <v-btn color="orange" small v-show="props.item.sales_done == 'X'" class="ma-0 white--text" block>Kirim Sebagian</v-btn>
                        <v-btn color="grey" small v-show="props.item.sales_done == 'N'" class="ma-0 white--text" block>Data Baru</v-btn> -->
                        <!-- {{ props.item.sales_done == "Y"? "Selesai" : (props.item.sales_done == "X"?"Dikirim Sebagian":"Baru") }} -->

                        <span class="white--text success px-2 py-3 d-block" v-if="props.item.sales_done == 'Y'">SELESAI</span>
                        <span class="white--text orange px-2 py-3 d-block" v-if="props.item.sales_done == 'X'">SEBAGIAN</span>
                        <span class="white--text grey px-2 py-3 d-block" v-if="props.item.sales_done == 'N'">BARU</span>
                        <!-- <span class="white--text px-2 py-3 d-block"
                            :class="{'success':props.item.sales_done == 'Y',
                                    'orange':props.item.sales_done == 'X',
                                    'grey':props.item.sales_done == 'N'}">
                            
                        </span> -->
                    </td> 

                    <td class="text-xs-left pa-2" :class="bg_proforma(props.item)" @click="select(props.item)">{{ props.item.sales_date }}</td>
                    <td class="text-xs-left pa-2" :class="bg_proforma(props.item)" @click="select(props.item)"><b>{{ props.item.sales_number }}</b></td>
                    <td class="text-xs-left pa-2" :class="bg_proforma(props.item)" @click="select(props.item)" v-show="!is_sales">
                        {{ props.item.customer_name }}
                    </td> 
                    <!-- <td class="text-xs-left pa-2" :class="bg_proforma(props.item)" @click="select(props.item)" v-show="!is_sales">
                        {{ props.item.staff_name }}
                    </td>  -->
                    
                    <td class="text-xs-left pa-2" :class="bg_proforma(props.item)" @click="select(props.item)" v-show="!is_sales">
                        {{ props.item.sales_note }}
                    </td>
                    <td class="text-xs-right pa-2" :class="bg_proforma(props.item)" @click="select(props.item)" v-show="!is_sales">
                        <span class="caption grey--text">Rp</span> <b>{{ one_money(props.item.sales_grandtotal) }}</b>
                    </td>
                    <td class=" pa-2" :class="bg_proforma(props.item)" @click="select(props.item)" v-show="!is_sales">
                        <v-layout v-for="(p,m) in props.item.payments" :key="'pay'+m">
                            <v-flex xs6><a href="javascript:;" @click="editPay(props.item, p)">{{ p.pay_date }}</a></v-flex>
                            <v-flex xs6 class="text-xs-right"><a href="javascript:;" @click="editPay(props.item, p)"><span class="caption grey--text">Rp</span> <b>{{ one_money(p.pay_amount) }}</b></a></v-flex>
                        </v-layout>
                        
                    </td>
                    
                    <!-- <td class="text-xs-left pa-2" @click="select(props.item)" v-show="is_sales">
                        {{ props.item.sales_receipt }}
                    </td> 

                    <td class="text-xs-left pa-2" @click="select(props.item)">
                        {{ props.item.accounts.join(', ') }}
                    </td>                   
                    <td class="text-xs-right pa-2" @click="select(props.item)">Rp {{ one_money(props.item.sales_debit) }}</td>
                    <td class="text-xs-right pa-2" @click="select(props.item)">Rp {{ one_money(props.item.sales_credit) }}</td> -->
                    <td class="text-xs-center pa-0 pr-1" :class="bg_proforma(props.item)" @click="select(props.item)">
                        
                        <!-- <v-layout row wrap> -->
                            <!-- <v-flex xs6 pr-1> -->
                                <v-btn color="orange white--text" class="btn-icon ma-0" small  @click="pay(props.item)"><v-icon>attach_money</v-icon></v-btn>
                                <v-btn color="primary" class="btn-icon ma-0" small  @click="edit(props.item)"><v-icon>create</v-icon></v-btn>
                            <!-- </v-flex>
                            <v-flex xs6 pl-1> -->
                                <v-btn color="red" 
                            :dark="props.item.sales_done=='N'" 
                            :disabled="props.item.sales_done!='N'" 
                            class="btn-icon ma-0" small  @click="del(props.item)"><v-icon>delete</v-icon></v-btn>
                            <!-- </v-flex> -->
                        <!-- </v-layout> -->

                        <!-- <v-btn @click="proforma(props.item)" 
                            :disabled="props.item.sales_done!='N' || props.item.sales_proforma!='N'"
                            :dark="props.item.sales_done=='N' && props.item.sales_proforma=='N'"
                            v-show="props.item.sales_proforma!='Y'"
                            class="btn-icon ma-0 cyan mt-1" small block
                            title="Buat Proforma Invoice"><v-icon class="mr-1">money</v-icon>Proforma</v-btn> -->
                        
                        <!-- <v-btn @click="proforma_print(props.item)" 
                            v-show="props.item.sales_proforma=='Y'" dark
                            class="btn-icon ma-0 amber mt-1" small block
                            title="Cetak Proforma Invoice"><v-icon class="mr-1">print</v-icon>Proforma</v-btn> -->
                        
                        
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
        
        <common-dialog-delete :data="sales_id" @confirm_del="confirm_del" v-if="dialog_delete"></common-dialog-delete>
        <common-dialog-confirm :data="sales_id" @confirm="confirm_post" v-if="dialog_confirm" :text="text_post"></common-dialog-confirm>
        <payment></payment>
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

a { display: block; text-decoration: none; border-bottom: dashed 1px #226799 }
</style>

<script>
let ts = Math.round(Math.random() * 1e10)
module.exports = {
    components : {
        "common-dialog-delete" : httpVueLoader("../../common/components/common-dialog-delete.vue"),
        "common-dialog-confirm" : httpVueLoader("../../common/components/common-dialog-confirm.vue"),
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue?t='+ts),
        'payment' : httpVueLoader('./sales-order-payment.vue')
    },

    data () {
        return {
            headers: [
                {
                    text: "",
                    align: "center",
                    sortable: false,
                    width: "3%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "TANGGAL",
                    align: "left",
                    sortable: false,
                    width: "8%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "NOMOR",
                    align: "left",
                    sortable: false,
                    width: "8%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "CUSTOMER",
                    align: "left",
                    sortable: false,
                    width: "15%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "CATATAN",
                    align: "left",
                    sortable: false,
                    width: "28%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                
                {
                    text: "TOTAL TAGIHAN",
                    align: "right",
                    sortable: false,
                    width: "12%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "RIWAYAT BAYAR",
                    align: "left",
                    sortable: false,
                    width: "15%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "ACTION",
                    align: "center",
                    sortable: false,
                    width: "11%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                }
            ]
        }
    },

    computed : {
        saless () {
            return this.$store.state.sales.saless
        },

        dialog_delete () {
            return this.$store.state.dialog_delete
        },

        dialog_confirm () {
            return this.$store.state.dialog_confirm
        },

        sales_id () {
            return this.$store.state.sales.selected_sales.M_JournalID
        },

        query : {
            get () { return this.$store.state.sales.search },
            set (v) { this.$store.commit('sales/update_search', v) }
        },

        curr_page : {
            get () { return this.$store.state.sales.current_page },
            set (v) { this.$store.commit('sales/update_current_page', v) }
        },

        xtotal_page () {
            return this.$store.state.sales.total_sales_page
        },

        text_post () {
            let j = this.$store.state.sales.selected_sales
            return "Apakah anda yakin akan melakukan Posting Jurnal tersebut ?"
        },

        s_date : {
            get () { return this.$store.state.sales.s_date },
            set (v) { this.$store.commit('sales/set_common', ['s_date', v]) }
        },

        e_date : {
            get () { return this.$store.state.sales.e_date },
            set (v) { this.$store.commit('sales/set_common', ['e_date', v]) }
        },

        title() {
            return "PENJUALAN BARANG (SALES)"
        },

        is_sales() {
            if (this.$store.state.filter.indexOf("J.03")>-1)
                return true
            return false
        },

        custom_title () {
            return this.$store.state.custom_title?this.$store.state.custom_title:false
        },

        staffs () {
            return this.$store.state.sales_new.staffs
        },

        selected_staff : {
            get () { return this.$store.state.sales.selected_staff },
            set (v) { 
                this.$store.commit('sales/set_selected_staff', v) 
                this.search()
            }
        }
    },

    methods : {
        __c (a, b) { this.$store.commit('sales_new/set_object', [a, b]) },
        __cp (a, b) { this.$store.commit('payment/set_object', [a, b]) },

        one_money (x) {
            return window.one_money(x)
        },

        add () {
            let x = []
            x.push(JSON.parse(JSON.stringify(this.$store.state.sales_new.detail_default)))
            this.$store.commit('sales_new/set_common', ['edit', false])
            this.$store.commit('sales_new/set_common', ['proforma', false])
            this.$store.commit('sales_new/set_common', ['sales_date', this.$store.state.sales_new.current_date.split('-').reverse().join('-')])
            this.$store.commit('sales_new/set_common', ['sales_number', ''])
            this.$store.commit('sales_new/set_common', ['sales_disc', 0])
            this.$store.commit('sales_new/set_common', ['sales_discrp', 0])
            this.$store.commit('sales_new/set_common', ['sales_ref', ''])
            this.$store.commit('sales_new/set_common', ['sales_note', ''])
            this.$store.commit('sales_new/set_common', ['sales_memo', ''])
            this.$store.commit('sales_new/set_common', ['sales_customer_name', ''])
            this.$store.commit('sales_new/set_common', ['sales_receipt', ''])
            this.$store.commit('sales_new/set_common', ['sales_shipping', 0])
            this.$store.commit('sales_new/set_common', ['sales_dp', 0])
            this.$store.commit('sales_new/set_common', ['sales_proforma', 'N'])
            this.$store.commit('sales_new/set_selected_staff', null)
            this.$store.commit('sales_new/set_selected_customer', null)
            this.$store.commit('sales_new/set_selected_offer', null)
            this.$store.commit('sales_new/set_selected_address', null)
            this.$store.commit('sales_new/set_selected_paymentplan', null)
            this.$store.commit('sales_new/set_selected_term', null)
            this.$store.commit('sales_new/set_selected_expedition', null)
            this.$store.commit('sales_new/set_common', ['expedition_mode', 'E'])
            this.$store.commit('sales_new/set_common', ['expedition_name', ''])
            this.$store.commit('sales_new/set_details', x)
            this.$store.commit('sales_new/set_selected_affiliate', null)
            this.$store.commit('sales_new/set_common', ['affiliate_fee', 0])
            this.$store.commit('sales_new/set_common', ['dialog_new', true])
            
        },

        edit (x) {
            if (this.$store.state.sales_new.customers.length < 1) {
                this.$store.commit('set_dialog_progress', true)
                this.$store.dispatch('sales_new/search_customer').then(() => {
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
            this.$store.commit('sales_new/set_common', ['edit', true])
            this.$store.commit('sales_new/set_common', ['view', sc.sales_done == 'Y' ? true : false])
            this.$store.commit('sales_new/set_common', ['proforma', sc.proforma?sc.proforma:false])
            this.$store.commit('sales_new/set_common', ['sales_id', sc.sales_id])
            this.$store.commit('sales_new/set_common', ['sales_date', sc.sales_date])
            this.$store.commit('sales_new/set_common', ['sales_note', sc.sales_note])
            this.$store.commit('sales_new/set_common', ['sales_memo', sc.sales_memo])
            this.$store.commit('sales_new/set_common', ['sales_customer_name', sc.sales_customer_name])
            this.$store.commit('sales_new/set_common', ['sales_number', sc.sales_number])
            this.$store.commit('sales_new/set_common', ['sales_disc', sc.sales_disc])
            this.$store.commit('sales_new/set_common', ['sales_discrp', sc.sales_discrp])
            this.$store.commit('sales_new/set_common', ['sales_ref', sc.sales_ref])
            this.$store.commit('sales_new/set_common', ['sales_shipping', sc.sales_shipping])
            this.$store.commit('sales_new/set_common', ['sales_dp', sc.sales_dp])
            this.$store.commit('sales_new/set_common', ['sales_proforma', sc.sales_proforma])
            this.$store.commit('sales_new/set_common', ['sales_ppn', sc.sales_ppn])
            this.$store.commit('sales_new/set_common', ['proforma_number', sc.proforma_number])
            this.$store.commit('sales_new/set_common', ['proforma_duedate', sc.proforma_duedate])

            this.$store.commit('sales_new/set_common', ['sales_disctype', 
                Math.round(sc.sales_discrp)>0?'R':'P'])

            this.$store.commit('sales_new/set_selected_customer', null)
            for (let v of this.$store.state.sales_new.customers)
                if (v.customer_id == sc.customer_id)
                    this.$store.commit('sales_new/set_selected_customer', v)

            this.$store.commit('sales_new/set_selected_staff', null)
            for (let v of this.$store.state.sales_new.staffs)
                if (sc.sales_staff == v.staff_id)
                    this.$store.commit('sales_new/set_selected_staff', v)

            this.$store.commit('sales_new/set_selected_offer', 
                {sales_id:x.offer_id,sales_number:x.offer_number,sales_date:x.offer_date})
            this.$store.commit('sales_new/set_common', ['edits', x.offer_id])
            this.$store.dispatch('sales_new/search_offer')

            this.$store.commit('sales_new/set_selected_address', null)
            this.$store.dispatch('sales_new/search_address')

            this.$store.commit('sales_new/set_selected_paymentplan', null)
            for (let v of this.$store.state.sales_new.paymentplans)
                if (sc.payment_id == v.paymentplan_id)
                    this.$store.commit('sales_new/set_selected_paymentplan', v)

            this.$store.commit('sales_new/set_selected_term', null)
            for (let v of this.$store.state.sales_new.terms)
                if (sc.term_id == v.term_id)
                    this.$store.commit('sales_new/set_selected_term', v)

            this.$store.commit('sales_new/set_selected_expedition', null)
            for (let v of this.$store.state.sales_new.expeditions)
                if (sc.expedition_id == v.expedition_id)
                    this.$store.commit('sales_new/set_selected_expedition', v)
            this.$store.commit('sales_new/set_common', ['expedition_mode', 'E'])
            this.$store.commit('sales_new/set_common', ['expedition_name', ''])

            this.$store.commit('sales_new/set_selected_affiliate', null)
            this.$store.commit('sales_new/set_common', ['affiliate_fee', sc.affiliate_fee])

            if (sc.affiliate_id != 0) {
                for (let a of this.$store.state.sales_new.affiliates)
                    if (a.affiliate_id == sc.affiliate_id)
                        this.$store.commit('sales_new/set_selected_affiliate', a)
            }
            
            let details = sc.details
            // let acc = this.$store.state.sales_new.accounts
            // for (let d of details)
            //     for (let a of acc)
            //         if (a.account_id == d.account)
            //             d.account = a
            // console.log(details)
            this.$store.commit('sales_new/set_details', details)

            this.$store.commit('sales_new/set_common', ['dialog_new', true])
        },

        del (x) {
            this.select(x)
            this.$store.commit('set_dialog_delete', true)
        },

        confirm_del (x) {
            this.$store.dispatch('sales/del', {id:x.data})
        },

        post (x) {
            this.select(x)
            this.$store.commit('set_dialog_confirm', true)
        },

        confirm_post (x) {
            this.$store.dispatch('sales/post', {id:x.data})
        },

        select (x) {
            this.$store.commit('sales/set_selected_sales', x)
        },

        search () {
            return this.$store.dispatch('sales/search', {})
        },

        change_page(x) {
            this.curr_page = x
            this.$store.dispatch('sales/search', {})
        },

        change_s_date(x) {
            this.$store.commit('sales/set_common', ['s_date', x.new_date])
            this.$store.commit('sales/set_common', ['current_page', 1])
            this.$store.dispatch('sales/search')
        },

        change_e_date(x) {
            this.$store.commit('sales/set_common', ['e_date', x.new_date])
            this.$store.commit('sales/set_common', ['current_page', 1])
            this.$store.dispatch('sales/search')
        },

        proforma (z) {
            let x = []
            this.select(z)
            let sc = JSON.parse(JSON.stringify(z))
            sc.sales_proforma = 'Y'
            sc.proforma = true

            this.edit(sc)
            return

            x.push(JSON.parse(JSON.stringify(this.$store.state.invoice_new.detail_default)))
            this.$store.commit('invoice_new/set_common', ['edit', false])
            this.$store.commit('invoice_new/set_common', ['proforma', true])
            this.$store.commit('invoice_new/set_common', ['invoice_date', this.$store.state.invoice_new.current_date])
            this.$store.commit('invoice_new/set_common', ['invoice_due_date', this.$store.state.invoice_new.current_date])
            this.$store.commit('invoice_new/set_common', ['invoice_note', ''])
            this.$store.commit('invoice_new/set_common', ['invoice_number', ''])
            this.$store.commit('invoice_new/set_common', ['invoice_disc', 0])
            this.$store.commit('invoice_new/set_common', ['invoice_discrp', 0])
            this.$store.commit('invoice_new/set_common', ['invoice_disctype', 'P'])
            this.$store.commit('invoice_new/set_common', ['invoice_shipping', 0])
            this.$store.commit('invoice_new/set_common', ['invoice_proforma', 'Y'])
            this.$store.commit('invoice_new/set_invoice_dps', [])
            this.$store.commit('invoice_new/set_selected_customer', null)
            // this.$store.commit('invoice_new/set_selected_warehouse', null)
            this.$store.commit('invoice_new/set_selected_term', null)
            this.$store.commit('invoice_new/set_items', [])
            this.$store.commit('invoice_new/set_details', x)
            
            for (let v of this.$store.state.invoice_new.customers)
                if (v.customer_id == sc.customer_id)
                    this.$store.commit('invoice_new/set_selected_customer', v)

            for (let v of this.$store.state.invoice_new.terms)
                if (v.term_id == sc.term_id)
                    this.$store.commit('invoice_new/set_selected_term', v)

            this.$store.dispatch('invoice_new/search_item')
            
            this.$store.commit('invoice_new/set_common', ['dialog_proforma', true])
        },

        proforma_print (z) {
            let URL = this.$store.state.invoice.URL
            this.$store.commit('set_report_url', URL+"report/one_sales_002?proforma=Y&id="+z.sales_id)
            this.$store.commit('set_dialog_print', true)
        },

        bg_proforma (x) {
            if (x.sales_proforma=='Y')
                return 'amber lighten-4'
            return 'white'
        },

        add_exp () {
            return
        },

        pay (x) {
            this.select(x)

            this.__cp("payment_total", x.sales_grandtotal)
            this.__cp("payment_paid", x.sales_paid)
            this.__cp("payment_unpaid", x.sales_unpaid)
            this.__cp("payment_amount", 0)
            this.__cp("payment_note", '')
            this.__cp("selected_payment_type", null)
            this.__cp("payment_id", 0)
            this.__cp("edit", false)
            this.$store.commit("payment/set_object", ["dialog", true])
        },

        editPay (x, y) {
            this.select(x)

            this.__cp("payment_total", x.sales_grandtotal)
            this.__cp("payment_paid", x.sales_paid)
            this.__cp("payment_unpaid", x.sales_unpaid)
            this.__cp("payment_amount", y.pay_amount)
            this.__cp("payment_date", y.pay_date)
            this.__cp("payment_note", y.pay_note)
            this.__cp("payment_id", y.pay_id)
            this.__cp("selected_payment", y)
            this.__cp("edit", true)
            let pt = null
            for (let ptx of this.$store.state.payment.payment_types) {
                if (ptx.paymenttype_id == y.pay_type) pt = ptx
            }
            this.__cp("selected_payment_type", pt)

            this.$store.commit("payment/set_object", ["dialog", true])
        }
    },

    mounted () {
        if (this.is_sales)
            this.headers[2].text="NOMOR INVOICE"

        let get = window.location.search.substring(1)
        let qs = window.parse_query_string(get)
        if (get != "") {
            if (qs.sdate) this.s_date = qs.sdate
            if (qs.edate) this.e_date = qs.edate
            if (qs.no) {
                this.$store.commit('sales/set_common', ['search', qs.no])
                this.$store.commit('sales/set_common', ['to_edit', true])
                this.$store.commit('sales_new/set_common', ['view', true])

                this.$store.dispatch('sales/search', {}).then((r) => {
                    this.edit(r.records[0])
                })
            }        
        } else {
            this.$store.dispatch('sales/search', {})
        }

        
        
        // this.$store.dispatch('sales/search', {}).then((response) => {
        //     console.log(response)
        // })
//         var url_string = "http://www.example.com/t.html?a=1&b=3&c=m2-m3-m4-m5"; 
// var url = new URL(url_string);
// var c = url.searchParams.get("c")
    }
}
</script>