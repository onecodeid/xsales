<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0">
            <v-layout row wrap>
                <v-flex xs9>
                    <h3 class="display-1 font-weight-light zalfa-text-title">KAS & BANK</h3>
                </v-flex>
                <v-flex xs3 class="text-xs-right">

                    <!-- <v-text-field
                        solo
                        hide-details
                        placeholder="Pencarian" v-model="query"
                        @change="search"
                    >
                        <template v-slot:append-outer>
                            <v-btn color="primary" class="ma-0 btn-icon" @click="search">
                                <v-icon>search</v-icon>
                            </v-btn>      

                            <v-btn color="success" class="ma-0 ml-2 btn-icon" @click="add">
                                <v-icon>add</v-icon>
                            </v-btn>  
                        </template>
                    </v-text-field> -->
                </v-flex>
            </v-layout>
        </v-card-title>
        <v-card-text class="pt-2">
            <finance-cash-card></finance-cash-card>
            <v-data-table 
                :headers="headers"
                :items="accounts"
                :loading="false"
                hide-actions
                class="elevation-1">
                <template slot="items" slot-scope="props">
                    
                    <td class="text-xs-left pa-2" @click="select(props.item)"
                        v-show="props.item.parent_code==''">{{ props.item.M_AccountCode }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)"
                        v-show="props.item.parent_code!=''">└─ {{ props.item.M_AccountCode }}</td>

                    <td class="text-xs-left pa-2" @click="select(props.item)"
                        :class="{'pl-4':props.item.parent_code!=''}">{{ props.item.M_AccountName }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.last_date }}</td>
                    <td class="text-xs-right pa-2" @click="select(props.item)">Rp <b>{{ one_money(last_balance(props.item)) }}</b></td>
                    
                    
                    <!-- <td class="text-xs-center pa-0" @click="select(props.item)">
                        <v-btn color="primary" class="btn-icon ma-0" small @click="edit(props.item)"><v-icon>create</v-icon></v-btn>
                        <v-btn color="red" dark class="btn-icon ma-0" small @click="del(props.item)"><v-icon>delete</v-icon></v-btn>
                    </td> -->
                    <td class="text-xs-center pa-0" @click="select(props.item)" v-show="props.item.parent!=true">
                        <v-btn color="success" @contextmenu="show($event, props.item)" @click="show_list(props.item)">Detail</v-btn>
                        <v-btn color="success" class="ma-0" depressed @click="select(props.item); cash_me(journaltypes[0])/*payment_invoice(props.item)*/">
                            {{journaltypes[0]?journaltypes[0].journaltype_name:''}}
                        </v-btn>
                        
                        <v-menu offset-y left>
                            <template v-slot:activator="{ on }">
                                <v-btn color="success btn-icon" class="ma-0" depressed v-on="on"><v-icon>keyboard_arrow_down</v-icon></v-btn>
                            </template>
                            <v-list>
                                <v-list-tile
                                v-for="(item, index) in journaltypes"
                                :key="index"
                                @click="select(props.item); cash_me(item);"
                                v-show="index>0"
                                >
                                <v-list-tile-title>{{ item.journaltype_name }}</v-list-tile-title>
                                </v-list-tile>
                            </v-list>
                        </v-menu>
                        
                    </td>
                    <td class="text-xs-center pa-0" @click="select(props.item)" v-show="!!props.item.parent"></td>

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

        <common-dialog-delete :data="account_id" @confirm_del="confirm_del" v-if="dialog_delete"></common-dialog-delete>
        <v-menu
            v-model="showMenu"
            :position-x="x"
            :position-y="y"
            absolute
            offset-y
        >
            <v-list>
            <v-list-tile
                v-for="(item, index) in items"
                :key="index"
                @click="item.menuAction(item.title)"
            >

                <v-list-tile-title>{{ item.title }}</v-list-tile-title>
            </v-list-tile>
            </v-list>
        </v-menu>
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
        "finance-cash-card" : httpVueLoader("./finance-cash-card.vue")
    },

    data () {
        return {
            headers: [
                {
                    text: "KODE",
                    align: "left",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "NAMA",
                    align: "left",
                    sortable: false,
                    width: "40%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "TRANSAKSI TERAKHIR",
                    align: "left",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "SALDO TERAKHIR",
                    align: "right",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "ACTION",
                    align: "center",
                    sortable: false,
                    width: "30%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                }
            ],
            menus: [
                { title: 'Penerimaan Uang', code: 'M.01' },
                { title: 'Pembayaran / Pengeluaran', code: 'M.02' }
            ],
            items: [
                { title: 'Buka', menuAction: action => {
                    this.show_list_x()
                }},
                { title: 'Buka di Tab Baru', menuAction: action => { 
                    window.open(window.location.href.replace(/\?[a-z0-9\=]+/, '') + '?selected=' +
                        this.selected_account.M_AccountID)

                 }}
            ],
            showMenu: false,
            x: 0,
            y: 0
        }
    },

    computed : {
        accounts () {
            return this.$store.state.cash.accounts
        },

        selected_account () {
            return this.$store.state.cash.selected_account
        },

        dialog_delete () {
            return this.$store.state.dialog_delete
        },

        account_id () {
            return this.$store.state.cash.selected_account.M_AccountID
        },

        query : {
            get () { return this.$store.state.cash.search },
            set (v) { this.$store.commit('cash/update_search', v) }
        },

        curr_page : {
            get () { return this.$store.state.cash.current_page },
            set (v) { this.$store.commit('cash/update_current_page', v) }
        },

        xtotal_page () {
            return this.$store.state.cash.total_account_page
        },

        journaltypes () {
            return this.$store.state.cash.journaltypes
        },

        selected_journaltype : {
            get () { return this.$store.state.cash.selected_journaltype },
            set (v) { this.$store.commit('cash/set_selected_journaltype', v) }
        },

        snackbar : {
            get () { return this.$store.state.cash.snackbar },
            set (v) { this.$store.commit('cash/set_common', ['snackbar', v]) }
        },

        snackbar_text () {
            return this.$store.state.cash.snackbar_text
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        add () {
            this.$store.commit('cash_new/set_common', ['edit', false])
            this.$store.commit('cash_new/set_common', ['account_name', ''])
            this.$store.commit('cash_new/set_common', ['account_code', ''])

            this.$store.commit('cash_invoice/set_selected_customer', null)
            this.$store.commit('cash_invoice/set_selected_bank', null)
            this.$store.commit('cash_invoice/set_selected_bank_account', null)

            let d = this.$store.state.cash_invoice.current_date
            this.$store.commit('cash_invoice/set_common', ['payment_date', d])
            this.$store.commit('cash_invoice/set_common', ['giro_date', d])
            this.$store.commit('cash_invoice/set_common', ['giro_number', ''])
            this.$store.commit('cash_invoice/set_common', ['transfer_date', d])
            this.$store.commit('cash_invoice/set_common', ['payment_note', ''])

            let z = this.selected_account
            for (let ba of this.$store.state.cash_invoice.bank_accounts) {
                if (ba.account_id == z.bank_account_id)
                    this.$store.commit('cash_invoice/set_selected_bank_account', ba)
            }

            this.$store.commit('cash_new/set_common', ['dialog_new', true])
        },

        edit (x) {
            this.select(x)
            let sc = x
            this.$store.commit('cash_new/set_common', ['edit', true])
            this.$store.commit('cash_new/set_common', ['account_name', sc.M_AccountName])
            this.$store.commit('cash_new/set_common', ['account_code', sc.M_AccountCode])

            this.$store.commit('cash_new/set_common', ['dialog_new', true])
        },

        del (x) {
            this.select(x)
            this.$store.commit('set_dialog_delete', true)
        },

        confirm_del (x) {
            this.$store.dispatch('cash/del', {id:x.data})
        },

        select (x) {
            this.$store.commit('cash/set_selected_account', x)
        },

        search () {
            return this.$store.dispatch('cash/search', {})
        },

        change_page(x) {
            this.curr_page = x
            this.$store.dispatch('cash/search', {})
        },

        payment_invoice() {
            let x = []
            x.push(JSON.parse(JSON.stringify(this.$store.state.cash_invoice.detail_default)))
            this.$store.commit('cash_invoice/set_details', x)

            // this.$store.commit('cash_invoice/set_selected_bank_account', null);
            // for (let ba of this.$store.state.cash_invoice.bank_accounts) {
            //     if (ba.account_id == this.selected_account.bank_account_id)
            //         this.$store.commit('cash_invoice/set_selected_bank_account', ba);
            // }
            
            this.$store.commit('cash_invoice/set_common', ['edit', false])
            this.$store.commit('cash_invoice/set_common', ['dialog_new', true])
        },

        payment_bill() {
            let x = []
            x.push(JSON.parse(JSON.stringify(this.$store.state.cash_bill.detail_default)))
            this.$store.commit('cash_bill/set_details', x)
            
            this.$store.commit('cash_invoice/set_common', ['edit', false])
            this.$store.commit('cash_invoice/set_common', ['dialog_new', true])
        },

        cash_receive() {
            let x = []
            x.push(JSON.parse(JSON.stringify(this.$store.state.cash_receive.detail_default)))
            this.$store.commit('cash_receive/set_details', x)
            this.$store.commit('cash_receive/set_common', ["out", false])
            
            this.$store.commit('cash_invoice/set_common', ['edit', false])
            this.$store.commit('cash_invoice/set_common', ['dialog_new', true])
        },

        cash_out() {
            let x = []
            x.push(JSON.parse(JSON.stringify(this.$store.state.cash_receive.detail_default)))
            this.$store.commit('cash_receive/set_details', x)
            this.$store.commit('cash_receive/set_common', ["out", true])
            
            this.$store.commit('cash_invoice/set_common', ['edit', false])
            this.$store.commit('cash_invoice/set_common', ['dialog_new', true])
        },

        show_list(x) {
            this.select(x)
            this.$store.commit('set_selected_tab', 2)
            this.$store.dispatch('journal_detail/search')

            return false
        },

        show_list_x() {
            return this.show_list(this.selected_account)
        },

        menu_open(x) {
            if (x == 'M.01') {
                this.$store.commit('cash_receive/set_common', ['dialog_new', true])
                let x = []
                let d = this.$store.state.cash_receive.current_date
                x.push(JSON.parse(JSON.stringify(this.$store.state.cash_receive.detail_default)))
                this.$store.commit('cash_receive/set_details', x)
            }

            return
        },

        cash_me (x) {
            this.selected_journaltype = x

            this.$store.commit('cash_new/set_common', ['edit', false])
            this.$store.commit('cash_new/set_common', ['account_name', ''])
            this.$store.commit('cash_new/set_common', ['account_code', ''])

            this.$store.commit('cash_invoice/set_selected_customer', null)
            this.$store.commit('cash_invoice/set_selected_bank', null)
            this.$store.commit('cash_invoice/set_selected_bank_account', null)

            let d = this.$store.state.cash_invoice.current_date
            this.$store.commit('cash_invoice/set_common', ['payment_date', d])
            this.$store.commit('cash_invoice/set_common', ['giro_date', d])
            this.$store.commit('cash_invoice/set_common', ['giro_number', ''])
            this.$store.commit('cash_invoice/set_common', ['transfer_date', d])
            this.$store.commit('cash_invoice/set_common', ['payment_note', ''])
            this.$store.commit('cash_invoice/set_object', ['selected_tags', []])

            let z = this.selected_account
            for (let ba of this.$store.state.cash_invoice.bank_accounts) {
                if (ba.account_id == z.bank_account_id) {
                    this.$store.commit('cash_invoice/set_selected_bank_account', ba)
                    this.$store.commit('cash_invoice/set_common', ['account_number', "No. "+ba.account_number])
                }
                    
            }

            if (x.journaltype_code == 'J.11')
                this.payment_invoice()
            else if (x.journaltype_code == 'J.12')
                this.payment_bill()
            else if (x.journaltype_code == 'J.13')
                this.cash_receive()
            else if (x.journaltype_code == 'J.14')
                this.cash_out()
        },

        last_balance(x) {
            let d = x.balance_debit - x.balance_credit
            if (x.account_side == 'P') 
                d = 0 - d

            return d
        },
        show (e, v) {
            this.select(v)
            e.preventDefault()
            this.showMenu = false
            this.x = e.clientX
            this.y = e.clientY
            this.$nextTick(() => {
                this.showMenu = true
            })
        }
    }
}
</script>