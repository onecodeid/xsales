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
                :items="dps"
                :loading="false"
                hide-actions
                class="elevation-1">
                <template slot="items" slot-scope="props">
                    
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.dp_date }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.dp_number }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)" v-show="!is_sales">
                        {{ props.item.vendor_name }}
                    </td> 
                    <td class="text-xs-left pa-2" @click="select(props.item)" v-show="!is_sales">
                        {{ props.item.paymenttype_name }}
                    </td> 
                    
                    <td class="text-xs-left pa-2" @click="select(props.item)" v-show="!is_sales">
                        {{ props.item.dp_note }}
                    </td> 
                    <td class="text-xs-right pa-2" @click="select(props.item)" v-show="!is_sales">
                        <span class="grey--text caption">Rp</span> <b>{{ one_money(props.item.dp_amount) }}</b>
                    </td>
                    <td class="text-xs-left pa-2" @click="select(props.item)" v-show="!is_sales">
                        <div class="fill-height orange white--text text-xs-center" v-if="props.item.dp_acc=='N'" style="display:flex;align-items:center;justify-content:center;">BELUM KONFIRMASI</div>
                        <div v-show="props.item.dp_acc=='Y'" class="fill-height">
                            <div class="fill-height grey white--text text-xs-center" v-if="props.item.dp_used==0" style="display:flex;align-items:center;justify-content:center;">BELUM DIPAKAI</div>
                            <div class="fill-height orange white--text text-xs-center" v-if="props.item.dp_used>0&&props.item.dp_unused>0" style="display:flex;align-items:center;justify-content:center;">SEBAGIAN</div>
                            <div class="fill-height green white--text text-xs-center" v-if="props.item.dp_used>0&&props.item.dp_unused==0" style="display:flex;align-items:center;justify-content:center;">SUDAH DIPAKAI</div>
                        </div>
                        
                        <!-- <v-btn color="grey" dark small class="ma-0" block v-show="props.item.dp_used==0">Belum Dipakai</v-btn>
                        <v-btn color="orange" small class="ma-0" block dark v-show="props.item.dp_used>0&&props.item.dp_unused>0">Dipakai sebagian</v-btn>
                        <v-btn color="green" small class="ma-0" block dark v-show="props.item.dp_used>0&&props.item.dp_unused==0">Terpakai seluruhnya</v-btn> -->
                        <!-- <span v-show="props.item.dp_lunas=='Y'">Lunas</span>
                        <span v-show="props.item.dp_lunas=='N'&&props.item.dp_paid>0">Dibayar sebagian</span>
                        <span v-show="props.item.dp_lunas=='N'&&props.item.dp_paid==0">Belum dibayar</span> -->
                    </td>
                    <!-- <td class="text-xs-left pa-2" @click="select(props.item)" v-show="is_sales">
                        {{ props.item.dp_receipt }}
                    </td> 

                    <td class="text-xs-left pa-2" @click="select(props.item)">
                        {{ props.item.accounts.join(', ') }}
                    </td>                   
                    <td class="text-xs-right pa-2" @click="select(props.item)">Rp {{ one_money(props.item.dp_debit) }}</td>
                    <td class="text-xs-right pa-2" @click="select(props.item)">Rp {{ one_money(props.item.dp_credit) }}</td> -->
                    <td class="text-xs-center pa-0" @click="select(props.item)">
                        <v-btn color="cyan" class="btn-icon ma-0" small @click="journal(props.item)" dark><v-icon>assignment</v-icon></v-btn>
                        <v-btn color="primary" class="btn-icon ma-0" small @click="edit(props.item)"><v-icon>create</v-icon></v-btn>
                        <v-btn color="red" 
                            class="btn-icon ma-0" small @click="del(props.item)" :dark="props.item.dp_used==0"
                            :disabled="props.item.dp_used>0"><v-icon>delete</v-icon></v-btn>
                        
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
        
        <common-dialog-delete :data="dp_id" @confirm_del="confirm_del" v-if="dialog_delete"></common-dialog-delete>
        <common-dialog-confirm :data="dp_id" @confirm="confirm_post" v-if="dialog_confirm" :text="text_post"></common-dialog-confirm>
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
                    width: "21%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "TIPE BAYAR",
                    align: "left",
                    sortable: false,
                    width: "8%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "CATATAN",
                    align: "left",
                    sortable: false,
                    width: "20%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "NOMINAL",
                    align: "right",
                    sortable: false,
                    width: "15%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "STATUS",
                    align: "center",
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
            ]
        }
    },

    computed : {
        dps () {
            return this.$store.state.dp.dps
        },

        dialog_delete () {
            return this.$store.state.dialog_delete
        },

        dialog_confirm () {
            return this.$store.state.dialog_confirm
        },

        dp_id () {
            return this.$store.state.dp.selected_dp.M_JournalID
        },

        query : {
            get () { return this.$store.state.dp.search },
            set (v) { this.$store.commit('dp/update_search', v) }
        },

        curr_page : {
            get () { return this.$store.state.dp.current_page },
            set (v) { this.$store.commit('dp/update_current_page', v) }
        },

        xtotal_page () {
            return this.$store.state.dp.total_dp_page
        },

        text_post () {
            let j = this.$store.state.dp.selected_dp
            return "Apakah anda yakin akan melakukan Posting Jurnal tersebut ?"
        },

        s_date : {
            get () { return this.$store.state.dp.s_date },
            set (v) { this.$store.commit('dp/set_common', ['s_date', v]) }
        },

        e_date : {
            get () { return this.$store.state.dp.e_date },
            set (v) { this.$store.commit('dp/set_common', ['e_date', v]) }
        },

        title() {
            return "DAFTAR UANG MUKA VENDOR"
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
            this.$store.commit('dp_new/set_common', ['edit', false])
                
            this.$store.commit('dp_new/set_common', ['dp_date', this.$store.state.dp_new.current_date])
            this.$store.commit('dp_new/set_common', ['dp_transfer_date', this.$store.state.dp_new.current_date])
            this.$store.commit('dp_new/set_common', ['dp_giro_date', this.$store.state.dp_new.current_date])
            this.$store.commit('dp_new/set_common', ['dp_note', ''])
            this.$store.commit('dp_new/set_common', ['dp_number', ''])
            this.$store.commit('dp_new/set_common', ['dp_giro_number', ''])
            this.$store.commit('dp_new/set_common', ['dp_amount', '0'])
            this.$store.commit('dp_new/set_common', ['dp_acc', 'N'])

            this.$store.commit('dp_new/set_selected_bank', null)
            this.$store.commit('dp_new/set_selected_bankaccount', null)
            this.$store.commit('dp_new/set_selected_account', null)
            this.$store.commit('dp_new/set_selected_paymenttype', null)
            this.$store.commit('dp_new/set_selected_vendor', null)

            this.$store.commit('dp_new/set_common', ['dialog_new', true])
        },

        edit (x) {
            this.select(x)
            let sc = x
            console.log(x)
            this.$store.commit('dp_new/set_common', ['edit', true])
            
            this.$store.commit('dp_new/set_common', ['dp_id', x.dp_id])
            this.$store.commit('dp_new/set_common', ['dp_date', x.dp_date])
            this.$store.commit('dp_new/set_common', ['dp_transfer_date', x.dp_transfer_date])
            this.$store.commit('dp_new/set_common', ['dp_giro_date', x.dp_giro_date])
            this.$store.commit('dp_new/set_common', ['dp_note', x.dp_note])
            this.$store.commit('dp_new/set_common', ['dp_number', x.dp_number])
            this.$store.commit('dp_new/set_common', ['dp_giro_number', x.dp_giro_number])
            this.$store.commit('dp_new/set_common', ['dp_amount', x.dp_amount])
            this.$store.commit('dp_new/set_common', ['dp_acc', x.dp_acc])

            this.$store.commit('dp_new/set_selected_bank', null)
            this.$store.commit('dp_new/set_selected_bankaccount', null)
            this.$store.commit('dp_new/set_selected_account', null)
            this.$store.commit('dp_new/set_selected_paymenttype', null)
            this.$store.commit('dp_new/set_selected_vendor', null)

            for (let p of this.$store.state.dp_new.paymenttypes)
                if (p.paymenttype_id == x.dp_paymenttype)
                    this.$store.commit('dp_new/set_selected_paymenttype', p)

            for (let b of this.$store.state.dp_new.banks)
                if (b.bank_id == x.dp_bank)
                    this.$store.commit('dp_new/set_selected_bank', b)

            for (let b of this.$store.state.dp_new.bankaccounts)
                if (b.account_id == x.dp_bankaccount)
                    this.$store.commit('dp_new/set_selected_bankaccount', b)

            for (let b of this.$store.state.dp_new.accounts)
                if (b.M_AccountID == x.journal_account)
                    this.$store.commit('dp_new/set_selected_account', b)

            for (let v of this.$store.state.dp_new.vendors)
                if (v.vendor_id == x.vendor_id)
                    this.$store.commit('dp_new/set_selected_vendor', v)

            this.$store.commit('dp_new/set_common', ['dialog_new', true])
        },

        del (x) {
            this.select(x)
            this.$store.commit('set_dialog_delete', true)
        },

        confirm_del (x) {
            this.$store.dispatch('dp/del', {id:x.data})
        },

        post (x) {
            this.select(x)
            this.$store.commit('set_dialog_confirm', true)
        },

        confirm_post (x) {
            this.$store.dispatch('dp/post', {id:x.data})
        },

        select (x) {
            this.$store.commit('dp/set_selected_dp', x)
        },

        search () {
            return this.$store.dispatch('dp/search', {})
        },

        change_page(x) {
            this.curr_page = x
            this.$store.dispatch('dp/search', {})
        },

        change_s_date(x) {
            this.$store.commit('dp/set_common', ['s_date', x.new_date])
            this.$store.dispatch('dp/search')
        },

        change_e_date(x) {
            this.$store.commit('dp/set_common', ['e_date', x.new_date])
            this.$store.dispatch('dp/search')
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
            for (let d of x.details)
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
            this.$store.commit('journal_new/set_details', x.details)
        }
    },

    mounted () {
        console.log(this.is_sales)
        if (this.is_sales)
            this.headers[2].text="NOMOR INVOICE"
    }
}
</script>