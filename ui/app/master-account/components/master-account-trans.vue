<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0">
            <v-layout row wrap>
                <v-flex xs8 pr-2>
                    <h3 class="display-1 font-weight-light zalfa-text-title main-title">
                        <span>
                            <a href="../" class="zalfa-text-title main-title"><u>DAFTAR AKUN »</u></a></span> <b class="blue--text">{{ title }}</b>
                    </h3>
                </v-flex>
                <!-- <v-flex xs2 pr-2 class="text-xs-right">
                    &nbsp;
                </v-flex> -->
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
                        </template>
                    </v-text-field>
                </v-flex>
            </v-layout>
        </v-card-title>
        <v-card-text class="pt-2">
            <v-data-table 
                :headers="headers"
                :items="journals"
                :loading="false"
                hide-actions
                class="elevation-1">
                <template slot="items" slot-scope="props">
                    <td class="text-xs-left pa-1" @click="select(props.item)" style="height:auto;transform:scale(1)">
                        <v-btn color="red" class="btn-icon ma-0 white--text" small v-if="props.item.accflow=='OUT'" depressed title="Transaksi Keluar"><v-icon>chevron_left</v-icon></v-btn>
                        <v-btn color="success" class="btn-icon ma-0 white--text" small v-if="props.item.accflow=='IN'" depressed title="Transaksi Masuk"><v-icon>chevron_right</v-icon></v-btn>
                        <!-- <span class="success white--text px-2 py-3 " v-if="props.item.accflow=='IN'">+</span>
                        <span class="red pa-2 py-3 d-block white--text" v-if="props.item.accflow=='OUT'">-</span> -->
                        <!-- <span class="grey white--text pa-2 py-3 d-block" v-if="props.item.cash_type_code=='CASH.TRANSFER'">TRANSFER</span> -->
                    </td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.journal_date }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)"><a href="#" @click="select(props.item),detail($event, props.item)">{{ props.item.journal_number }}</a></td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">
                        <a href="#" @click="detailRef($event, props.item)" v-show="journal_types_link.indexOf(props.item.journal_type)>-1">{{ props.item.journaltype_name }}</a>
                        <span v-show="journal_types_link.indexOf(props.item.journal_type)<0">{{ props.item.journaltype_name }}</span>
                    </td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.ledgernote }}</td>
                    <!-- <td class="text-xs-left pa-2" @click="select(props.item)">
                        <span v-for="(acc, i) in props.item.accounts" :class="{'font-weight-bold primary--text':(acc==props.item.accname)}"><span v-show="i>0">, </span>{{ acc }}</span>
                    </td> -->
                    <td class="text-xs-right pa-2" @click="select(props.item)">
                        <span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.jdebit)) }}</b>
                    </td>
                    <td class="text-xs-right pa-2" @click="select(props.item)">
                        <span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.jcredit)) }}</b>
                    </td>
                    <!-- <td class="text-xs-left pa-2" @click="select(props.item)">
                        <v-layout row wrap>
                            <v-flex xs12>{{props.item.from_account_name}}</v-flex>
                            <v-flex xs12>{{props.item.to_account_name}}</v-flex>
                        </v-layout>
                    </td> 
                    
                    <td class="text-xs-left pa-2" @click="select(props.item)">
                        {{ props.item.cash_from }} 
                    </td> 

                    <td class="text-xs-left pa-2" @click="select(props.item)">
                        <v-layout row wrap>
                            <v-flex xs12>{{ props.item.cash_note }}</v-flex><v-flex xs12 v-show="props.item.cash_memo != ''" class="blue--text"><i>memo : {{props.item.cash_memo}}</i></v-flex>
                        </v-layout>
                    </td> 

                    <td class="text-xs-right pa-2" @click="select(props.item)">
                        <span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.cash_total)) }}</b>
                    </td>
                    
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
                    </td> -->
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
        <trans-journal-view></trans-journal-view>
    </v-card>
</template>
<style scoped>
table.v-table tbody td {
    height: auto !important;
}

.v-text-field.v-text-field--solo .v-input__control {
    min-height: 36px;
}

.v-text-field.v-text-field--solo .v-input__append-outer {
    margin-top: 0px;
    margin-left: 0px;
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
            journal_types_link: ['J.11', 'J.12', 'J.13', 'J.14', 'J.20'],
            headers : [
                {
                    text: "",
                    align: "left",
                    sortable: false,
                    width: "4%",
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
                    text: "TRANSAKSI",
                    align: "left",
                    sortable: false,
                    width: "15%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "DESKRIPSI",
                    align: "left",
                    sortable: false,
                    width: "37%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                // {
                //     text: "AKUN / PERKIRAAN",
                //     align: "left",
                //     sortable: false,
                //     width: "20%",
                //     class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                // },
                {
                    text: "DEBIT",
                    align: "left",
                    sortable: false,
                    width: "12%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text text-xs-right"
                },
                {
                    text: "CREDIT",
                    align: "left",
                    sortable: false,
                    width: "12%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text text-xs-right"
                }
            ]
        }
    },

    computed : {
        s_date : {
            get () { return this.$store.state.accountTrans.s_date },
            set (v) { this.$store.commit('accountTrans/set_common', ['s_date', v]) }
        },

        e_date : {
            get () { return this.$store.state.accountTrans.e_date },
            set (v) { this.$store.commit('accountTrans/set_common', ['e_date', v]) }
        },

        title() {
            if (this.$store.state.accountTrans.account) return this.$store.state.accountTrans.account.account_name.toUpperCase()
            return "SELECTED ACCOUNT"//(this.selected_account?this.selected_account.account_name:'')
        },

        query : {
            get () { return this.$store.state.accountTrans.search },
            set (v) { this.setObject('search', v) }
        },

        curr_page : {
            get () { return this.$store.state.accountTrans.current_page },
            set (v) { this.setObject('current_page', v) }
        },

        xtotal_page () {
            return this.$store.state.accountTrans.total_journal_page
        },

        journals () {
            return this.$store.state.accountTrans.journals
        }
    },

    methods : {
        one_money(x) {
            return window.one_money(x)
        },

        setObject(x, y) {
            this.$store.commit('accountTrans/set_object', [x, y])
        },

        change_s_date(x) {
            this.setObject("s_date", x.new_date)
            this.$store.dispatch('accountTrans/search')
        },

        change_e_date(x) {
            this.setObject("e_date", x.new_date)
            this.$store.dispatch('accountTrans/search')
        },

        search () {
            return this.$store.dispatch('accountTrans/search')
        },

        change_page(x) {
            this.curr_page = x
            this.$store.dispatch('accountTrans/search')
        },

        select (x) {
            this.setObject("selected_journal", x)
        },

        detail (x, y) {
            x.preventDefault()
            return this.journal(y)
        },

        journal(x) {
            this.$store.commit('journal_new/set_common', ['dialog_new', true])
            this.$store.commit('journal_new/set_common', ['view', true])
            this.$store.commit('journal_new/set_common', ['journal_note', x.journal_note])
            this.$store.commit('journal_new/set_common', ['journal_receipt', x.journal_receipt])
            this.$store.commit('journal_new/set_common', ['journal_date', x.journal_date])

            // this.$store.dispatch('fin002/searchJournalDetail').then((x) => {
            //     let accs = []
            //     for (let y of x)
            //         accs.push(y.account)
            let accs = []
            for (let d of x.details)
                accs.push(d.accountx)
            this.$store.commit('journal_new/set_accounts', accs)
            this.$store.commit('journal_new/set_details', x.details)
            // })
            // let accs = []
            // for (let d of x.accounts)
            //     if (d.account.account_id != null)
            //         accs.push(d.account)
            // this.$store.commit('journal_new/set_accounts', accs)
            // this.$store.commit('journal_new/set_details', x.accounts)
        },

        detailRef (x, y) {
            x.preventDefault()
            this.select(y)
            if (y.journal_type == 'J.11')
                window.open('../../finance-payable/pay/' + y.journal_refid, '_blank')
            if (y.journal_type == 'J.12')
                window.open('../../finance-bill/pay/' + y.journal_refid, '_blank')
            if (y.journal_type == 'J.13')
                window.open('../../finance-cash/receive/?id=' + y.journal_refid, '_blank')
            if (y.journal_type == 'J.14')
                window.open('../../finance-cash/pay/?id=' + y.journal_refid, '_blank')
            if (y.journal_type == 'J.20')
                window.open('../../sales-invoice/view/' + y.journal_refid, '_blank')
                // this.$store.dispatch('fin002/searchRef')
        }
    }
}
</script>