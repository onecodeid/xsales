<template>
    <v-card>
        <v-card-title primary-title class="cyan white--text pt-3">
            <h3>
                <span v-show="!edit">INPUT PENERIMAAN BARANG</span>
                <span v-show="!!edit">UBAH PENERIMAAN BARANG</span>
            </h3>
        </v-card-title>
        <v-card-text>
            <v-layout row wrap>
                <v-flex xs12>
                    <v-layout row wrap>

                        <v-flex xs2>
                            <v-layout row wrap>
                                <v-flex xs6 pr-2>
                                    <common-datepicker
                                        label="Tanggal"
                                        :date="receive_date.split('-').reverse().join('-')"
                                        data="0"
                                        @change="change_receive_date"
                                        classs=""
                                        hints=" "
                                        :details="true"
                                        :solo="false"
                                        v-if="dialog"
                                        :disabled="view"
                                    ></common-datepicker>
                                </v-flex>
                                <v-flex xs6>
                                    <v-text-field
                                        label="Nomor Faktur"
                                        v-model="receive_number"
                                        :readonly="receive_post||is_sales||view"
                                        
                                    ></v-text-field>
                                </v-flex>
                            </v-layout>
                            
                            <v-text-field
                                label="Nomor Referensi Vendor"
                                v-model="receive_ref_number"
                                :readonly="receive_post||is_sales||view"
                                
                            ></v-text-field>
                            <!-- <v-text-field
                                label="Tanggal Transaksi"
                                :value="receive_date"
                                v-show="!!receive_post||!!is_sales||!!view"
                                readonly
                                :disabled="view"
                            ></v-text-field> -->

                            
                        </v-flex>
                        <v-flex xs4 pl-5>
                            <v-autocomplete
                                :items="vendors"
                                v-model="selected_vendor"
                                return-object
                                item-text="vendor_name"
                                item-value="vendor_id"
                                label="Vendor / Supplier"
                                :disabled="details.length>0 && !!details[0].item"
                            ></v-autocomplete>

                            <v-autocomplete
                                :items="warehouses"
                                v-model="selected_warehouse"
                                return-object
                                item-text="warehouse_name"
                                item-value="warehouse_id"
                                label="Diterima di Gudang"
                                :disabled="details.length>0 && !!details[0].item"
                            ></v-autocomplete>

                            <!-- <v-layout row wrap>
                                <v-flex xs6>
                                    <v-checkbox label="Harga termasuk PPN?" v-model="receive_ppn" true-value="Y" false-value="N"></v-checkbox>        
                                </v-flex>
                            </v-layout> -->
                            
                        </v-flex>

                        <v-flex xs3 pl-4>
                            <v-textarea
                                label="Memo untuk bagian Keuangan"
                                rows="4"
                                v-model="receive_memo"
                                outline
                                :readonly="view"
                            ></v-textarea>
                        </v-flex>

                        <v-flex xs3 pl-2>
                            <v-textarea
                                label="Catatan"
                                rows="4"
                                v-model="receive_note"
                                outline
                                :readonly="view"
                            ></v-textarea>
                        </v-flex>
                        


                        <v-flex xs12>
                            <v-card>
                                <v-card-title primary-title class="py-2 px-3 cyan white--text">
                                    <v-layout row wrap>
                                        <v-flex xs6><h3 class="subheading text-xs-center">PRODUK</h3></v-flex>
                                        <v-flex xs6>
                                            <v-layout row wrap>
                                                <v-flex xs2 pr-2>
                                                    <h3 class="subheading text-xs-center">QTY</h3>
                                                </v-flex>
                                                <v-flex xs2 pr-2>
                                                    <h3 class="subheading text-xs-center">UNIT</h3>
                                                </v-flex>
                                                <v-flex xs8 pr-2>
                                                    <h3 class="subheading text-xs-center">CATATAN</h3>
                                                </v-flex>
                                            </v-layout>
                                        </v-flex>
                                    </v-layout>
                                </v-card-title>
                                <v-card-text class="px-3 py-2" v-show="!!selected_vendor&&!!selected_warehouse">
                                    <v-layout row wrap v-for="(d, n) in details" :key="n" :class="{'mt-2':n>0}">
                                        <v-flex xs6 pr-3>
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
                                                item-value="detail_id"
                                                placeholder="Pilih..."
                                                v-show="!d.item"
                                            >
                                                <template slot="item" slot-scope="data">
                                                    <v-layout row wrap>
                                                        <v-flex xs12>{{data.item.item_name}} ({{data.item.detail_qty}}/{{data.item.detail_received}})</v-flex>
                                                        <v-flex xs12 class="caption blue--text">
                                                            {{data.item.purchase_number}} - {{data.item.vendor_name}}
                                                        </v-flex>
                                                    </v-layout> 
                                                </template>

                                                <template slot="selection" slot-scope="data">
                                                    <span class="blue--text">{{data.item.purchase_number}}</span> &nbsp; {{data.item.item_name}}
                                                </template>

                                                <template slot="prepend" v-if="!is_sales">
                                                    <v-btn color="red" class="ma-0 mr-1" icon :dark="false" flat @click="del_detail(n)" ><v-icon>delete</v-icon></v-btn>
                                                </template>
                                            </v-autocomplete>

                                            <v-layout row wrap v-if="!!d.item">
                                                <v-flex xs12>
                                                    <v-text-field
                                                        solo
                                                        :value="d.item.item_name + '   (' + d.item.detail_qty + '/' + d.item.detail_received + ')'"
                                                        hide-details
                                                        :clearable="!view"
                                                        readonly
                                                        @click:clear="update_item(n, null)"
                                                        >
                                                        <template slot="prepend" v-if="!is_sales">
                                                            <v-btn color="red" class="ma-0 mr-1" icon :dark="false" flat @click="del_detail(n)" :disabled="!!view" ><v-icon>delete</v-icon></v-btn>
                                                        </template>
                                                    </v-text-field>
                                                </v-flex>    
                                                <v-flex xs12 class="caption blue--text pl-5 mt-2">
                                                    {{d.item.purchase_number}} - <span class="red--text">{{d.item.vendor_name}}</span>
                                                </v-flex>
                                                
                                            </v-layout>
                                        </v-flex>


                                        <v-flex xs6>
                                            <v-layout row wrap>
                                                <v-flex xs2 pr-2>
                                                    <v-text-field
                                                        label=""
                                                        solo
                                                        hide-details
                                                        :value="d.qty"
                                                        reverse
                                                        dense
                                                        @change="set_details"
                                                        @input="update_amount(n, 'qty', $event)"

                                                        suffix=""
                                                        :readonly="view"
                                                    ></v-text-field>
                                                </v-flex>
                                                <v-flex xs2 pr-2>
                                                    <v-text-field
                                                        label=""
                                                        solo
                                                        hide-details
                                                        :value="d.item?d.item.item_unit:''"
                                                        dense
                                                        readonly
                                                        flat
                                                    ></v-text-field>
                                                </v-flex>
                                                <v-flex xs8 pr-2>
                                                    <v-text-field
                                                        label=""
                                                        solo
                                                        hide-details
                                                        :value="d.note"
                                                        dense
                                                        :readonly="view"
                                                    ></v-text-field>
                                                    <div v-if="!!d.item" v-show="d.item.purchase_memo!=''" class="caption mt-2 blue--text">
                                                        <!-- <v-divider class="my-2"></v-divider> -->
                                                        <b>Memo purchasing</b> : <i><u>{{d.item.purchase_memo}}</u></i>
                                                    </div>
                                                </v-flex>
                                            </v-layout>    
                                        </v-flex>
                                        
                                    </v-layout>

                                    <v-layout row wrap v-show="!view">
                                        <v-flex xs2 class="text-xs-center" pt-2 offset-md5>
                                            <v-btn color="primary btn-icon" @click="add_detail" :disabled="!btn_add_enabled"><v-icon>add</v-icon></v-btn>
                                        </v-flex>
                                        <v-flex xs5>
                                            <!-- <v-layout row wrap>
                                                <v-flex xs2 offset-md5 pt-2>
                                                    TOTAL
                                                </v-flex>
                                                <v-flex xs5 pt-2>
                                                    {{one_money(receive_total)}}
                                                </v-flex>
                                            </v-layout> -->
                                        </v-flex>
                                    </v-layout>
                                    
                                </v-card-text>

                                <v-card-text class="px-3 py-2" v-show="!selected_vendor||!selected_warehouse">
                                    <v-layout row wrap>
                                        <v-flex xs12 class="text-xs-center red--text pa-2">
                                            Pilih dulu vendor dan gudangnya ...
                                        </v-flex>
                                    </v-layout>
                                </v-card-text>
                            </v-card>

                            
                            <!-- <v-layout row wrap>
                                <v-flex xs12 px-3 pt-3>
                                    <v-layout row wrap>
                                        <v-flex xs7 py-3>
                                            <h3 class="title">TOTAL</h3>
                                        </v-flex>
                                        <v-flex xs5>
                                            <v-layout row wrap>
                                                <v-flex xs6 pr-2>
                                                    <v-text-field
                                                        label=""
                                                        solo
                                                        hide-details
                                                        :value="one_money(total.debit)"
                                                        reverse
                                                        dense
                                                        flat
                                                        suffix="Rp"
                                                        readonly
                                                    ></v-text-field>
                                                </v-flex>
                                                <v-flex xs6 pl-2>
                                                    <v-text-field
                                                        label=""
                                                        solo
                                                        hide-details
                                                        :value="one_money(total.credit)"
                                                        reverse
                                                        dense
                                                        flat
                                                        suffix="Rp"
                                                        readonly
                                                    ></v-text-field>
                                                </v-flex>
                                            </v-layout>
                                        </v-flex>    
                                    </v-layout>
                                </v-flex>
                                
                            </v-layout> -->
                            
                        </v-flex>
                    </v-layout>
                </v-flex>
            </v-layout>
        </v-card-text>

        <v-card-actions>
            <v-btn color="primary" flat @click="dialog=!dialog" v-show="!view">Batal</v-btn>
            <v-spacer></v-spacer>
            <!-- <v-btn color="primary" @click="add_detail">Add</v-btn>                 -->
            <v-btn color="primary" @click="save_confirm" :disabled="!btn_save_enabled" :dark="btn_save_enabled" v-show="!view">Simpan</v-btn>
            <!-- <v-btn color="primary" @click="save" :disabled="!btn_save_enabled" :dark="btn_save_enabled" v-show="!view">Simpan</v-btn> -->
            <v-btn color="primary" @click="dialog=!dialog" v-show="view">Tutup</v-btn>
        </v-card-actions>
    </v-card>
</template>

<style>
.input-dense .v-input__control {
    min-height: 36px !important;
}

.dialog-new .v-input__prepend-outer {
    margin: 0px !important;
}

.dialog-new .v-text-field.v-text-field--solo .v-input__control {
    min-height: 36px;
    padding: 0;
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
            get () { return this.$store.state.receive_new.dialog_new },
            set (v) { this.$store.commit('receive_new/set_common', ['dialog_new', v]) }
        },

        receive_note : {
            get () { return this.$store.state.receive_new.receive_note },
            set (v) { this.$store.commit('receive_new/set_common', ['receive_note', v]) }
        },

        receive_memo : {
            get () { return this.$store.state.receive_new.receive_memo },
            set (v) { this.$store.commit('receive_new/set_common', ['receive_memo', v]) }
        },

        receive_number : {
            get () { return this.$store.state.receive_new.receive_number },
            set (v) { this.$store.commit('receive_new/set_common', ['receive_number', v]) }
        },

        receive_ref_number : {
            get () { return this.$store.state.receive_new.receive_ref_number },
            set (v) { this.$store.commit('receive_new/set_common', ['receive_ref_number', v]) }
        },

        receive_ppn : {
            get () { return this.$store.state.receive_new.receive_ppn },
            set (v) { 
                this.$store.commit('receive_new/set_common', ['receive_ppn', v]) 
                this.retotal(0)
            }
        },

        details () {
            return this.$store.state.receive_new.details
        },

        total () {
            let x = 0
            let y = 0
            for (let j of this.details) {
                x = x + Math.round(j.debit)
                y = y + Math.round(j.credit)
            }
            return {debit:x,credit:y}
        },

        receive_date () {
            return this.$store.state.receive_new.receive_date
        },

        receive_post () {
            let x = this.$store.state
            if (!x.receive_new.edit) return false
            if (x.receive.selected_receive.receive_confirm == 'N') return false
            return true
        },

        items () {
            return this.$store.state.receive_new.items
        },

        btn_save_enabled () {
            // let ttl = this.total
            // if (ttl.debit == 0 && ttl.credit == 0) return false
            // if (ttl.debit != ttl.credit) return false
            // if (this.receive_note == '') return false
            // if (!!this.$store.state.receive_new.save) return false
            // if (!!this.receive_post) return false
            // if (this.view) return false

            // for (let d of this.details)
            //     if (!d.account && (Math.round(d.credit) > 0 || Math.round(d.debit) > 0))
            //         return false

            return true
        },

        btn_add_enabled () {
            // let d = this.details[this.details.length-1]
            // if (!d) return false
            // if (!d.account) return false
            // if (this.is_sales) return false
            // if (this.view) return false

            // if (this.$store.state.receive_new.edit) {
            //     let j = this.$store.state.receive.selected_receive
            //     if (j.receive_post=='Y') return false
            // }
            
            return true
        },

        is_sales() {
            if (this.$store.state.filter.indexOf("J.03")>-1)
                return true
            return false
        },

        edit() {
            return this.$store.state.receive_new.edit
        },

        view () {
            let r = this.$store.state.receive.selected_receive
            if (!this.edit) return false
            if (r.receive_bill!=0 || r.receive_confirm=='Y') return true
            // return false
            return this.$store.state.receive_new.view
        },

        vendors () {
            return this.$store.state.receive_new.vendors
        },

        selected_vendor : {
            get () { return this.$store.state.receive_new.selected_vendor },
            set (v) { 
                this.$store.commit('receive_new/set_selected_vendor', v)
                this.$store.dispatch('receive_new/search_item')
            }
        },

        receive_total () {
            let total = 0
            for (let x of this.details)
                total = total + Math.round(x.total)
            return total
        },

        warehouses () {
            return this.$store.state.receive_new.warehouses
        },

        selected_warehouse : {
            get () { return this.$store.state.receive_new.selected_warehouse },
            set (v) { 
                this.$store.commit('receive_new/set_selected_warehouse', v)
                this.$store.dispatch('receive_new/search_item')
            }
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        one_mask_money (x) {
            return window.one_mask_money(x)
        },

        save () {
            this.$store.commit('receive_new/set_common', ['save_n_confirm', false])
            this.$store.dispatch('receive_new/save').then((d => {
                console.log(d)
            }))
        },

        save_confirm () {
            this.$store.commit('receive_new/set_common', ['save_n_confirm', true])
            this.$store.dispatch('receive_new/save').then((d => {
                let e = JSON.parse(d)
                // console.log(e)
            }))
        },

        update_amount (idx, side, amount) {
            let d = this.details            
            d[idx][side] = amount
            this.retotal(idx)
            // this.$store.commit('receive_new/set_details', d)
        },

        add_detail () {
            let d = this.details
            let dfl = JSON.parse(JSON.stringify(this.$store.state.receive_new.detail_default))
            d.push(dfl)
            this.$store.commit('receive_new/set_details', d)
        },

        set_details () {
            this.$store.commit('receive_new/set_details', this.details)
        },

        del_detail (x) {
            let d = this.details
            d.splice(x, 1)
            this.$store.commit('receive_new/set_details', d)
        },

        change_receive_date(x) {
            this.$store.commit('receive_new/set_common', ['receive_date', x.new_date])
        },

        update_item(idx, v) {
            let d = this.details
            d[idx].item = v
            
            this.$store.commit('receive_new/set_details', d)
        },

        change_ppn(idx, v) {
            let d = this.details
            d[idx].ppn = v
            this.retotal(idx)
        },

        retotal(idx) {
            let d = this.details
            if (idx == 0) {
                for (let idxn in d) {
                    d[idxn].total = d[idxn].price * d[idxn].qty * (100 - d[idxn].disc) * (d[idxn].ppn == "Y" && this.receive_ppn == "N"?1.1:1) / 100
                }
            } else {
                d[idx].total = d[idx].price * d[idx].qty * (100 - d[idx].disc) * (d[idx].ppn == "Y" && this.receive_ppn == "N"?1.1:1) / 100
                console.log(d[idx].price)
                console.log(d[idx].qty)
                console.log(d[idx].disc)
                console.log(d[idx].ppn)
                
            }
        }
    },

    mounted () {
        this.$store.dispatch('receive_new/search_vendor')
        this.$store.dispatch('receive_new/search_warehouse')
        // this.$store.dispatch('receive_new/search_item')
    },

    watch : {}
}
</script>