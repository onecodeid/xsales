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
                :items="purchases"
                :loading="false"
                hide-actions
                class="elevation-1">
                <template slot="items" slot-scope="props">
                    <td class="text-xs-left pa-1" @click="select(props.item)" style="writing-mode:tb-rl;height:auto;transform:scale(-1)">
                        <span class="success white--text px-2 py-3 d-block" v-if="props.item.purchase_done == 'Y'">SELESAI</span>
                        <span class="orange pa-2 py-3 d-block white--text" v-if="props.item.purchase_done == 'X'">PARSIAL</span>
                        <span class="grey white--text pa-2 py-3 d-block" v-if="props.item.purchase_done == 'N'">NEW</span>
                    </td>

                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.purchase_date }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.purchase_number }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)" v-show="!is_sales">
                        {{ props.item.vendor_name }}
                    </td> 
                    <td class="text-xs-right pa-2" @click="select(props.item)" v-show="!is_sales">
                        <div><span class="caption grey--text">Rp</span> <b class="blue--text">{{ one_money(props.item.purchase_grandtotal) }}</b></div>
                        <div><span class="caption"><i>— {{props.item.term_name}}</i></span></div>
                    </td>
                    <td class="text-xs-left pa-2" @click="select(props.item)" v-show="!is_sales">
                        {{ props.item.purchase_note }}
                        <div v-show="props.item.purchase_memo!=''">
                            <div class="caption blue--text"><i>memo : {{props.item.purchase_memo}}</i></div>
                        </div>
                    </td>

                    <!-- <td class="text-xs-left pa-2" @click="select(props.item)" v-show="!is_sales">
                        <v-btn color="success" small v-show="props.item.purchase_done == 'Y'" class="ma-0" block>Selesai</v-btn>
                        <v-btn color="orange" small v-show="props.item.purchase_done == 'X'" class="ma-0 white--text" block>Kirim Sebagian</v-btn>
                        <v-btn color="grey" small v-show="props.item.purchase_done == 'N'" class="ma-0 white--text" block>Data Baru</v-btn>
                    </td>  -->

                    <!-- <td class="text-xs-left pa-2" @click="select(props.item)" v-show="is_sales">
                        {{ props.item.purchase_receipt }}
                    </td> 

                    <td class="text-xs-left pa-2" @click="select(props.item)">
                        {{ props.item.accounts.join(', ') }}
                    </td>                   
                    <td class="text-xs-right pa-2" @click="select(props.item)">Rp {{ one_money(props.item.purchase_debit) }}</td>
                    <td class="text-xs-right pa-2" @click="select(props.item)">Rp {{ one_money(props.item.purchase_credit) }}</td> -->
                    <td class="text-xs-center pa-0" @click="select(props.item)">
                        <v-btn color="orange" class="btn-icon ma-0" small @click="print_po(props.item)" dark><v-icon>print</v-icon></v-btn>
                        <v-btn color="primary" class="btn-icon ma-0" small @click="edit(props.item)"><v-icon>create</v-icon></v-btn>
                        <v-btn color="red" 
                            :dark="props.item.purchase_done=='N'" 
                            :disabled="props.item.purchase_done!='N'" 
                            class="btn-icon ma-0" small @click="del(props.item)"><v-icon>delete</v-icon></v-btn>
                        
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
        
        <common-dialog-delete :data="purchase_id" @confirm_del="confirm_del" v-if="dialog_delete"></common-dialog-delete>
        <common-dialog-confirm :data="purchase_id" @confirm="confirm_post" v-if="dialog_confirm" :text="text_post"></common-dialog-confirm>
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
        "common-dialog-print" : httpVueLoader("../../common/components/common-dialog-print.vue")
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
                    width: "7%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "NOMOR",
                    align: "left",
                    sortable: false,
                    width: "13%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "VENDOR",
                    align: "left",
                    sortable: false,
                    width: "23%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "TOTAL",
                    align: "right",
                    sortable: false,
                    width: "15%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "CATATAN & MEMO",
                    align: "left",
                    sortable: false,
                    width: "29%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
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
        purchases () {
            return this.$store.state.purchase.purchases
        },

        dialog_delete () {
            return this.$store.state.dialog_delete
        },

        dialog_confirm () {
            return this.$store.state.dialog_confirm
        },

        purchase_id () {
            return this.$store.state.purchase.selected_purchase.M_JournalID
        },

        query : {
            get () { return this.$store.state.purchase.search },
            set (v) { this.$store.commit('purchase/update_search', v) }
        },

        curr_page : {
            get () { return this.$store.state.purchase.current_page },
            set (v) { this.$store.commit('purchase/update_current_page', v) }
        },

        xtotal_page () {
            return this.$store.state.purchase.total_purchase_page
        },

        text_post () {
            let j = this.$store.state.purchase.selected_purchase
            return "Apakah anda yakin akan melakukan Posting Jurnal tersebut ?"
        },

        s_date : {
            get () { return this.$store.state.purchase.s_date },
            set (v) { this.$store.commit('purchase/set_common', ['s_date', v]) }
        },

        e_date : {
            get () { return this.$store.state.purchase.e_date },
            set (v) { this.$store.commit('purchase/set_common', ['e_date', v]) }
        },

        title() {
            return "PEMESANAN BARANG (PURCHASING)"
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
            x.push(JSON.parse(JSON.stringify(this.$store.state.purchase_new.detail_default)))
            this.$store.commit('purchase_new/set_common', ['edit', false])
            this.$store.commit('purchase_new/set_common', ['purchase_date', this.$store.state.purchase_new.current_date.split('-').reverse().join('-')])
            this.$store.commit('purchase_new/set_common', ['purchase_note', ''])
            this.$store.commit('purchase_new/set_common', ['purchase_memo', ''])
            this.$store.commit('purchase_new/set_common', ['purchase_receipt', ''])
            this.$store.commit('purchase_new/set_common', ['purchase_ppn', 'N'])
            this.$store.commit('purchase_new/set_common', ['purchase_ppn_amount', 0])
            this.$store.commit('purchase_new/set_common', ['purchase_disc', 0])
            this.$store.commit('purchase_new/set_common', ['purchase_discrp', 0])
            this.$store.commit('purchase_new/set_common', ['purchase_shipping', 0])
            this.$store.commit('purchase_new/set_common', ['purchase_dp', 0])
            this.$store.commit('purchase_new/set_details', x)
            this.$store.commit('purchase_new/set_selected_vendor', null)
            this.$store.commit('purchase_new/set_object', ['selected_term', null])
            this.$store.commit('purchase_new/set_common', ['dialog_new', true])
        },

        edit (x) {
            if (this.$store.state.purchase_new.vendors.length < 1) {
                this.$store.commit('set_dialog_progress', true)
                this.$store.dispatch('purchase_new/search_vendor').then(() => {
                    this.$store.commit('set_dialog_progress', false)
                    this.do_edit(x)
                })
            }
            else
                this.do_edit(x)
        },

        do_edit (x) {
            this.select(x)
            let sc = x
            this.$store.commit('purchase_new/set_common', ['edit', true])
            this.$store.commit('purchase_new/set_common', ['purchase_id', sc.purchase_id])
            this.$store.commit('purchase_new/set_common', ['purchase_date', sc.purchase_date])
            this.$store.commit('purchase_new/set_common', ['purchase_note', sc.purchase_note])
            this.$store.commit('purchase_new/set_common', ['purchase_memo', sc.purchase_memo])
            this.$store.commit('purchase_new/set_common', ['purchase_number', sc.purchase_number])
            this.$store.commit('purchase_new/set_common', ['purchase_ppn', sc.purchase_ppn])
            this.$store.commit('purchase_new/set_common', ['purchase_ppn_amount', sc.purchase_ppn_amount])
            this.$store.commit('purchase_new/set_common', ['purchase_disc', sc.purchase_disc])
            this.$store.commit('purchase_new/set_common', ['purchase_discrp', sc.purchase_discrp])
            this.$store.commit('purchase_new/set_common', ['purchase_shipping', sc.purchase_shipping])
            this.$store.commit('purchase_new/set_common', ['purchase_dp', sc.purchase_dp])

            this.$store.commit('purchase_new/set_selected_vendor', null)
            for (let v of this.$store.state.purchase_new.vendors)
                if (v.vendor_id == sc.vendor_id)
                    this.$store.commit('purchase_new/set_selected_vendor', v)

            this.$store.commit('purchase_new/set_selected_paymentplan', null)
            for (let v of this.$store.state.purchase_new.paymentplans)
                if (v.paymentplan_id == sc.purchase_payment)
                    this.$store.commit('purchase_new/set_selected_paymentplan', v)

            this.$store.commit('purchase_new/set_selected_staff', null)
            for (let v of this.$store.state.purchase_new.staffs)
                if (v.staff_id == sc.purchase_staff)
                    this.$store.commit('purchase_new/set_selected_staff', v)

            this.$store.commit('purchase_new/set_object', ['selected_term', null])
            for (let v of this.$store.state.purchase_new.terms)
                if (v.term_id == sc.term_id)
                    this.$store.commit('purchase_new/set_object', ['selected_term', v])

            let details = sc.details
            // let acc = this.$store.state.purchase_new.accounts
            // for (let d of details)
            //     for (let a of acc)
            //         if (a.account_id == d.account)
            //             d.account = a
            console.log(details)
            this.$store.commit('purchase_new/set_details', details)

            this.$store.commit('purchase_new/set_common', ['dialog_new', true])
        },

        del (x) {
            this.select(x)
            this.$store.commit('set_dialog_delete', true)
        },

        confirm_del (x) {
            this.$store.dispatch('purchase/del', {id:x.data})
        },

        post (x) {
            this.select(x)
            this.$store.commit('set_dialog_confirm', true)
        },

        confirm_post (x) {
            this.$store.dispatch('purchase/post', {id:x.data})
        },

        select (x) {
            this.$store.commit('purchase/set_selected_purchase', x)
        },

        search () {
            return this.$store.dispatch('purchase/search', {})
        },

        change_page(x) {
            this.curr_page = x
            this.$store.dispatch('purchase/search', {})
        },

        change_s_date(x) {
            this.$store.commit('purchase/set_common', ['s_date', x.new_date])
            this.$store.dispatch('purchase/search')
        },

        change_e_date(x) {
            this.$store.commit('purchase/set_common', ['e_date', x.new_date])
            this.$store.dispatch('purchase/search')
        },

        print_po (x) {
            this.select(x)
            let so = x
            this.report_url = this.$store.state.purchase.URL+"report/one_purchase_001?id="+so.purchase_id+
                                '&token='+this.$store.state.purchase.token
            this.$store.commit('set_dialog_print', true)
        }
    },

    mounted () {
        console.log(this.is_sales)
        if (this.is_sales)
            this.headers[2].text="NOMOR INVOICE"

        this.$store.dispatch('purchase_new/search_staff')
        this.$store.dispatch('purchase_new/search_term')

        let get = window.location.search.substring(1)
        let qs = window.parse_query_string(get)
        if (get != "") {
            if (qs.sdate) this.s_date = qs.sdate
            if (qs.edate) this.e_date = qs.edate
            if (qs.no) {
                this.$store.commit('purchase/set_common', ['search', qs.no])

                this.$store.dispatch('purchase/search', {}).then((r) => {
                    this.edit(r.records[0])
                })
            }        
        } else {
            this.$store.dispatch('purchase/search', {})
        }
    }
}
</script>