<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0" v-show="!custom_title">
            <v-layout row wrap>
                <v-flex xs5>
                    <h3 class="display-1 font-weight-light zalfa-text-title">{{title}}</h3>
                </v-flex>
                <v-flex xs2 pr-2>
                    <v-btn color="red" dark class="ma-0 ml-2 btn-icon" @click="calendar" v-show="!is_sales" title="Pengiriman dari Proforma">
                        <v-icon class="mr-1">event</v-icon>
                        Kalndr Pengiriman
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

                            <v-btn color="success" class="ma-0 ml-2 btn-icon" @click="add" v-show="!is_sales" title="Pengiriman Reguler">
                                <v-icon>add</v-icon>
                            </v-btn>

                            <!-- <v-btn color="amber" dark class="ma-0 ml-2 btn-icon" @click="proforma" v-show="!is_sales" title="Pengiriman dari Proforma">
                                <v-icon>unarchive</v-icon>
                            </v-btn> -->

                            <!-- <v-btn color="red" dark class="ma-0 ml-2 btn-icon" @click="calendar" v-show="!is_sales" title="Pengiriman dari Proforma">
                                <v-icon>unarchive</v-icon>
                            </v-btn> -->
                        </template>
                    </v-text-field>
                </v-flex>
            </v-layout>
        </v-card-title>
        <v-card-text class="pt-2">
            <v-data-table 
                :headers="headers"
                :items="deliverys"
                :loading="false"
                hide-actions
                class="elevation-1">
                <template slot="items" slot-scope="props">
                    <td class="text-xs-left pa-1" :class="bg_proforma(props.item)" @click="select(props.item)" style="writing-mode:tb-rl;height:auto;transform:scale(-1)">
                        <span class="orange white--text px-2 py-3 d-block" v-if="props.item.delivery_invoice!=0">INVOICE</span>
                        <span class="success pa-2 py-3 d-block white--text" v-if="props.item.delivery_invoice==0&&props.item.delivery_confirm=='Y'">DIKIRIM</span>
                        <span class="grey white--text pa-2 py-3 d-block" v-if="props.item.delivery_confirm=='N'&&props.item.delivery_invoice==0">DRAFT</span>
                    </td>
                    <td class="text-xs-left pa-2" :class="bg_proforma(props.item)" @click="select(props.item)">{{ props.item.delivery_date }}</td>
                    <td class="text-xs-left pa-2" :class="bg_proforma(props.item)" @click="select(props.item)">{{ props.item.delivery_number }}</td>
                    <td class="text-xs-left pa-2" :class="bg_proforma(props.item)" @click="select(props.item)" v-show="!is_sales">
                        {{ props.item.customer_name }}
                        <div v-show="props.item.sales_memos!=''">
                            <div class="caption blue--text" v-for="(m,n) in props.item.sales_memos.split(';')" :key="n" v-show="m!=''"><i>— {{m}}</i></div>
                        </div>
                    </td> 
                    <td class="text-xs-left pa-2" :class="bg_proforma(props.item)" @click="select(props.item)" v-show="!is_sales">
                        {{ props.item.warehouse_name }}
                    </td> 
                    <td class="text-xs-right pa-2" :class="bg_proforma(props.item)" @click="select(props.item)" v-show="!is_sales">
                        {{ one_money(props.item.delivery_total_qty) }}
                    </td>
                    <td class="text-xs-left pa-2" :class="bg_proforma(props.item)" @click="select(props.item)" v-show="!is_sales">
                        {{ props.item.delivery_note }}
                    </td>
                    <!-- <td class="text-xs-center pa-0 px-0" :class="bg_proforma(props.item)" @click="select(props.item)" v-show="!is_sales">
                        <span v-show="props.item.delivery_invoice!=0">
                            <span class="orange--text"><b><i>DITAGIHKAN</i></b></span>
                        </span>
                        <span v-show="props.item.delivery_invoice==0&&props.item.delivery_confirm=='Y'"
                            class="">
                            <v-btn color="grey" small depressed class="white--text btn-icon ma-0 caption" outline disabled>Dikirim</v-btn>
                            <v-btn color="orange" class="btn-icon ma-0" small @click="invoice(props.item)"
                            v-show="props.item.delivery_confirm=='Y'&&props.item.delivery_invoice==0" dark depressed title="Buat Tagihan / Invoice"><v-icon>money</v-icon></v-btn>
                            </span>
                        <span v-show="props.item.delivery_confirm=='N'" class="">
                            <v-btn color="grey" small depressed class="white--text btn-icon ma-0 caption" outline disabled>Draft</v-btn>
                            <v-btn color="success" class="btn-icon ma-0" small @click="confirm(props.item)" 
                            v-show="props.item.delivery_confirm=='N'&&props.item.delivery_invoice==0" depressed title="Kirim Barang"><v-icon>check</v-icon></v-btn>
                        </span>
                    </td>  -->
                    <!-- <td class="text-xs-left pa-2" @click="select(props.item)" v-show="is_sales">
                        {{ props.item.delivery_receipt }}
                    </td> 

                    <td class="text-xs-left pa-2" @click="select(props.item)">
                        {{ props.item.accounts.join(', ') }}
                    </td>                   
                    <td class="text-xs-right pa-2" @click="select(props.item)">Rp {{ one_money(props.item.delivery_debit) }}</td>
                    <td class="text-xs-right pa-2" @click="select(props.item)">Rp {{ one_money(props.item.delivery_credit) }}</td> -->
                    <td class="text-xs-center pa-2 action-buttons" :class="bg_proforma(props.item)" @click="select(props.item)">
                        <v-layout row wrap>

                            <v-flex xs12>
                                <!-- <v-btn color="orange" class="ma-0" small @click="print_do(props.item)" dark v-show="props.item.delivery_invoice>0"><v-icon>print</v-icon> &nbsp; Cetak</v-btn> -->

                                <v-btn color="orange" class="ma-0 btn-icon" small @click="print_do(props.item)" dark><v-icon>print</v-icon></v-btn>
                                <v-btn color="success" class="ma-0" small @click="confirm(props.item)" 
                                    v-show="props.item.delivery_confirm=='N'&&props.item.delivery_invoice==0" depressed title="Kirim Barang"><v-icon >send</v-icon> &nbsp; Kirim</v-btn>

                                <!-- <v-btn color="orange" class="ma-0" small @click="invoice(props.item)"
                                    v-show="props.item.delivery_confirm=='Y'&&props.item.delivery_invoice==0" dark depressed title="Buat Tagihan / Invoice"><v-icon>money</v-icon> &nbsp; Invoice </v-btn> -->

                                <!-- <v-btn color="success" class="btn-icon ma-0" small @click="confirm(props.item)" 
                                    v-show="props.item.delivery_confirm=='N'&&props.item.delivery_invoice==0"><v-icon>check</v-icon></v-btn> -->
                                <!-- <v-btn color="orange" class="btn-icon ma-0" small @click="invoice(props.item)"
                                    v-show="props.item.delivery_confirm=='Y'&&props.item.delivery_invoice==0" dark><v-icon>money</v-icon></v-btn> -->
                                <v-btn color="primary" class="btn-icon ma-0" small @click="edit(props.item)"><v-icon>create</v-icon></v-btn>
                                <v-btn color="red" 
                                    class="btn-icon ma-0" small @click="del(props.item)" 
                                    :disabled="props.item.delivery_invoice!=0"
                                    :dark="props.item.delivery_invoice==0"
                                    ><v-icon>delete</v-icon></v-btn>
                            </v-flex>
                            <!-- <v-flex xs12 pt-1>
                                <v-btn color="orange" class="ma-0" small @click="print_do(props.item)" dark v-show="props.item.delivery_invoice>0"><v-icon>print</v-icon> &nbsp; Cetak</v-btn>
                            </v-flex> -->
                        </v-layout>
                        
                        
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
        
        <v-snackbar
            v-model="snackbar"
            multi-line
            :timeout="3000"
            top
            vertical
            >
            {{ snackbar_text }}
            <v-btn
                color="pink"
                flat
                @click="snackbar = false"
            >
                Tutup
            </v-btn>
        </v-snackbar>
        
        <common-dialog-delete :data="delivery_id" @confirm_del="confirm_del" v-if="dialog_delete"></common-dialog-delete>
        <common-dialog-confirm :data="delivery_id" @confirm="confirm_post" v-if="dialog_confirm" :text="text_post"></common-dialog-confirm>
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
.action-buttons button:not(.btn-icon) { min-width: 101px }
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
                    text: "TANGGAL",
                    align: "left",
                    sortable: false,
                    width: "10%",
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
                    text: "DARI VENDOR",
                    align: "left",
                    sortable: false,
                    width: "17%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "GUDANG ASAL",
                    align: "left",
                    sortable: false,
                    width: "14%",
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
                    text: "CATATAN",
                    align: "left",
                    sortable: false,
                    width: "21%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                // {
                //     text: "STATUS",
                //     align: "center",
                //     sortable: false,
                //     width: "8%",
                //     class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                // },
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
                    width: "17%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                }
            ],

            report_url: ''
        }
    },

    computed : {
        deliverys () {
            return this.$store.state.delivery.deliverys
        },

        dialog_delete () {
            return this.$store.state.dialog_delete
        },

        dialog_confirm () {
            return this.$store.state.dialog_confirm
        },

        delivery_id () {
            return this.$store.state.delivery.selected_delivery.M_JournalID
        },

        query : {
            get () { return this.$store.state.delivery.search },
            set (v) { this.$store.commit('delivery/update_search', v) }
        },

        curr_page : {
            get () { return this.$store.state.delivery.current_page },
            set (v) { this.$store.commit('delivery/update_current_page', v) }
        },

        xtotal_page () {
            return this.$store.state.delivery.total_delivery_page
        },

        text_post () {
            let j = this.$store.state.delivery.selected_delivery
            return "Apakah anda yakin akan melakukan Posting Jurnal tersebut ?"
        },

        s_date : {
            get () { return this.$store.state.delivery.s_date },
            set (v) { this.$store.commit('delivery/set_common', ['s_date', v]) }
        },

        e_date : {
            get () { return this.$store.state.delivery.e_date },
            set (v) { this.$store.commit('delivery/set_common', ['e_date', v]) }
        },

        title() {
            return "PENGIRIMAN BARANG"
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

        snackbar : {
            get () { return this.$store.state.delivery.snackbar },
            set (v) { this.$store.commit('delivery/set_common', ['snackbar', v]) }
        },

        snackbar_text () {
            return this.$store.state.delivery.snackbar_text
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        add () {
            let x = []
            x.push(JSON.parse(JSON.stringify(this.$store.state.delivery_new.detail_default)))
            this.$store.commit('delivery_new/set_common', ['edit', false])
            this.$store.commit('delivery_new/set_common', ['single', false])
            this.$store.commit('delivery_new/set_common', ['delivery_date', this.$store.state.delivery_new.current_date.split('-').reverse().join('-')])
            this.$store.commit('delivery_new/set_common', ['delivery_note', ''])
            this.$store.commit('delivery_new/set_common', ['delivery_send_note', ''])
            this.$store.commit('delivery_new/set_common', ['delivery_memo', ''])
            this.$store.commit('delivery_new/set_common', ['delivery_number', ''])
            this.$store.commit('delivery_new/set_common', ['delivery_ref_number', ''])
            this.$store.commit('delivery_new/set_common', ['delivery_proforma', 'N'])
            this.$store.commit('delivery_new/set_selected_customer', null)
            this.$store.commit('delivery_new/set_selected_warehouse', null)
            this.$store.commit('delivery_new/set_selected_staff', null)
            this.$store.commit('delivery_new/set_selected_deliverytype', null)
            this.$store.commit('delivery_new/set_items', [])
            this.$store.commit('delivery_new/set_details', x)
            this.$store.commit('delivery_new/set_common', ['dialog_new', true])
        },

        edit (x) {
            if (this.$store.state.delivery_new.customers.length < 1) {
                this.$store.commit('set_dialog_progress', true)
                this.$store.dispatch('delivery_new/search_customer').then(() => {
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
            this.$store.commit('delivery_new/set_common', ['edit', true])
            this.$store.commit('delivery_new/set_common', ['delivery_id', sc.delivery_id])
            this.$store.commit('delivery_new/set_common', ['delivery_date', sc.delivery_date])
            this.$store.commit('delivery_new/set_common', ['delivery_note', sc.delivery_note])
            this.$store.commit('delivery_new/set_common', ['delivery_send_note', sc.delivery_send_note])
            this.$store.commit('delivery_new/set_common', ['delivery_memo', sc.delivery_memo])
            this.$store.commit('delivery_new/set_common', ['delivery_number', sc.delivery_number])
            this.$store.commit('delivery_new/set_common', ['delivery_ref_number', sc.delivery_ref_number])
            this.$store.commit('delivery_new/set_common', ['delivery_proforma', sc.delivery_proforma])
            this.$store.commit('delivery_new/set_common', ['delivery_proforma', sc.delivery_proforma])
            this.$store.commit('delivery_new/set_object', ['invoices', sc.invoices])
            
            this.$store.commit('delivery_new/set_object', ['addresses', [sc.delivery_address]])
            this.$store.commit('delivery_new/set_object', ['selected_address', sc.delivery_address])

            this.$store.commit('delivery_new/set_selected_customer', null)
            this.$store.commit('delivery_new/set_selected_warehouse', null)
            this.$store.commit('delivery_new/set_selected_staff', null)
            for (let v of this.$store.state.delivery_new.customers)
                if (v.customer_id == sc.customer_id)
                    this.$store.commit('delivery_new/set_selected_customer', v)
            for (let v of this.$store.state.delivery_new.staffs)
                if (v.staff_id == sc.delivery_staff)
                    this.$store.commit('delivery_new/set_selected_staff', v)
            for (let v of this.$store.state.delivery_new.warehouses)
                if (v.warehouse_id == sc.warehouse_id)
                    this.$store.commit('delivery_new/set_selected_warehouse', v)

            for (let vv of this.$store.state.delivery_new.deliverytypes)
                if (vv.deliverytype_id == sc.delivery_type)
                    this.$store.commit('delivery_new/set_object', ['selected_deliverytype', vv])

            for (let vv of this.$store.state.sales_new.expeditions)
                if (vv.expedition_id == sc.delivery_expedition)
                    this.$store.commit('delivery_new/set_object', ['selected_expedition', vv])

            let details = sc.items
            this.$store.commit('delivery_new/set_details', details)
            this.$store.dispatch('delivery_new/search_item')

            this.$store.commit('delivery_new/set_common', ['dialog_new', true])
        },

        del (x) {
            this.select(x)
            this.$store.commit('set_dialog_delete', true)
        },

        confirm_del (x) {
            this.$store.dispatch('delivery/del', {id:x.data})
        },

        post (x) {
            this.select(x)
            this.$store.commit('set_dialog_confirm', true)
        },

        confirm_post (x) {
            this.$store.dispatch('delivery/post', {id:x.data})
        },

        select (x) {
            this.$store.commit('delivery/set_selected_delivery', x)
        },

        search () {
            return this.$store.dispatch('delivery/search', {})
        },

        change_page(x) {
            this.curr_page = x
            this.$store.dispatch('delivery/search', {})
        },

        change_s_date(x) {
            this.$store.commit('delivery/set_common', ['s_date', x.new_date])
            this.$store.dispatch('delivery/search')
        },

        change_e_date(x) {
            this.$store.commit('delivery/set_common', ['e_date', x.new_date])
            this.$store.dispatch('delivery/search')
        },

        confirm (x) {
            this.select(x)
            this.$store.commit('delivery/set_common', ['dialog_confirm', true])
            // this.$store.dispatch('delivery/confirm')
        },

        calendar () {
            this.$store.commit('delivery/set_common', ['dialog_calendar', true])
        },

        invoice (x) {
            this.select(x)
            this.$store.commit('delivery/set_common', ['dialog_invoice', true])
            // this.$store.dispatch('delivery/invoice')
        },

        print_do (x) {
            this.select(x)
            let so = x
            this.report_url = this.$store.state.delivery.URL+"report/one_sales_001?id="+so.delivery_id

            for (let ps of this.$store.state.system.page_sizes)
                if (ps.id=='A5L')
                    this.$store.commit('set_selected_page_size', ps)

            this.$store.commit('set_dialog_print', true)
        },

        proforma () {
            this.$store.commit('delivery_new/set_common', ['delivery_proforma', 'Y'])
            this.$store.commit('delivery_new/set_common', ['dialog_proforma', true])

            this.$store.commit('delivery_new/set_selected_proforma', null)
            this.$store.commit('delivery_new/set_selected_deliverytype', null)
            this.$store.commit('delivery_new/set_common', ['delivery_send_note', ''])
            this.$store.commit('delivery_new/set_common', ['delivery_note', ''])
            this.$store.commit('delivery_new/set_common', ['delivery_memo', ''])
            this.$store.commit('delivery_new/set_selected_staff', null)
            this.$store.commit('delivery_new/set_selected_warehouse', null) 
        },

        bg_proforma (x) {
            // console.log(x)
            if (x.delivery_proforma=='Y')
                return 'amber lighten-4'
            return 'white'
        }
    },

    mounted () {
        if (this.is_sales)
            this.headers[2].text="NOMOR INVOICE"
    }
}
</script>