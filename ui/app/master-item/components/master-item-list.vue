<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0">
            <v-layout row wrap>
                <v-flex xs6>
                    <h3 class="display-1 font-weight-light zalfa-text-title">MASTERDATA BARANG</h3>
                </v-flex>
                <v-flex xs3 pr-2>
                    <!-- <v-select
                        :items="assemblies"
                        v-model="selected_assembly"
                        label="label"
                        solo
                        placeholder="SEMUA ITEM"
                        return-object
                        item-value="id"
                        item-text="text"
                        clearable
                    ></v-select> -->
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

                            <v-btn color="success" class="ma-0 ml-2 btn-icon" @click="add" :disabled="!!view">
                                <v-icon>add</v-icon>
                            </v-btn>
                            <v-btn color="orange" dark class="ma-0 ml-2 btn-icon" @click="print_stock_recapt">
                                <v-icon>print</v-icon>
                            </v-btn> 
                        </template>
                    </v-text-field>
                </v-flex>
            </v-layout>
        </v-card-title>
        <v-card-text class="pt-2">
            <v-data-table 
                :headers="headers"
                :items="items"
                :loading="false"
                hide-actions
                class="elevation-1">
                <template slot="items" slot-scope="props">
                    
                    <tr :class="[props.item.item_assembly=='Y'?'cyan lighten-4':'']" @dblclick="histories(props.item)">
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.item_code }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.item_name }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.category_name }}</td>
                    
                    <td class="text-xs-right pa-2" @click="select(props.item)">{{ one_money(props.item.stock_qty) }}</td>
                    <td class="text-xs-center pa-2" @click="select(props.item)">{{ props.item.unit_name }}</td>
                    <td class="text-xs-center pa-2" @click="select(props.item)">
                        <v-btn color="orange" small @click="purchase_history(props.item)" class="ma-0 btn-icon white--text" title="Histori Pembelian"><v-icon>history</v-icon></v-btn>
                    </td>
                    
                    
                    <td class="text-xs-right pa-2" @click="select(props.item)">Rp {{ one_money(props.item.item_hpp) }}</td>
                    <td class="text-xs-right pa-2" @click="select(props.item)">Rp {{ one_money(props.item.item_price) }}</td>
                    
                    <td class="text-xs-center pa-0" @click="select(props.item)">
                        <!-- <v-btn color="orange" dark class="btn-icon ma-0" small @click="print_stock_card(props.item)" title="Cetak Kartu Stok"><v-icon>print</v-icon></v-btn> -->
                        <v-btn color="primary" class="btn-icon ma-0" small @click="edit(props.item)"><v-icon>create</v-icon></v-btn>
                        <v-btn color="red" class="btn-icon ma-0" small @click="del(props.item)" :disabled="!!view" :dark="!view"><v-icon>delete</v-icon></v-btn>
                    </td>
                    </tr>
                    <!-- <td class="text-xs-center pa-2" v-bind:class="{'amber lighten-4':isSelected(props.item)}" @click="selectMe(props.item)">{{ props.item.M_DoctorHP}}</td>
                    <td class="text-xs-left pa-2" v-bind:class="{'amber lighten-4':isSelected(props.item)}" @click="selectMe(props.item)">{{ props.item.status}}</td> -->
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
        <common-dialog-delete :data="item_id" @confirm_del="confirm_del" v-if="dialog_delete"></common-dialog-delete>
        <master-item-stock-card-print></master-item-stock-card-print>

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
        "common-dialog-print" : httpVueLoader("../../common/components/common-dialog-print.vue"),
        "master-item-stock-card-print" : httpVueLoader("./master-item-stock-card-print.vue?t=123")
    },

    data () {
        return {
            headers: [
                {
                    text: "KODE",
                    align: "left",
                    sortable: false,
                    width: "5%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "NAMA",
                    align: "left",
                    sortable: false,
                    width: "29%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "KATEGORI",
                    align: "left",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "STOK",
                    align: "right",
                    sortable: false,
                    width: "7%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "UNIT",
                    align: "center",
                    sortable: false,
                    width: "5%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "HISTORY",
                    align: "center",
                    sortable: false,
                    width: "5%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "HPP / HARGA BELI",
                    align: "right",
                    sortable: false,
                    width: "7%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "HARGA JUAL",
                    align: "right",
                    sortable: false,
                    width: "7%",
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
        items () {
            return this.$store.state.item.items
        },

        assemblies () {
            return this.$store.state.item.assemblies
        },

        dialog_delete () {
            return this.$store.state.dialog_delete
        },

        item_id () {
            return this.$store.state.item.selected_item.M_ItemID
        },

        query : {
            get () { return this.$store.state.item.search },
            set (v) { this.$store.commit('item/update_search', v) }
        },

        curr_page : {
            get () { return this.$store.state.item.current_page },
            set (v) { this.$store.commit('item/update_current_page', v) }
        },

        xtotal_page () {
            return this.$store.state.item.total_item_page
        },

        dialog_report : {
            get () { return this.$store.state.dialog_print },
            set (v) { this.$store.commit('set_dialog_print', v) }
        },

        report_url () {
            return this.$store.state.report_url
        },

        selected_assembly : {
            get () { return this.$store.state.item.selected_assembly },
            set (v) { 
                this.$store.commit('item/set_object', ['selected_assembly', v])
                this.search()
            }
        },

        view () { return this.$store.state.view }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        add () {
            this.$store.dispatch('item_new/search_level_price', {})
            this.$store.commit('item_new/set_common', ['edit', false])
            this.$store.commit('item_new/set_common', ['item_name', ''])
            this.$store.commit('item_new/set_common', ['item_alias', ''])
            this.$store.commit('item_new/set_common', ['item_code', ''])
            this.$store.commit('item_new/set_common', ['item_weight', 0])
            this.$store.commit('item_new/set_common', ['item_img', ''])
            this.$store.commit('item_new/set_common', ['item_min', 0])
            this.$store.commit('item_new/set_common', ['item_hpp', 0])
            this.$store.commit('item_new/set_common', ['item_hpp_edit', true])
            this.$store.commit('item_new/set_common', ['item_assembly', 'N'])
            this.$store.commit('item_new/set_common', ['item_assembly_note', ""])
            this.$store.commit('item_new/set_common', ['item_assembly_qty', 0])
            this.$store.commit('item_new/set_selected_unit', null)
            this.$store.commit('item_new/set_selected_category', null)
            this.$store.commit('item_new/set_selected_pack', null)
            this.$store.commit('item_new/set_object', ['selected_disc', null])
            this.$store.commit('item_new/set_selected_view_in_pack', this.$store.state.item_new.view_in_packs[0])
            this.$store.commit('item_new/set_common', ['sdate', new Date().toISOString().substr(0, 10)])
            this.$store.commit('item_new/set_common', ['edate', new Date().toISOString().substr(0, 10)])
            this.$store.commit('item_new/set_dialog_new', true)

            this.$store.commit('item_new/set_aliases', [])
            this.$store.commit('item_new/set_itempacks', [])
            this.$store.commit('item_new/set_object', ['assemblies', []])
            this.$store.commit('item_new/set_object', ['assembly_costs', []])
        },

        edit (x) {
            this.select(x)
            let sc = x
            this.$store.commit('item_new/set_common', ['edit', true])
            this.$store.commit('item_new/set_common', ['item_name', sc.item_name])
            this.$store.commit('item_new/set_common', ['item_alias', sc.item_alias])
            this.$store.commit('item_new/set_common', ['item_code', sc.item_code])
            this.$store.commit('item_new/set_common', ['item_min', sc.item_min_stock])
            this.$store.commit('item_new/set_common', ['item_hpp', sc.item_hpp])
            this.$store.commit('item_new/set_common', ['item_hpp_edit', true])
            this.$store.commit('item_new/set_common', ['item_price', sc.item_price])
            this.$store.commit('item_new/set_common', ['item_assembly', sc.item_assembly])
            this.$store.commit('item_new/set_common', ['item_assembly_note', sc.item_assembly_note])
            this.$store.commit('item_new/set_common', ['item_assembly_qty', sc.item_assembly_qty])

            // UNIT
            let u = this.$store.state.item_new.units
            this.$store.commit('item_new/set_selected_unit', null)
            for (let x of u)
                if (x.unit_id == sc.unit_id)
                    this.$store.commit('item_new/set_selected_unit', x)

            // CATEGORY
            u = this.$store.state.item_new.categories
            this.$store.commit('item_new/set_selected_category', null)
            for (let x of u)
                if (x.category_id == sc.category_id)
                    this.$store.commit('item_new/set_selected_category', x)

            // CATEGORY
            uu = this.$store.state.item_new.discs
            this.$store.commit('item_new/set_object', ['selected_disc', null])
            for (let x of uu)
                if (x.disc_id == sc.disc_id)
                    this.$store.commit('item_new/set_object', ['selected_disc', x])

            // CPACK
            u = this.$store.state.item_new.packs
            this.$store.commit('item_new/set_selected_pack', null)
            for (let x of u)
                if (x.pack_id == sc.pack_id)
                    this.$store.commit('item_new/set_selected_pack', x)

            // VIEW IN PACK
            u = this.$store.state.item_new.view_in_packs
            this.$store.commit('item_new/set_selected_view_in_pack', u[0])
            for (let x of u)
                if (x.id == sc.view_pack)
                    this.$store.commit('item_new/set_selected_view_in_pack', x)

            // STOCKMINS
            this.$store.commit("item_new/set_stocks", x.stocks)

            // ASSEMBLIES
            if (x.item_assembly == "Y") {
                this.$store.commit("item_new/set_object", ['assemblies', x.assemblies])

                let costs = []
                for (let cost of x.costs)
                    costs.push({account:cost,amount:cost.amount})
                this.$store.commit("item_new/set_object", ['assembly_costs', costs])
            } else {
                this.$store.commit('item_new/set_object', ['assemblies', []])
                this.$store.commit('item_new/set_object', ['assembly_costs', []])
            }
            

            this.$store.dispatch('item_new/search_alias')
            this.$store.dispatch('item_new/search_itempack')
            this.$store.commit('item_new/set_dialog_new', true)
        },

        del (x) {
            this.select(x)
            this.$store.commit('set_dialog_delete', true)
        },

        confirm_del (x) {
            this.$store.dispatch('item/del')
        },

        select (x) {
            this.$store.commit('item/set_selected_item', x)
        },

        search () {
            return this.$store.dispatch('item/search', {})
        },

        change_page(x) {
            this.curr_page = x
            this.$store.dispatch('item/search', {})
        },

        print_stock_card (x) {
            this.$store.commit('item/set_common', ['dialog_stock_card', true])
        },

        view_stock (x) {
            this.select(x)
            this.$store.commit('item_new/set_common', ['dialog_stock', true])
            this.$store.dispatch('item_new/search_stock')
        },

        print_stock_recapt () {
            this.$store.commit('set_report_url', this.$store.state.item.URL+'report/one_iv_001')
            this.$store.commit('set_dialog_print', true)
        },

        purchase_history(v) {
            return this.histories(v)
            // this.$store.commit('item_logpurchase/set_object', ['purchases', []])
            // this.$store.commit('item_logpurchase/set_object', ['selected_purchase', null])
            // this.$store.commit('item_logpurchase/set_common', ['item_name', '-'])

            // this.select(v)
            // this.$store.commit('item_logpurchase/set_object', ['purchases', v.log_purchase])        
            // this.$store.commit('item_logpurchase/set_common', ['item_name', v.item_name])
            // this.$store.commit('item_logpurchase/set_common', ['dialog', true])
        },

        histories (v) {
            this.select(v)
            this.$store.dispatch('item/search_history')
            this.$store.commit('item/set_common', ['dialog_history', true])
        }
    }
}
</script>