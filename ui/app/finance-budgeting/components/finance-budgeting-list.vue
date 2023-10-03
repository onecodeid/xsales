<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0">
            <v-layout row wrap>
                <v-flex xs4>
                    <h3 class="display-1 font-weight-light zalfa-text-title">MANAJEMEN ANGGARAN</h3>
                </v-flex>
                <v-flex xs4 pr-2 class="pl-2">
                    <v-select
                        :items="years"
                        item-text="year_name"
                        item-value="year_value"
                        return-object
                        v-model="selected_year"
                        label="Tahun"
                        hide-details
                        solo
                    >
                    <template slot="prepend">
                        <v-btn color="success" class="ma-0 btn-icon" @click="prev" :disabled="selected_start_month_idx<1"><v-icon class="mr-2">arrow_back</v-icon> Prev</v-btn>
                        <v-btn color="success" class="ma-0 ml-2 btn-icon" @click="next" :disabled="selected_start_month_idx>10">Next <v-icon class="ml-2">arrow_forward</v-icon></v-btn>
                    </template>
                    <template slot="prepend-inner">TAHUN </template>
                    </v-select>
                    
                </v-flex>
                <v-flex xs2 pr-2 class="pl-2">
                    <v-select
                        :items="endyears"
                        item-text="label"
                        item-value="edate"
                        return-object
                        v-model="selected_start_month"
                        label="Periode"
                        hide-details
                        solo
                    >
                    </v-select>
                </v-flex>
                <v-flex xs2 class="text-xs-right pl-2">
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

                            <v-btn color="amber darken-2" class="ma-0 ml-2 btn-icon white--text" @click="save">
                                <v-icon>save</v-icon>
                            </v-btn>  
                        </template>
                    </v-text-field>
                </v-flex>
            </v-layout>
        </v-card-title>
        <!--  -->
        <v-card-text class="pt-2">
            <v-data-table 
                :headers="headers"
                :items="accounts"
                :loading="false"
                hide-actions
                class="elevation-1">
                <template slot="headers">
                    <tr>
                        <th v-for="(h, n) in headers" :key="'h'+n" 
                            role="columnheader" scope="col" :width="h.width" :aria-label="h.text+': Not sorted.'" aria-sort="none" 
                            :class="['column', h.class]" :colspan="h.colspan?h.colspan:1"
                            :rowspan="h.rowspan?h.rowspan:1">
                            <div class="d-flex align-center">
                                <span class="grow align-center justify-center">{{ h.text }}</span>
                                <span class="align-end" v-show="n>=2">
                                    <v-tooltip bottom><template v-slot:activator="{ on }">
                                        <v-btn color="cyan darken-3" class="ma-0 ml-2 btn-icon white--text" @click="copyTo(h.date)" small depressed v-on="on"><v-icon>content_copy</v-icon></v-btn>
                                    </template><span>Salin dari bulan sebelumnya</span></v-tooltip>

                                    <v-tooltip bottom><template v-slot:activator="{ on }">
                                        <v-btn color="red lighten-2" class="ma-0 btn-icon white--text" @click="save" small depressed v-on="on"><v-icon>clear</v-icon></v-btn>
                                    </template><span>Hapus semua isian di bulan ini</span></v-tooltip>
                                </span>
                            </div>
                        </th>
                    </tr>
                    <tr>
                        <th v-for="(h, n) in headers2" :key="'h2'+n" 
                            role="columnheader" scope="col" :width="h.width" :aria-label="h.text+': Not sorted.'" aria-sort="none" 
                            :class="['column', h.class]" :colspan="h.colspan?h.colspan:1">
                            {{ h.text }}
                        </th>
                    </tr>
                </template>
                <template slot="items" slot-scope="props">
                    <tr :class="props.item.account_pos=='D'?'cyan':'pink'" class="lighten-5" @click="select(props.item)">
                        <td class="text-xs-left pa-1"
                            v-show="props.item.parent_code==''">{{ props.item.group_name }}
                            
                            </td>
                        <td class="text-xs-left pa-1" 
                            v-show="props.item.parent_code!=''">└─ {{ props.item.M_AccountCode }}</td>
                        
                        <td class="text-xs-left pa-1 column-name" 
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

                        <template v-for="(b, n) in props.item.budgets">
                            <td class="text-xs-left pa-1" :class="{'grey lighten-5':b.date==selected_start_month.label3}" v-bind:key="'b'+n">
                                <!-- <span v-show="props.item.M_AccountParent!='Y'">{{ b.budget }}</span> -->
                                <v-text-field v-show="props.item.M_AccountParent!='Y'"
                                    dense solo hide-details :value="b.budget" reverse @change="changeBudget($event, props.index, n)"
                                    suffix="Rp"
                                ></v-text-field>
                            </td>
                            <td class="text-xs-right pa-1" :class="{'blue-grey lighten-5':b.date==selected_start_month.label3}" v-bind:key="'a'+n">
                                <div class="d-flex" v-show="props.item.M_AccountParent!='Y'">
                                    <span class="align-start pl-3">Rp</span>
                                    <span class="grow">{{ one_money(b.actual) }}</span>
                                </div>
                                
                            </td>
                            <td class="text-xs-right pa-1 amber lighten-5" v-bind:key="'c'+n">
                                <div class="d-flex" v-show="props.item.M_AccountParent!='Y'">
                                    <span class="grow">{{ b.budget > 0 ? one_money(b.actual*100/b.budget) : 0 }} %</span>
                                </div>
                                
                            </td>
                        </template>
                        
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
            top right
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
.v-text-field.v-text-field--solo .v-input__append-outer, .v-input__prepend-outer {
    margin-top: 0px !important;
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
        "common-dialog-delete" : httpVueLoader("../../common/components/common-dialog-delete.vue"),
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue')
    },

    data () {
        return {
        }
    },

    computed : {
        __s () {
            return this.$store.state.budgeting
        },

        headers () {
            let h = [{ text: "KODE", align: "left", sortable: false, width: "10%", class: "pa-2 cyan lighten-2 white--text", rowspan: 2 },
                    { text: "NAMA", align: "left", sortable: false, width: "20%", class: "pa-2 cyan lighten-2 white--text", rowspan: 2 }]
            
            if (this.accounts[0]) {
                for (let n in this.accounts[0].budgets) {
                    let b = this.accounts[0].budgets[n]
                    h.push({ text: moment(b.date).format('MMM YYYY').toUpperCase(), date: b.date, align: "left", sortable: false, width: "23%", class: "pa-2 cyan white--text "+(n==1?'lighten-1':'lighten-3'), colspan: 3 })
                }
            }

            return h
        },

        headers2 () {
            let h = []
            
            if (this.accounts[0]) {
                for (let n in this.accounts[0].budgets) {
                    let b = this.accounts[0].budgets[n]
                    h.push({ text: 'BUDGET', align: "left", sortable: false, width: "9%", class: "pa-2 cyan white--text "+(n==1?'lighten-1':'lighten-3') })
                    h.push({ text: 'REALISASI', align: "left", sortable: false, width: "9%", class: "pa-2 cyan white--text "+(n==1?'lighten-1':'lighten-3') })
                    h.push({ text: '( % )', align: "left", sortable: false, width: "5%", class: "pa-2 cyan white--text "+(n==1?'lighten-1':'lighten-3') })
                }
            }

            return h
        },

        colors () {
            return ['cyan', 'light-blue', 'teal', 'light-green', 'brown', 'blue-grey']
        },

        accounts () {
            return this.$store.state.budgeting.accounts
        },

        dialog_delete () {
            return this.$store.state.dialog_delete
        },

        account_id () {
            return this.$store.state.budgeting.selected_accountg.M_AccountID
        },

        query : {
            get () { return this.$store.state.budgeting.search },
            set (v) { this.$store.commit('budgeting/update_search', v) }
        },

        curr_page : {
            get () { return this.$store.state.budgeting.current_page },
            set (v) { this.$store.commit('budgeting/update_current_page', v) }
        },

        xtotal_page () {
            return this.$store.state.budgeting.total_account_page
        },

        snackbar : {
            get () { return this.$store.state.budgeting.snackbar },
            set (v) { 
                this.$store.commit('budgeting/set_common', ['snackbar', v]) 
            }
        },

        snackbar_text : {
            get () { return this.$store.state.budgeting.snackbar_text },
            set (v) { 
                this.$store.commit('budgeting/set_common', ['snackbar_text', v]) 
            }
        },

        s_date : {
            get () { return this.$store.state.budgeting.s_date },
            set (v) { this.$store.commit('budgeting/set_object', ['s_date', v]) }
        },

        e_date : {
            get () { return this.$store.state.budgeting.e_date },
            set (v) { this.$store.commit('budgeting/set_object', ['e_date', v]) }
        },

        endyears () {
            let x = JSON.parse(JSON.stringify(this.$store.state.budgeting.months))
            return x
        },

        years () {
            return this.__s.years
        },

        selected_year : {
            get () { return this.__s.selected_year },
            set (v) { 
                this.__c('selected_year', v) 
                this.__d("search_months").then((x) => {
                    this.__c("selected_start_month", x[1])
                    this.__c("selected_end_month", x[x.length-1])

                    this.search()
                })
            }
        },

        selected_start_month : {
            get () { return this.$store.state.budgeting.selected_start_month },
            set (v) { 
                this.$store.commit('budgeting/set_object', ['selected_start_month', v])
                this.search()
            }
        },

        selected_end_month : {
            get () { return this.$store.state.budgeting.selected_end_month },
            set (v) { 
                this.$store.commit('budgeting/set_object', ['selected_end_month', v])
                // this.search()
            }
        },

        selected_start_month_idx () {
            if (!!this.selected_start_month) {
                for (let n in this.endyears) if (this.endyears[n].sdate==this.selected_start_month.sdate) return n
            }

            return -1
        },

        months () {
            return []
            // let m, ms = [], sm = moment(this.selected_start_month.sdate), fm = 'YYYY-MM'
            // if (!!sm) {
            //     m = sm.format('MM')
            //     if (m=='01') ms = [sm.format(fm), sm.add(1, 'months').format(fm), sm.add(2, 'months').format(fm)]
            //     else if (m=='12') ms = [sm.substract(2, 'months').format(fm), sm.substract(1, 'months').format(fm), sm.format(fm)]
            //     else ms = [sm.substract(1, 'months').format(fm), sm.format(fm), sm.add(1, 'months').format(fm)]
            // }

            return ms
        }
    },

    methods : {
        __d (x, y) { return !!y?this.$store.dispatch("budgeting/"+x,y):this.$store.dispatch("budgeting/"+x) },
        __c (x, y) { return this.$store.commit("budgeting/set_object", [x, y]) },

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
            this.$store.dispatch('budgeting/del', {id:x.data})
        },

        select (x) {
            this.__c('selected_account', x)
        },

        search () {
            return this.$store.dispatch('budgeting/search', {})
        },

        change_page(x) {
            this.curr_page = x
            this.$store.dispatch('budgeting/search', {})
        },

        change_s_date(x) {
            this.$store.commit('budgeting/set_object', ['s_date', x.new_date])
            this.search()
        },

        change_e_date(x) {
            this.$store.commit('budgeting/set_object', ['e_date', x.new_date])
            this.search()
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
        },

        changeBudget(x, y, z) {
            let acc = JSON.parse(JSON.stringify(this.accounts))
            acc[y].budgets[z].budget = parseFloat(x)

            this.__c("selected_budget", acc[y].budgets[z])
            this.$store.commit("budgeting/set_object", ["accounts", acc])

            this.__d("saveBudget").then((x) => {
                this.snackbar=true,this.snackbar_text="Budget berhasil diupdate"
            })
        },

        save () {
            this.$store.dispatch("budgeting/save").then((x) => {
                this.search()
            })
        },

        prev () {
            let n, e = this.endyears, l = e.length, s = this.selected_start_month
            for (let m in e) if (e[m].sdate == s.sdate) n = parseFloat(m)

            if (n != 0) this.selected_start_month = e[n-1]
        },

        next () {
            let n, e = this.endyears, l = e.length, s = this.selected_start_month
            for (let m in e) if (e[m].sdate == s.sdate) n = parseFloat(m)

            if (n != (e.length-1)) this.selected_start_month = e[n+1]
        },

        copyTo (x) {
            let y = x.split('-')
            this.__d("copyTo", {year:y[0],month:y[1]})
            this.__d("search").then((x) => {
                this.snackbar=true,this.snackbar_text="Budget berhasil disalin"
            })
            
        }
    },

    mounted () {
        // this.$store.dispatch('account_new/search_group')
        this.__d("search_years").then((u) => {
            this.__c("selected_year", u[0])
            this.__d("search_months").then((x) => {
                this.__c("selected_start_month", x[1])
                this.__c("selected_end_month", x[x.length-1])

                // this.$store.dispatch('budgeting/search', {})
                this.search()
            })
        })
        
    }
}
</script>