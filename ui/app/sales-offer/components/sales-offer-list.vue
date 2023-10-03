<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0" v-show="!custom_title">
            <v-layout row wrap>
                <v-flex xs4>
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
                <v-flex xs2 pl-2>
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
                </v-flex>
                <v-flex xs2 pl-2>
                    <v-select
                        :items="leadtypes"
                        v-model="selected_leadtype"
                        return-object
                        item-text="leadtype_name"
                        item-value="leadtype_id"
                        label="Tipe Prospek"
                        solo
                        clearable
                    >
                        <!-- <template slot="selection" slot-scope="data">
                            <span class="red white--text">{{data.item.leadtype_name}}</span>
                        </template> -->
                    </v-select>
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
                    <td class="text-xs-left pa-1" @click="select(props.item)" style="writing-mode:tb-rl;height:auto;transform:scale(-1)">
                        <span class="white--text px-2 py-3 d-block"
                            :class="{'cyan':props.item.lead_code=='C',
                                    'yellow':props.item.lead_code=='W',
                                    'red':props.item.lead_code=='H'}">
                        {{ props.item.lead_name }}
                        </span>
                    </td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.sales_date }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.sales_number }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)" v-show="!is_sales">
                        {{ props.item.customer_name }}
                    </td> 
                    <td class="text-xs-left pa-2" @click="select(props.item)" v-show="!is_sales">
                        {{ props.item.staff_name }}
                    </td> 
                    <td class="text-xs-right pa-2" @click="select(props.item)" v-show="!is_sales">
                        <span class="caption grey--text">Rp</span> <b>{{ one_money(props.item.sales_grandtotal) }}</b>
                    </td>
                    <td class="text-xs-left pa-2" @click="select(props.item)" v-show="!is_sales">
                        {{ props.item.sales_note }}
                    </td>
                    <td class="text-xs-left pa-2" @click="select(props.item)" v-show="props.item.so_number!=''">
                        {{ props.item.so_number }}
                    </td>
                    <td class="text-xs-left pa-2" @click="select(props.item)" v-show="props.item.so_number==''">
                        <v-btn color="success" small block @click="convert(props.item)">Penjualan</v-btn>
                    </td>
                    <!-- <td class="text-xs-left pa-2" @click="select(props.item)" v-show="!is_sales">
                        <v-btn color="success" small v-show="props.item.sales_done == 'Y'" class="ma-0" block>Selesai</v-btn>
                        <v-btn color="orange" small v-show="props.item.sales_done == 'X'" class="ma-0 white--text" block>Kirim Sebagian</v-btn>
                        <v-btn color="grey" small v-show="props.item.sales_done == 'N'" class="ma-0 white--text" block>Data Baru</v-btn>
                    </td>  -->

                    <!-- <td class="text-xs-left pa-2" @click="select(props.item)" v-show="!is_sales">
                        {{ props.item.lead_name }}
                    </td> -->

                    <!-- <td class="text-xs-left pa-2" @click="select(props.item)" v-show="is_sales">
                        {{ props.item.sales_receipt }}
                    </td> 

                    <td class="text-xs-left pa-2" @click="select(props.item)">
                        {{ props.item.accounts.join(', ') }}
                    </td>                   
                    <td class="text-xs-right pa-2" @click="select(props.item)">Rp {{ one_money(props.item.sales_debit) }}</td>
                    <td class="text-xs-right pa-2" @click="select(props.item)">Rp {{ one_money(props.item.sales_credit) }}</td> -->
                    <td class="text-xs-center pa-0" @click="select(props.item)">
                        <v-btn color="orange" dark class="btn-icon ma-0" small @click="printMe(props.item)"><v-icon>print</v-icon></v-btn>
                        <v-btn color="primary" class="btn-icon ma-0" small @click="edit(props.item)"><v-icon>create</v-icon></v-btn>
                        <v-btn color="red" 
                            :dark="props.item.sales_done=='N'" 
                            :disabled="props.item.sales_done!='N'" 
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
        
        <common-dialog-delete :data="sales_id" @confirm_del="confirm_del" v-if="dialog_delete"></common-dialog-delete>
        <common-dialog-confirm :data="sales_id" @confirm="confirm_post" v-if="dialog_confirm" :text="text_post"></common-dialog-confirm>
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
                    text: "NAMA PROSPEK",
                    align: "left",
                    sortable: false,
                    width: "19%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "SALES",
                    align: "left",
                    sortable: false,
                    width: "8%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "TOTAL",
                    align: "right",
                    sortable: false,
                    width: "12%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "CATATAN",
                    align: "left",
                    sortable: false,
                    width: "24%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "NO SO",
                    align: "left",
                    sortable: false,
                    width: "8%",
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
            return "PENAWARAN BARANG"
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

        leadtypes () {
            return this.$store.state.sales_new.leadtypes
        },

        selected_leadtype : {
            get () { return this.$store.state.sales.selected_leadtype },
            set (v) { 
                this.$store.commit('sales/set_selected_leadtype', v) 
                this.search()
            }
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
        one_money (x) {
            return window.one_money(x)
        },

        add () {
            let x = []
            x.push(JSON.parse(JSON.stringify(this.$store.state.sales_new.detail_default)))
            this.$store.commit('sales_new/set_common', ['edit', false])
            this.$store.commit('sales_new/set_common', ['sales_date', this.$store.state.sales_new.current_date.split('-').reverse().join('-')])
            this.$store.commit('sales_new/set_common', ['sales_note', ''])
            this.$store.commit('sales_new/set_common', ['sales_memo', ''])
            this.$store.commit('sales_new/set_common', ['sales_receipt', ''])
            this.$store.commit('sales_new/set_common', ['sales_franco', ''])
            this.$store.commit('sales_new/set_common', ['sales_delivery', ''])
            this.$store.commit('sales_new/set_common', ['sales_validity', ''])
            this.$store.commit('sales_new/set_common', ['sales_used', 'N'])
            this.$store.commit('sales_new/set_common', ['sales_shipping', 0])
            
            this.$store.commit('sales_new/set_selected_staff', null)
            this.$store.commit('sales_new/set_selected_customer', null)
            this.$store.commit('sales_new/set_selected_leadtype', null)
            this.$store.commit('sales_new/set_selected_paymentplan', null)
            this.$store.commit('sales_new/set_selected_term', null)
            this.$store.commit('sales_new/set_details', x)
            this.$store.commit('sales_new/set_common', ['dialog_new', true])
        },

        edit (x) {
            this.select(x)
            let sc = x
            this.$store.commit('sales_new/set_common', ['edit', true])
            this.$store.commit('sales_new/set_common', ['sales_id', sc.sales_id])
            this.$store.commit('sales_new/set_common', ['sales_date', sc.sales_date])
            this.$store.commit('sales_new/set_common', ['sales_note', sc.sales_note])
            this.$store.commit('sales_new/set_common', ['sales_memo', sc.sales_memo])
            this.$store.commit('sales_new/set_common', ['sales_number', sc.sales_number])
            this.$store.commit('sales_new/set_common', ['sales_franco', sc.sales_franco])
            this.$store.commit('sales_new/set_common', ['sales_delivery', sc.sales_delivery])
            this.$store.commit('sales_new/set_common', ['sales_validity', sc.sales_validity])
            this.$store.commit('sales_new/set_common', ['sales_used', sc.sales_used])
            this.$store.commit('sales_new/set_common', ['sales_shipping', sc.sales_shipping])

            this.$store.commit('sales_new/set_selected_customer', null)
            for (let v of this.$store.state.sales_new.customers)
                if (v.customer_id == sc.customer_id)
                    this.$store.commit('sales_new/set_selected_customer', v)

            this.$store.commit('sales_new/set_selected_leadtype', null)
            for (let v of this.$store.state.sales_new.leadtypes)
                if (v.leadtype_id == sc.sales_lead)
                    this.$store.commit('sales_new/set_selected_leadtype', v)

            this.$store.commit('sales_new/set_selected_staff', null)
            for (let v of this.$store.state.sales_new.staffs)
                if (sc.sales_staff == v.staff_id)
                    this.$store.commit('sales_new/set_selected_staff', v)

            this.$store.commit('sales_new/set_selected_paymentplan', null)
            for (let v of this.$store.state.sales_new.paymentplans)
                if (v.paymentplan_id == sc.sales_payment)
                    this.$store.commit('sales_new/set_selected_paymentplan', v)

            this.$store.commit('sales_new/set_selected_term', null)
            for (let v of this.$store.state.sales_new.terms)
                if (v.term_id == sc.sales_term)
                    this.$store.commit('sales_new/set_selected_term', v)

            let details = sc.details
            // let acc = this.$store.state.sales_new.accounts
            // for (let d of details)
            //     for (let a of acc)
            //         if (a.account_id == d.account)
            //             d.account = a
            console.log(details)
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
            this.$store.dispatch('sales/search')
        },

        change_e_date(x) {
            this.$store.commit('sales/set_common', ['e_date', x.new_date])
            this.$store.dispatch('sales/search')
        },

        printMe (x) {
            this.select(x)
            this.report_url = this.$store.state.sales.URL+"report/one_sales_004?id="+x.sales_id+
                "&token="+this.$store.state.sales.one_token
            this.$store.commit('set_dialog_print', true)
        },

        convert (x) {
            this.select(x)
            this.$store.dispatch('sales/convert_to_sales')
        }
    },

    mounted () {
        console.log(this.is_sales)
        if (this.is_sales)
            this.headers[2].text="NOMOR INVOICE"

        this.$store.dispatch('sales_new/search_leadtype')
    }
}
</script>