<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0">
            <v-layout row wrap>
                <v-flex xs9>
                    <h3 class="display-1 font-weight-light zalfa-text-title">MASTERDATA AKUN</h3>
                </v-flex>
                <v-flex xs3 class="text-xs-right">
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

                            <v-btn color="success" class="ma-0 ml-2 btn-icon" @click="add">
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
                :items="accounts"
                :loading="false"
                hide-actions
                class="elevation-1">
                <template slot="items" slot-scope="props">
                    <tr :class="props.item.account_pos=='D'?'cyan':'pink'" class="lighten-5">
                        <td class="text-xs-left pa-1" @click="select(props.item)"
                            v-show="props.item.parent_code==''">{{ props.item.M_AccountCode }}
                            
                            </td>
                        <td class="text-xs-left pa-1" @click="select(props.item)" 
                            v-show="props.item.parent_code!=''">└─ {{ props.item.M_AccountCode }}</td>
                        
                        <td class="text-xs-left pa-1 column-name" @click="select(props.item)" 
                            :class="{'pl-4':props.item.parent_code!=''}">
                            <v-layout row wrap>
                                <v-flex class="d-flex align-center">
                                    <a :href="'./trans/'+props.item.M_AccountID">{{ props.item.M_AccountName }}</a>
                                </v-flex>
                                <!-- <v-flex>
                                    
                                    <v-btn color="success lighten-5" depressed class="ma-0 ml-2 btn-icon btn-row-add" @click="add_next(props.item)" small title="Tambah akun setelahnya" v-if="props.item.parent_code!=''"><v-icon>add</v-icon></v-btn> 
                                    <v-btn color="orange lighten-5" dark depressed class="ma-0 ml-2 btn-icon btn-row-child" @click="add_child(props.item)" small v-if="props.item.parent_code==''" title="Tambah akun anak"><v-icon>person_add</v-icon></v-btn>
                                </v-flex> -->
                            </v-layout>
                            
                            </td>
                        <td class="text-xs-left pa-1" @click="select(props.item)">{{ props.item.group_name }}</td>
                        <td class="text-xs-left pa-1" @click="select(props.item)">{{ props.item.last_date }}</td>
                        
                        <td class="text-xs-right pa-1" @click="select(props.item)">
                            <div v-show="props.item.parent==false">
                                <v-btn color="primary" small @click="set_balance(props.item)" class="ma-0" v-show="props.item.balance_open_credit==0&&props.item.balance_open_debit==0">SET</v-btn>
                                
                                <span v-show="props.item.balance_open_credit!=0||props.item.balance_open_debit!=0" class="mr-1">Rp 
                                    <!-- <span class="font-weight-bold red--text" v-show="props.item.M_AccountType=='P'">{{one_money(props.item.balance_open_credit-props.item.balance_open_debit)}}</span>
                                    <span class="font-weight-bold blue--text" v-show="props.item.M_AccountType=='A'">{{one_money(props.item.balance_open_debit-props.item.balance_open_credit)}}</span> -->

                                    <span class="font-weight-bold red--text" v-show="props.item.balance_open_credit-props.item.balance_open_debit>0">{{one_money(props.item.balance_open_credit-props.item.balance_open_debit)}}</span>
                                    <span class="font-weight-bold blue--text" v-show="props.item.balance_open_debit-props.item.balance_open_credit>=0">{{one_money(props.item.balance_open_debit-props.item.balance_open_credit)}}</span>
                                </span>

                                <a href="javascript:;" v-show="props.item.balance_open_credit!=0||props.item.balance_open_debit!=0" @click="set_balance(props.item)"><v-icon small class="blue--text">edit</v-icon></a>
                            </div>
                        </td>
                        
                        <td class="text-xs-right pa-1" @click="select(props.item)">
                            <!-- <div v-show="props.item.parent==false">
                                Rp <b>{{ one_money(last_balance(props.item)) }}</b>
                                </div> -->
                            <div v-show="props.item.parent==false && props.item.balance_debit >= props.item.balance_credit" class="blue--text">Rp <b>{{ one_money(props.item.balance_debit-props.item.balance_credit) }}</b></div>
                            <div v-show="props.item.parent==false && props.item.balance_debit < props.item.balance_credit" class="red--text">Rp <b>{{ one_money(props.item.balance_credit-props.item.balance_debit) }}</b></div>
                        </td>
                        
                        
                        <td class="text-xs-center pa-0" @click="select(props.item)">
                            <v-btn color="primary" class="btn-icon ma-0" small @click="edit(props.item)" v-show="props.item.M_AccountRemovable=='Y'"><v-icon>create</v-icon></v-btn>
                            <v-btn color="red" dark class="btn-icon ma-0" small @click="del(props.item)" v-show="props.item.M_AccountRemovable=='Y'"><v-icon>delete</v-icon></v-btn>
                        </td>
                    </tr>
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
        
        <common-dialog-delete :data="account_id" @confirm_del="confirm_del" v-if="dialog_delete"></common-dialog-delete>
        <v-snackbar
            v-model="snackbar"
            top
            vertical
                multi-line
        >
            {{snackbar_text}}
            <v-btn flat color="primary" @click.native="snackbar = false">Close</v-btn>
        </v-snackbar>
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

table.v-table tbody td, table.v-table tbody th {
    height: 44px;
}

button[class*="btn-row"] {
    float:right;
    /* display: none; */
}

.column-name:hover .btn-row-add {
    background-color: #4caf50 !important;
    border-color: #4caf50 !important;
}
.column-name:hover .btn-row-child {
    background-color: #ff9800!important;
    border-color: #ff9800!important;
}

</style>

<script>
module.exports = {
    components : {
        "common-dialog-delete" : httpVueLoader("../../common/components/common-dialog-delete.vue")
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
                    width: "25%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "GRUP",
                    align: "left",
                    sortable: false,
                    width: "15%",
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
                    text: "SALDO AWAL",
                    align: "right",
                    sortable: false,
                    width: "15%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "SALDO TERAKHIR",
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
            ]
        }
    },

    computed : {
        accounts () {
            return this.$store.state.account.accounts
        },

        dialog_delete () {
            return this.$store.state.dialog_delete
        },

        account_id () {
            return this.$store.state.account.selected_account.M_AccountID
        },

        query : {
            get () { return this.$store.state.account.search },
            set (v) { this.$store.commit('account/update_search', v) }
        },

        curr_page : {
            get () { return this.$store.state.account.current_page },
            set (v) { this.$store.commit('account/update_current_page', v) }
        },

        xtotal_page () {
            return this.$store.state.account.total_account_page
        },

        snackbar : {
            get () { return this.$store.state.account.snackbar },
            set (v) { 
                this.$store.commit('account/set_common', ['snackbar', v]) 
            }
        },

        snackbar_text : {
            get () { return this.$store.state.account.snackbar_text },
            set (v) { 
                this.$store.commit('account/set_common', ['snackbar_text', v]) 
            }
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        add_next(x) {
            this.select(x)
            this.$store.commit('account_new/set_common', ['edit', false])
            this.$store.commit('account_new/set_common', ['account_name', ''])
            this.$store.commit('account_new/set_common', ['account_parent', 0])
            this.$store.commit('account_new/set_common', ['account_from', x.account_id])
            this.$store.commit('account_new/set_common', ['dialog_new', true])

            let group = null
            if (!!x.M_AccountGroupID)
                group = {
                    group_code : x.M_AccountGroupCode,
                    group_id : x.M_AccountGroupID,
                    group_name : x.M_AccountGroupName
                }
            this.$store.commit('account_new/set_object', ['selected_group', group])
            this.$store.commit('account_new/set_object', ['selected_action', this.$store.state.account_new.actions[1]])
        },

        add_child(x) {
            this.select(x)
            this.$store.commit('account_new/set_common', ['edit', false])
            this.$store.commit('account_new/set_common', ['account_name', ''])
            this.$store.commit('account_new/set_common', ['account_parent', x.account_id])
            this.$store.commit('account_new/set_common', ['account_from', 0])
            this.$store.commit('account_new/set_common', ['dialog_new', true])

            let group = null
            if (!!x.M_AccountGroupID)
                group = {
                    group_code : x.M_AccountGroupCode,
                    group_id : x.M_AccountGroupID,
                    group_name : x.M_AccountGroupName
                }
            this.$store.commit('account_new/set_object', ['selected_group', group])
            this.$store.commit('account_new/set_object', ['selected_action', this.$store.state.account_new.actions[0]])
        },

        add_from (x) {
            // parent id : 0 || ?
            // group id
            // name 
            this.select(x)
            this.$store.commit('account_new/set_common', ['account_code_prefix', x.M_AccountCode])
            this.$store.commit('account_new/set_common', ['account_code', "< Otomatis >"])
            this.$store.commit('account_new/set_common', ['edit', false])
            this.$store.commit('account_new/set_common', ['account_name', ''])
            this.$store.commit('account_new/set_common', ['dialog_new', true])

            let group = null
            if (!!x.M_AccountGroupID)
                group = {
                    group_code : x.M_AccountGroupCode,
                    group_id : x.M_AccountGroupID,
                    group_name : x.M_AccountGroupName
                }

            /**
             * Find last code
             */
            // let codenext = 1
            let codemax = ''
            let compare = x.parent === true || x.parent_code == "" ? x.account_code : x.parent_code
            for (let acc of this.accounts) {
                if (acc.parent_code == compare) {
                    codemax = acc.account_code
                }
            }
            if (codemax == '') codemax = compare + "00"
            let codenext = Math.round(codemax.substr(-1, 2)) + 1
            codemax = codemax.substr(0, codemax.length-2) + String(codenext).padStart(2, '0')
            /** END OF LAST CODE */

            this.$store.commit('account_new/set_common', ['account_code', codemax])
            

            this.$store.commit('account_new/set_object', ['selected_group', group])
        },

        add () {
            this.$store.commit('account_new/set_common', ['edit', false])
            this.$store.commit('account_new/set_common', ['account_name', ''])
            this.$store.commit('account_new/set_common', ['account_pos', 'D'])
            this.$store.commit('account_new/set_object', ['selected_account', null])
            this.$store.commit('account_new/set_object', ['selected_group', null])
            this.$store.commit('account_new/set_common', ['dialog_new', true])
        },

        edit (x) {
            this.select(x)
            let sc = x
            this.$store.commit('account_new/set_common', ['edit', true])
            this.$store.commit('account_new/set_common', ['account_name', sc.M_AccountName])
            this.$store.commit('account_new/set_common', ['account_code', sc.M_AccountCode])
            this.$store.commit('account_new/set_common', ['account_code_prefix', ''])
            this.$store.commit('account_new/set_common', ['account_pos', sc.account_pos])

            let group = null
            for (let y of this.$store.state.account_new.groups)
                if (y.group_id == x.M_AccountGroupID) {
                    console.log(y)
                    this.$store.commit('account_new/set_object', ['selected_group', y])
                }

            this.$store.commit('account_new/set_common', ['dialog_new', true])
        },

        del (x) {
            this.select(x)
            this.$store.commit('set_dialog_delete', true)
        },

        confirm_del (x) {
            this.$store.dispatch('account/del', {id:x.data})
        },

        select (x) {
            this.$store.commit('account/set_selected_account', x)
        },

        search () {
            return this.$store.dispatch('account/search', {})
        },

        change_page(x) {
            this.curr_page = x
            this.$store.dispatch('account/search', {})
        },

        set_balance (x) {
            this.select(x)
            this.$store.commit('account_new/set_common', ['dialog_balance', true])
            this.$store.commit('account_new/set_common', ['balance', 
                x.M_AccountType=='P'?x.balance_open_credit-x.balance_open_debit:x.balance_open_debit-x.balance_open_credit])

            this.$store.commit('account_new/set_common', ['balanceDebit', x.balance_open_debit])
            this.$store.commit('account_new/set_common', ['balanceCredit', x.balance_open_credit])
        },

        last_balance(x) {
            let d = x.balance_debit - x.balance_credit
            if (x.account_side == 'P') 
                d = 0 - d

            return d
        }
    },

    mounted () {
        this.$store.dispatch('account_new/search_group')
    }
}
</script>