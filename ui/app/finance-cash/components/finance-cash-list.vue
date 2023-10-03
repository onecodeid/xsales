<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0">
            <v-layout row wrap>
                <v-flex xs6>
                    <h3 class="display-1 font-weight-light zalfa-text-title main-title">
                        <span @click="backToAccountList($event)">
                            <u>DAFTAR AKUN »</u></span> <b class="blue--text">{{ title }}</b>
                    </h3>
                </v-flex>
                <v-flex xs2 pr-2 class="text-xs-right" v-show="!importx">
                    <v-menu offset-y>
                        <template v-slot:activator="{ on }">
                            <v-btn
                            color="success"
                            class="ma-0 ml-2 btn-icon"
                            dark
                            v-on="on"
                            :disabled="!!view"
                            >
                            <v-icon>add</v-icon> <span class="mr-1">Transaksi</span>
                            </v-btn>
                        </template>
                        <v-list>
                            <v-list-tile
                            v-for="(item, index) in add_menus"
                            :key="index"
                           
                            >
                            <v-list-tile-title><a :href="item.url+(selected_account?selected_account.account_id:0)"  @click="item.action($event)" class="black--text">{{ item.title }}</a></v-list-tile-title>
                            </v-list-tile>
                        </v-list>
                    </v-menu>
                </v-flex>
                <v-flex xs2 pr-2 class="text-xs-right" v-show="!!importx">
                    <v-chip close v-show="md5!=''" @input="importHistory">{{ md5.substr(0, 12) }}...</v-chip>
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
                            
                            
                            <v-btn color="red" class="ma-0 btn-icon" @click="search">
                                <v-icon>search</v-icon>
                            </v-btn>       
                        </template>
                    </v-text-field>

                    
                </v-flex>
            </v-layout>
        </v-card-title>
        <v-card-text class="pt-2">
            <v-data-table 
                :headers="headers"
                :items="cashes"
                :loading="false"
                hide-actions
                class="elevation-1">
                <template slot="items" slot-scope="props">
                    <td class="text-xs-left pa-1" @click="select(props.item)" style="writing-mode:tb-rl;height:auto;transform:scale(-1)">
                        <span class="orange white--text px-2 py-3 d-block" v-if="props.item.cash_type_code=='CASH.RECEIVE'">TERIMA</span>
                        <span class="success pa-2 py-3 d-block white--text" v-if="props.item.cash_type_code=='CASH.PAY'">BAYAR</span>
                        <span class="grey white--text pa-2 py-3 d-block" v-if="props.item.cash_type_code=='CASH.TRANSFER'">TRANSFER</span>
                        <!-- <span class="grey white--text pa-2 py-3 d-block">RETUR</span> -->
                    </td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.cash_date }}
                        <div v-show="!!importx">[<a :href="'./'+props.item.cash_md5" class="caption" :title="'Lihat history '+props.item.cash_md5">{{ props.item.cash_md5.substr(0, 8) }}...</a>]</div>
                    </td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.cash_number }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">
                        <v-layout row wrap>
                            <v-flex xs12>{{props.item.from_account_name}}</v-flex>
                            <v-flex xs12>{{props.item.to_account_name}}</v-flex>
                        </v-layout>
                    </td> 
                    
                    <td class="text-xs-left pa-2" @click="select(props.item)">
                        {{ props.item.cash_from }} 
                        <!-- <span v-show="props.item.tax_name!=''">{{ props.item.tax_name }} ({{ props.item.tax_amount }}%)</span> -->
                        <!-- <br /> -->
                        <!-- <span class="cyan--text" v-show="props.item.cash_memo!=''"><i>memo : {{ props.item.cash_memo}}</i></span> -->
                    </td> 

                    <td class="text-xs-left pa-2" @click="select(props.item)">
                        <v-layout row wrap>
                            <v-flex xs12>{{ props.item.cash_note }}</v-flex><v-flex xs12 v-show="props.item.cash_memo != ''" class="blue--text"><i>memo : {{props.item.cash_memo}}</i></v-flex>
                        </v-layout>
                    </td> 

                    <td class="text-xs-right pa-2" @click="select(props.item)">
                        <span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.cash_total)) }}</b>
                    </td>
                    <!-- <td class="text-xs-left pa-2" @click="select(props.item)" v-show="!is_sales">
                        <v-btn color="success" small class="ma-0" block v-show="props.item.cash_lunas=='Y'">Lunas</v-btn>
                        <v-btn color="orange" small class="ma-0" block dark v-show="props.item.cash_lunas=='N'&&props.item.cash_paid>0">Bayar Sebagian</v-btn>
                        <v-btn color="grey" small class="ma-0" block dark v-show="props.item.cash_lunas=='N'&&props.item.cash_paid==0">Belum Dibayar</v-btn>
                    </td> -->
                    <td class="text-xs-center pa-0" @click="select(props.item)">
                        
                        <v-btn color="cyan" class="btn-icon ma-0" small title="Jurnal transaksi" @click="journal(props.item)" dark>
                            <v-icon>list</v-icon>
                        </v-btn>

                        <v-btn color="primary" class="btn-icon ma-0" small title="Ubah cash" :disabled="!!view">
                            <a :href="edit_href(props.item)" 
                                @click="edit(props.item, $event)" class="white--text">
                                <v-icon>create</v-icon>
                            </a>
                        </v-btn>

                        <v-btn color="red" class="btn-icon ma-0" small title="Hapus transaksi" @click="del(props.item)" dark :disabled="!!view" :dark="!view">
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
        
        

        <common-dialog-delete :data="cash_id" @confirm_del="confirm_del" v-if="dialog_delete"></common-dialog-delete>
        <common-dialog-progress></common-dialog-progress>
        <!-- <common-dialog-confirm :data="cash_id" @confirm="confirm_post" v-if="dialog_confirm" :text="text_post"></common-dialog-confirm>
        <common-dialog-print :report_url="report_url" v-if="dialog_report"></common-dialog-print> -->
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
        "common-dialog-progress" : httpVueLoader("../../common/components/common-dialog-progress.vue?t="+t),
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
                    text: "AKUN / PERKIRAAN",
                    align: "left",
                    sortable: false,
                    width: "12%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "DARI / KEPADA",
                    align: "left",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "DESKRIPSI / MEMO",
                    align: "left",
                    sortable: false,
                    width: "32%",
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
                    text: "TOTAL",
                    align: "right",
                    sortable: false,
                    width: "15%",
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
        cashes () {
            return this.$store.state.cash.cashes
        },

        dialog_delete () {
            return this.$store.state.dialog_delete
        },

        view () {
            return this.$store.state.cash.view
        },

        // dialog_confirm () {
        //     return this.$store.state.dialog_confirm
        // },

        cash_id () {
            return this.$store.state.cash.selected_cash.cash_id
        },

        query : {
            get () { return this.$store.state.cash.search },
            set (v) { this.setObject('search', v) }
        },

        curr_page : {
            get () { return this.$store.state.cash.current_page },
            set (v) { this.setObject('current_page', v) }
        },

        xtotal_page () {
            return this.$store.state.cash.total_cash_page
        },

        s_date : {
            get () { return this.$store.state.cash.s_date },
            set (v) { this.$store.commit('cash/set_common', ['s_date', v]) }
        },

        e_date : {
            get () { return this.$store.state.cash.e_date },
            set (v) { this.$store.commit('cash/set_common', ['e_date', v]) }
        },

        title() {
            if (!!this.importx) return "History Import"
            return (this.selected_account?this.selected_account.account_name:'')
        },

        selected_account () {
            return this.$store.state.cash.selected_account
        },

        importx () {
            return this.$store.state.cash.import
        },

        md5 () {
            return this.$store.state.cash.search_md5
        }

        // dialog_report : {
        //     get () { return this.$store.state.dialog_print },
        //     set (v) { this.$store.commit('set_dialog_print', v) }
        // }
    },

    methods : {
        setObject(x, y) {
            this.$store.commit('cash/set_object', [x, y])
        },

        setNewObject(x, y) {
            this.$store.commit('cashNew/set_object', [x, y])
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
            this.setNewObject("cash_type_code", "CASH.RECEIVE")
            this.setNewObject("selected_account_to", this.selected_account)
            this.setNewObject("selected_account", null)
            this.add(e)
            
        },

        add_pay(e) {
            e.preventDefault()
            this.setNewObject("cash_type_code", "CASH.PAY")
            this.setNewObject("selected_account", this.selected_account)
            this.setNewObject("selected_account_to", null)
            this.add(e)
        },

        add_transfer(e) {
            e.preventDefault()
            this.setNewObject("cash_type_code", "CASH.TRANSFER")
            this.setNewObject("selected_account", this.selected_account)
            this.setNewObject("selected_account_to", null)
            this.add(e)
        },

        add (e) {
            // this.setNewObject("selected_account_to", x)
            e.preventDefault()
            this.setNewObject("edit", false)

            this.setNewObject("dialog_new", true)
            this.setNewObject("cash_date", this.$store.state.cash.current_date)
            this.$store.commit("tag/set_object", ["selected_tagnames", []])
            for (let c of ["cash_id", "cash_amount", "cash_disc", "cash_discrp"]) { this.setNewObject(c, 0) }
            for (let c of ["cash_memo", "cash_from", "cash_note", "cash_img", "cash_number"]) { this.setNewObject(c, "") }
            for (let c of ["selected_tax"]) { this.setNewObject(c, null) }

            this.$store.commit("cashNew/resetDetail")
            
            // window.location = '../receive/'
            // let x = []
            // x.push(JSON.parse(JSON.stringify(this.$store.state.cash_new.detail_default)))
            // this.$store.commit('cash_new/set_common', ['edit', false])
            // this.$store.commit('cash_new/set_common', ['cash_date', this.$store.state.cash_new.current_date])
            // this.$store.commit('cash_new/set_common', ['cash_due_date', this.$store.state.cash_new.current_date])
            // this.$store.commit('cash_new/set_common', ['cash_note', ''])
            // this.$store.commit('cash_new/set_common', ['cash_number', ''])
            // this.$store.commit('cash_new/set_common', ['cash_disc', 0])
            // this.$store.commit('cash_new/set_common', ['cash_discrp', 0])
            // this.$store.commit('cash_new/set_common', ['cash_disctype', 'P'])
            // this.$store.commit('cash_new/set_common', ['cash_shipping', 0])
            // this.$store.commit('cash_new/set_common', ['cash_memo', ''])
            // this.$store.commit('cash_new/set_common', ['cash_address', ''])
            // this.$store.commit('cash_new/set_cash_dps', [])
            // this.$store.commit('cash_new/set_selected_customer', null)
            // // this.$store.commit('cash_new/set_selected_warehouse', null)
            // this.$store.commit('cash_new/set_selected_term', null)
            // this.$store.commit('cash_new/set_items', [])
            // this.$store.commit('cash_new/set_details', x)
            // this.$store.commit('cash_new/set_common', ['dialog_delivery', true])
            return
        },

        edit_href (x) {
            return '../' + x.cash_type_code.replace(/(CASH\.)/, '').toLowerCase() + '/?id=' + x.cash_id
        },

        edit (x, e) {
            e.preventDefault()
            // this.setNewObject("selected_cash")

            this.setNewObject("edit", true)
            this.setNewObjects(x, [
                "cash_id",
                "cash_date",
                "cash_memo",
                "cash_note",
                "cash_date",
                "cash_from",
                "cash_number",
                "cash_amount",
                "cash_disc",
                "cash_discrp",
                "cash_img",
                "cash_type_code",
                "cash_type_name"
            ])
            this.setNewObject("dialog_new", true)

            this.$store.commit("tag/set_object", ['selected_tagnames', JSON.parse(x.cash_tags)])
            if (x.tax_id != 0) {
                this.setNewObject("selected_tax", {tax_id:x.tax_id,tax_name:x.tax_name,tax_amount:x.tax_amount})
            }
            this.setNewObject("selected_account", {account_id:x.from_account_id,account_name:x.from_account_name})
            this.setNewObject("selected_account_to", {account_id:x.to_account_id,account_name:x.to_account_name})

            // DETAILS

            this.$store.commit("cashNew/resetDetail")
            let details = JSON.parse(JSON.stringify(this.$store.state.cashNew.details))
            let details_add = JSON.parse(JSON.stringify(this.$store.state.cashNew.details_add))

            let side = x.cash_type_code == 'CASH.PAY' ? 'credit' : 'debit'
            let otherSide = (side == 'credit' ? 'debit' : 'credit')
            let dtls = []
            for (let d of x.details) {
                for (acc of this.$store.state.cash.accounts) {
                    if (acc.account_id == d.account && d[otherSide] > 0) {
                        dtls.push({account:acc,debit:d.debit,credit:d.credit})
                    }
                }
            }
            this.setNewObject("details", dtls)

            let n = 0
            for (let d of x.details) {
                for (acc of this.$store.state.cash.accounts) {
                    if (acc.account_id == d.account && d[side] > 0) {
                        details_add[n] = {account:acc,debit:d.debit,credit:d.credit}
                        n++
                    }
                }
            }
            this.setNewObject("details_add", details_add)

            // END OF DETAILS
            
            // window.location.replace("../new/?id="+x.cash_id)
            // if (this.$store.state.cash_new.customers.length < 1) {
            //     this.$store.commit('set_dialog_progress', true)
            //     this.$store.dispatch('cash_new/search_customer').then(() => {
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
            this.$store.commit('cash_new/set_common', ['edit', true])
            this.$store.commit('cash_new/set_common', ['cash_id', sc.cash_id])
            this.$store.commit('cash_new/set_common', ['cash_date', sc.cash_date])
            // this.$store.commit('cash_new/set_common', ['cash_due_date', 
            //     moment(sc.cash_date, "YYYY-MM-DD").add(Math.round(sc.cash_term), 'days').format('DD-MM-YYYY')])
            this.$store.commit('cash_new/set_common', ['cash_due_date', sc.cash_due_date.split('-').reverse().join('-')])
            this.$store.commit('cash_new/set_common', ['cash_note', sc.cash_note])
            this.$store.commit('cash_new/set_common', ['cash_memo', sc.cash_memo])
            this.$store.commit('cash_new/set_common', ['cash_number', sc.cash_number])
            this.$store.commit('cash_new/set_common', ['cash_disc', sc.cash_disc])
            this.$store.commit('cash_new/set_common', ['cash_discrp', sc.cash_discrp])
            this.$store.commit('cash_new/set_common', ['cash_dp', sc.cash_dp])
            this.$store.commit('cash_new/set_common', ['cash_shipping', sc.cash_shipping])
            this.$store.commit('cash_new/set_common', ['cash_proforma', sc.cash_proforma])
            this.$store.commit('cash_new/set_common', ['sales_name', sc.sales.staff_short])
            this.$store.commit('cash_new/set_cash_dps', sc.cash_dps) 

            this.$store.commit('cash_new/set_common', ['cash_disctype', 
                Math.round(sc.cash_discrp)>0?'R':'P'])

            this.$store.commit('cash_new/set_selected_customer', null)
            // this.$store.commit('cash_new/set_selected_warehouse', null)
            for (let v of this.$store.state.cash_new.customers)
                if (v.customer_id == sc.customer_id)
                    this.$store.commit('cash_new/set_selected_customer', v)
            
            for (let v of this.$store.state.cash_new.terms)
                if (v.term_id == sc.cash_term)
                    this.$store.commit('cash_new/set_selected_term', v)

            let details = sc.details
            let dfl = JSON.parse(JSON.stringify(this.$store.state.cash_new.detail_default))
            this.$store.dispatch('cash_new/search_dp')

            if (sc.cash_proforma == 'Y') {
                let dx = []
                for (let d in details) {
                    dx.push(dfl)
                    dx[d].delivery = details[d].sales
                    dx[d].items = details[d].items
                }
                this.$store.commit('cash_new/set_details', dx)
                // this.$store.dispatch('cash_new/search_item')
                this.$store.commit('cash_new/set_common', ['dialog_proforma', true])
            }
            else {
                
                // let acc = this.$store.state.cash_new.accounts
                for (let d of details)
                    d.delivery.items = d.items
                //     for (let a of acc)
                //         if (a.account_id == d.account)
                //             d.account = a

                this.$store.commit('cash_new/set_details', details)
                this.$store.commit('cash_new/set_common', ['dialog_new', true])
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
            this.$store.commit('cash_new/set_common', ['cash_address', address])
            
        },

        del (x) {
            this.select(x)
            this.$store.commit('set_dialog_delete', true)
        },

        confirm_del (x) {
            this.$store.dispatch('cash/del', {id:x.data})
        },

        post (x) {
            this.select(x)
            this.$store.commit('set_dialog_confirm', true)
        },

        confirm_post (x) {
            this.$store.dispatch('cash/post', {id:x.data})
        },

        select (x) {
            this.setObject('selected_cash', x)
        },

        search () {
            return this.$store.dispatch('cash/search', {})
        },

        change_page(x) {
            this.curr_page = x
            this.$store.dispatch('cash/search', {})
        },

        change_s_date(x) {
            this.$store.commit('cash/set_common', ['s_date', x.new_date])
            this.$store.dispatch('cash/search')
        },

        change_e_date(x) {
            this.$store.commit('cash/set_common', ['e_date', x.new_date])
            this.$store.dispatch('cash/search')
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

        print_cash (x) {
            this.select(x)
            let so = x
            this.report_url = this.$store.state.cash.URL+"report/one_sales_002?id="+so.cash_id
            this.$store.commit('set_dialog_print', true)
        },

        bg_proforma (x) {
            if (x.cash_proforma=='Y')
                return 'amber lighten-4'
            return 'white'
        },

        proforma_edit (z) {

            let sc = z
            this.$store.commit('cash_new/set_common', ['cash_proforma', 'Y'])
            this.$store.dispatch('cash_new/search_item')
            
            this.$store.commit('cash_new/set_common', ['dialog_proforma', true])
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
            let tmp_acc_id = this.$store.state.cash.tmp_acc_id

            if (!!this.importx)
                window.location.replace("../list")
            else if (tmp_acc_id == 0)
                this.setObject("selected_tab", "TAB.01")
            else
                window.location.replace("../list")
        },

        importHistory () {
            history.pushState(null, 'IMPORT HISTORY', '../history');
            location.reload(true)
        }
    },

    mounted () {
        // this.$store.dispatch('cash/search')
    }
}
</script>