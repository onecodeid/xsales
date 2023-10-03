<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0">
            <v-layout row wrap>
                <v-flex xs7>
                    <h3 class="display-1 font-weight-light zalfa-text-title">DAFTAR RETUR</h3>
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

                            <v-btn color="success" class="ma-0 ml-2 btn-icon" @click="add" @contextmenu="show_add_menu($event)">
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
                :items="returs"
                :loading="false"
                hide-actions
                class="elevation-1">
                <template slot="items" slot-scope="props">
                    <!-- <td class="text-xs-left pa-1" @click="select(props.item)" style="writing-mode:tb-rl;height:auto;transform:scale(-1)"> -->
                        <!-- <span class="orange white--text px-2 py-3 d-block" v-if="props.item.retur_lunas=='N'&&props.item.retur_paid>0">LUNAS</span>
                        <span class="success pa-2 py-3 d-block white--text" v-if="props.item.retur_lunas=='Y'">SEBAGIAN</span>
                        <span class="grey white--text pa-2 py-3 d-block" v-if="props.item.retur_lunas=='N'&&props.item.retur_paid==0">BARU</span> -->
                        <!-- <span class="grey white--text pa-2 py-3 d-block">RETUR</span> -->
                    <!-- </td> -->
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.retur_date }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.retur_number }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">
                        {{ props.item.customer_name }}
                    </td> 
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.invoice_number }}</td>
                    
                    <td class="text-xs-left pa-2" @click="select(props.item)">
                        {{ props.item.retur_note }}
                        <!-- <br /> -->
                        <!-- <span class="cyan--text" v-show="props.item.retur_memo!=''"><i>memo : {{ props.item.retur_memo}}</i></span> -->
                    </td> 
                    <td class="text-xs-right pa-2" @click="select(props.item)">
                        <span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.retur_total)) }}</b>
                    </td>
                    <!-- <td class="text-xs-left pa-2" @click="select(props.item)" v-show="!is_sales">
                        <v-btn color="success" small class="ma-0" block v-show="props.item.retur_lunas=='Y'">Lunas</v-btn>
                        <v-btn color="orange" small class="ma-0" block dark v-show="props.item.retur_lunas=='N'&&props.item.retur_paid>0">Bayar Sebagian</v-btn>
                        <v-btn color="grey" small class="ma-0" block dark v-show="props.item.retur_lunas=='N'&&props.item.retur_paid==0">Belum Dibayar</v-btn>
                    </td> -->
                    <td class="text-xs-center pa-0" @click="select(props.item)">
                        <v-btn color="cyan" class="btn-icon ma-0 mr-1" small @click="memo(props.item)" title="Memo kredit" dark><v-icon>list</v-icon></v-btn>
                        <v-btn color="primary" class="btn-icon ma-0" small @click="edit(props.item)" title="Ubah retur"><v-icon>create</v-icon></v-btn>
                        <!-- <div class="row">
                            <div class="col-12">
                                <v-btn color="orange" class="btn-icon ma-0" small @click="print_retur(props.item)" dark title="Cetak retur"><v-icon>print</v-icon></v-btn>
                                <v-btn color="cyan" class="btn-icon ma-0" small @click="journal(props.item)" dark v-show="props.item.journal_id!=0" title="Jurnal"><v-icon>assignment</v-icon></v-btn>
                            </div>
                            <div class="col-12 mt-1">
                                <v-btn color="primary" class="btn-icon ma-0" small @click="edit(props.item)" title="Ubah retur"><v-icon>create</v-icon></v-btn>
                                <v-btn color="red" 
                                    class="btn-icon ma-0" small @click="del(props.item)" dark title="Hapus retur"><v-icon>delete</v-icon></v-btn>
                            </div>
                        </div> -->
                        
                        
                        
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
        
        <v-menu
            v-model="showAddMenu"
            :position-x="x"
            :position-y="y"
            absolute
            offset-y
        >
            <v-list>
            <v-list-tile
                v-for="(item, index) in add_menus"
                :key="index"
                @click="item.menuAction(item.title)"
            >

                <v-list-tile-title>{{ item.title }}</v-list-tile-title>
            </v-list-tile>
            </v-list>
        </v-menu>

        <!-- <common-dialog-delete :data="retur_id" @confirm_del="confirm_del" v-if="dialog_delete"></common-dialog-delete>
        <common-dialog-confirm :data="retur_id" @confirm="confirm_post" v-if="dialog_confirm" :text="text_post"></common-dialog-confirm>
        <common-dialog-print :report_url="report_url" v-if="dialog_report"></common-dialog-print> -->
        <finance-memo-new-dialog></finance-memo-new-dialog>
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
let t = Math.random() * 1e10
module.exports = {
    components : {
        "common-dialog-delete" : httpVueLoader("../../common/components/common-dialog-delete.vue"),
        "common-dialog-confirm" : httpVueLoader("../../common/components/common-dialog-confirm.vue"),
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        "common-dialog-print" : httpVueLoader("../../common/components/common-dialog-print-size.vue"),
        "finance-memo-new-dialog" : httpVueLoader("../../finance-memo/components/finance-memo-new-dialog.vue?t="+t)
    },

    data () {
        return {
            headers: [
                // {
                //     text: "",
                //     align: "left",
                //     sortable: false,
                //     width: "3%",
                //     class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                // },
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
                    text: "CUSTOMER",
                    align: "left",
                    sortable: false,
                    width: "23%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "NOMOR INVOICE",
                    align: "left",
                    sortable: false,
                    width: "15%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "ALASAN RETUR",
                    align: "left",
                    sortable: false,
                    width: "20%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "TOTAL RETUR",
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
                { title: 'Buka', menuAction: action => {
                    window.location.replace("../new")
                }},
                { title: 'Buka di Tab Baru', menuAction: action => { 
                    window.open("../new")

                 }}
            ],
            showAddMenu: false,
            x: 0,
            y: 0
        }
    },

    computed : {
        returs () {
            return this.$store.state.retur.returs
        },

        // dialog_delete () {
        //     return this.$store.state.dialog_delete
        // },

        // dialog_confirm () {
        //     return this.$store.state.dialog_confirm
        // },

        // retur_id () {
        //     return this.$store.state.retur.selected_retur.M_JournalID
        // },

        query : {
            get () { return this.$store.state.retur.search },
            set (v) { this.setObject('search', v) }
        },

        curr_page : {
            get () { return this.$store.state.retur.current_page },
            set (v) { this.setObject('current_page', v) }
        },

        xtotal_page () {
            return this.$store.state.retur.total_retur_page
        },

        s_date : {
            get () { return this.$store.state.retur.s_date },
            set (v) { this.$store.commit('retur/set_common', ['s_date', v]) }
        },

        e_date : {
            get () { return this.$store.state.retur.e_date },
            set (v) { this.$store.commit('retur/set_common', ['e_date', v]) }
        },

        title() {
            return "DAFTAR RETUR"
        },

        // dialog_report : {
        //     get () { return this.$store.state.dialog_print },
        //     set (v) { this.$store.commit('set_dialog_print', v) }
        // }
    },

    methods : {
        setObject(x, y) {
            this.$store.commit('retur/set_object', [x, y])
        },

        one_money (x) {
            return window.one_money(x)
        },

        add () {
            window.location.replace('../new/')
            // let x = []
            // x.push(JSON.parse(JSON.stringify(this.$store.state.retur_new.detail_default)))
            // this.$store.commit('retur_new/set_common', ['edit', false])
            // this.$store.commit('retur_new/set_common', ['retur_date', this.$store.state.retur_new.current_date])
            // this.$store.commit('retur_new/set_common', ['retur_due_date', this.$store.state.retur_new.current_date])
            // this.$store.commit('retur_new/set_common', ['retur_note', ''])
            // this.$store.commit('retur_new/set_common', ['retur_number', ''])
            // this.$store.commit('retur_new/set_common', ['retur_disc', 0])
            // this.$store.commit('retur_new/set_common', ['retur_discrp', 0])
            // this.$store.commit('retur_new/set_common', ['retur_disctype', 'P'])
            // this.$store.commit('retur_new/set_common', ['retur_shipping', 0])
            // this.$store.commit('retur_new/set_common', ['retur_memo', ''])
            // this.$store.commit('retur_new/set_common', ['retur_address', ''])
            // this.$store.commit('retur_new/set_retur_dps', [])
            // this.$store.commit('retur_new/set_selected_customer', null)
            // // this.$store.commit('retur_new/set_selected_warehouse', null)
            // this.$store.commit('retur_new/set_selected_term', null)
            // this.$store.commit('retur_new/set_items', [])
            // this.$store.commit('retur_new/set_details', x)
            // this.$store.commit('retur_new/set_common', ['dialog_delivery', true])
            return
        },

        edit (x) {
            this.select(x)
            window.location.replace("../new/?id="+x.retur_id)
            // if (this.$store.state.retur_new.customers.length < 1) {
            //     this.$store.commit('set_dialog_progress', true)
            //     this.$store.dispatch('retur_new/search_customer').then(() => {
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
            this.$store.commit('retur_new/set_common', ['edit', true])
            this.$store.commit('retur_new/set_common', ['retur_id', sc.retur_id])
            this.$store.commit('retur_new/set_common', ['retur_date', sc.retur_date])
            // this.$store.commit('retur_new/set_common', ['retur_due_date', 
            //     moment(sc.retur_date, "YYYY-MM-DD").add(Math.round(sc.retur_term), 'days').format('DD-MM-YYYY')])
            this.$store.commit('retur_new/set_common', ['retur_due_date', sc.retur_due_date.split('-').reverse().join('-')])
            this.$store.commit('retur_new/set_common', ['retur_note', sc.retur_note])
            this.$store.commit('retur_new/set_common', ['retur_memo', sc.retur_memo])
            this.$store.commit('retur_new/set_common', ['retur_number', sc.retur_number])
            this.$store.commit('retur_new/set_common', ['retur_disc', sc.retur_disc])
            this.$store.commit('retur_new/set_common', ['retur_discrp', sc.retur_discrp])
            this.$store.commit('retur_new/set_common', ['retur_dp', sc.retur_dp])
            this.$store.commit('retur_new/set_common', ['retur_shipping', sc.retur_shipping])
            this.$store.commit('retur_new/set_common', ['retur_proforma', sc.retur_proforma])
            this.$store.commit('retur_new/set_common', ['sales_name', sc.sales.staff_short])
            this.$store.commit('retur_new/set_retur_dps', sc.retur_dps) 

            this.$store.commit('retur_new/set_common', ['retur_disctype', 
                Math.round(sc.retur_discrp)>0?'R':'P'])

            this.$store.commit('retur_new/set_selected_customer', null)
            // this.$store.commit('retur_new/set_selected_warehouse', null)
            for (let v of this.$store.state.retur_new.customers)
                if (v.customer_id == sc.customer_id)
                    this.$store.commit('retur_new/set_selected_customer', v)
            
            for (let v of this.$store.state.retur_new.terms)
                if (v.term_id == sc.retur_term)
                    this.$store.commit('retur_new/set_selected_term', v)

            let details = sc.details
            let dfl = JSON.parse(JSON.stringify(this.$store.state.retur_new.detail_default))
            this.$store.dispatch('retur_new/search_dp')

            if (sc.retur_proforma == 'Y') {
                let dx = []
                for (let d in details) {
                    dx.push(dfl)
                    dx[d].delivery = details[d].sales
                    dx[d].items = details[d].items
                }
                this.$store.commit('retur_new/set_details', dx)
                // this.$store.dispatch('retur_new/search_item')
                this.$store.commit('retur_new/set_common', ['dialog_proforma', true])
            }
            else {
                
                // let acc = this.$store.state.retur_new.accounts
                for (let d of details)
                    d.delivery.items = d.items
                //     for (let a of acc)
                //         if (a.account_id == d.account)
                //             d.account = a

                this.$store.commit('retur_new/set_details', details)
                this.$store.commit('retur_new/set_common', ['dialog_new', true])
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
            this.$store.commit('retur_new/set_common', ['retur_address', address])
            
        },

        del (x) {
            this.select(x)
            this.$store.commit('set_dialog_delete', true)
        },

        confirm_del (x) {
            this.$store.dispatch('retur/del', {id:x.data})
        },

        post (x) {
            this.select(x)
            this.$store.commit('set_dialog_confirm', true)
        },

        confirm_post (x) {
            this.$store.dispatch('retur/post', {id:x.data})
        },

        select (x) {
            this.setObject('selected_retur', x)
        },

        search () {
            return this.$store.dispatch('retur/search', {})
        },

        change_page(x) {
            this.curr_page = x
            this.$store.dispatch('retur/search', {})
        },

        change_s_date(x) {
            this.$store.commit('retur/set_common', ['s_date', x.new_date])
            this.$store.dispatch('retur/search')
        },

        change_e_date(x) {
            this.$store.commit('retur/set_common', ['e_date', x.new_date])
            this.$store.dispatch('retur/search')
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

        print_retur (x) {
            this.select(x)
            let so = x
            this.report_url = this.$store.state.retur.URL+"report/one_sales_002?id="+so.retur_id
            this.$store.commit('set_dialog_print', true)
        },

        bg_proforma (x) {
            if (x.retur_proforma=='Y')
                return 'amber lighten-4'
            return 'white'
        },

        proforma_edit (z) {

            let sc = z
            this.$store.commit('retur_new/set_common', ['retur_proforma', 'Y'])
            this.$store.dispatch('retur_new/search_item')
            
            this.$store.commit('retur_new/set_common', ['dialog_proforma', true])
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

        memo (x) {
            // this.$store.commit("memo/set_object", ["memo_id", x.memo_id])
            // this.$store.dispatch("memo/search_id").then(res => {
                // this.$store.commit("memoNew/set_object", ["dialog_new", true])
                // this.$store.commit("memoNew/set_object", ["edit", true])
                // this.$store.commit("memoNew/set_object", ["memo_id", res.memo_id])
                // this.$store.commit("memoNew/set_object", ["memo_number", res.memo_number])

                // this.$store.commit("memoNew/set_object", ["memo_note", res.memo_note])
                // this.$store.commit("memoNew/set_object", ["memo_amount", res.memo_amount])
                // this.$store.commit("memoNew/set_object", ["memo_used", res.memo_used])
                // this.$store.commit("memoNew/set_object", ["memo_refunded", res.memo_refunded])
                // this.$store.commit("memoNew/set_object", ["invoice_number", res.invoice_number])
                

                // this.setNewObject("selected_customer", {customer_name:x.customer_name})
            // })
        }
    },

    mounted () {
        this.$store.dispatch('retur/search')
    }
}
</script>