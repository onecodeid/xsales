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
                        :date="e_date"
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
                        :date="s_date"
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
                :items="journals"
                :loading="false"
                hide-actions
                class="elevation-1">
                <template slot="items" slot-scope="props">
                    
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.journal_date }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.journal_number }}</td>
                    
                    <td class="text-xs-left pa-2" @click="select(props.item)" v-show="!is_sales">
                        {{ props.item.journal_note }}
                    </td> 
                    <td class="text-xs-left pa-2" @click="select(props.item)" v-show="is_sales">
                        {{ props.item.journal_receipt }}
                    </td> 

                    <td class="text-xs-left pa-2" @click="select(props.item)">
                        {{ props.item.accounts.join(', ') }}
                    </td>                   
                    <td class="text-xs-right pa-2" @click="select(props.item)">Rp {{ one_money(props.item.journal_debit) }}</td>
                    <td class="text-xs-right pa-2" @click="select(props.item)">Rp {{ one_money(props.item.journal_credit) }}</td>
                    <td class="text-xs-center pa-0" @click="select(props.item)">
                        <v-btn color="primary" class="btn-icon ma-0" small @click="edit(props.item)"><v-icon>create</v-icon></v-btn>
                        <v-btn color="red" 
                            :dark="props.item.journal_post=='N'&&!is_sales" 
                            :disabled="props.item.journal_post=='Y'||is_sales" 
                            class="btn-icon ma-0" small @click="del(props.item)"><v-icon>delete</v-icon></v-btn>
                        <!-- <v-btn color="red" 
                            :dark="props.item.journal_post=='N'" 
                            :disabled="props.item.journal_post=='Y'" 
                            class="btn-icon ma-0" small @click="post(props.item)"><v-icon>send</v-icon></v-btn> -->
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
        
        <common-dialog-delete :data="journal_id" @confirm_del="confirm_del" v-if="dialog_delete"></common-dialog-delete>
        <common-dialog-confirm :data="journal_id" @confirm="confirm_post" v-if="dialog_confirm" :text="text_post"></common-dialog-confirm>
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
                    width: "7%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "NOMOR",
                    align: "left",
                    sortable: false,
                    width: "7%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "CATATAN",
                    align: "left",
                    sortable: false,
                    width: "28%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "PERKIRAAN",
                    align: "left",
                    sortable: false,
                    width: "28%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "DEBIT",
                    align: "right",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "KREDIT",
                    align: "right",
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
        journals () {
            return this.$store.state.journal.journals
        },

        dialog_delete () {
            return this.$store.state.dialog_delete
        },

        dialog_confirm () {
            return this.$store.state.dialog_confirm
        },

        journal_id () {
            return this.$store.state.journal.selected_journal.M_JournalID
        },

        query : {
            get () { return this.$store.state.journal.search },
            set (v) { this.$store.commit('journal/update_search', v) }
        },

        curr_page : {
            get () { return this.$store.state.journal.current_page },
            set (v) { this.$store.commit('journal/update_current_page', v) }
        },

        xtotal_page () {
            return this.$store.state.journal.total_journal_page
        },

        text_post () {
            let j = this.$store.state.journal.selected_journal
            return "Apakah anda yakin akan melakukan Posting Jurnal tersebut ?"
        },

        s_date : {
            get () { return this.$store.state.journal.s_date },
            set (v) { this.$store.commit('journal/set_common', ['s_date', v]) }
        },

        e_date : {
            get () { return this.$store.state.journal.e_date },
            set (v) { this.$store.commit('journal/set_common', ['e_date', v]) }
        },

        title() {
            return this.$store.state.title
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
            x.push(JSON.parse(JSON.stringify(this.$store.state.journal_new.detail_default)))
            this.$store.commit('journal_new/set_common', ['edit', false])
            this.$store.commit('journal_new/set_common', ['journal_date', this.$store.state.journal_new.current_date])
            this.$store.commit('journal_new/set_common', ['journal_note', ''])
            this.$store.commit('journal_new/set_common', ['journal_receipt', ''])
            this.$store.commit('journal_new/set_details', x)
            this.$store.commit('journal_new/set_common', ['dialog_new', true])
        },

        edit (x) {
            this.select(x)
            let sc = x
            this.$store.commit('journal_new/set_common', ['edit', true])
            this.$store.commit('journal_new/set_common', ['journal_id', sc.journal_id])
            this.$store.commit('journal_new/set_common', ['journal_date', sc.journal_date])
            this.$store.commit('journal_new/set_common', ['journal_note', sc.journal_note])
            this.$store.commit('journal_new/set_common', ['journal_receipt', sc.journal_receipt])

            let details = sc.details
            let acc = this.$store.state.journal_new.accounts
            for (let d of details)
                for (let a of acc)
                    if (a.account_id == d.account)
                        d.account = a
            this.$store.commit('journal_new/set_details', details)

            this.$store.commit('journal_new/set_common', ['dialog_new', true])
        },

        del (x) {
            this.select(x)
            this.$store.commit('set_dialog_delete', true)
        },

        confirm_del (x) {
            this.$store.dispatch('journal/del', {id:x.data})
        },

        post (x) {
            this.select(x)
            this.$store.commit('set_dialog_confirm', true)
        },

        confirm_post (x) {
            this.$store.dispatch('journal/post', {id:x.data})
        },

        select (x) {
            this.$store.commit('journal/set_selected_journal', x)
        },

        search () {
            return this.$store.dispatch('journal/search', {})
        },

        change_page(x) {
            this.curr_page = x
            this.$store.dispatch('journal/search', {})
        },

        change_s_date(x) {
            this.$store.commit('journal/set_common', ['s_date', x.new_date])
            this.$store.dispatch('journal/search')
        },

        change_e_date(x) {
            this.$store.commit('journal/set_common', ['e_date', x.new_date])
            this.$store.dispatch('journal/search')
        }
    },

    mounted () {
        console.log(this.is_sales)
        if (this.is_sales)
            this.headers[2].text="NOMOR INVOICE"
    }
}
</script>