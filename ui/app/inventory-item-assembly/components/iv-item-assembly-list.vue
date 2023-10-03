<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0">
            <v-layout row wrap>
                <v-flex xs4>
                    <h3 class="display-1 font-weight-light zalfa-text-title">PERAKITAN PRODUK</h3>
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

                <v-flex xs3 pr-2>
                    <v-select
                        :items="warehouses"
                        v-model="selected_warehouse"
                        label="Gudang"
                        return-object
                        item-text="warehouse_name"
                        item-value="warehouse_id"
                        solo
                        hint="Pilih gudang"
                        persistent-hint
                        clearable
                        placeholder="Semua Gudang"
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
                :items="assemblys"
                :loading="false"
                hide-actions
                class="elevation-1">
                <template slot="items" slot-scope="props">
                    <td class="text-xs-center pa-2" @click="select(props.item)">{{ reverse_date(props.item.I_AssemblyDate) }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.I_AssemblyNumber }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.warehouse_name }}</td>
                    
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.out_item_name }}</td>
                    <td class="text-xs-right pa-2" @click="select(props.item)">{{ props.item.out_item_qty }} {{props.item.unit_name}}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.item_names }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.I_AssemblyNote }}</td>
                    <td class="text-xs-center pa-0" @click="select(props.item)">
                        <v-btn color="primary" class="btn-icon ma-0" small @click="edit(props.item)"><v-icon>info</v-icon></v-btn>
                        <v-btn color="red" dark class="btn-icon ma-0" small @click="del(props.item)"><v-icon>delete</v-icon></v-btn>
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
        
        <common-dialog-delete :data="assembly_id" @confirm_del="confirm_del" v-if="dialog_delete"></common-dialog-delete>
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
                    text: "GUDANG",
                    align: "left",
                    sortable: false,
                    width: "11%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "OUTPUT PRODUK",
                    align: "left",
                    sortable: false,
                    width: "15%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "OUTPUT QTY",
                    align: "right",
                    sortable: false,
                    width: "7%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "PRODUK BAHAN",
                    align: "left",
                    sortable: false,
                    width: "25%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "CATATAN",
                    align: "left",
                    sortable: false,
                    width: "18%",
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
        assemblys () {
            return this.$store.state.assembly.assemblys
        },

        dialog_delete () {
            return this.$store.state.dialog_delete
        },

        assembly_id () {
            return this.$store.state.assembly.selected_assembly.I_AssemblyID
        },

        edate : {
            get () { return this.$store.state.assembly.e_date },
            set (v) { this.$store.commit('assembly/set_edate', v) }
        },

        sdate : {
            get () { return this.$store.state.assembly.s_date },
            set (v) { this.$store.commit('assembly/set_sdate', v) }
        },

        query : {
            get () { return this.$store.state.assembly.query },
            set (v) { this.$store.commit('assembly/set_common', ['query', v]) }
        },

        warehouses () {
            return this.$store.state.assembly_new.warehouses
        },

        selected_warehouse : {
            get () { return this.$store.state.assembly.selected_warehouse },
            set (v) { 
                this.$store.commit('assembly/set_selected_warehouse', v)
                this.search()
            }
        }
    },

    methods : {
        add () {
            this.$store.commit('assembly_new/set_common', ['edit', false])
            this.$store.commit('assembly_new/set_common', ['view', false])
            this.$store.commit('assembly_new/set_dialog_new', true)
            this.$store.commit('assembly_new/set_details', [])
            this.$store.commit('assembly_new/set_common', ['assembly_date', this.$store.state.assembly.current_date])
            this.$store.commit('assembly_new/set_common', ['assembly_note', ''])
            this.$store.commit('assembly_new/set_selected_warehouse', null)
            this.$store.commit('assembly_new/set_selected_out_item', null)
            this.$store.commit('assembly_new/set_common', ['out_item_qty', 0])
        },

        edit (x) {
            this.select(x)
            let sc = x
            this.$store.commit('assembly_new/set_common', ['edit', true])
            this.$store.commit('assembly_new/set_common', ['view', true])
            this.$store.commit('assembly_new/set_common', ['assembly_date', sc.I_AssemblyDate.substr(0,10)])
            this.$store.commit('assembly_new/set_common', ['assembly_note', sc.I_AssemblyNote])
            this.$store.commit('assembly_new/set_common', ['assembly_number', sc.I_AssemblyNumber])
            this.$store.commit('assembly_new/set_details', sc.details)
            this.$store.commit('assembly_new/set_costs', sc.costs)
            this.$store.commit('assembly_new/set_selected_warehouse', sc.warehouse)
            this.$store.commit('assembly_new/set_selected_out_item', sc.out_item)
            this.$store.commit('assembly_new/set_common', ['out_item_qty', sc.out_item_qty])
            // this.$store.commit('assembly_new/set_common', ['assembly_address', sc.I_AssemblyAddress])
            // this.$store.commit('assembly_new/set_common', ['search_city', sc.full_address])
            // this.$store.commit('assembly_new/set_selected_city', {kelurahan_id:sc.kelurahan_id,full_address:sc.full_address})
            this.$store.commit('assembly_new/set_dialog_new', true)

            this.$store.commit('assembly_new/set_selected_warehouse', null)
            for (let w of this.warehouses)
                if (w.warehouse_id == x.I_AssemblyM_WarehouseID)
                    this.$store.commit('assembly_new/set_selected_warehouse', w)

        },

        del (x) {
            this.select(x)
            this.$store.commit('set_dialog_delete', true)
        },

        confirm_del (x) {
            this.$store.dispatch('assembly/del', {id:x.data})
        },

        select (x) {
            this.$store.commit('assembly/set_selected_assembly', x)
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
            this.$store.dispatch('assembly/search', {})
        }
    },

    mounted () {
        this.$store.dispatch('assembly_new/search_warehouse')
    }
}
</script>