<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0">
            <v-layout row wrap>
                <v-flex xs3>
                    <h3 class="display-1 font-weight-light zalfa-text-title">TRANSFER GUDANG</h3>
                </v-flex>
                
                <v-flex xs3>
                    <v-layout row wrap>
                        <v-flex xs6>
                            <common-datepicker
                                label="Tanggal"
                                :date="sdate"
                                data="0"
                                @change="change_sdate"
                                classs="mt-0 ml-5"
                                hints="Tanggal Awal"
                                :details="true"
                            ></common-datepicker>
                        </v-flex>
                        <v-flex xs6>
                            <common-datepicker
                                label="Tanggal"
                                :date="edate"
                                data="0"
                                @change="change_edate"
                                classs="mt-0 ml-1 mr-5"
                                hints="Tanggal Akhir"
                                :details="true"
                            ></common-datepicker>
                        </v-flex>
                    </v-layout>
                </v-flex>

                <v-flex xs2 pr-1>
                    <v-select
                        :items="warehouses"
                        v-model="selected_warehouse"
                        placeholder="Gudang Asal"    
                        return-object
                        item-text="warehouse_short_name"
                        item-value="warehouse_id"
                        solo
                        clearable
                        hint="Gudang Asal"
                        persistent-hint
                    ></v-select>
                </v-flex>
                <v-flex xs2 pl-1 pr-2>
                    <v-select
                        :items="warehouses"
                        v-model="selected_to_warehouse"
                        placeholder="Gudang Tujuan"
                        return-object
                        item-text="warehouse_short_name"
                        item-value="warehouse_id"
                        solo
                        clearable
                        hint="Gudang Tujuan"
                        persistent-hint
                    ></v-select>
                </v-flex>

                <v-flex xs2 class="text-xs-right">
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
                :items="transfers"
                :loading="false"
                hide-actions
                class="elevation-1">
                <template slot="items" slot-scope="props">
                    <td class="text-xs-center pa-2" @click="select(props.item)">{{ reverse_date(props.item.I_TransferDate) }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.I_TransferNumber }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.from_warehouse_name }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.to_warehouse_name }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.I_TransferNote }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.item_names }}</td>
                    <td class="text-xs-center pa-0" @click="select(props.item)">
                        <v-btn color="primary" class="btn-icon ma-0" small @click="edit(props.item)"><v-icon>info</v-icon></v-btn>
                        <!-- <v-btn color="red" dark class="btn-icon ma-0" small @click="del(props.item)"><v-icon>delete</v-icon></v-btn> -->
                    </td>
                    <!-- <td class="text-xs-center pa-2" v-bind:class="{'amber lighten-4':isSelected(props.item)}" @click="selectMe(props.item)">{{ props.item.M_DoctorHP}}</td>
                    <td class="text-xs-left pa-2" v-bind:class="{'amber lighten-4':isSelected(props.item)}" @click="selectMe(props.item)">{{ props.item.status}}</td> -->
                </template>
            </v-data-table>
            <v-divider></v-divider>
            <v-pagination
                style="margin-top:10px;margin-bottom:10px"
                v-model="curr_page"
                :length="xtotal_page"
            ></v-pagination>
        </v-card-text>
        
        <common-dialog-delete :data="transfer_id" @confirm_del="confirm_del" v-if="dialog_delete"></common-dialog-delete>
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
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue')
    },

    data () {
        return {
            curr_page: 1,
            xtotal_page: 1,
            headers: [
                {
                    text: "TANGGAL",
                    align: "center",
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
                    text: "GUDANG ASAL",
                    align: "left",
                    sortable: false,
                    width: "11%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "GUDANG TUJUAN",
                    align: "left",
                    sortable: false,
                    width: "11%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "CATATAN",
                    align: "left",
                    sortable: false,
                    width: "25%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "ITEM",
                    align: "left",
                    sortable: false,
                    width: "29%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "ACTION",
                    align: "center",
                    sortable: false,
                    width: "8%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                }
            ]
        }
    },

    computed : {
        transfers () {
            return this.$store.state.transfer.transfers
        },

        dialog_delete () {
            return this.$store.state.dialog_delete
        },

        transfer_id () {
            return this.$store.state.transfer.selected_transfer.I_TransferID
        },

        edate : {
            get () { return this.$store.state.transfer.e_date },
            set (v) { this.$store.commit('transfer/set_edate', v) }
        },

        sdate : {
            get () { return this.$store.state.transfer.s_date },
            set (v) { this.$store.commit('transfer/set_sdate', v) }
        },

        query : {
            get () { return this.$store.state.transfer.query },
            set (v) { this.$store.commit('transfer/set_common', ['query', v]) }
        },

        warehouses () {
            return this.$store.state.transfer_new.warehouses
        },

        selected_warehouse : {
            get () { return this.$store.state.transfer.selected_warehouse },
            set (v) { 
                this.$store.commit('transfer/set_selected_warehouse', v)
                this.search()
            }
        },

        selected_to_warehouse : {
            get () { return this.$store.state.transfer.selected_to_warehouse },
            set (v) { 
                this.$store.commit('transfer/set_selected_to_warehouse', v)
                this.search()
            }
        }
    },

    methods : {
        add () {
            this.$store.commit('transfer_new/set_common', ['edit', false])
            this.$store.commit('transfer_new/set_dialog_new', true)
            this.$store.commit('transfer_new/set_items', [])
            this.$store.commit('transfer_new/set_common', ['transfer_date', this.$store.state.transfer.current_date])
            this.$store.commit('transfer_new/set_common', ['transfer_note', ''])
            this.$store.commit('transfer_new/set_selected_warehouse', null)
            this.$store.commit('transfer_new/set_selected_to_warehouse', null)
        },

        edit (x) {
            if (this.warehouses.length < 1) {
                this.$store.dispatch('transfer_new/search_warehouse').then(() => {
                    this.do_edit(x)
                })
            } else {
                this.do_edit(x)
            }
        },

        do_edit (x) {
            this.select(x)
            let sc = x
            this.$store.commit('transfer_new/set_common', ['edit', true])
            this.$store.commit('transfer_new/set_common', ['transfer_date', sc.I_TransferDate.substr(0,10)])
            this.$store.commit('transfer_new/set_common', ['transfer_note', sc.I_TransferNote])
            this.$store.commit('transfer_new/set_common', ['transfer_number', sc.I_TransferNumber])
            this.$store.dispatch('transfer_new/search_detail', {})
            // this.$store.commit('transfer_new/set_common', ['transfer_address', sc.I_TransferAddress])
            // this.$store.commit('transfer_new/set_common', ['search_city', sc.full_address])
            // this.$store.commit('transfer_new/set_selected_city', {kelurahan_id:sc.kelurahan_id,full_address:sc.full_address})
            this.$store.commit('transfer_new/set_selected_warehouse', null)
            for (let w of this.warehouses)
                if (w.warehouse_id == x.I_TransferM_WarehouseID)
                    this.$store.commit('transfer_new/set_selected_warehouse', w)

            this.$store.commit('transfer_new/set_selected_to_warehouse', null)
            for (let w of this.warehouses)
                if (w.warehouse_id == x.I_TransferToM_WarehouseID)
                    this.$store.commit('transfer_new/set_selected_to_warehouse', w)

            this.$store.commit('transfer_new/set_dialog_new', true)
        },

        del (x) {
            this.select(x)
            this.$store.commit('set_dialog_delete', true)
        },

        confirm_del (x) {
            this.$store.dispatch('transfer/del', {id:x.data})
        },

        select (x) {
            this.$store.commit('transfer/set_selected_transfer', x)
        },

        reverse_date(x) {
            return x.substr(0,10).split('-').reverse().join('-')
        },

        change_edate(x) {
            this.edate = x.new_date
            this.search()
        },

        change_sdate(x) {
            this.sdate = x.new_date
            this.search()
        },

        search () {
            this.$store.dispatch('transfer/search', {})
        }
    },

    mounted () {
        this.$store.dispatch('transfer_new/search_warehouse')

        let get = window.location.search.substring(1)
        let qs = window.parse_query_string(get)
        if (get != "") {
            if (qs.sdate) this.sdate = qs.sdate
            if (qs.edate) this.edate = qs.edate
            if (qs.no) {
                this.query = qs.no
                // this.$store.commit('adjustment/set_common', ['search', qs.no])

                this.$store.dispatch('transfer/search', {}).then((r) => {
                    this.edit(r.records[0])
                })
            }        
        } else {
            this.$store.dispatch('transfer/search', {})
        }
        
    }
}
</script>