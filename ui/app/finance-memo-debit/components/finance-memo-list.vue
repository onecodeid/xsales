<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0">
            <v-layout row wrap>
                <v-flex xs6>
                    <h3 class="display-1 font-weight-light zalfa-text-title main-title">
                        <span @click="backToAccountList($event)">
                            DAFTAR MEMO DEBIT</span>
                    </h3>
                </v-flex>
                <v-flex xs2 pr-2 class="text-xs-right">
                    <v-btn
                    color="success"
                    class="ma-0 ml-2 btn-icon"
                    dark
                    @click="add"
                    >
                        <v-icon>add</v-icon>
                    </v-btn>
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

                            <!-- <v-btn color="success" class="ma-0 ml-2 btn-icon" ref="anu">
                                <a href="../receive" @click="add($event)" class="white--text" >
                                <v-icon>add</v-icon>
                                </a>
                            </v-btn>   -->

                            
                                
                        </template>
                    </v-text-field>

                    
                </v-flex>
            </v-layout>
        </v-card-title>
        <v-card-text class="pt-2">
            <v-data-table 
                :headers="headers"
                :items="memos"
                :loading="false"
                hide-actions
                class="elevation-1">
                <template slot="items" slot-scope="props">
                    <td class="text-xs-left pa-1" @click="select(props.item)" style="writing-mode:tb-rl;height:auto;transform:scale(-1)">
                        <span class="cyan pa-2 py-3 d-block white--text" v-if="props.item.bill_id!=0">RETUR</span>
                        <span class="orange pa-2 py-3 d-block white--text" v-if="props.item.bill_id==0">MEMO</span>
                        <!-- <span class="orange white--text px-2 py-3 d-block" v-if="props.item.memo_type_code=='CASH.RECEIVE'">TERIMA</span>
                        <span class="success pa-2 py-3 d-block white--text" v-if="props.item.memo_type_code=='CASH.PAY'">BAYAR</span>
                        <span class="grey white--text pa-2 py-3 d-block" v-if="props.item.memo_type_code=='CASH.TRANSFER'">TRANSFER</span> -->
                    </td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.memo_date }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.memo_number }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">
                        <div class="cyan--text">{{ props.item.vendor_name }}</div>
                        <div class="caption" v-show="bill_id!=0">{{ props.item.bill_number }}</div>
                        <div class="caption" v-show="bill_id==0">{{ props.item.account_name }}</div>
                    </td> 
                    <td class="text-xs-left pa-2" @click="select(props.item)">
                        <v-layout row wrap>
                            <v-flex xs12>{{ props.item.memo_note }}</v-flex>
                        </v-layout>
                    </td> 
                    <td class="text-xs-right pa-2" @click="select(props.item)">
                        <span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.memo_amount)) }}</b>
                    </td>
                    <td class="text-xs-right pa-2" @click="select(props.item)">
                        <span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.memo_used-(0-props.item.memo_refunded))) }}</b>
                    </td>
                    <td class="text-xs-center pa-0" @click="select(props.item)">
                        <v-btn color="cyan" class="btn-icon ma-0" small title="Jurnal transaksi" dark>
                            <a :href="'../refund/0-'+props.item.memo_id" 
                                @click="refund(props.item, $event)" class="white--text">
                                <v-icon>refresh</v-icon>
                            </a>
                        </v-btn>
                        <v-btn color="primary" class="btn-icon ma-0" small title="Ubah Memo" :disabled="props.item.bill_id!=0||props.item.memu_used>0">
                            <a :href="edit_href(props.item)" 
                                @click="edit(props.item, $event)" class="white--text">
                                <v-icon>create</v-icon>
                            </a>
                        </v-btn>
                        <v-btn color="red" class="btn-icon ma-0" small title="Hapus transaksi" @click="del(props.item)" 
                            :dark="props.item.bill_id==0&&(props.item.memo_used==0&&props.item.memo_refunded==0)" 
                            :disabled="props.item.bill_id!=0||props.item.memo_used>0||props.item.memo_refunded>0">
                            <v-icon>delete</v-icon>
                        </v-btn>
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
        
        <common-dialog-delete :data="memo_id" @confirm_del="confirm_del" v-if="dialog_delete"></common-dialog-delete>
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

.v-menu__content {
    min-width: 114px !important;
}

.main-title span {
    cursor: pointer;
    font-size: .8em;
}
</style>

<style>
.v-menu__content .v-list__tile__title a {
    text-decoration: none;
}
</style>

<script>
var t = Math.ceil(Math.random() * 1e10)

module.exports = {
    components : {
        "common-dialog-delete" : httpVueLoader("../../common/components/common-dialog-delete.vue"),
        "common-dialog-confirm" : httpVueLoader("../../common/components/common-dialog-confirm.vue"),
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        "common-dialog-print" : httpVueLoader("../../common/components/common-dialog-print-size.vue"),
        "trans-journal-view" : httpVueLoader("../../trans-journal/components/trans-journal-new.vue?t="+t)
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
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "VENDOR / INVOICE",
                    align: "left",
                    sortable: false,
                    width: "12%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "DESKRIPSI",
                    align: "left",
                    sortable: false,
                    width: "25%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                
                // {
                //     text: "ALASAN RETUR",
                //     align: "left",
                //     sortable: false,
                //     width: "20%",
                //     class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                // },
                {
                    text: "JUMLAH",
                    align: "right",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "TERPAKAI",
                    align: "right",
                    sortable: false,
                    width: "10%",
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

            report_url: '',
            add_menus: [
                { title: 'Penerimaan', action: this.add_receive, url: '../receive/acc-' },
                { title: 'Pengiriman', action: this.add_pay, url: '../pay/acc-' },
                { title: 'Transfer', action: this.add_transfer, url: '../transfer/acc-' }
            ]
        }
    },

    computed : {
        memos () {
            return this.$store.state.memo.memos
        },

        dialog_delete () {
            return this.$store.state.dialog_delete
        },

        // dialog_confirm () {
        //     return this.$store.state.dialog_confirm
        // },

        bill_id () {
            return this.$store.state.memoNew.bill_id
        },

        memo_id () {
            return this.$store.state.memo.selected_memo.memo_id
        },

        query : {
            get () { return this.$store.state.memo.search },
            set (v) { this.setObject('search', v) }
        },

        curr_page : {
            get () { return this.$store.state.memo.current_page },
            set (v) { this.setObject('current_page', v) }
        },

        xtotal_page () {
            return this.$store.state.memo.total_memo_page
        },

        s_date : {
            get () { return this.$store.state.memo.s_date },
            set (v) { this.$store.commit('memo/set_common', ['s_date', v]) }
        },

        e_date : {
            get () { return this.$store.state.memo.e_date },
            set (v) { this.$store.commit('memo/set_common', ['e_date', v]) }
        },

        title() {
            return (this.selected_account?this.selected_account.account_name:'')
        },

        selected_account () {
            return this.$store.state.memo.selected_account
        }

        // dialog_report : {
        //     get () { return this.$store.state.dialog_print },
        //     set (v) { this.$store.commit('set_dialog_print', v) }
        // }
    },

    methods : {
        setObject(x, y) {
            this.$store.commit('memo/set_object', [x, y])
        },

        setNewObject(x, y) {
            this.$store.commit('memoNew/set_object', [x, y])
        },

        setRefundObject(x, y) {
            this.$store.commit('memoRefund/set_object', [x, y])
        },

        setObjects(x, y) {
            for (let v of y)
                this.setObject(v, x[v])
        },

        setNewObjects(x, y) {
            for (let v of y)
                this.setNewObject(v, x[v])
        },

        one_money (x) {
            return window.one_money(x)
        },

        add_receive(e) {
            e.preventDefault()
            this.setNewObject("memo_type_code", "CASH.RECEIVE")
            this.setNewObject("selected_account_to", this.selected_account)
            this.setNewObject("selected_account", null)
            this.add(e)
            
        },

        add_pay(e) {
            e.preventDefault()
            this.setNewObject("memo_type_code", "CASH.PAY")
            this.setNewObject("selected_account", this.selected_account)
            this.setNewObject("selected_account_to", null)
            this.add(e)
        },

        add_transfer(e) {
            e.preventDefault()
            this.setNewObject("memo_type_code", "CASH.TRANSFER")
            this.setNewObject("selected_account", this.selected_account)
            this.setNewObject("selected_account_to", null)
            this.add(e)
        },

        add (e) {
            // this.setNewObject("selected_account_to", x)
            e.preventDefault()
            this.setNewObject("edit", false)

            this.setNewObject("dialog_new", true)
            this.setNewObject("memo_date", this.$store.state.memo.current_date)
            this.$store.commit("tag/set_object", ["selected_tagnames", []])
            for (let c of ["memo_id", "memo_amount", "memo_disc", "memo_discrp", "bill_id"]) { this.setNewObject(c, 0) }
            for (let c of ["memo_memo", "memo_from", "memo_note", "memo_img", "memo_number"]) { this.setNewObject(c, "") }
            for (let c of ["selected_tax", "selected_account", "selected_customer"]) { this.setNewObject(c, null) }
            
            // window.location = '../receive/'
            // let x = []
            // x.push(JSON.parse(JSON.stringify(this.$store.state.memo_new.detail_default)))
            // this.$store.commit('memo_new/set_common', ['edit', false])
            // this.$store.commit('memo_new/set_common', ['memo_date', this.$store.state.memo_new.current_date])
            // this.$store.commit('memo_new/set_common', ['memo_due_date', this.$store.state.memo_new.current_date])
            // this.$store.commit('memo_new/set_common', ['memo_note', ''])
            // this.$store.commit('memo_new/set_common', ['memo_number', ''])
            // this.$store.commit('memo_new/set_common', ['memo_disc', 0])
            // this.$store.commit('memo_new/set_common', ['memo_discrp', 0])
            // this.$store.commit('memo_new/set_common', ['memo_disctype', 'P'])
            // this.$store.commit('memo_new/set_common', ['memo_shipping', 0])
            // this.$store.commit('memo_new/set_common', ['memo_memo', ''])
            // this.$store.commit('memo_new/set_common', ['memo_address', ''])
            // this.$store.commit('memo_new/set_memo_dps', [])
            // this.$store.commit('memo_new/set_selected_customer', null)
            // // this.$store.commit('memo_new/set_selected_warehouse', null)
            // this.$store.commit('memo_new/set_selected_term', null)
            // this.$store.commit('memo_new/set_items', [])
            // this.$store.commit('memo_new/set_details', x)
            // this.$store.commit('memo_new/set_common', ['dialog_delivery', true])
            return
        },

        edit_href (x) {
            return ''
            // return '../' + x.memo_type_code.replace(/(CASH\.)/, '').toLowerCase() + '/?id=' + x.memo_id
        },

        edit (x, e) {
            e.preventDefault()
            // this.setNewObject("selected_memo")

            this.setNewObject("edit", true)
            this.setNewObject("dialog_new", true)
            this.setNewObjects(x, [
                "memo_id",
                "memo_date",
                "memo_note",
                // "memo_date",
                // "memo_from",
                "memo_number",
                "memo_amount",
                "memo_used",
                "memo_refunded",
                "bill_number",
                "bill_id"
                // "memo_disc",
                // "memo_discrp",
                // "memo_img",
                // "memo_type_code",
                // "memo_type_name"
            ])

            this.setNewObject("selected_vendor", {vendor_name:x.vendor_name})

            this.setNewObject("selected_account", null)
            for (let a of this.$store.state.memoNew.accounts) {
                if (a.account_id == x.memo_account)
                    this.setNewObject("selected_account", a)
            }

            this.$store.commit("tag/set_object", ["selected_tagnames", x.memo_tags])

            // this.$store.commit("tag/set_object", ['selected_tagnames', JSON.parse(x.memo_tags)])
            // if (x.tax_id != 0) {
            //     this.setNewObject("selected_tax", {tax_id:x.tax_id,tax_name:x.tax_name,tax_amount:x.tax_amount})
            // }
            // this.setNewObject("selected_account", {account_id:x.from_account_id,account_name:x.from_account_name})
            // this.setNewObject("selected_account_to", {account_id:x.to_account_id,account_name:x.to_account_name})
            // window.location.replace("../new/?id="+x.memo_id)
            // if (this.$store.state.memo_new.customers.length < 1) {
            //     this.$store.commit('set_dialog_progress', true)
            //     this.$store.dispatch('memo_new/search_customer').then(() => {
            //         this.$store.commit('set_dialog_progress', false)
            //         this.do_edit(x)
            //     })
            // } else {
            //     this.do_edit(x)
            // }
            return
        },

        do_edit (x) {
            this.select(x)
            let sc = x
            this.$store.commit('memo_new/set_common', ['edit', true])
            this.$store.commit('memo_new/set_common', ['memo_id', sc.memo_id])
            this.$store.commit('memo_new/set_common', ['memo_date', sc.memo_date])
            // this.$store.commit('memo_new/set_common', ['memo_due_date', 
            //     moment(sc.memo_date, "YYYY-MM-DD").add(Math.round(sc.memo_term), 'days').format('DD-MM-YYYY')])
            this.$store.commit('memo_new/set_common', ['memo_due_date', sc.memo_due_date.split('-').reverse().join('-')])
            this.$store.commit('memo_new/set_common', ['memo_note', sc.memo_note])
            this.$store.commit('memo_new/set_common', ['memo_memo', sc.memo_memo])
            this.$store.commit('memo_new/set_common', ['memo_number', sc.memo_number])
            this.$store.commit('memo_new/set_common', ['memo_disc', sc.memo_disc])
            this.$store.commit('memo_new/set_common', ['memo_discrp', sc.memo_discrp])
            this.$store.commit('memo_new/set_common', ['memo_dp', sc.memo_dp])
            this.$store.commit('memo_new/set_common', ['memo_shipping', sc.memo_shipping])
            this.$store.commit('memo_new/set_common', ['memo_proforma', sc.memo_proforma])
            this.$store.commit('memo_new/set_common', ['sales_name', sc.sales.staff_short])
            this.$store.commit('memo_new/set_memo_dps', sc.memo_dps) 

            this.$store.commit('memo_new/set_common', ['memo_disctype', 
                Math.round(sc.memo_discrp)>0?'R':'P'])

            this.$store.commit('memo_new/set_selected_customer', null)
            // this.$store.commit('memo_new/set_selected_warehouse', null)
            for (let v of this.$store.state.memo_new.customers)
                if (v.customer_id == sc.customer_id)
                    this.$store.commit('memo_new/set_selected_customer', v)
            
            for (let v of this.$store.state.memo_new.terms)
                if (v.term_id == sc.memo_term)
                    this.$store.commit('memo_new/set_selected_term', v)

            let details = sc.details
            let dfl = JSON.parse(JSON.stringify(this.$store.state.memo_new.detail_default))
            this.$store.dispatch('memo_new/search_dp')

            if (sc.memo_proforma == 'Y') {
                let dx = []
                for (let d in details) {
                    dx.push(dfl)
                    dx[d].delivery = details[d].sales
                    dx[d].items = details[d].items
                }
                this.$store.commit('memo_new/set_details', dx)
                // this.$store.dispatch('memo_new/search_item')
                this.$store.commit('memo_new/set_common', ['dialog_proforma', true])
            }
            else {
                
                // let acc = this.$store.state.memo_new.accounts
                for (let d of details)
                    d.delivery.items = d.items
                //     for (let a of acc)
                //         if (a.account_id == d.account)
                //             d.account = a

                this.$store.commit('memo_new/set_details', details)
                this.$store.commit('memo_new/set_common', ['dialog_new', true])
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
            this.$store.commit('memo_new/set_common', ['memo_address', address])
            
        },

        del (x) {
            this.select(x)
            this.$store.commit('set_dialog_delete', true)
        },

        confirm_del (x) {
            this.$store.dispatch('memo/del', {id:x.data})
        },

        post (x) {
            this.select(x)
            this.$store.commit('set_dialog_confirm', true)
        },

        confirm_post (x) {
            this.$store.dispatch('memo/post', {id:x.data})
        },

        select (x) {
            this.setObject('selected_memo', x)
        },

        search () {
            return this.$store.dispatch('memo/search', {})
        },

        change_page(x) {
            this.curr_page = x
            this.$store.dispatch('memo/search', {})
        },

        change_s_date(x) {
            this.$store.commit('memo/set_common', ['s_date', x.new_date])
            this.$store.dispatch('memo/search')
        },

        change_e_date(x) {
            this.$store.commit('memo/set_common', ['e_date', x.new_date])
            this.$store.dispatch('memo/search')
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
            //     for (let a of this.$store.state.memo_delivery.accounts) {
            //         if (a.account_id == d.account)
            //             d.account = a
            //     }
            //     details.push(d)
            // }
            this.$store.commit('journal_new/set_details', x.accounts)
        },

        print_memo (x) {
            this.select(x)
            let so = x
            this.report_url = this.$store.state.memo.URL+"report/one_sales_002?id="+so.memo_id
            this.$store.commit('set_dialog_print', true)
        },

        bg_proforma (x) {
            if (x.memo_proforma=='Y')
                return 'amber lighten-4'
            return 'white'
        },

        proforma_edit (z) {

            let sc = z
            this.$store.commit('memo_new/set_common', ['memo_proforma', 'Y'])
            this.$store.dispatch('memo_new/search_item')
            
            this.$store.commit('memo_new/set_common', ['dialog_proforma', true])
        },

        show_add_menu (e) {
            e.preventDefault()
            this.showAddMenu = false
            this.x = e.clientX - 100
            this.y = e.clientY
            this.$nextTick(() => {
                this.showAddMenu = true
            })
        },

        backToAccountList () {
            let tmp_acc_id = this.$store.state.memo.tmp_acc_id
            if (tmp_acc_id == 0)
                this.setObject("selected_tab", "TAB.01")
            else
                window.location.replace("../list")
        },

        refund(x, e) {
            e.preventDefault()
            this.setRefundObject("refund_id", 0)
            this.setRefundObject("edit", false)
            this.setRefundObject("memo_id", x.memo_id)
            this.setRefundObject("memo_number", x.memo_number)
            this.setRefundObject("memo_amount", x.memo_amount)
            this.setRefundObject("memo_used", x.memo_used)
            this.setRefundObject("memo_refunded", x.memo_refunded)
            this.setRefundObject("refund_note", "")
            this.setRefundObject("refund_amount", 0)
            this.setRefundObject("selected_customer", x.customer)
            this.setRefundObject("selected_account", null)
            this.$store.commit("memoRefund/set_object", ["dialog_new", true])
        }
    },

    mounted () {
        this.$store.dispatch('memo/search').then(res => {
            this.$store.dispatch("memo/search_account_cash")
        })
    }
}
</script>