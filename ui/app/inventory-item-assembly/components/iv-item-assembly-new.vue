<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="1000px"
        transition="dialog-transition"
        content-class="dialog-new"
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <v-layout row wrap>
                    <v-flex xs6>
                        <h3 v-show="!edit">PERAKITAN PRODUK [ BARU ]</h3>
                        <h3 v-show="edit">PERAKITAN PRODUK NO <span class="orange--text">{{ selected_assembly.I_AssemblyNumber }}</span></h3>
                    </v-flex>
                    <v-flex xs6 class="text-xs-right">
                        <!-- <v-btn color="success" small @click="add_item" v-show="!edit"
                            :disabled="!selected_warehouse">
                            <v-icon class="mr-2 zalfa-font-12">add</v-icon> Tambah Item</v-btn> -->
                    </v-flex>
                </v-layout>
                
            </v-card-title>
            
            <v-card-text>
                <v-layout row wrap>
                    <!-- <v-flex xs6 pr-4>
                        <v-layout row wrap>
                            <v-flex xs12>
                                <v-text-field
                                    label="Nomor"
                                    hide-details
                                    v-model="assembly_number"
                                    readonly
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs12 mt-3>
                                <v-text-field
                                    label="Tanggal"
                                    hide-details
                                    v-model="assembly_date"
                                ></v-text-field>
                            </v-flex>
                        </v-layout>
                    </v-flex> -->

                    <v-flex xs12>
                        <v-layout row wrap>
                            <v-flex xs4>
                                <v-select
                                    :items="warehouses"
                                    v-model="selected_warehouse"
                                    label="Gudang"
                                    return-object
                                    item-text="warehouse_name"
                                    item-value="warehouse_id"
                                    :disabled="!!view"
                                    :error="!selected_warehouse"
                                    :error-count="!selected_warehouse?1:0"
                                    :error-messages="!selected_warehouse?['Pilih dulu Gudangnya !']:[]"
                                ></v-select>
                            </v-flex>
                            <v-flex xs6 pl-4>
                                <v-autocomplete
                                    :items="items_master"
                                    :value="selected_out_item"
                                    return-object
                                    solo
                                    hint="OUTPUT PRODUK"
                                    persistent-hint
                                    clearable
                                    :readonly="!!selected_out_item"
                                    @change="update_item($event)"
                                    item-text="item_name"
                                    item-value="item_id"
                                    placeholder="Pilih..."
                                    v-show="!selected_out_item"
                                >
                                    <template slot="item" slot-scope="data">
                                        {{data.item.item_name}}
                                    </template>

                                    <template slot="selection" slot-scope="data">
                                        {{data.item.item_name}}
                                    </template>
                                </v-autocomplete>

                                <v-layout row wrap v-if="!!selected_out_item">
                                    <v-flex xs12>
                                        <v-text-field
                                            solo
                                            :value="selected_out_item.item_name"
                                            :clearable="!view"
                                            readonly
                                            @click:clear="update_item(null)"
                                            class="blue--text"
                                            hint="OUTPUT PRODUK"
                                            persistent-hint
                                            >
                                        </v-text-field>
                                    </v-flex>
                                    
                                </v-layout>
                            </v-flex>
                            <v-flex xs2 pl-2>
                                <v-text-field
                                    solo
                                    v-model="out_item_qty"
                                    class="blue--text"
                                    hint="OUTPUT QTY"
                                    persistent-hint
                                    reverse
                                    :readonly="!!view"
                                    >
                                    <template slot="prepend-inner">
                                        <span v-if="!!selected_out_item" class="pl-1">{{selected_out_item.unit_name}}</span>
                                        <span v-show="!selected_out_item" class="pl-1">Unit</span>
                                    </template>
                                </v-text-field>
                            </v-flex>
                            <v-flex xs2>
                                <common-datepicker
                                    label="Tanggal"
                                    :date="assembly_date"
                                    data="0"
                                    @change="assembly_date = $event.new_date"
                                    hints="TANGGAL PERAKITAN"
                                    :details="true"
                                    :prepend_icon="true"
                                    v-if="dialog && !view"
                                ></common-datepicker>
                                <v-text-field
                                    readonly
                                    :value="assembly_date"
                                    solo
                                    hint="TANGGAL PERAKITAN"
                                    v-show="!!view"
                                    persistent-hint
                                >
                                    <template slot="prepend-inner">
                                        <v-icon class="primary--text">calendar_today</v-icon>
                                    </template>
                                </v-text-field>
                            </v-flex>
                            <v-flex xs10 pl-4>
                                <v-text-field
                                    label="Catatan"
                                    v-model="assembly_note"
                                    
                                    :readonly="!!view"
                                    placeholder="Tuliskan catatan anda disini">
                                </v-text-field>
                            </v-flex>
                        </v-layout>
                    </v-flex>
                </v-layout>

                <v-layout row wrap>
                    <v-flex xs12>
                        <v-alert
                            :value="error"
                            color="error"
                            icon="warning"
                            dismissible
                            >
                            {{error_msg}}
                        </v-alert>
                    </v-flex>
                </v-layout>
                <v-layout row wrap>
                    <v-flex xs12>
                        <v-card>
                            <v-card-title primary-title class="py-2 px-3 cyan white--text">
                                <v-layout row wrap>
                                    <v-flex xs5><h3 class="subheading text-xs-center">PRODUK</h3></v-flex>
                                    <v-flex xs7>
                                        <v-layout row wrap>
                                            <v-flex xs3 pr-2>
                                                <h3 class="subheading text-xs-right">QTY / {{ selected_out_unit }}</h3>
                                            </v-flex>
                                            <v-flex xs3 pr-3>
                                                <h3 class="subheading text-xs-right">BIAYA / {{ selected_out_unit }}</h3>
                                            </v-flex>
                                            <v-flex xs3 pr-3>
                                                <h3 class="subheading text-xs-right">TOTAL QTY</h3>
                                            </v-flex>
                                            <v-flex xs3 pr-3>
                                                <h3 class="subheading text-xs-right">TOTAL BIAYA</h3>
                                            </v-flex>
                                        </v-layout>
                                    </v-flex>
                                </v-layout>
                            </v-card-title>
                            <v-card-text class="px-3 py-2" v-show="!!selected_warehouse">
                                <v-layout row wrap v-for="(d, n) in details" :key="d.detail_id" :class="{'mt-2':n>0}">
                                    <v-flex xs5 pr-3>
                                        <v-autocomplete
                                            :items="items"
                                            :value="d.item"
                                            return-object
                                            solo
                                            hide-details
                                            clearable
                                            :readonly="!!d.item"
                                            @change="update_item_detail(n, $event)"
                                            item-text="item_name"
                                            item-value="detail_id"
                                            placeholder="Pilih..."
                                            v-show="!d.item"
                                        >
                                            <template slot="item" slot-scope="data">
                                                <v-layout row wrap>
                                                    <v-flex xs12>{{data.item.item_name}}</v-flex>
                                                    <!-- <v-flex xs12 class="caption blue--text">
                                                    </v-flex> -->
                                                </v-layout> 
                                            </template>

                                            <template slot="selection" slot-scope="data">
                                                <span class="blue--text">{{data.item.item_code}}</span> &nbsp; {{data.item.item_name}}
                                            </template>

                                            <template slot="prepend">
                                                <v-btn color="red" class="ma-0 mr-1" icon :dark="false" flat @click="del_detail(n)" :disabled="!!view" ><v-icon>delete</v-icon></v-btn>
                                            </template>
                                        </v-autocomplete>

                                        <v-layout row wrap v-if="!!d.item">
                                            <v-flex xs12>
                                                <v-text-field
                                                    solo
                                                    :value="d.item.item_name"
                                                    hide-details
                                                    :clearable="false"
                                                    readonly
                                                    @click:clear="update_item_detail(n, null)"
                                                    >
                                                    <!-- <template slot="prepend">
                                                        <v-btn color="red" class="ma-0 mr-1" icon :dark="false" flat @click="del_detail(n)" :disabled="!!view" ><v-icon>delete</v-icon></v-btn>
                                                    </template> -->
                                                </v-text-field>
                                            </v-flex>    
                                            <v-flex xs12 class="caption blue--text pl-5 mt-2">
                                                -
                                            </v-flex>
                                            
                                        </v-layout>
                                    </v-flex>


                                    <v-flex xs7>
                                        <v-layout row wrap>
                                            <v-flex xs3 pr-2 class="text-xs-right py-2 px-3">
                                                <div v-if="!!d.item" class="pt-1 subheading">{{one_money(d.basic_qty)}}</div>
                                                <div v-show="!d.item" class="pt-1 subheading">0</div>
                                            </v-flex>

                                            <v-flex xs3 pa-2 class="text-xs-right py-2 px-3">
                                                <div v-if="!!d.item" class="pt-1 subheading">Rp {{one_money(d.hpp)}}</div>
                                                <div v-show="!d.item" class="pt-1 subheading">Rp 0</div>
                                            </v-flex>
                                            <!-- <v-flex xs1 pr-2>
                                                <v-text-field
                                                    label=""
                                                    solo
                                                    hide-details
                                                    :value="d.item?d.item.unit_name:''"
                                                    dense
                                                    readonly
                                                    flat
                                                ></v-text-field>
                                            </v-flex> -->
                                            <v-flex xs3 pr-2 class="">
                                                <v-text-field
                                                    label=""
                                                    solo
                                                    hide-details
                                                    :value="d.qty"
                                                    reverse
                                                    dense
                                                    @input="update_amount(n, 'qty', $event)"
                                                    suffix=""
                                                    :readonly="true"
                                                >
                                                    <template slot="prepend-inner">
                                                        <span v-if="!!d.item" class="pl-1">{{d.item.unit_name}}</span>
                                                        <span v-show="!d.item" class="pl-1">Unit</span>
                                                    </template>
                                                </v-text-field>
                                                <span class="caption d-block text-xs-right pr-2"><i>stok : <b>{{one_money(d.item?d.item.stock:0)}}</b></i></span>
                                                
                                            </v-flex>
                                            <v-flex xs3 pr-2 class="text-xs-right py-2 px-3">
                                                <div v-if="!!d.item" class="pt-1 subheading">Rp {{one_money(d.hpp*d.qty)}}</div>
                                                <div v-show="!d.item" class="pt-1 subheading">Rp 0</div>
                                            </v-flex>
                                        </v-layout>    
                                    </v-flex>
                                    
                                </v-layout>

                                <!-- <v-layout row wrap v-show="!view">
                                    <v-flex xs2 class="text-xs-center" pt-2 offset-md5>
                                        <v-btn color="primary btn-icon" @click="add_detail" :disabled="!btn_add_enabled"><v-icon>add</v-icon></v-btn>
                                    </v-flex>
                                    <v-flex xs5>
                                    </v-flex>
                                </v-layout> -->
                                
                            </v-card-text>

                            <v-card-text class="px-3 py-2" v-show="!selected_warehouse||!selected_out_item">
                                <v-layout row wrap>
                                    <v-flex xs12 class="text-xs-center red--text pa-2">
                                        Pilih dulu gudangnya dan produknya...
                                    </v-flex>
                                </v-layout>
                            </v-card-text>
                        </v-card>
                        
                        <v-divider></v-divider>
                    </v-flex>

                    <v-flex xs6 pt-2>
                        <iv-item-assembly-cost></iv-item-assembly-cost>
                    </v-flex>
                    <v-flex xs6 pt-2>
                        <v-layout row wrap class="text-xs-right subheading">
                            <v-flex xs8 py-2>
                                TOTAL PERKIRAAN BIAYA   
                            </v-flex>
                            <v-flex xs4 py-2 pr-3>
                                Rp <b>{{one_money(total_hpp)}}</b>
                            </v-flex>
                        </v-layout>

                        <v-layout row wrap class="text-xs-right subheading">
                            <v-flex xs8 py-2>
                                TOTAL BIAYA TETAP
                            </v-flex>
                            <v-flex xs4 py-2 pr-3>
                                Rp <b>{{one_money(total_cost)}}</b>
                            </v-flex>
                        </v-layout>

                        <v-layout row wrap class="text-xs-right subheading blue--text">
                            <v-flex xs9 offset-xs3>
                                <v-divider class=""></v-divider>
                            </v-flex>
                            <v-flex xs8 py-2>
                                <b>TOTAL BIAYA PRODUKSI</b>
                            </v-flex>
                            <v-flex xs4 py-2 pr-3>
                                Rp <b>{{one_money(total_cost+total_hpp)}}</b>
                            </v-flex>
                        </v-layout>

                        <v-layout row wrap class="text-xs-right subheading blue--text">
                            <v-flex xs9 offset-xs3>
                                <v-divider class=""></v-divider>
                            </v-flex>
                            <v-flex xs8 py-2>
                                TOTAL BIAYA PRODUKSI / 
                                <span v-if="!!selected_out_item">{{selected_out_item.unit_name}}</span>
                                <span v-show="!selected_out_item">Unit</span>
                            </v-flex>
                            <v-flex xs4 py-2 pr-3>
                                Rp <b>{{one_money((total_cost+total_hpp)/out_item_qty)}}</b>
                            </v-flex>
                        </v-layout>
                    </v-flex>
                </v-layout>

                <!-- <v-layout row wrap>
                    <v-flex xs2 class="text-xs-center" pt-2 offset-md5>
                        <v-btn color="primary btn-icon" @click="add_detail" :disabled="!btn_add_enabled"><v-icon>add</v-icon></v-btn>
                    </v-flex>
                </v-layout> -->
            </v-card-text>

            <v-card-actions>
                <v-btn color="primary" flat @click="dialog=!dialog">Tutup</v-btn>
                <v-spacer></v-spacer>
                <v-btn color="primary" @click="save" v-show="!edit" :disabled="!btn_save_enable">Simpan</v-btn>                
            </v-card-actions>
        </v-card>

        <common-dialog-confirm data="0" @confirm="confirm_save" v-if="dialog_confirm" 
            text="Apakah data yang anda masukkan sudah benar? Proses perakitan tidak dapat diubah kembali..."></common-dialog-confirm>
    </v-dialog>
</template>

<style>
.zalfa-input-super-dense .v-input__control {
    min-height: 36px !important;
}

.zalfa-font-12 {
    font-size: 1.2em;
}

.dialog-new table.v-table thead tr {
    height: auto !important;
}
</style>

<script>
let t = Math.random()
module.exports = {
    components: {
        "common-dialog-confirm" : httpVueLoader("../../common/components/common-dialog-confirm.vue"),
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue?t='+t),
        "iv-item-assembly-cost" : httpVueLoader("./iv-item-assembly-cost.vue")
    },

    data () {
        return {
            headers: [
                {
                    text: "NAMA ITEM",
                    align: "center",
                    sortable: false,
                    width: "35%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "QTY / " + this.selected_out_unit,
                    align: "left",
                    sortable: false,
                    width: "15%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "CATATAN",
                    align: "left",
                    sortable: false,
                    width: "27%",
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
        dialog : {
            get () { return this.$store.state.assembly_new.dialog_new },
            set (v) { this.$store.commit('assembly_new/set_common', ['dialog_new', v]) }
        },

        assembly_number : {
            get () { return this.$store.state.assembly_new.assembly_number },
            set (v) { this.$store.commit('assembly_new/set_common', ['assembly_number', v]) }
        },

        assembly_date : {
            get () { return this.$store.state.assembly_new.assembly_date },
            set (v) { this.$store.commit('assembly_new/set_common', ['assembly_date', v]) }
        },

        assembly_note : {
            get () { return this.$store.state.assembly_new.assembly_note },
            set (v) { this.$store.commit('assembly_new/set_common', ['assembly_note', v]) }
        },

        items () { 
            return this.$store.state.assembly_new.items_av
        },

        items_master () { 
            return this.$store.state.assembly_new.items_master
        },

        edit () {
            return this.$store.state.assembly_new.edit
        },

        btn_save_enable () {
            if (this.assembly_note != '' && this.details.length > 0)
                return true

            return false
        },

        warehouses () {
            return this.$store.state.assembly_new.warehouses
        },

        selected_warehouse : {
            get () { return this.$store.state.assembly_new.selected_warehouse },
            set (v) { 
                this.$store.commit('assembly_new/set_selected_warehouse', v)
                this.$store.dispatch('assembly_new/search_stock')
            }
        },

        selected_assembly () {
            return this.$store.state.assembly.selected_assembly
        },

        selected_out_item () {
            return this.$store.state.assembly_new.selected_out_item
        },

        view () {
            return this.$store.state.assembly_new.view
        },

        out_item_qty : {
            get () { return this.$store.state.assembly_new.out_item_qty },
            set (v) { 
                this.$store.commit('assembly_new/set_common', ['out_item_qty', v])
                this.detail_recount(v)
            }
        },

        details () { 
            return this.$store.state.assembly_new.details
        },

        dialog_confirm : {
            get () { return this.$store.state.dialog_confirm },
            set (v) { this.$store.commit('set_dialog_confirm', v) }
        },

        total_hpp () {
            let hpp = 0
            for (let d of this.details)
                hpp += (parseFloat(d.hpp) * d.qty)

            return hpp
        },

        total_cost () {
            let cost = 0
            for (let c of this.$store.state.assembly_new.costs)
                cost += parseFloat(c.amount)

            return cost
        },

        error () {
            return this.$store.state.assembly_new.error
        },

        error_msg () {
            return this.$store.state.assembly_new.error_msg
        },

        selected_out_unit () {
            if (!this.selected_out_item)
                return 'Unit'
            return this.selected_out_item.unit_name
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        select (x) {
            this.$store.commit('assembly_new/set_selected_item', x)
        },

        save () {
            this.$store.commit('set_dialog_confirm', true)
        },

        confirm_save () {
            this.$store.dispatch('assembly_new/save')
        },

        add_item () {
            this.$store.dispatch('assembly_new/search_item', {})
            this.$store.commit('assembly_new/set_common', ['dialog_item', true])
        },

        change_qty(i, v) {
            let x = this.items
            x[i][`item_qty`] = v

            this.$store.commit('assembly_new/set_details', x)
        },

        del (idx) {
            this.items.splice(idx, 1)
        },

        update_item (v) {
            this.$store.commit('assembly_new/set_selected_out_item', v)
            if (!!v) {
                let assmb = v.assemblies
                for (let i in assmb)
                    assmb[i].basic_qty = assmb[i].qty
                this.$store.commit('assembly_new/set_details', assmb)

                let costs = []
                // for (let cost of v.costs)
                //     costs.push({account:cost,amount:cost.amount,base_amount:cost.amount})
                this.$store.commit('assembly_new/set_costs', costs)
                this.$store.dispatch('assembly_new/search_stock')

                this.out_item_qty = v.item_assembly_qty
            } else {
                this.$store.commit('assembly_new/set_details', [])
                this.$store.commit('assembly_new/set_costs', [])
            }
        },

        btn_add_enabled () {            
            return true
        },

        add_detail () {
            let d = this.details
            let dfl = JSON.parse(JSON.stringify(this.$store.state.assembly_new.detail_default))
            d.push(dfl)
            this.$store.commit('assembly_new/set_details', d)
        },

        del_detail (x) {
            let d = this.details
            d.splice(x, 1)
            this.$store.commit('assembly_new/set_details', d)
        },

        update_item_detail (n, v) {
            let d = this.details
            d[n].item = v
            if (!!v)
                d[n].hpp = v.item_hpp
            else
                d[n].hpp = 0
            
            this.$store.commit('assembly_new/set_details', d)
        },

        set_details (d) {
            this.$store.commit('assembly_new/set_details', d?d:this.details)
        },

        update_amount (idx, side, amount) {
            let d = this.details            
            d[idx][side] = amount

            this.set_details(d)
        },

        detail_recount(v) {
            let dtls = JSON.parse(JSON.stringify(this.details))
            for (let d of dtls) {
                d.qty = d.ratio * v
            }

            // let costs = JSON.parse(JSON.stringify(this.$store.state.assembly_new.costs))
            // for (let i in costs)
            //     costs[i].amount = costs[i].base_amount * v

            this.$store.commit('assembly_new/set_details', dtls)
            // this.$store.commit('assembly_new/set_object', ['costs', costs])
        }
    },

    watch : {
    },

    mounted () {
        this.$store.dispatch('assembly_new/search_item_master')
    }
}
</script>