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
                <h3 class="headline">DATA ASET BARU</h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs6>
                        <h3 class="title">Detail Asset</h3>
                    </v-flex>
                    <v-flex xs6>
                        <h3 class="title">Depresiasi</h3>
                    </v-flex>
                    <v-flex xs6 class="pr-5">
                        <v-divider class="my-2"></v-divider>    
                    </v-flex>
                    <v-flex xs6>
                        <v-divider class="my-2"></v-divider>    
                    </v-flex>
                </v-layout>
                <v-layout row wrap>
                    <v-flex xs6 class="pr-5">
                        <v-layout row wrap>
                            <v-flex xs12>
                                <v-text-field
                                    label="Nama Asset"
                                    v-model="asset_name"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs4>
                                <v-text-field
                                    label="Nomor / Kode Asset"
                                    v-model="asset_code"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs8 class="pl-4">
                                <v-autocomplete
                                    :items="accounts"
                                    v-model="selected_account"
                                    return-object
                                    item-value="account_id"
                                    item-text="account_name"
                                    label="Akun Aset"
                                    clearable
                                    :readonly="!!selected_account"
                                >
                                    <template slot="item" slot-scope="data">
                                        <span class="blue--text mr-4">{{data.item.account_code}}</span> {{data.item.account_name}}
                                    </template>

                                    <template slot="selection" slot-scope="data">
                                        <span class="blue--text mr-4">{{data.item.account_code}}</span> {{data.item.account_name}}
                                    </template>
                                </v-autocomplete>
                            </v-flex>

                            <v-flex xs12>
                                <v-textarea
                                    label="Deskripsi Asset"
                                    v-model="asset_description"
                                    rows="2"
                                >
                                </v-textarea>
                            </v-flex>

                            <v-flex xs4>
                                <common-datepicker
                                    label="Tanggal Akuisisi"
                                    :date="acq_date"
                                    data="0"
                                    @change="change_acq_date"
                                    classs=""
                                    hints=" "
                                    :details="true"
                                    :solo="false"
                                ></common-datepicker>
                            </v-flex>
                            <v-flex xs8 class="pl-4">
                                <v-text-field
                                    label="Biaya Akuisisi"
                                    v-model="asset_acq_cost"
                                    :mask="mask_money"
                                    reverse
                                    suffix="Rp"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs12>
                                <v-autocomplete
                                    :items="accounts"
                                    v-model="acq_account"
                                    return-object
                                    item-value="account_id"
                                    item-text="account_name"
                                    label="Akun yang Dikreditkan"
                                    :readonly="!!acq_account"
                                    clearable
                                >
                                    <template slot="item" slot-scope="data">
                                        <span class="blue--text mr-4">{{data.item.account_code}}</span> {{data.item.account_name}}
                                    </template>

                                    <template slot="selection" slot-scope="data">
                                        <span class="blue--text mr-4">{{data.item.account_code}}</span> {{data.item.account_name}}
                                    </template>
                                </v-autocomplete>
                            </v-flex>
                        </v-layout>
                    </v-flex>

                    <v-flex xs6>
                        <v-layout row wrap>
                            <v-flex xs12>
                                <v-checkbox 
                                    label="Asset Terdepresiasi ?" 
                                    v-model="asset_depreciable" 
                                    true-value="Y"
                                    false-value="N"
                                    outline
                                    box>
                                </v-checkbox>
                            </v-flex>
                            <v-flex xs12>
                                <v-select
                                    :items="methods"
                                    v-model="selected_method"
                                    return-object
                                    item-value="depmethod_id"
                                    item-text="depmethod_name"
                                    label="Metode Depresiasi"
                                    :disabled="!is_depreciable"
                                >
                                    <template slot="item" slot-scope="data">
                                        {{data.item.depmethod_name}}
                                    </template>

                                    <template slot="selection" slot-scope="data">
                                        {{data.item.depmethod_name}}
                                    </template>
                                </v-select>
                            </v-flex>
                            <v-flex xs4>
                                <v-text-field
                                    label="Waktu Ekonomis"
                                    v-model="asset_dep_time"
                                    type="number" min="1"
                                    suffix="Tahun"
                                    readonly
                                    :disabled="!is_depreciable"
                                ></v-text-field>
                            </v-flex>
                            <v-flex xs8 class="pl-4">
                                <v-text-field
                                    label="Penyusutan / Tahun"
                                    v-model="dep_rate"
                                    type="number" min="1"
                                    suffix="%"
                                    
                                    :disabled="!is_depreciable"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs12>
                                <v-autocomplete
                                    :items="accounts"
                                    v-model="asset_dep_account"
                                    return-object
                                    item-value="account_id"
                                    item-text="account_name"
                                    label="Akun Depresiasi"
                                    :disabled="!is_depreciable"
                                    :readonly="!!asset_dep_account"
                                    clearable
                                >
                                    <template slot="item" slot-scope="data">
                                        <span class="blue--text mr-4">{{data.item.account_code}}</span> {{data.item.account_name}}
                                    </template>

                                    <template slot="selection" slot-scope="data">
                                        <span class="blue--text mr-4">{{data.item.account_code}}</span> {{data.item.account_name}}
                                    </template>
                                </v-autocomplete>
                            </v-flex>

                            <v-flex xs12>
                                <v-autocomplete
                                    :items="accounts"
                                    v-model="asset_dep_accumulated_account"
                                    return-object
                                    item-value="account_id"
                                    item-text="account_name"
                                    label="Akun Kumulasi Depresiasi"
                                    :disabled="!is_depreciable"
                                    :readonly="!!asset_dep_accumulated_account"
                                    clearable
                                >
                                    <template slot="item" slot-scope="data">
                                        <span class="blue--text mr-4">{{data.item.account_code}}</span> {{data.item.account_name}}
                                    </template>

                                    <template slot="selection" slot-scope="data">
                                        <span class="blue--text mr-4">{{data.item.account_code}}</span> {{data.item.account_name}}
                                    </template>
                                </v-autocomplete>
                            </v-flex>
                        </v-layout>
                        

                        
                    </v-flex>
                </v-layout>

                
            </v-card-text>

            <v-card-actions>
                <v-btn color="primary" flat @click="dialog=!dialog">Batal</v-btn>
                <v-spacer></v-spacer>
                <v-btn color="primary" @click="save">Simpan</v-btn>                
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
module.exports = {
    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue')
    },

    data () {
        return { }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.asset_new.dialog_new },
            set (v) { this.$store.commit('asset_new/set_common', ['dialog_new', v]) }
        },

        asset_name : {
            get () { return this.$store.state.asset_new.asset_name },
            set (v) { this.$store.commit('asset_new/set_common', ['asset_name', v]) }
        },

        asset_code : {
            get () { return this.$store.state.asset_new.asset_code },
            set (v) { this.$store.commit('asset_new/set_common', ['asset_code', v]) }
        },

        asset_acq_cost : {
            get () { return this.$store.state.asset_new.asset_acq_cost },
            set (v) { this.$store.commit('asset_new/set_common', ['asset_acq_cost', v]) }
        },

        accounts () {
            return this.$store.state.asset_new.accounts
        },

        selected_account : {
            get () { return this.$store.state.asset_new.selected_account },
            set (v) { this.$store.commit('asset_new/set_selected_account', v) }
        },

        methods () {
            return this.$store.state.asset_new.methods
        },

        selected_method : {
            get () { return this.$store.state.asset_new.selected_method },
            set (v) { this.$store.commit('asset_new/set_selected_method', v) }
        },

        acq_account : {
            get () { return this.$store.state.asset_new.asset_acq_account },
            set (v) { this.$store.commit('asset_new/set_acq_account', v) }
        },

        acq_date : {
            get () { return this.$store.state.asset_new.acq_date },
            set (v) { this.$store.commit('asset_new/set_common', ['acq_date', v]) }
        },

        asset_description : {
            get () { return this.$store.state.asset_new.asset_description },
            set (v) { this.$store.commit('asset_new/set_common', ['asset_description', v]) }
        },

        asset_depreciable : {
            get () { return this.$store.state.asset_new.asset_depreciable },
            set (v) { this.$store.commit('asset_new/set_common', ['asset_depreciable', v]) }
        },

        is_depreciable () {
            if (this.asset_depreciable == "Y")
                return true
            return false
        },

        asset_dep_time : {
            get () { return this.$store.state.asset_new.asset_dep_time },
            set (v) { 
                // let x = this.selected_method
                // let y = v
                // let z = 0;
                
                // if (x.depmethod_code=='DEP.STRAIGHT')
                //     z = (100/y).toFixed(2)
                // if (x.depmethod_code=='DEP.REDUCE')
                //     z = (100*2/y).toFixed(2)

                // this.asset_dep_rate = z
                this.$store.commit('asset_new/set_common', ['asset_dep_time', v])
            }
        },

        asset_dep_rate : {
            get () { return this.$store.state.asset_new.asset_dep_rate },
            set (v) { 
                let x = this.selected_method
                let y = v
                let z = 0;
                
                if (x.depmethod_code=='DEP.STRAIGHT')
                    z = Math.round(100/y)//.toFixed(2)
                if (x.depmethod_code=='DEP.REDUCE')
                    z = Math.round(100/(y*2))//.toFixed(2)

                this.asset_dep_time = z

                this.$store.commit('asset_new/set_common', ['asset_dep_rate', v]) 
                this.$store.commit('asset_new/set_common', ['asset_dep_time', z])
            }
        },

        asset_dep_account : {
            get () { return this.$store.state.asset_new.asset_dep_account },
            set (v) { this.$store.commit('asset_new/set_dep_account', v) }
        },

        asset_dep_accumulated_account : {
            get () { return this.$store.state.asset_new.asset_dep_accumulated_account },
            set (v) { this.$store.commit('asset_new/set_dep_accumulated_account', v) }
        },

        dep_rate : {
            get () { return this.$store.state.asset_new.asset_dep_rate },
            set (v) { 
                let x = this.selected_method
                let y = v
                let z = 0;
                
                if (x.depmethod_code=='DEP.STRAIGHT')
                    z = (100/y).toFixed(1)
                if (x.depmethod_code=='DEP.REDUCE')
                    z = (100/(y*2)).toFixed(1)

                this.asset_dep_time = z
                this.$store.commit('asset_new/set_common', ['asset_dep_rate', v]) 
            }
        },

        mask_money(x) {
            return window.one_mask_money(this.asset_acq_cost)
        }
    },

    methods : {
        save () {
            this.$store.dispatch('asset_new/save')
        },

        change_acq_date(x) {
            this.acq_date = x.new_date
        }
    },

    mounted () {
        this.$store.dispatch('asset_new/search_account')
        this.$store.dispatch('asset_new/search_method')
    },

    watch : {}
}
</script>