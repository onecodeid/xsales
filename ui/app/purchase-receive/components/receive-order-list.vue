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
                :items="receives"
                :loading="false"
                hide-actions
                class="elevation-1">
                <template slot="items" slot-scope="props">
                    <td class="text-xs-left pa-1" @click="select(props.item)" style="writing-mode:tb-rl;height:auto;transform:scale(-1)">
                        <span class="success white--text px-2 py-3 d-block" v-if="props.item.receive_bill!=0">INVOICE</span>
                        <span class="orange pa-2 py-3 d-block white--text" v-if="props.item.receive_bill==0&&props.item.receive_confirm=='Y'">DITERIMA</span>
                        <span class="grey white--text pa-2 py-3 d-block" v-if="props.item.receive_confirm=='N'">DRAFT</span>
                    </td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.receive_date }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">
                        {{ props.item.receive_number }}
                        <div class="caption" v-show="props.item.receive_ref_number!=''"><i>ref : {{props.item.receive_ref_number}}</i></div>
                    </td>
                    <td class="text-xs-left pa-2" @click="select(props.item)" v-show="!is_sales">
                        {{ props.item.vendor_name }}
                    </td> 
                    <td class="text-xs-left pa-2" @click="select(props.item)" v-show="!is_sales">
                        {{ props.item.warehouse_name }}
                    </td> 
                    <td class="text-xs-right pa-2" @click="select(props.item)" v-show="!is_sales">
                        {{ one_money(props.item.receive_total_qty) }}
                    </td>
                    <td class="text-xs-left pa-2" @click="select(props.item)" v-show="!is_sales">
                        {{ props.item.receive_note }}
                        <div v-show="props.item.purchase_memos!=''">
                            <div class="caption blue--text" v-for="(m,n) in props.item.purchase_memos.split(';')" :key="n" v-show="m!=''"><i>memo : {{m}}</i></div>
                        </div>
                    </td>
                    <!-- <td class="text-xs-center pa-0 px-0" @click="select(props.item)" v-show="!is_sales">
                        <span v-show="props.item.receive_bill!=0">
                            <v-btn color="orange" small depressed class="white--text" block>Tagihan</v-btn>
                        </span>
                        <span v-show="props.item.receive_bill==0&&props.item.receive_confirm=='Y'"
                            class="">
                            <v-btn color="green" small depressed class="white--text" block>Konfirmasi</v-btn>
                            </span>
                        <span v-show="props.item.receive_confirm=='N'" class="">
                            <v-btn color="grey" small depressed class="white--text" block>Data Baru</v-btn>
                        </span>
                    </td>  -->
                    <!-- <td class="text-xs-left pa-2" @click="select(props.item)" v-show="is_sales">
                        {{ props.item.receive_receipt }}
                    </td> 

                    <td class="text-xs-left pa-2" @click="select(props.item)">
                        {{ props.item.accounts.join(', ') }}
                    </td>                   
                    <td class="text-xs-right pa-2" @click="select(props.item)">Rp {{ one_money(props.item.receive_debit) }}</td>
                    <td class="text-xs-right pa-2" @click="select(props.item)">Rp {{ one_money(props.item.receive_credit) }}</td> -->
                    <td class="text-xs-center pa-2" @click="select(props.item)">
                        <v-btn color="success" class="btn-icon ma-0" small @click="confirm(props.item)" 
                            v-show="props.item.receive_confirm=='N'&&props.item.receive_bill==0"><v-icon>check</v-icon></v-btn>
                        <v-btn color="orange" class="btn-icon ma-0" small @click="bill(props.item)"
                            v-show="props.item.receive_confirm=='Y'&&props.item.receive_bill==0" dark><v-icon>money</v-icon></v-btn>
                        <v-btn color="primary" class="btn-icon ma-0" small @click="edit(props.item)"><v-icon>create</v-icon></v-btn>
                        <v-btn color="red" 
                            class="btn-icon ma-0" small @click="del(props.item)" 
                            :disabled="props.item.receive_bill!=0||props.item.receive_confirm=='XYZ'"
                            :dark="props.item.receive_bill==0"
                            ><v-icon>delete</v-icon></v-btn>
                        
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
        
        <common-dialog-delete :data="receive_id" @confirm_del="confirm_del" v-if="dialog_delete"></common-dialog-delete>
        <common-dialog-confirm :data="receive_id" @confirm="confirm_post" v-if="dialog_confirm" :text="text_post"></common-dialog-confirm>
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
                    width: "14%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "DARI VENDOR",
                    align: "left",
                    sortable: false,
                    width: "20%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "GUDANG TUJUAN",
                    align: "left",
                    sortable: false,
                    width: "12%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "TOTAL QTY",
                    align: "right",
                    sortable: false,
                    width: "8%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "CATATAN & MEMO",
                    align: "left",
                    sortable: false,
                    width: "25%",
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
        receives () {
            return this.$store.state.receive.receives
        },

        dialog_delete () {
            return this.$store.state.dialog_delete
        },

        dialog_confirm () {
            return this.$store.state.dialog_confirm
        },

        receive_id () {
            return this.$store.state.receive.selected_receive.M_JournalID
        },

        query : {
            get () { return this.$store.state.receive.search },
            set (v) { this.$store.commit('receive/update_search', v) }
        },

        curr_page : {
            get () { return this.$store.state.receive.current_page },
            set (v) { this.$store.commit('receive/update_current_page', v) }
        },

        xtotal_page () {
            return this.$store.state.receive.total_receive_page
        },

        text_post () {
            let j = this.$store.state.receive.selected_receive
            return "Apakah anda yakin akan melakukan Posting Jurnal tersebut ?"
        },

        s_date : {
            get () { return this.$store.state.receive.s_date },
            set (v) { this.$store.commit('receive/set_common', ['s_date', v]) }
        },

        e_date : {
            get () { return this.$store.state.receive.e_date },
            set (v) { this.$store.commit('receive/set_common', ['e_date', v]) }
        },

        title() {
            return "PENERIMAAN BARANG"
        },

        is_sales() {
            if (this.$store.state.filter.indexOf("J.03")>-1)
                return true
            return false
        },

        custom_title () {
            return this.$store.state.custom_title?this.$store.state.custom_title:false
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        add () {
            let x = []
            x.push(JSON.parse(JSON.stringify(this.$store.state.receive_new.detail_default)))
            this.$store.commit('receive_new/set_common', ['edit', false])
            this.$store.commit('receive_new/set_common', ['receive_date', this.$store.state.receive_new.current_date.split('-').reverse().join('-')])
            this.$store.commit('receive_new/set_common', ['receive_note', ''])
            this.$store.commit('receive_new/set_common', ['receive_memo', ''])
            this.$store.commit('receive_new/set_common', ['receive_number', ''])
            this.$store.commit('receive_new/set_common', ['receive_ref_number', ''])
            this.$store.commit('receive_new/set_selected_vendor', null)
            this.$store.commit('receive_new/set_selected_warehouse', null)
            this.$store.commit('receive_new/set_items', [])
            this.$store.commit('receive_new/set_details', x)
            this.$store.commit('receive_new/set_common', ['dialog_purchase', true])
            this.$store.commit('purchase/set_selected_purchase', null)
        },

        edit (x) {
            this.select(x)
            let sc = x
            this.$store.commit('receive_new/set_common', ['edit', true])
            this.$store.commit('receive_new/set_common', ['receive_id', sc.receive_id])
            this.$store.commit('receive_new/set_common', ['receive_date', sc.receive_date])
            this.$store.commit('receive_new/set_common', ['receive_note', sc.receive_note])
            this.$store.commit('receive_new/set_common', ['receive_memo', sc.receive_memo])
            this.$store.commit('receive_new/set_common', ['receive_number', sc.receive_number])
            this.$store.commit('receive_new/set_common', ['receive_ref_number', sc.receive_ref_number])

            this.$store.commit('receive_new/set_selected_vendor', null)
            this.$store.commit('receive_new/set_selected_warehouse', null)
            for (let v of this.$store.state.receive_new.vendors)
                if (v.vendor_id == sc.vendor_id)
                    this.$store.commit('receive_new/set_selected_vendor', v)
            for (let v of this.$store.state.receive_new.warehouses)
                if (v.warehouse_id == sc.warehouse_id)
                    this.$store.commit('receive_new/set_selected_warehouse', v)

            let details = sc.items
            // let acc = this.$store.state.receive_new.accounts
            // for (let d of details)
            //     for (let a of acc)
            //         if (a.account_id == d.account)
            //             d.account = a
            this.$store.commit('receive_new/set_details', details)

            this.$store.commit('receive_new/set_common', ['dialog_new', true])
        },

        del (x) {
            this.select(x)
            this.$store.commit('set_dialog_delete', true)
        },

        confirm_del (x) {
            this.$store.dispatch('receive/del', {id:x.data})
        },

        post (x) {
            this.select(x)
            this.$store.commit('set_dialog_confirm', true)
        },

        confirm_post (x) {
            this.$store.dispatch('receive/post', {id:x.data})
        },

        select (x) {
            this.$store.commit('receive/set_selected_receive', x)
        },

        search () {
            return this.$store.dispatch('receive/search', {}).then((d) => {
                console.log(d)
            })
        },

        change_page(x) {
            this.curr_page = x
            this.$store.dispatch('receive/search', {})
        },

        change_s_date(x) {
            this.$store.commit('receive/set_common', ['s_date', x.new_date])
            this.$store.dispatch('receive/search')
        },

        change_e_date(x) {
            this.$store.commit('receive/set_common', ['e_date', x.new_date])
            this.$store.dispatch('receive/search')
        },

        confirm (x) {
            this.select(x)
            this.$store.commit('receive/set_common', ['dialog_confirm', true])
            // this.$store.dispatch('receive/confirm')
        },

        bill (x) {
            this.select(x)
            this.$store.commit('receive/set_common', ['dialog_bill', true])
            // this.$store.dispatch('receive/bill')
        },

        search_purchase () {
            // this.$store.commit('purchase/set_common', ['search', ''])
            // this.$store.commit('purchase/set_common', ['current_page', 1])
            // this.$store.commit('purchase/set_common', ['search', ''])
            // let prm = {
            //     token : one_token(),
            //     search : context.state.search,
            //     page : context.state.current_page,
            //     sdate : context.state.s_date,
            //     edate : context.state.e_date
            // }

            this.$store.dispatch('purchase/search', {done:'N'})
        }
    },

    mounted () {
        console.log(this.is_sales)
        if (this.is_sales)
            this.headers[2].text="NOMOR INVOICE"

        this.$store.commit('purchase/set_common', ['s_date', '2021-01-01'])
        this.$store.dispatch('purchase/search', {done:'N'})
    }
}
</script>