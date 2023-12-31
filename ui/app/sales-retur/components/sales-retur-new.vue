<template>
    <v-card>
        <v-card-title primary-title class="teal lighten-5">
            <h3 class="display-1 font-weight-light zalfa-text-title">RETUR PENJUALAN</h3>
        </v-card-title>
        <v-card-text>
            <v-layout row wrap>
                <v-flex xs4 pr-4>
                    <v-layout row wrap>
                        <v-flex xs6 pr-4>
                            <common-datepicker
                                label="Tanggal Retur"
                                :date="payment_date" data="0" hints=" "
                                @change="change_payment_date"
                                :details="true" :solo="false"
                            ></common-datepicker>
                        </v-flex>
                        <v-flex xs6>
                            <v-text-field
                                label="Nomor Retur"
                                :value="retur_number"
                            ></v-text-field>
                        </v-flex>
                    </v-layout>

                    <v-autocomplete
                        :items="customers"
                        v-model="selected_customer"
                        return-object
                        item-text="customer_name"
                        item-value="customer_id"
                        label="Customer"
                        :loading="customers.length<1"
                    >
                        <!-- <template slot="append-outer">
                            <v-btn color="success" class="ma-0 ml-2 btn-icon" @click="add_customer" v-show="!view">
                                <v-icon>add</v-icon>
                            </v-btn> 
                        </template> -->
                    </v-autocomplete>

                    
                    
                    <!-- <v-text-field
                        label="Terima Dari" placeholder=" "
                        v-model="retur_from"
                    ></v-text-field> -->

                    <!-- <v-autocomplete
                            :items="accounts"
                            return-object
                            clearable
                            v-show="!selected_account"
                            item-text="account_name"
                            item-value="account_id"
                            placeholder="Pilih..."
                            item-disabled="parent"
                            v-model="selected_account"
                            label="Akun / Perkiraan Tujuan"
                        >
                        <template slot="item" slot-scope="data">
                            <div class="v-list__tile__content">
                                <div class="v-list__tile__title"><span class="blue--text mr-2">{{data.item.account_code}}</span> {{data.item.account_name}}</div> 
                            </div>
                            
                        </template>

                        <template slot="selection" slot-scope="data">
                            <v-layout row wrap>
                                <div class="v-list__tile__title"><span class="blue--text mr-2">{{data.item.account_code}}</span> {{data.item.account_name}}</div>
                            </v-layout>
                        </template>
                    </v-autocomplete> -->

                    <!-- <v-text-field
                        label="Akun / Perkiraan Tujuan"
                        :value="selected_account?selected_account.account_name:''"
                        v-show="!!selected_account"
                        clearable
                        readonly
                        @click:clear="selected_account=null"
                    >
                    </v-text-field>

                    <v-textarea
                        label="Deskripsi"
                        v-model="retur_note"
                        outline
                        rows="2"
                    >
                    </v-textarea>

                    <v-textarea
                        label="Memo"
                        v-model="retur_memo"
                        outline
                        rows="2"
                    >
                    </v-textarea>

                    <common-tag mode="outline" label="Tag(s)"></common-tag> -->
                </v-flex>

                <v-flex xs3 pr-4>
                    <v-text-field
                        label="Invoice"
                        :value="!!selected_invoice?
                            selected_invoice.sales_number : ''"
                        readonly
                        hide-details
                    >
                        <template slot="append-outer">
                            <v-btn color="orange" :dark="!!selected_customer" :disabled="!selected_customer" small class="btn-icon ma-0"> Pilih </v-btn>
                        </template>
                    </v-text-field>

                    <v-layout row wrap v-if="!!selected_invoice" class="mt-1">
                        <v-flex xs12>
                            <label><i>Tgl Invoice : <b>{{selected_invoice.sales_date}}</b></i></label>  
                        </v-flex>
                        
                    </v-layout>
                    

                    
                </v-flex>

                <v-flex xs5>
                    <!-- <v-layout row wrap>
                        <v-flex xs5 pr-3>
                            &nbsp;
                        </v-flex>
                        <v-flex xs7>
                            <v-select
                                :items="warehouses"
                                v-model="selected_warehouse"
                                return-object
                                item-text="warehouse_name"
                                item-value="warehouse_id"
                                label="Diterima di Gudang"
                            ></v-select>
                        </v-flex>
                    </v-layout> -->
                    

                    <v-text-field
                        label="Alasan Retur"
                        outline
                        placeholder="Wajib Diisi !"
                        v-model="retur_note">
                    </v-text-field>
                </v-flex>

                

                <v-flex xs12>
                    <sales-retur-new-detail></sales-retur-new-detail>
                </v-flex>
            </v-layout>

            <sales-retur-invoice></sales-retur-invoice>
        </v-card-text>

        <v-card-actions class="px-3">
            <v-layout row wrap>
                <v-flex xs12 class="text-xs-center">
                    <v-btn color="success" @click="cancel" outline>Batal</v-btn>
                    <v-btn color="success" @click="save" :disabled="!btn_save_enabled">Simpan</v-btn>
                </v-flex>
            </v-layout>
            
        </v-card-actions>
    </v-card>
</template>

<script>
let rnd = Math.random() * 1e10
module.exports = {
    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        'common-tag' : httpVueLoader('../../common/components/common-tag.vue?t='+rnd),
        'sales-retur-invoice' : httpVueLoader('./sales-retur-sales.vue?t='+rnd),
        'sales-retur-new-detail' : httpVueLoader('./sales-retur-new-detail.vue?t='+rnd)
    },

    data () {
        return {
            imageName: '',
            imageUrl: '',
            imageFile: ''
        }
    },

    computed : {
        retur_id () {
                return this.$store.state.retur.retur_id
        },

        edit () {
                return this.$store.state.retur.edit
        },

        payment_date () {
                return this.$store.state.retur.retur_date
        },

        retur_number () {
            return this.$store.state.retur.retur_number
        },

        customers () {
            return this.$store.state.retur.customers
        },

        selected_customer : {
            get () { return this.$store.state.retur.selected_customer },
            set (v) { 
                this.setObject("selected_customer", v)
                // this.$store.commit('invoice/set_object', ['selected_customer', v])
                this.$store.commit('sales/set_object', ['selected_customer', v])

                // this.$store.commit('invoice/set_object', ['s_date', "2022-01-01"])
                this.$store.commit('sales/set_object', ['s_date', "2022-01-01"])

                // this.$store.commit('invoice/set_object', ['retur', true])
                this.$store.commit('sales/set_object', ['retur', true])

                // this.$store.dispatch('invoice/search')
                this.$store.dispatch('sales/search')

                if (!this.edit)
                    this.setObject("dialog_invoice", true)
            }
        },

        selected_invoice () {
            if (Object.keys(this.$store.state.invoice.selected_invoice).length === 0) return null
            return this.$store.state.invoice.selected_invoice
            // if (!this.$store.state.invoice.selected_invoice)
            //     return {}
            
        },

        items () {
            return this.$store.state.retur.items
        },

        warehouses () {
            return this.$store.state.retur.warehouses
        },

        selected_warehouse : {
            get () { return this.$store.state.retur.selected_warehouse },
            set (v) { 
                this.setObject("selected_warehouse", v)
            }
        },
        // accounts () {
        //     return this.$store.state.retur.accounts
        // },

        // selected_account : {
        //     get () { return this.$store.state.retur.selected_account },
        //     set (v) { this.$store.commit('retur.set_object', ['selected_account', v]) }
        // },

        // taxes () {
        //     return this.$store.state.retur.taxes
        // },

        // selected_tax : {
        //     get () { return this.$store.state.retur.selected_tax },
        //     set (v) { this.$store.commit('retur.set_object', ['selected_tax', v]) }
        // },

        // amount : {
        //     get () { return this.$store.state.retur.retur_amount },
        //     set (v) { this.$store.commit('retur.set_object', ['retur_amount', v]) }
        // },

        // disc () {
        //     return this.$store.state.disc.disc
        // },

        // discrp () {
        //     return this.$store.state.disc.discrp
        // },

        // subtotal () {
        //     return (this.amount * (100 - this.disc) / 100) - this.discrp
        // },

        // taxrp () {
        //     if (!this.selected_tax) return 0
        //     let tax = this.subtotal * this.selected_tax.tax_amount / 100

        //     return tax
        // },

        // total () {
        //     return this.subtotal + this.taxrp 
        // },

        // retur_from : {
        //     get () { return this.$store.state.retur.retur_from },
        //     set (v) { this.$store.commit('retur.set_object', ['retur_from', v]) }
        // },

        retur_note : {
            get () { return this.$store.state.retur.retur_note },
            set (v) { this.setObject('retur_note', v) }
        },

        // retur_memo : {
        //     get () { return this.$store.state.retur.retur_memo },
        //     set (v) { this.$store.commit('retur.set_object', ['retur_memo', v]) }
        // },

        // item_img : {
        //     get () {
        //         if (this.$store.state.retur.retur_img)
        //             return this.$store.state.retur.retur_img
        //         return ''
        //     },
        //     set (v) { this.$store.commit('retur.set_object', ['retur_img', v]) }
        // }
        btn_save_enabled () {
            if (!this.retur_note || 
                !this.selected_customer || 
                !this.selected_invoice ||
                !this.selected_warehouse)
                return false
            return true
        }
    },

    methods : {
        setObject(x, y) {
            return this.$store.commit('retur/set_object', 
                    [x, y])
        },

        change_payment_date(x) {
            this.$store.commit('retur/set_common', ['retur_date', x.new_date])
        },

        save () {
            this.$store.dispatch('retur/save').then(res => {
                if (res.retur_id)
                    window.location.replace("../list")
            })
        },

        cancel () {
            window.location.replace('../list')
        }

        // amountValidation(e) {
        //     if (!(e.key.match(/[0-9\,\b]/) || [8,37,39].indexOf(e.keyCode) > -1))
        //         e.preventDefault()
        // },

        // pickFile () {
        //     this.$refs.image.click ()
        // },

        // onFilePicked (e) {
        //     const files = e.target.files
        //     if(files[0] !== undefined) {
        //         this.imageName = files[0].name
                
        //         if(this.imageName.lastIndexOf('.') <= 0) {
        //             return
        //         }
        //         const fr = new FileReader ()
        //         fr.readAsDataURL(files[0])
        //         fr.addEventListener('load', () => {
        //             this.imageUrl = fr.result
        //             this.imageFile = files[0] // this is an image file that can be sent to server...
        //             this.getBase64(files[0])
        //         })
        //     } else {
        //         this.imageName = ''
        //         this.imageFile = ''
        //         this.imageUrl = ''
        //     }
        // },

        // getBase64(file) {
        //     let vue = this
        //     let photo_uri = ''
        //     var reader = new FileReader();
        //     reader.readAsDataURL(file);
        //     reader.onload = function () {
        //     //   console.log(reader.result);
        //         photo_uri = reader.result
        //         vue.item_img = photo_uri
        //         reader.onerror = function (error) {
        //             console.log('Error: ', error);
        //         };
        //     }

            
        // }
    },

    mounted () {
        this.$store.dispatch('retur/search_customer')
        this.$store.dispatch('retur/search_warehouse')

        let id = window.getParameter('id')
        if (id) {
            this.setObject('retur_id', id)
            this.setObject("edit", true)

            this.$store.dispatch('retur/search_id').then(res => {
                this.setObject("retur_number", res.retur_number)
                this.setObject("memo_id", res.memo_id)
                this.setObject("memo_used", res.memo_used)
                this.setObject("memo_refunded", res.memo_refunded)
                
                this.retur_note = res.retur_note
                this.selected_warehouse = { warehouse_id:res.warehouse_id, warehouse_name:res.warehouse_name }
                this.selected_customer = { customer_id:res.customer_id, customer_name:res.customer_name }

                this.$store.commit('invoice/set_object', ['selected_invoice', res])
                this.setObject('items', res.items)
            })

            
        }
        // this.$store.dispatch('retur.search_account').then(res => {
        //     this.$store.dispatch('retur.search_tax').then(resx => {
        //         this.$store.dispatch('retur.search_tag')
        //     })
        // })
    },

    watch : {
        
        // amount (v) {
        //     let re = /[^0-9]/gi;
        //     console.log(re)
        //     this.$set(this, 'amount', "vb");
        // }
    }
}