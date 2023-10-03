<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0" v-show="!custom_title">
            <v-layout row wrap>
                <v-flex xs4>
                    <h3 class="display-1 font-weight-light zalfa-text-title">{{title}}</h3>
                    <!-- <div class="orange lighten-2" style="height:5px"></div> -->
                </v-flex>
                <v-flex xs3 class="pr-4">
                    <!-- <div class="orange lighten-2 fill-height d-flex align-center text-xs-center"><h3><a href="../due/" class="white--text">HUTANG JATUH TEMPO</a></h3></div> -->
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
        <v-card-text class="pt-0">
            <v-layout>
                <v-flex xs12 md6 lg4 xl3 pa-1 v-for="(h, n) in headerStats" :key="n">
                    <v-card class="justify-center">
                        <v-card-title primary-title class="pa-1 white--text justify-center body-2" :class="h.color">{{ h.title.toUpperCase() }}</v-card-title>
                        <v-card-text class="display-1 justify-center text-xs-center lighten-5" :class="h.color">
                            <a :href="'../../'+h.url" v-show="!!h.url" target="_blank">Rp {{ one_money(h.amount) }}</a>
                            <span v-show="!h.url">Rp {{ one_money(h.amount) }}</span>
                        </v-card-text>
                    </v-card>
                </v-flex>
            </v-layout>
            <v-data-table 
                :headers="headers"
                :items="bills"
                :loading="false"
                hide-actions
                class="elevation-1">
                <template slot="items" slot-scope="props">
                    <td class="text-xs-left pa-1" @click="select(props.item)" style="writing-mode:tb-rl;height:auto;transform:scale(-1)">
                        <span class="success white--text px-2 py-3 d-block" v-if="props.item.bill_lunas=='Y'">LUNAS</span>
                        <span class="orange pa-2 py-3 d-block white--text" v-if="props.item.bill_lunas=='N'&&props.item.bill_paid>0">SEBAGIAN</span>
                        <span class="grey white--text pa-2 py-3 d-block" v-if="props.item.bill_lunas=='N'&&props.item.bill_paid==0">NEW</span>

                        <!-- <v-btn color="success" small class="ma-0" block v-show="props.item.bill_lunas=='Y'">Lunas</v-btn>
                        <v-btn color="orange" small class="ma-0" block dark v-show="props.item.bill_lunas=='N'&&props.item.bill_paid>0">Bayar Sebagian</v-btn>
                        <v-btn color="grey" small class="ma-0" block dark v-show="props.item.bill_lunas=='N'&&props.item.bill_paid==0">Belum Dibayar</v-btn> -->
                    </td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.bill_date }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.bill_number }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)" v-show="!is_sales">
                        <b>{{ props.item.vendor_name }}</b><br>
                        <div class="caption primary--text" v-show="props.item.bill_note!=''"><i><b>Catatan :</b> {{ props.item.bill_note }}</i></div>
                    </td> 
                    <!-- <td class="text-xs-left pa-2" @click="select(props.item)" v-show="!is_sales">
                        -
                    </td>  -->

                    <td class="text-xs-right pa-2" @click="select(props.item)" v-show="!is_sales">
                        <span class="grey--text caption">Rp</span> <b>{{ one_money(props.item.bill_grand_total) }}</b>
                    </td>
                    
                    <td class="text-xs-left pa-2" @click="select(props.item)" v-show="!is_sales">
                        <v-layout row wrap v-for="(p,i) in props.item.payments" :key="i">
                            <v-flex>
                                <a href="#"  @click="edit_payment(props.item, p.pay_id, $event)" >
                                    <!-- <v-chip label> -->
                                        {{p.pay_number}}, {{p.pay_date}}
                                    <!-- </v-chip> -->
                                </a>
                                
                            </v-flex>
                            <v-flex class="text-xs-right">
                                <!-- <v-chip label> -->
                                    <span class="grey--text caption">Rp</span> <b class="cyan--text">{{ one_money(Math.round(p.pay_total)) }}</b>    
                                <!-- </v-chip> -->
                                
                            </v-flex>
                        </v-layout>
                    </td> 
                    
                    <td class="text-xs-right pa-2" @click="select(props.item)" v-show="!is_sales">
                        <span class="grey--text caption">Rp</span> <b>{{ one_money(props.item.bill_unpaid) }}</b>
                    </td>
                    <!-- <td class="text-xs-left pa-2" @click="select(props.item)" v-show="!is_sales">
                        <v-btn color="success" small class="ma-0" block v-show="props.item.bill_lunas=='Y'">Lunas</v-btn>
                        <v-btn color="orange" small class="ma-0" block dark v-show="props.item.bill_lunas=='N'&&props.item.bill_paid>0">Bayar Sebagian</v-btn>
                        <v-btn color="grey" small class="ma-0" block dark v-show="props.item.bill_lunas=='N'&&props.item.bill_paid==0">Belum Dibayar</v-btn>
                    </td> -->
                    <td class="text-xs-center pa-0" @click="select(props.item)">
                        <div class="row">
                            <div class="col-12">
                                <v-btn color="cyan" class="btn-icon ma-0" small @click="journal(props.item)" :dark="false" v-show="props.item.journal_id!=0" disabled><v-icon>assignment</v-icon></v-btn>
                                <!-- <v-btn color="orange" class="btn-icon ma-0" small @click="print_invoice(props.item)" dark title="Cetak invoice"><v-icon>print</v-icon></v-btn> -->
                                <v-btn color="cyan" class="btn-icon ma-0" small @click="pay(props.item)" dark
                                    title="Pembayaran Hutang"><v-icon>attach_money</v-icon></v-btn>
                                <!-- <v-btn color="cyan" class="btn-icon ma-0" small @click="journal(props.item)" dark v-show="props.item.journal_id!=0" title="Jurnal"><v-icon>assignment</v-icon></v-btn> -->
                            </div>
                            <div class="col-12 mt-1">
                                <v-btn color="primary" class="btn-icon ma-0" small @click="edit(props.item)"><v-icon>create</v-icon></v-btn>
                                <v-btn color="red" 
                                    class="btn-icon ma-0" small @click="del(props.item)" :dark="parseFloat(props.item.bill_paid)==0" :disabled="parseFloat(props.item.bill_paid)>0"><v-icon>delete</v-icon></v-btn>
                                <!-- <v-btn color="primary" class="btn-icon ma-0" small @click="edit(props.item)" title="Ubah invoice" disabled><v-icon>create</v-icon></v-btn> -->
                                <!-- <v-btn color="red" 
                                    class="btn-icon ma-0" small @click="del(props.item)" :dark="false" title="Hapus invoice" disabled><v-icon>delete</v-icon></v-btn> -->
                                    
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
        
        <common-dialog-delete :data="bill_id" @confirm_del="confirm_del" v-if="dialog_delete"></common-dialog-delete>
        <common-dialog-confirm :data="bill_id" @confirm="confirm_post" v-if="dialog_confirm" :text="text_post"></common-dialog-confirm>
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
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue')
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
                    width: "8%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "VENDOR",
                    align: "left",
                    sortable: false,
                    width: "30%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "TOTAL TAGIHAN",
                    align: "right",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "HISTORI PEMBAYARAN",
                    align: "left",
                    sortable: false,
                    width: "21%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "SISA TAGIHAN",
                    align: "right",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                
                // {
                //     text: "PERKIRAAN",
                //     align: "left",
                //     sortable: false,
                //     width: "28%",
                //     class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                // },
                // {
                //     text: "DEBIT",
                //     align: "right",
                //     sortable: false,
                //     width: "10%",
                //     class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                // },
                // {
                //     text: "KREDIT",
                //     align: "right",
                //     sortable: false,
                //     width: "10%",
                //     class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                // },
                {
                    text: "ACTION",
                    align: "center",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                }
            ]
        }
    },

    computed : {
        bills () {
            return this.$store.state.bill.bills
        },

        dialog_delete () {
            return this.$store.state.dialog_delete
        },

        dialog_confirm () {
            return this.$store.state.dialog_confirm
        },

        bill_id () {
            return this.$store.state.bill.selected_bill.M_JournalID
        },

        query : {
            get () { return this.$store.state.bill.search },
            set (v) { this.$store.commit('bill/update_search', v) }
        },

        curr_page : {
            get () { return this.$store.state.bill.current_page },
            set (v) { this.$store.commit('bill/update_current_page', v) }
        },

        xtotal_page () {
            return this.$store.state.bill.total_bill_page
        },

        text_post () {
            let j = this.$store.state.bill.selected_bill
            return "Apakah anda yakin akan melakukan Posting Jurnal tersebut ?"
        },

        s_date : {
            get () { return this.$store.state.bill.s_date },
            set (v) { this.$store.commit('bill/set_common', ['s_date', v]) }
        },

        e_date : {
            get () { return this.$store.state.bill.e_date },
            set (v) { this.$store.commit('bill/set_common', ['e_date', v]) }
        },

        title() {
            return "DAFTAR HUTANG"
        },

        is_sales() {
            if (this.$store.state.filter.indexOf("J.03")>-1)
                return true
            return false
        },

        custom_title () {
            return this.$store.state.custom_title?this.$store.state.custom_title:false
        },

        headerStats () {
            return this.$store.state.bill.headerStats
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        setObject (x, y) {
            this.$store.commit("bill/set_object", [x, y])
        },

        setObjectPay (x, y) {
            this.$store.commit("billPay/set_object", [x, y])
        },

        add () {
            let x = []
            x.push(JSON.parse(JSON.stringify(this.$store.state.bill_new.detail_default)))
            this.$store.commit('bill_new/set_common', ['edit', false])
            this.$store.commit('bill_new/set_common', ['bill_date', this.$store.state.bill_new.current_date.split('-').reverse().join('-')])
            this.$store.commit('bill_new/set_common', ['bill_note', ''])
            this.$store.commit('bill_new/set_common', ['bill_number', ''])
            this.$store.commit('bill_new/set_common', ['bill_dp', 0])
            this.$store.commit('bill_new/set_selected_vendor', null)
            this.$store.commit('bill_new/set_selected_warehouse', null)
            this.$store.commit('bill_new/set_items', [])
            this.$store.commit('bill_new/set_details', x)
            this.$store.commit('bill_new/set_bill_dps', [])
            this.$store.commit('bill_new/set_common', ['dialog_new', true])
        },

        edit (x) {
            this.select(x)
            let sc = x
            this.$store.commit('bill_new/set_common', ['edit', true])
            this.$store.commit('bill_new/set_common', ['bill_id', sc.bill_id])
            this.$store.commit('bill_new/set_common', ['bill_date', sc.bill_date])
            this.$store.commit('bill_new/set_common', ['bill_note', sc.bill_note])
            this.$store.commit('bill_new/set_common', ['bill_number', sc.bill_number])
            this.$store.commit('bill_new/set_common', ['bill_disc', sc.bill_disc])
            this.$store.commit('bill_new/set_common', ['bill_discrp', sc.bill_discrp])
            this.$store.commit('bill_new/set_common', ['bill_dp', sc.bill_dp])
            
            this.$store.commit('bill_new/set_common', ['bill_disctype', 
                Math.round(sc.bill_discrp)>0?'R':'P'])

            this.$store.commit('bill_new/set_selected_vendor', null)
            // this.$store.commit('bill_new/set_selected_warehouse', null)
            for (let v of this.$store.state.bill_new.vendors)
                if (v.vendor_id == sc.vendor_id)
                    this.$store.commit('bill_new/set_selected_vendor', v)

            // dp
            this.$store.dispatch('bill_new/search_dp').then((x) => {
                this.$store.commit('bill_new/set_bill_dps', sc.bill_dps)
            })
            
            // for (let v of this.$store.state.bill_new.warehouses)
            //     if (v.warehouse_id == sc.warehouse_id)
            //         this.$store.commit('bill_new/set_selected_warehouse', v)
            this.$store.commit("bill_new/set_object", ['selected_term', {term_id:sc.term_id,term_name:sc.term_name}])
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

        del (x) {
            this.select(x)
            this.$store.commit('set_dialog_delete', true)
        },

        confirm_del (x) {
            this.$store.dispatch('bill/del', {id:x.data})
        },

        post (x) {
            this.select(x)
            this.$store.commit('set_dialog_confirm', true)
        },

        confirm_post (x) {
            this.$store.dispatch('bill/post', {id:x.data})
        },

        select (x) {
            this.$store.commit('bill/set_selected_bill', x)
        },

        search () {
            return this.$store.dispatch('bill/search', {})
        },

        change_page(x) {
            this.curr_page = x
            this.$store.dispatch('bill/search', {})
        },

        change_s_date(x) {
            this.$store.commit('bill/set_common', ['s_date', x.new_date])
            this.$store.dispatch('bill/search')
        },

        change_e_date(x) {
            this.$store.commit('bill/set_common', ['e_date', x.new_date])
            this.$store.dispatch('bill/search')
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
            //     for (let a of this.$store.state.cash_receive.accounts) {
            //         if (a.account_id == d.account)
            //             d.account = a
            //     }
            //     details.push(d)
            // }
            this.$store.commit('journal_new/set_details', x.accounts)
        },

        pay(x) {
            this.select(x)
            this.setObjectPay("dialog_pay", true)

            this.setObjectPay("edit", false)
            this.setObjectPay("bill_id", x.bill_id)
            this.setObjectPay("bill_number", x.bill_number)
            this.setObjectPay("bill_date", x.bill_date)
            this.setObjectPay("bill_total", x.bill_total)
            this.setObjectPay("bill_grand_total", x.bill_grand_total)
            this.setObjectPay("bill_paid", x.bill_paid)
            this.setObjectPay("bill_unpaid", x.bill_unpaid)

            this.setObjectPay("pay_amount", 0)
            this.setObjectPay("pay_date", this.$store.state.billPay.current_date)
            this.setObjectPay("pay_note", '')
            this.setObjectPay("pay_id", 0)

            this.setObjectPay("selected_vendor", {vendor_id:x.vendor_id,vendor_name:x.vendor_name})
            this.setObjectPay("selected_account", null)
            this.setObjectPay("selected_disc_account", null)
            this.$store.commit('disc/set_object', ['disctype', 'R'])
            this.$store.commit('disc/set_object', ['disc', 0])
            this.$store.commit('disc/set_object', ['discrp', 0])
            this.setObjectPay("disc_amount", 0)
            this.setObjectPay("use_disc", false)

            this.setObjectPay("use_memo", false)
            this.setObjectPay("selected_memos", [this.$store.state.billPay.default_memo])

            this.$store.dispatch("billPay/search_memo")
            return
        },

        edit_payment(x, y, e) {
            e.preventDefault()

            this.setObjectPay("pay_id", y)
            this.$store.dispatch("billPay/search_pay_id").then(res => {
                this.setObjectPay("bill_id", res.bill_id)
                this.setObjectPay("bill_number", res.bill_number) 
                this.setObjectPay("bill_date", res.bill_date) 
                this.setObjectPay("bill_grand_total", res.bill_grand_total) 
                this.setObjectPay("bill_unpaid", x.bill_unpaid) 
                this.setObjectPay("bill_paid", x.bill_paid) 
                this.setObjectPay("edit", true)

                this.setObjectPay("pay_amount", res.pay_amount)
                this.setObjectPay("pay_date", res.pay_date)
                this.setObjectPay("pay_note", res.pay_note)
                this.setObjectPay("pay_id", res.pay_id)

                this.setObjectPay("selected_vendor", res.vendor)
                this.setObjectPay("selected_account", res.credit_account)
                this.setObjectPay("selected_disc_account", res.disc_account)

                this.$store.commit('disc/set_object', ['disc', res.pay_disc])
                this.$store.commit('disc/set_object', ['discrp', res.pay_discrp])
                this.setObjectPay("disc_amount", parseFloat(res.pay_discamount))

                this.setObjectPay("use_disc", parseFloat(res.pay_discamount)>0?true:false)

                if (parseFloat(res.pay_disc) > 0)
                    this.$store.commit('disc/set_object', ['disctype', 'P'])
                else
                    this.$store.commit('disc/set_object', ['disctype', 'R'])

                // memos
                this.setObjectPay("use_memo", false)
                this.setObjectPay("selected_memos", [this.$store.state.billPay.default_memo])
                if (!!res.memos) {
                    let selected_memos = []
                    for (let m of res.memos) {
                        selected_memos.push({selected_memo:m,amount:m.memo_amount})
                    }
                    this.setObjectPay("selected_memos", selected_memos)
                    this.setObjectPay("use_memo", res.memos.length > 0 ? true : false)
                }

                this.setObjectPay("dialog_pay", true)
            })
        }
    },

    mounted () {
        console.log(this.is_sales)
        if (this.is_sales)
            this.headers[2].text="NOMOR INVOICE"

        this.$store.dispatch('bill/search')
        this.$store.dispatch('bill/searchHeaderStats')
    }
}
</script>