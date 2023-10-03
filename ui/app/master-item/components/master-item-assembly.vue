<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="1000px"
        transition="dialog-transition"
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <h3>PRODUK RAKITAN</h3>
            </v-card-title>
            <v-card-text>
                <v-text-field
                    label="Produk Output"
                    :value="item_name"
                    readonly
                ></v-text-field>
                <!-- <v-layout row wrap>
                    <v-flex xs3 pr-3>
                        <v-text-field
                            label="Output Qty"
                            v-model="item_assembly_qty"
                            :suffix="unit_name"
                        ></v-text-field>
                    </v-flex>
                    <v-flex xs9>
                        <v-text-field
                            label="Catatan Perakitan"
                            placeholder="Tuliskan catatan anda disini"
                            v-model="item_assembly_note"
                        ></v-text-field>        
                    </v-flex>
                </v-layout> -->
                
                <v-alert
                    :value="true"
                    color="warning"
                    icon="priority_high"
                    outline
                    >
                    Catatan : Qty yang dimasukkan adalah komposisi untuk membuat Produk Output sebanyak {{item_assembly_qty}} {{unit_name}}
                </v-alert>
                <v-card>
                    <v-card-title primary-title class="py-2 px-3 cyan white--text">
                        <v-layout row wrap>
                            <v-flex xs5><h3 class="subheading text-xs-center">PRODUK</h3></v-flex>
                            <v-flex xs7>
                                <v-layout row wrap>
                                    <v-flex xs3 pr-2>
                                        <h3 class="subheading text-xs-center">QTY</h3>
                                    </v-flex>
                                    <v-flex xs3 pr-3>
                                        <h3 class="subheading text-xs-right">HPP</h3>
                                    </v-flex>
                                    <v-flex xs6 pr-3>
                                        <h3 class="subheading text-xs-right">TOTAL HPP</h3>
                                    </v-flex>
                                </v-layout>
                            </v-flex>
                        </v-layout>
                    </v-card-title>
                    <v-card-text class="px-3 py-2">
                        <v-layout row wrap v-for="(d, n) in assemblies" :key="n" :class="{'mt-2':n>0}">
                            <v-flex xs5 pr-3>
                                <v-autocomplete
                                    :items="items"
                                    :value="d.item"
                                    return-object
                                    solo
                                    hide-details
                                    clearable
                                    :readonly="!!d.item"
                                    @change="update_item(n, $event)"
                                    item-text="item_name"
                                    item-value="item_id"
                                    placeholder="Pilih..."
                                    v-show="!d.item"
                                >
                                    <template slot="item" slot-scope="data">
                                        {{data.item.item_name}}
                                    </template>

                                    <template slot="selection" slot-scope="data">
                                        {{data.item.item_name}}
                                    </template>

                                    <template slot="prepend">
                                        <v-btn color="red" class="ma-0 mr-1" icon :dark="false" flat @click="del_detail(n)" ><v-icon>delete</v-icon></v-btn>
                                    </template>
                                </v-autocomplete>

                                <v-layout row wrap v-if="!!d.item">
                                    <v-flex xs12>
                                        <v-text-field
                                            solo
                                            :value="d.item.item_name"
                                            hide-details
                                            :clearable="!view"
                                            readonly
                                            @click:clear="update_item(n, null)"
                                            >
                                            <template slot="prepend">
                                                <v-btn color="red" class="ma-0 mr-1" icon :dark="false" flat @click="del_detail(n)" :disabled="!!view" ><v-icon>delete</v-icon></v-btn>
                                            </template>
                                        </v-text-field>
                                    </v-flex>
                                    
                                </v-layout>
                            </v-flex>

                            

                            <v-flex xs7>
                                <v-layout row wrap>
                                    <v-flex xs3 pr-2>
                                        <v-text-field
                                            label=""
                                            solo
                                            hide-details
                                            :value="d.qty"
                                            reverse
                                            dense
                                            @input="update_amount(n, 'qty', $event)"
                                            suffix=""
                                            :readonly="!!view"
                                        >
                                            <template slot="prepend-inner">
                                                <span v-if="!!d.item" class="pl-1">{{d.item.unit_name}}</span>
                                                <span v-show="!d.item" class="pl-1">Unit</span>
                                            </template>
                                        </v-text-field>
                                        <!-- <span class="caption d-block text-xs-right pr-2"><i>stok : <b>{{one_money(d.item?d.item.I_StockQty:0)}}</b></i></span> -->
                                    </v-flex>

                                    <v-flex xs3 pa-2 class="text-xs-right py-2 px-3">
                                        <div v-if="!!d.item" class="pt-1 subheading">Rp {{vf_commafy(d.hpp)}}</div>
                                        <div v-show="!d.item" class="pt-1 subheading">Rp 0</div>
                                    </v-flex>
                                        
                                    <v-flex xs6 class="text-xs-right py-2 px-3">
                                        <div v-if="!!d.item" class="pt-1 subheading">Rp {{vf_commafy(d.hpp * d.qty)}}</div>
                                        <div v-show="!d.item" class="pt-1 subheading">Rp 0</div>
                                    </v-flex>
                                </v-layout>  
                                
                                
                            </v-flex>
                            
                        </v-layout>

                        <v-layout row wrap v-show="!view">
                            <v-flex xs2 class="text-xs-center" pt-2 offset-md5>
                                <v-btn color="primary btn-icon" @click="add_detail" :disabled="!btn_add_enabled"><v-icon>add</v-icon></v-btn>
                            </v-flex>
                            <v-flex xs5>
                            </v-flex>
                        </v-layout>
                    </v-card-text>
                </v-card>
                

                <v-divider class="my-2"></v-divider>
                <v-layout row wrap>
                    <v-flex xs7 class="pt-2">
                       <!-- <master-item-assembly-cost></master-item-assembly-cost>  -->
                       &nbsp;
                    </v-flex>
                    <v-flex xs4 offset-xs1>
                        <!-- <v-layout row wrap pt-3>
                            <v-flex xs7 pl-2 pb-2 pr-4 offset-xs1>
                                <span class="subheading">SUBTOTAL</span>
                            </v-flex>
                            <v-flex xs4 px-3 pb-2 pt-0 class="text-xs-right">
                                <span class="caption">Rp</span> <span class="subheading">{{one_money(sub_total)}}</span>
                            </v-flex>
                        </v-layout>

                        <v-layout row wrap pt-3>
                            <v-flex xs7 pl-2 pb-2 pr-4 offset-xs1>
                                <span class="subheading">TOTAL BIAYA</span>
                            </v-flex>
                            <v-flex xs4 px-3 pb-2 pt-0 class="text-xs-right">
                                <span class="caption">Rp</span> <span class="subheading">{{one_money(cost_total)}}</span>
                            </v-flex>
                        </v-layout> -->

                        <v-layout row wrap pt-3>
                            <v-flex xs7 pl-2 pb-2 pr-4 offset-xs1>
                                <span class="subheading">TOTAL HPP</span>
                            </v-flex>
                            <v-flex xs4 px-3 pb-2 pt-0 class="text-xs-right">
                                <span class="caption">Rp</span> <span class="subheading">{{vf_commafy(sub_total + cost_total)}}</span>
                            </v-flex>
                        </v-layout>
                    </v-flex>
                </v-layout>
            </v-card-text>
            <v-card-actions>
                <v-btn color="primary" flat @click="dialog=!dialog" v-show="!view">Batal</v-btn>
                <v-spacer></v-spacer>
                <v-btn color="primary" @click="save" v-show="!view">Simpan</v-btn>                
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<style>
.input-dense .v-input__control {
    min-height: 36px !important;
}
</style>
<script>
var t = Math.ceil(Math.random() * 1e10)
module.exports = {
    components : {
        "master-item-assembly-cost" : httpVueLoader("./master-item-assembly-cost.vue?t="+t)
    },

    props : ["data"],
    data () {
        return {
        }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.item_new.dialog_assembly },
            set (v) { this.$store.commit('item_new/set_common', ['dialog_assembly', v]) }
        },

        assemblies : {
            get () { return this.$store.state.item_new.assemblies_tmp },
            set (v) { this.$store.commit('item_new/set_object', ['assemblies_tmp', v]) }
        },

        items () {
            return this.$store.state.item_new.items
        },

        view () { return false },

        costs () { 
            return this.$store.state.item_new.assembly_costs_tmp
        },

        sub_total () {
            let total = 0
            for (let ass of this.assemblies) {
                if (!!ass.item)
                    total += parseFloat(ass.hpp * ass.qty)
            }

            return total
        },

        cost_total () {
            let total = 0
            for (let cost of this.costs) {
                total += parseFloat(cost.amount)
            }

            return total
        },

        item_assembly_note : {
            get () { return this.$store.state.item_new.item_assembly_note },
            set (v) { this.$store.commit('item_new/set_common', ['item_assembly_note', v]) }
        },

        item_assembly_qty : {
            get () { return this.$store.state.item_new.item_assembly_qty },
            set (v) { this.$store.commit('item_new/set_common', ['item_assembly_qty', v]) }
        },

        item_name () {
            if (!this.edit)
                return this.$store.state.item_new.item_name
            return this.$store.state.item.selected_item.item_name
        },

        unit_name () {
            if (!this.edit) {
                if (!!this.$store.state.item_new.selected_unit)
                    return this.$store.state.item_new.selected_unit.unit_name
                return ""
            }
                
            return this.$store.state.item.selected_item.unit_name
        },

        edit () {
            return this.$store.state.item_new.edit
        },

        btn_add_enabled () {
            return true
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        vf_commafy (x) {
            return window.vf_commafy(x, 2)
        },

        one_mask_money (x) {
            return window.one_mask_money(x)
        },

        add_detail () {
            let d = this.assemblies
            let dfl = JSON.parse(JSON.stringify(this.$store.state.item_new.assembly_default))
            d.push(dfl)
            this.assemblies = d
        },

        del_detail (x) {
            let d = this.assemblies
            d.splice(x, 1)
            this.assemblies = d
        },

        update_item(idx, v) {
            let d = this.assemblies
            d[idx].item = v
            d[idx].hpp = v.item_hpp
            
            if (!v)
                d[idx].qty = 0
            this.assemblies = d
        },

        update_amount (idx, side, amount) {
            let d = this.assemblies           
            d[idx][side] = amount

            this.assemblies = d
        },

        save () {
            this.$store.commit('item_new/set_object', ['assemblies', JSON.parse(JSON.stringify(this.assemblies))])
            this.$store.commit('item_new/set_object', ['assembly_costs', JSON.parse(JSON.stringify(this.costs))])
            this.dialog = false
        },

        add_detail () {
            let d = this.assemblies
            let dfl = JSON.parse(JSON.stringify(this.$store.state.item_new.assembly_default))
            d.push(dfl)
            this.assemblies = d
        }
    },

    mounted () {
        // this.assemblies = this.data
        this.$store.dispatch('item_new/search_item')
    }
}
</script>