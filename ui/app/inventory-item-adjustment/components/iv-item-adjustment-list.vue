<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0">
            <v-layout row wrap>
                <v-flex xs4>
                    <h3 class="display-1 font-weight-light zalfa-text-title">PENYESUAIAN STOK</h3>
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
                        disabled
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
                :items="adjustments"
                :loading="false"
                hide-actions
                class="elevation-1">
                <template slot="items" slot-scope="props">
                    <td class="text-xs-center pa-2" @click="select(props.item)">{{ reverse_date(props.item.I_AdjustDate) }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.I_AdjustNumber }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.warehouse_name }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.I_AdjustNote }}</td>
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
        
        <common-dialog-delete :data="adjustment_id" @confirm_del="confirm_del" v-if="dialog_delete"></common-dialog-delete>
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
                    width: "40%",
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
        adjustments () {
            return this.$store.state.adjustment.adjustments
        },

        dialog_delete () {
            return this.$store.state.dialog_delete
        },

        adjustment_id () {
            return this.$store.state.adjustment.selected_adjustment.I_AdjustID
        },

        edate : {
            get () { return this.$store.state.adjustment.edate },
            set (v) { this.$store.commit('adjustment/set_edate', v) }
        },

        sdate : {
            get () { return this.$store.state.adjustment.sdate },
            set (v) { this.$store.commit('adjustment/set_sdate', v) }
        },

        query : {
            get () { return this.$store.state.adjustment.query },
            set (v) { this.$store.commit('adjustment/set_common', ['query', v]) }
        },

        warehouses () {
            return this.$store.state.adjustment_new.warehouses
        },

        selected_warehouse : {
            get () { return this.$store.state.adjustment.selected_warehouse },
            set (v) { 
                this.$store.commit('adjustment/set_selected_warehouse', v)
                this.search()
            }
        }
    },

    methods : {
        add () {
            this.$store.commit('adjustment_new/set_common', ['edit', false])
            this.$store.commit('adjustment_new/set_dialog_new', true)
            this.$store.commit('adjustment_new/set_items', [])
            this.$store.commit('adjustment_new/set_common', ['adjustment_note', ''])
            // this.$store.commit('adjustment_new/set_selected_warehouse', null)
        },

        edit (x) {
            this.select(x)
            let sc = x
            this.$store.commit('adjustment_new/set_common', ['edit', true])
            this.$store.commit('adjustment_new/set_common', ['adjustment_note', sc.I_AdjustNote])
            this.$store.commit('adjustment_new/set_common', ['adjustment_number', sc.I_AdjustNumber])
            this.$store.dispatch('adjustment_new/search_detail', {})
            // this.$store.commit('adjustment_new/set_common', ['adjustment_address', sc.I_AdjustAddress])
            // this.$store.commit('adjustment_new/set_common', ['search_city', sc.full_address])
            // this.$store.commit('adjustment_new/set_selected_city', {kelurahan_id:sc.kelurahan_id,full_address:sc.full_address})
            this.$store.commit('adjustment_new/set_dialog_new', true)

            this.$store.commit('adjustment_new/set_selected_warehouse', null)
            for (let w of this.warehouses)
                if (w.warehouse_id == x.I_AdjustM_WarehouseID)
                    this.$store.commit('adjustment_new/set_selected_warehouse', w)

        },

        del (x) {
            this.select(x)
            this.$store.commit('set_dialog_delete', true)
        },

        confirm_del (x) {
            this.$store.dispatch('adjustment/del', {id:x.data})
        },

        select (x) {
            this.$store.commit('adjustment/set_selected_adjustment', x)
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
            this.$store.dispatch('adjustment/search', {})
        }
    },

    mounted () {
        this.$store.dispatch('adjustment_new/search_warehouse').then((x) => {
            this.selected_warehouse = x.records[0]
            this.$store.commit("adjustment_new/set_object", ["selected_warehouse", x.records[0]])
        })

        let get = window.location.search.substring(1)
        let qs = window.parse_query_string(get)
        if (get != "") {
            if (qs.sdate) this.sdate = qs.sdate
            if (qs.edate) this.edate = qs.edate
            if (qs.no) {
                this.query = qs.no
                // this.$store.commit('adjustment/set_common', ['search', qs.no])

                this.$store.dispatch('adjustment/search', {}).then((r) => {
                    this.edit(r.records[0])
                })
            }        
        } else {
            this.$store.dispatch('adjustment/search', {})
        }
    }
}
</script>