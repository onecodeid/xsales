<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0">
            <v-layout row wrap>
                <v-flex xs6>
                    <h3 class="display-1 font-weight-light zalfa-text-title main-title">
                        <b class="blue--text">{{ title }}</b>
                    </h3>
                </v-flex>
                <v-flex xs2 pr-2 class="text-xs-right">
                    <!-- <v-menu offset-y>
                        <template v-slot:activator="{ on }">
                            <v-btn
                            color="success"
                            class="ma-0 ml-2 btn-icon"
                            dark
                            v-on="on"
                            :disabled="!!view"
                            >
                            <v-icon>add</v-icon> <span class="mr-1">Transaksi</span>
                            </v-btn>
                        </template>
                        <v-list>
                            <v-list-tile
                            v-for="(item, index) in add_menus"
                            :key="index"
                           
                            >
                            <v-list-tile-title><a :href="item.url+(selected_account?selected_account.account_id:0)"  @click="item.action($event)" class="black--text">{{ item.title }}</a></v-list-tile-title>
                            </v-list-tile>
                        </v-list>
                    </v-menu> -->
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
                <v-flex xs2 class="text-xs-right" pl-2>
                    
                    <v-text-field
                        solo
                        hide-details
                        placeholder="Pencarian" v-model="query"
                        @change="search"
                    >
                        <template v-slot:append-outer>
                            
                            
                            <v-btn color="red white--text" class="ma-0 btn-icon" @click="search">
                                <v-icon>search</v-icon>
                            </v-btn>    
                            
                            <v-btn color="success" class="ma-0 btn-icon ml-1" @click="add">
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
                    <tr :class="is_selected(props.item)?'cyan lighten-4':''">
                        <td class="text-xs-left pa-1" @click="select(props.item)" style="writing-mode:tb-rl;height:auto;transform:scale(-1)">

                        </td>
                        <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.journal_date }}</td>
                        <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.journal_number }}</td>
                        <td class="text-xs-left pa-2" @click="select(props.item)">
                            <v-layout row wrap>
                                <v-flex xs12>{{ props.item.accounts.join(", ") }}</v-flex>
                            </v-layout>
                        </td> 
                        <td class="text-xs-left pa-2" @click="select(props.item)">
                            <v-layout row wrap>
                                <v-flex xs12>{{ props.item.journal_note }}</v-flex>
                            </v-layout>
                        </td> 

                        <td class="text-xs-right pa-2" @click="select(props.item)">
                            <span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.journal_debit)) }}</b>
                        </td>
                        <td class="text-xs-right pa-2" @click="select(props.item)">
                            <span class="grey--text caption">Rp</span> <b>{{ one_money(Math.round(props.item.journal_credit)) }}</b>
                        </td>
                        <td class="text-xs-center pa-0" @click="select(props.item)">
                            
                            <!-- <v-btn color="cyan" class="btn-icon ma-0" small title="Jurnal transaksi" @click="journal(props.item)" dark>
                                <v-icon>list</v-icon>
                            </v-btn> -->

                            <v-btn color="primary" class="btn-icon ma-0" small title="Ubah cash" :disabled="!!view">
                                <a :href="edit_href(props.item)" 
                                    @click="edit(props.item, $event)" class="white--text">
                                    <v-icon>create</v-icon>
                                </a>
                            </v-btn>

                            <v-btn color="red" class="btn-icon ma-0" small title="Hapus transaksi" @click="del(props.item)" dark :disabled="!!view" :dark="!view">
                                <v-icon>delete</v-icon>
                            </v-btn>
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
        
        

        <common-dialog-delete :data="0" @confirm_del="confirm_del" v-if="dialog_delete"></common-dialog-delete>
        <new></new>
        <!-- <journal></journal> -->
        <!-- <common-dialog-confirm :data="journal_id" @confirm="confirm_del" v-if="dialog_confirm" :text="text_del"></common-dialog-confirm> -->
        <!-- <common-dialog-print :report_url="report_url" v-if="dialog_report"></common-dialog-print> -->
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

.v-menu__content {
    min-width: 114px !important;
}

.main-title span {
    cursor: pointer;
    font-size: .8em;
}
</style>

<style>
.v-menu__content .v-list__tile__title a {
    text-decoration: none;
}
</style>

<script>
var t = Math.ceil(Math.random() * 1e10)

module.exports = {
    components : {
        "common-dialog-delete" : httpVueLoader("../../common/components/common-dialog-delete.vue"),
        // "common-dialog-confirm" : httpVueLoader("../../common/components/common-dialog-confirm.vue"),
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        "common-dialog-print" : httpVueLoader("../../common/components/common-dialog-print-size.vue"),
        "new" : httpVueLoader("./finance-journal-general-new-dialog.vue"),
        // "journal" : httpVueLoader("../../trans-journal/components/trans-journal-new.vue?t="+t)
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
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "AKUN / PERKIRAAN",
                    align: "left",
                    sortable: false,
                    width: "22%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "DESKRIPSI / MEMO",
                    align: "left",
                    sortable: false,
                    width: "23%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "DEBIT",
                    align: "right",
                    sortable: false,
                    width: "12%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "KREDIT",
                    align: "right",
                    sortable: false,
                    width: "12%",
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

            report_url: ''
        }
    },

    computed : {
        journals () {
            return this.$store.state.general.journals
        },

        dialog_delete () {
            return this.$store.state.dialog_delete
        },

        view () {
            return this.$store.state.general.view
        },

        journal_id () {
            return this.selected_journal?this.$store.state.general.selected_journal.journal_id:0
        },

        query : {
            get () { return this.$store.state.general.search },
            set (v) { this.setObject('search', v) }
        },

        curr_page : {
            get () { return this.$store.state.general.current_page },
            set (v) { this.setObject('current_page', v) }
        },

        xtotal_page () {
            return this.$store.state.general.total_cash_page
        },

        s_date : {
            get () { return this.$store.state.general.s_date },
            set (v) { this.$store.commit('general/set_common', ['s_date', v]) }
        },

        e_date : {
            get () { return this.$store.state.general.e_date },
            set (v) { this.$store.commit('general/set_common', ['e_date', v]) }
        },

        title() {
            return "JURNAL UMUM"
        },

        selected_journal : {
            get () { return this.$store.state.general.selected_journal },
            set (v) { this.setObject('selected_journal', v) }
            // return this.$store.state.general.selected_account
        }
    },

    methods : {
        setObject(x, y) {
            this.$store.commit('general/set_object', [x, y])
        },

        setNewObject(x, y) {
            this.$store.commit('generalNew/set_object', [x, y])
        },

        setObjects(x, y) {
            for (let v of y)
                this.setObject(v, x[v])
        },

        setNewObjects(x, y) {
            for (let v of y)
                this.setNewObject(v, x[v])
        },

        one_money (x) {
            return window.one_money(x)
        },

        add (e) {
            e.preventDefault()
            this.setNewObject("edit", false)
            this.setNewObject("dialog_new", true)

            let details = JSON.parse(JSON.stringify(this.$store.state.generalNew.details))
            for (let d of details) {
                d.account = null
                d.debit = 0
                d.credit = 0
            }
            
            this.setNewObject('details', details)
            this.setNewObject('journal_id', 0)
            this.setNewObject('journal_note', '')
            this.setNewObject('journal_number', '')
            this.$store.commit("tag/set_object", ['selected_tagnames', []])
           
            return
        },

        edit_href (x) {
            return '../new/?id=' + x.journal_id
        },

        edit (x, e) {
            e.preventDefault()
            this.selected_journal = x

            this.setNewObject("edit", true)
            this.setNewObjects(x, [
                "journal_id",
                "journal_date",
                "journal_note",
                "journal_number"
            ])
            this.$store.commit("tag/set_object", ['selected_tagnames', x.journal_tags])

            let details = JSON.parse(JSON.stringify(this.$store.state.generalNew.details))
            for (let n in details) {
                if (x.details[n]) {
                    x.details[n].account = x.details[n].accountx
                    details[n] = x.details[n]
                }
                else {
                    details[n].account = null
                    details[n].debit = 0
                    details[n].credit = 0
                }
            }
            this.setNewObject("details", details)
            this.setNewObject("dialog_new", true)

            return
        },

        del (x) {
            this.select(x)
            this.$store.commit('set_dialog_delete', true)
        },

        confirm_del (x) {
            this.$store.dispatch('general/del', {id:x.data})
        },

        select (x) {
            this.setObject('selected_journal', x)
        },

        search () {
            return this.$store.dispatch('general/search', {})
        },

        change_page(x) {
            this.curr_page = x
            this.$store.dispatch('general/search', {})
        },

        change_s_date(x) {
            this.$store.commit('general/set_common', ['s_date', x.new_date])
            this.$store.dispatch('general/search')
        },

        change_e_date(x) {
            this.$store.commit('general/set_common', ['e_date', x.new_date])
            this.$store.dispatch('general/search')
        },

        is_selected(x) {
            if (!this.selected_journal) return false
            if (this.selected_journal.journal_id == x.journal_id) return true
            return false
        }
    },

    mounted () {
        // this.$store.dispatch('general/search')
    }
}
</script>