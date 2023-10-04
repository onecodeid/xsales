<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="600px"
        transition="dialog-transition"
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <h3 v-show="!edit">ENTRY BARANG BARU</h3>
                <h3 v-show="edit">UBAH DATA BARANG</h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                    <!-- <v-flex xs5 pr-5> -->
                        <v-layout row wrap>
                            <v-flex xs12 :class="`mt-`+v_space">
                                <v-text-field
                                    label="Nama Barang"
                                    v-model="item_name"
                                    :readonly="!!view"
                                ></v-text-field>
                            </v-flex>

                            <!-- <v-flex xs12 :class="`mt-`+v_space">
                                <v-text-field
                                    label="Nama Alias (opsional)"
                                    v-model="item_alias"
                                    :readonly="!!view"
                                ></v-text-field>
                            </v-flex> -->

                            <v-flex xs6 pr-3>
                                <v-text-field
                                    label="Kode Barang (SKU)"
                                    v-model="item_code"
                                    :readonly="!!view"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs6 pl-3>
                                <v-select
                                    :items="categories"
                                    v-model="selected_category"
                                    return-object
                                    label="Kategori"
                                    item-key="category_id"
                                    item-text="category_name"
                                    :disabled="!!view"
                                >
                                    <template slot="append-outer">
                                        <v-btn color="success" class="ma-0 ml-2 btn-icon" small @click="add_category" :disabled="!!view">
                                            <v-icon>add</v-icon>
                                        </v-btn> 
                                    </template>
                                </v-select>
                            </v-flex>

                            <v-flex xs6 pr-3>
                                <v-select
                                    :items="units"
                                    v-model="selected_unit"
                                    return-object
                                    label="Satuan"
                                    item-key="unit_id"
                                    item-text="unit_name"
                                    :disabled="!!view"
                                >
                                    <template slot="append-outer">
                                        <v-btn color="success" class="ma-0 ml-2 btn-icon" small @click="add_unit" :disabled="!!view">
                                            <v-icon>add</v-icon>
                                        </v-btn> 
                                    </template>
                                </v-select>
                            </v-flex>

                            <v-flex xs6 pl-3>
                                <v-select
                                    :items="discs"
                                    v-model="selected_disc"
                                    return-object
                                    label="Diskon Umum"
                                    item-key="disc_id"
                                    item-text="disc_name"
                                    :disabled="!!view"
                                >
                                    <template slot="item" slot-scope="data">
                                        {{data.item.disc_name}} ({{ data.item.disc_amount }}%)
                                    </template>

                                    <template slot="selection" slot-scope="data">
                                        {{data.item.disc_name}} ({{ data.item.disc_amount }}%)
                                    </template>
                                    <!-- <template slot="append-outer">
                                        <v-btn color="success" class="ma-0 ml-2 btn-icon" small @click="add_unit" :disabled="!!view">
                                            <v-icon>add</v-icon>
                                        </v-btn> 
                                    </template> -->
                                </v-select>
                                <!-- <v-text-field
                                    label="Min Stock"
                                    v-model="item_min"
                                    :suffix="selected_unit ? selected_unit.M_UnitName : '#'"
                                    :readonly="false"
                                ></v-text-field> -->
                            </v-flex>

                            <!-- <v-flex xs6 pr-3>
                                <v-select
                                    :items="packs"
                                    v-model="selected_pack"
                                    return-object
                                    label="Kemasan"
                                    item-key="pack_id"
                                    item-text="pack_name"
                                    :disabled="!!view"
                                >
                                    <template slot="append-outer">
                                        <v-btn color="success" class="ma-0 ml-2 btn-icon" small @click="add_pack" :disabled="!!view">
                                            <v-icon>add</v-icon>
                                        </v-btn> 
                                    </template>
                                </v-select>
                            </v-flex>
                            <v-flex xs6 pl-3>
                                <v-select
                                    :items="view_in_packs"
                                    v-model="selected_view_in_pack"
                                    return-object
                                    label="Tampilkan nota default dalam"
                                    item-key="id"
                                    item-text="text"
                                    :disabled="!!view || !selected_pack"
                                >
                                </v-select>
                            </v-flex> -->

                            <v-flex xs6 pr-3>
                                <v-text-field
                                    label="HPP / Harga Beli"
                                    v-model="item_hpp"
                                    class="text-xs-right"
                                    suffix="Rp"
                                    reverse
                                    :readonly="!!view || !item_hpp_edit"
                                    :disabled="!item_hpp_edit"
                                >
                                    <template slot="label" style="left:0px;right:auto">
                                        <div style="left:0px;right:auto">HPP / Harga Beli</div>
                                    </template>
                                    <!-- <template slot="prepend-inner">
                                        <v-btn color="primary" small depressed class="mt-0 mb-1 mr-0 ml-1 btn-icon" 
                                            flat @click="dialog_confirm=true" v-show="!item_hpp_edit"
                                            title="Ubah / Sesuaikan HPP">
                                            <v-icon small class="mr-0">edit</v-icon>
                                        </v-btn>
                                    </template> -->
                                </v-text-field>
                            </v-flex>

                            <!-- <v-flex xs6>
                                &nbsp;
                            </v-flex> -->

                            <v-flex xs6 pl-3>
                                <v-text-field
                                    label="Harga Jual (Default)"
                                    v-model="item_price"
                                    class="text-xs-right"
                                    suffix="Rp"
                                    reverse
                                    :readonly="!!view"
                                >
                                    <template slot="label" style="left:0px;right:auto">
                                        <div style="left:0px;right:auto">Harga Jual</div>
                                    </template>
                                </v-text-field>
                            </v-flex>
                        </v-layout>

                        <!-- <v-layout row wrap>
                            <v-flex xs12>
                                <v-card depressed>
                                    <v-card-title primary-title class="pa-2 cyan">
                                        <v-layout row wrap>
                                            <v-flex xs6>
                                                <v-checkbox label="Produk Rakitan" hide-details class="mt-0 white--text" 
                                                    v-model="item_assembly" 
                                                    :value="item_assembly"
                                                    true-value="Y"
                                                    false-value="N"
                                                    :disabled="assemblies.length>0"></v-checkbox>        
                                            </v-flex>
                                            <v-flex xs6 class="text-xs-right">
                                                <v-btn color="primary" small depressed class="ma-0" :disabled="item_assembly=='N'" @click="assembly()">
                                                    <v-icon small class="mr-2">edit</v-icon> Ubah</v-btn>
                                            </v-flex>
                                        </v-layout>
                                        
                                    </v-card-title>
                                    <v-card-text class="pa-2">
                                        <v-layout row wrap>
                                            <v-flex xs12 v-show="item_assembly=='N'">
                                                <v-icon>arrow_upward</v-icon> Check disini untuk menjadikan produk rakitan        
                                            </v-flex>
                                            <v-flex xs12 v-show="item_assembly=='Y'">
                                                <v-layout row wrap v-for="(ass, n) in assemblies" :key="n" v-if="!!ass.item">
                                                    <v-flex xs9>
                                                        {{ ass.item.item_name }}
                                                    </v-flex>
                                                    <v-flex xs3 class="text-xs-right">
                                                        {{ ass.qty }} {{ ass.item.unit_name }}
                                                    </v-flex>
                                                </v-layout>
                                            </v-flex>
                                        </v-layout>
                                        
                                    </v-card-text>
                                </v-card>
                            </v-flex>
                        </v-layout> -->
                    </v-flex>

                    <!-- <v-flex xs7>
                        <v-layout row wrap> -->
                            <!-- <v-flex xs12>
                                <master-item-aliases></master-item-aliases>   
                            </v-flex>

                            <v-flex xs12 mt-2>
                                <master-item-packs></master-item-packs>
                            </v-flex>

                            <v-flex xs12 mt-2>
                                <master-item-stockmins></master-item-stockmins>
                            </v-flex> -->
                            
                            <!-- <v-divider class="mb-4 mt-2"></v-divider> 
                            <master-item-assembly v-if="dialog_assembly" :data="assemblies"></master-item-assembly>                           
                        </v-layout>
                    </v-flex> -->
                </v-layout>
            </v-card-text>

            <v-card-actions>
                <v-btn color="primary" flat @click="dialog=!dialog" v-show="!view">Batal</v-btn>
                <v-spacer></v-spacer>
                <v-btn color="primary" @click="save" v-show="!view">Simpan</v-btn> 
                <v-btn color="primary" @click="dialog=!dialog" v-show="!!view">Tutup</v-btn>                
            </v-card-actions>
        </v-card>
        <common-dialog-confirm text="Anda ingin mengubah / menyesuaikan HPP ?" 
            @confirm="item_hpp_edit=true"
            btn_confirm="Saya Mengerti"></common-dialog-confirm>
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
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        'common-dialog-confirm' : httpVueLoader('../../common/components/common-dialog-confirm.vue'),
        "master-item-packs" : httpVueLoader("./master-item-packs.vue?t="+t),
        "master-item-aliases" : httpVueLoader("./master-item-aliases.vue?t="+t),
        "master-item-stockmins" : httpVueLoader("./master-item-stockmins.vue?t="+t),
        "master-item-assembly" : httpVueLoader("./master-item-assembly.vue?t="+t)
    },

    data () {
        return {
            headers: [
                {
                    text: "#",
                    align: "center",
                    sortable: false,
                    width: "15%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "CUSTOMER",
                    align: "left",
                    sortable: false,
                    width: "40%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "ALIAS",
                    align: "left",
                    sortable: false,
                    width: "45%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                }
            ]
        }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.item_new.dialog_new },
            set (v) { this.$store.commit('item_new/set_common', ['dialog_new', v]) }
        },

        dialog_confirm : {
            get () { return this.$store.state.dialog_confirm },
            set (v) { this.$store.commit('set_dialog_confirm', v) }
        },

        dialog_assembly () {
            return this.$store.state.item_new.dialog_assembly
        },

        item_name : {
            get () { return this.$store.state.item_new.item_name },
            set (v) { this.$store.commit('item_new/set_common', ['item_name', v]) }
        },

        item_alias : {
            get () { return this.$store.state.item_new.item_alias },
            set (v) { this.$store.commit('item_new/set_common', ['item_alias', v]) }
        },

        item_code : {
            get () { return this.$store.state.item_new.item_code },
            set (v) { this.$store.commit('item_new/set_common', ['item_code', v]) }
        },

        item_min : {
            get () { return this.$store.state.item_new.item_min },
            set (v) { this.$store.commit('item_new/set_common', ['item_min', v]) }
        },

        item_hpp : {
            get () { return this.$store.state.item_new.item_hpp },
            set (v) { this.$store.commit('item_new/set_common', ['item_hpp', v]) }
        },

        item_price : {
            get () { return this.$store.state.item_new.item_price },
            set (v) { this.$store.commit('item_new/set_common', ['item_price', v]) }
        },

        item_assembly : {
            get () { return this.$store.state.item_new.item_assembly },
            set (v) {
                this.$store.commit('item_new/set_common', ['item_assembly', v]) 
            }
        },

        units () {
            return this.$store.state.item_new.units
        },

        selected_unit : {
            get () { return this.$store.state.item_new.selected_unit },
            set (v) { this.$store.commit('item_new/set_selected_unit', v) }
        },

        packs () {
            return this.$store.state.item_new.packs
        },

        selected_pack : {
            get () { return this.$store.state.item_new.selected_pack },
            set (v) { this.$store.commit('item_new/set_selected_pack', v) }
        },

        view_in_packs () {
            return this.$store.state.item_new.view_in_packs
        },

        selected_view_in_pack : {
            get () { return this.$store.state.item_new.selected_view_in_pack },
            set (v) { this.$store.commit('item_new/set_selected_view_in_pack', v) }
        },

        categories () {
            return this.$store.state.item_new.categories
        },

        selected_category : {
            get () { return this.$store.state.item_new.selected_category },
            set (v) { this.$store.commit('item_new/set_selected_category', v) }
        },

        v_space () {
            return 0
        },

        edit () {
            return this.$store.state.item_new.edit
        },

        view () { return this.$store.state.view },

        assemblies() {
            return this.$store.state.item_new.assemblies
        },

        item_hpp_edit : {
            get () { return this.$store.state.item_new.item_hpp_edit },
            set (v) { this.$store.commit('item_new/set_common', ['item_hpp_edit', v]) }
        },

        discs () {
            return this.$store.state.item_new.discs
        },

        selected_disc : {
            get () { return this.$store.state.item_new.selected_disc },
            set (v) { this.$store.commit('item_new/set_object', ['selected_disc', v]) }
        }
    },

    methods : {
        save () {
            this.$store.dispatch('item_new/save')
        },

        del (x) {
            let aliases = this.$store.state.item_new.aliases
            aliases.splice(x, 1)
            this.$store.commit('item_new/set_aliases', aliases)
        },

        add_unit () {
            this.$store.commit('unit_new/set_common', ['edit', false])
            this.$store.commit('unit_new/set_common', ['unit_name', ''])
            this.$store.commit('unit_new/set_common', ['unit_code', ''])
            this.$store.commit('unit_new/set_dialog_new', true)
        
        },

        add_category () {
            this.$store.commit('category_new/set_common', ['edit', false])
            this.$store.commit('category_new/set_common', ['category_name', ''])
            this.$store.commit('category_new/set_common', ['category_code', ''])
            this.$store.commit('category_new/set_dialog_new', true)
        
        },

        add_pack () {
            this.$store.commit('pack_new/set_common', ['edit', false])
            this.$store.commit('pack_new/set_common', ['pack_name', ''])
            this.$store.commit('pack_new/set_common', ['pack_code', ''])
            this.$store.commit('pack_new/set_common', ['pack_conversion', 0])
            this.$store.commit('pack_new/set_selected_unit', null)

            if (!!this.$store.state.item_new.selected_unit)
                this.$store.commit('pack_new/set_selected_unit', this.$store.state.item_new.selected_unit)

            this.$store.commit('pack_new/set_dialog_new', true)
            return false
        },

        assembly() {
            let assemblies = JSON.parse(JSON.stringify(this.$store.state.item_new.assemblies))
            let costs = JSON.parse(JSON.stringify(this.$store.state.item_new.assembly_costs))
            if (assemblies.length < 1) {
                for(let n = 0; n < 3; n++) {
                    let x = JSON.parse(JSON.stringify(this.$store.state.item_new.assembly_default))
                    assemblies.push(x)
                }
            }
            this.$store.commit('item_new/set_object', ['assemblies_tmp', assemblies])
            this.$store.commit('item_new/set_object', ['assembly_costs_tmp', costs])
            this.$store.commit('item_new/set_common', ['item_assembly_qty', 1])
            this.$store.commit('item_new/set_common', ['dialog_assembly', true])
        }
    },

    mounted () {
        this.$store.dispatch('item_new/search_unit')
        this.$store.dispatch('item_new/search_disc')
        this.$store.dispatch('item_new/search_category', [])
    },

    watch : {
        // item_assembly (n, o) {
        //     if (n=="N" && o=="Y") {
        //         if (this.assemblies.length > 0) {
        //             this.item_assembly = "Y"
        //         }
        //     }
        // }
    }
}
</script>