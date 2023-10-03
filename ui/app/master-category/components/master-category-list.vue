<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0">
            <v-layout row wrap>
                <v-flex xs9>
                    <h3 class="display-1 font-weight-light zalfa-text-title">MASTERDATA KATEGORI</h3>
                </v-flex>
                <v-flex xs3 class="text-xs-right">
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
                :items="categorys"
                :loading="false"
                hide-actions
                class="elevation-1">
                <template slot="items" slot-scope="props">
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.category_code }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.category_name }}</td>
                    <td class="text-xs-center pa-0" @click="select(props.item)">
                        <v-btn color="primary" class="btn-icon ma-0" small @click="edit(props.item)"><v-icon>create</v-icon></v-btn>
                        <v-btn color="red" dark class="btn-icon ma-0" small @click="del(props.item)"><v-icon>delete</v-icon></v-btn>
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

        <common-dialog-print :report_url="report_url" v-if="dialog_report"></common-dialog-print>
        <common-dialog-delete :data="category_id" @confirm_del="confirm_del" v-if="dialog_delete"></common-dialog-delete>
        

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
        "common-dialog-print" : httpVueLoader("../../common/components/common-dialog-print.vue")
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
                    width: "80%",
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
        categorys () {
            return this.$store.state.category.categorys
        },

        dialog_delete () {
            return this.$store.state.dialog_delete
        },

        category_id () {
            return this.$store.state.category.selected_category.category_id
        },

        query : {
            get () { return this.$store.state.category.search },
            set (v) { this.$store.commit('category/update_search', v) }
        },

        curr_page : {
            get () { return this.$store.state.category.current_page },
            set (v) { this.$store.commit('category/update_current_page', v) }
        },

        xtotal_page () {
            return this.$store.state.category.total_category_page
        },

        dialog_report : {
            get () { return this.$store.state.dialog_print },
            set (v) { this.$store.commit('set_dialog_print', v) }
        },

        report_url () {
            return this.$store.state.report_url
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        add () {
            this.$store.commit('category_new/set_common', ['edit', false])
            this.$store.commit('category_new/set_common', ['category_name', ''])
            this.$store.commit('category_new/set_common', ['category_code', ''])
            this.$store.commit('category_new/set_dialog_new', true)
        },

        edit (x) {
            this.select(x)
            let sc = x
            this.$store.commit('category_new/set_common', ['edit', true])
            this.$store.commit('category_new/set_common', ['category_name', sc.category_name])
            this.$store.commit('category_new/set_common', ['category_code', sc.category_code])
            this.$store.commit('category_new/set_dialog_new', true)
        },

        del (x) {
            this.select(x)
            this.$store.commit('set_dialog_delete', true)
        },

        confirm_del (x) {
            this.$store.dispatch('category/del')
        },

        select (x) {
            this.$store.commit('category/set_selected_category', x)
        },

        search () {
            return this.$store.dispatch('category/search', {})
        },

        change_page(x) {
            this.curr_page = x
            this.$store.dispatch('category/search', {})
        }
    }
}
</script>