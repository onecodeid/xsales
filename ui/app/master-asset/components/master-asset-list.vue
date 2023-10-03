<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0">
            <v-layout row wrap>
                <v-flex xs9>
                    <h3 class="display-1 font-weight-light zalfa-text-title">MASTERDATA ASSET</h3>
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
                :items="assets"
                :loading="false"
                hide-actions
                class="elevation-1">
                <template slot="items" slot-scope="props">
                    
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.asset_code }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.asset_name }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)"><span class="blue--text mr-2">{{ props.item.account_code }}</span> {{ props.item.account_name }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.asset_acq_date }}</td>
                    <td class="text-xs-right py-2 pr-4 pl-0" @click="select(props.item)">Rp {{ one_money(props.item.asset_acq_cost) }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">
                        <span v-show="props.item.asset_depreciable=='Y'">
                            {{props.item.dep_method}}, {{props.item.asset_dep_time}} tahun / {{props.item.asset_dep_rate}}%
                        </span>
                        <span v-show="props.item.asset_depreciable!='Y'">Tidak</span>
                    </td>
                    
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
        
        <common-dialog-delete :data="asset_id" @confirm_del="confirm_del" v-if="dialog_delete"></common-dialog-delete>
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
        "common-dialog-delete" : httpVueLoader("../../common/components/common-dialog-delete.vue")
    },

    data () {
        return {
            headers: [
                {
                    text: "KODE",
                    align: "left",
                    sortable: false,
                    width: "7%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "NAMA",
                    align: "left",
                    sortable: false,
                    width: "20%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "AKUN",
                    align: "left",
                    sortable: false,
                    width: "18%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "TGL AKUISISI",
                    align: "left",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "NILAI AKUISISI",
                    align: "right",
                    sortable: false,
                    width: "12%",
                    class: "pa-2 pr-4 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "PENYUSUTAN",
                    align: "left",
                    sortable: false,
                    width: "23%",
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
        assets () {
            return this.$store.state.asset.assets
        },

        dialog_delete () {
            return this.$store.state.dialog_delete
        },

        asset_id () {
            return this.$store.state.asset.selected_asset.M_AssetID
        },

        query : {
            get () { return this.$store.state.asset.search },
            set (v) { this.$store.commit('asset/update_search', v) }
        },

        curr_page : {
            get () { return this.$store.state.asset.current_page },
            set (v) { this.$store.commit('asset/update_current_page', v) }
        },

        xtotal_page () {
            return this.$store.state.asset.total_asset_page
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        add () {
            this.$store.commit('asset_new/set_common', ['edit', false])
            this.$store.commit('asset_new/set_common', [
                ['asset_name',
                'asset_code',
                'asset_description',
                'acq_date',
                'asset_acq_cost'], ''])
            this.$store.commit('asset_new/set_common', ['asset_dep_time', null])
            this.$store.commit('asset_new/set_common', ['asset_depreciable', 'N'])
            this.$store.commit('asset_new/set_common', ['asset_dep_rate', 1])
            this.$store.commit('asset_new/set_common', ['asset_dep_value', 0])
            this.$store.commit('asset_new/set_common', ['asset_acq_cost', 0])

            this.$store.commit('asset_new/set_dep_accumulated_account', null)
            this.$store.commit('asset_new/set_dep_account', null)
            this.$store.commit('asset_new/set_selected_account', null)
            this.$store.commit('asset_new/set_selected_method', null)

            this.$store.commit('asset_new/set_common', ['dialog_new', true])
        },

        edit (x) {
            this.select(x)
            let sc = x
            this.$store.commit('asset_new/set_common', ['edit', true])
            this.$store.commit('asset_new/set_common', ['asset_id', sc.asset_id])
            this.$store.commit('asset_new/set_common', ['asset_name', sc.asset_name])
            this.$store.commit('asset_new/set_common', ['asset_code', sc.asset_code])
            this.$store.commit('asset_new/set_common', ['asset_description', sc.asset_description])
            this.$store.commit('asset_new/set_common', ['acq_date', sc.asset_acq_date])
            this.$store.commit('asset_new/set_common', ['asset_acq_cost', sc.asset_acq_cost])
            this.$store.commit('asset_new/set_common', ['asset_depreciable', sc.asset_depreciable])
            this.$store.commit('asset_new/set_common', ['asset_dep_time', sc.asset_dep_time])
            this.$store.commit('asset_new/set_common', ['asset_dep_rate', sc.asset_dep_rate])
            this.$store.commit('asset_new/set_common', ['asset_dep_value', sc.asset_dep_value])

            let methods = this.$store.state.asset_new.methods
            let accounts = this.$store.state.asset_new.accounts

            for (let m of methods)
                if (m.depmethod_id == sc.asset_dep_method)
                    this.$store.commit('asset_new/set_selected_method', m)

            for (let a of accounts) {
                if (a.account_id == sc.asset_account_id)
                    this.$store.commit('asset_new/set_selected_account', a)
                if (a.account_id == sc.asset_acq_account)
                    this.$store.commit('asset_new/set_acq_account', a)
                if (a.account_id == sc.asset_dep_account)
                    this.$store.commit('asset_new/set_dep_account', a)
                if (a.account_id == sc.asset_dep_accumulated_account)
                    this.$store.commit('asset_new/set_dep_accumulated_account', a)
            }
                

            // this.$store.commit('asset_new/set_common', ['asset_code', sc.asset_code])
            // this.$store.commit('asset_new/set_common', ['asset_code', sc.asset_code])
            // this.$store.commit('asset_new/set_common', ['asset_code', sc.asset_code])
            // this.$store.commit('asset_new/set_common', ['asset_code', sc.asset_code])
            // this.$store.commit('asset_new/set_common', ['asset_code', sc.asset_code])
            // this.$store.commit('asset_new/set_common', ['asset_code', sc.asset_code])

            
            //         : context.state.asset_description,
            //         : context.state.selected_account.account_id,
            //         : context.state.acq_date,
            //         : context.state.asset_acq_cost,
            //         : context.state.asset_acq_account.account_id,
            //         : context.state.asset_depreciable,
            //         : context.state.selected_method.depmethod_id,
            //         : context.state.asset_dep_time,
            //         : context.state.asset_dep_rate,
            //         : context.state.asset_dep_account.account_id,
            //         asset_dep_accumulated_account: context.state.asset_dep_accumulated_account.account_id,
            //         asset_dep_value: context.state.

            this.$store.commit('asset_new/set_common', ['dialog_new', true])
        },

        del (x) {
            this.select(x)
            this.$store.commit('set_dialog_delete', true)
        },

        confirm_del (x) {
            this.$store.dispatch('asset/del', {id:x.data})
        },

        select (x) {
            this.$store.commit('asset/set_selected_asset', x)
        },

        search () {
            return this.$store.dispatch('asset/search', {})
        },

        change_page(x) {
            this.curr_page = x
            this.$store.dispatch('asset/search', {})
        }
    }
}
</script>