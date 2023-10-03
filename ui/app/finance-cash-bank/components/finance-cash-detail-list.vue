<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0">
            <v-layout row wrap>
                <v-flex xs7>
                    <h3 class="display-1 font-weight-light zalfa-text-title">
                        <span class="grey--text font-weight-thin"><a href="javascript:;" class="grey--text font-weight-thin" @click="hide_detail()">KAS & BANK</a> »</span>
                        {{title.toUpperCase()}}</h3>
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

                            <!-- <v-btn color="success" class="ma-0 ml-2 btn-icon" @click="add" v-show="!is_sales">
                                <v-icon>add</v-icon>
                            </v-btn>   -->
                        </template>
                    </v-text-field>
                </v-flex>
            </v-layout>
        </v-card-title>
        <v-card-text class="pa-0">
            <trans-journal-list></trans-journal-list>
        </v-card-text>
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
        // "trans-journal-list" : httpVueLoader("../../trans-journal_detail/components/trans-journal-list.vue"),
        "trans-journal-list" : httpVueLoader("./finance-cash-detail-list-journal.vue?abc"),
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue')
    },

    computed : {
        s_date : {
            get () { return this.$store.state.journal_detail.s_date },
            set (v) { this.$store.commit('journal_detail/set_common', ['s_date', v]) }
        },

        e_date : {
            get () { return this.$store.state.journal_detail.e_date },
            set (v) { this.$store.commit('journal_detail/set_common', ['e_date', v]) }
        },

        query : {
            get () { return this.$store.state.journal_detail.search },
            set (v) { this.$store.commit('journal_detail/update_search', v) }
        },

        title () {
            if (this.$store.state.cash.selected_account)
                return this.$store.state.cash.selected_account.M_AccountName
            return ""
        }
    },

    methods : {
        change_s_date(x) {
            this.$store.commit('journal_detail/set_common', ['s_date', x.new_date])
            this.$store.dispatch('journal_detail/search')
        },

        change_e_date(x) {
            this.$store.commit('journal_detail/set_common', ['e_date', x.new_date])
            this.$store.dispatch('journal_detail/search')
        },

        search () {
            return this.$store.dispatch('journal_detail/search', {})
        },

        hide_detail () {
            this.$store.commit('set_selected_tab', 1)
        }
    }
}
</script>