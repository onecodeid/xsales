<template>
    <v-card>
        <v-card-title primary-title class="teal lighten-5">
            <h3 class="display-1 font-weight-light zalfa-text-title">TRANSFER UANG DARI 
                <span class="primary--text">{{selected_account?selected_account.account_name.toUpperCase():''}}</span>
            </h3>
        </v-card-title>
        <v-card-text>
            <v-layout row wrap>
                <v-flex xs6 pr-4>
                    <v-layout row wrap>
                        <v-flex xs6 pr-4>
                            <common-datepicker
                                label="Tanggal Transaksi"
                                :date="payment_date" data="0" hints=" "
                                @change="change_payment_date"
                                :details="true" :solo="false"
                                v-if="dialog||!!sa"
                            ></common-datepicker>
                        </v-flex>
                        <v-flex xs6>
                            <v-text-field
                                label="Nomor Transaksi"
                                :value="cash_number"
                                readonly
                            ></v-text-field>
                        </v-flex>
                    </v-layout>

                    <v-autocomplete
                            :items="cash_accounts"
                            return-object
                            clearable
                            v-show="!selected_account"
                            item-text="account_name"
                            item-value="account_id"
                            placeholder="Pilih..."
                            item-disabled="parent"
                            v-model="selected_account"
                            label="Rekening Kredit"
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
                    </v-autocomplete>

                    <v-text-field
                        label="Rekening Kredit"
                        :value="selected_account?selected_account.account_name:''"
                        v-show="!!selected_account"
                        readonly
                        @click:clear="selected_account=null"
                    >
                    </v-text-field>

                    <v-autocomplete
                            :items="cash_accounts"
                            return-object
                            clearable
                            v-show="!selected_account_to"
                            item-text="account_name"
                            item-value="account_id"
                            placeholder="Pilih..."
                            item-disabled="parent"
                            v-model="selected_account_to"
                            label="Rekening Debit"
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
                    </v-autocomplete>

                    <v-text-field
                        label="Rekening Debit"
                        :value="selected_account_to?selected_account_to.account_name:''"
                        v-show="!!selected_account_to"
                        clearable
                        readonly
                        @click:clear="selected_account_to=null"
                    >
                    </v-text-field>

                    <v-textarea
                        label="Deskripsi"
                        v-model="cash_note"
                        outline
                        rows="2"
                    >
                    </v-textarea>

                    <v-textarea
                        label="Memo"
                        v-model="cash_memo"
                        outline
                        rows="2"
                    >
                    </v-textarea>

                    <common-tag mode="outline" label="Tag(s)"></common-tag>
                </v-flex>

                <v-flex xs6 pl-4>
                    <v-layout row wrap mb-2>
                        <v-flex xs6 pa-3>
                            <label>NOMINAL</label>
                        </v-flex>
                        <v-flex xs6 pl-2>
                            <v-text-field
                                hide-details reverse dense solo
                                v-model="amount"
                                @keydown="amountValidation($event)"
                            >
                                <template slot="append"><span class="grey--text">Rp</span></template>
                            </v-text-field>
                        </v-flex>
                    </v-layout>

                    <v-layout row wrap mt-5>
                        <v-divider class="my-2"></v-divider>
                        <h4 class="ml-2">Bukti Transfer</h4>
                        <v-flex xs12 align-center justify-center>
                            <v-img :src="item_img" position="center center" class="ma-3">
                            </v-img>
                            <v-text-field label="Select Image" @click='pickFile' v-model='imageName' prepend-icon='attach_file'></v-text-field>
                            
                            <input
                                type="file"
                                style="display: none"
                                ref="image"
                                accept="image/*"
                                @change="onFilePicked"
                            >
                        </v-flex>
                    </v-layout>

                </v-flex>
            </v-layout>
        </v-card-text>
        <v-card-actions>
            <v-flex xs12 class="text-xs-right">
                <v-btn color="primary" @click="cancel" outline :disabled="!!saves">Batal</v-btn>
                <v-btn color="primary" @click="save" :disabled="!!saves">Simpan</v-btn>
            </v-flex>
        </v-card-actions>
    </v-card>
</template>
<style scoped>
.v-text-field.v-text-field--solo .v-input__control {
    /* min-height: 36px; */
    padding: 0;
}
</style>
<script>
let rnd = Math.random() * 1e10
module.exports = {
    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        'common-tag' : httpVueLoader('../../common/components/common-tag.vue?t='+rnd),
        'common-disc' : httpVueLoader('../../common/components/common-disc.vue?t='+rnd)
    },

    data () {
        return {
            imageName: '',
            imageUrl: '',
            imageFile: ''
        }
    },

    computed : {
        payment_date () {
                return this.$store.state.cashNew.cash_date
        },

        accounts () {
            return this.$store.state.cash.accounts
        },

        cash_accounts () {
            return this.$store.state.cash.cash_accounts
        },

        selected_account : {
            get () { return this.$store.state.cashNew.selected_account },
            set (v) { this.setObject('selected_account', v) }
        },

        selected_account_to : {
            get () { return this.$store.state.cashNew.selected_account_to },
            set (v) { this.setObject('selected_account_to', v) }
        },

        taxes () {
            return this.$store.state.cashNew.taxes
        },

        selected_tax : {
            get () { return this.$store.state.cashNew.selected_tax },
            set (v) { this.setObject('selected_tax', v) }
        },

        amount : {
            get () { return this.$store.state.cashNew.cash_amount },
            set (v) { this.setObject('cash_amount', v) }
        },

        disc () {
            return this.$store.state.disc.disc
        },

        discrp () {
            return this.$store.state.disc.discrp
        },

        subtotal () {
            return (this.amount * (100 - this.disc) / 100) - this.discrp
        },

        taxrp () {
            if (!this.selected_tax) return 0
            let tax = this.subtotal * this.selected_tax.tax_amount / 100

            return tax
        },

        total () {
            return this.subtotal + this.taxrp 
        },

        cash_number () {
            return this.$store.state.cashNew.cash_number
        },

        cash_from : {
            get () { return this.$store.state.cashNew.cash_from },
            set (v) { this.setObject('cash_from', v) }
        },

        cash_note : {
            get () { return this.$store.state.cashNew.cash_note },
            set (v) { this.setObject('cash_note', v) }
        },

        cash_memo : {
            get () { return this.$store.state.cashNew.cash_memo },
            set (v) { this.setObject('cash_memo', v) }
        },

        item_img : {
            get () {
                if (this.$store.state.cashNew.cash_img)
                    return this.$store.state.cashNew.cash_img
                return ''
            },
            set (v) { this.setObject('cash_img', v) }
        },

        edit () {
            return this.$store.state.cashNew.edit
        },

        saves () {
            return this.$store.state.cashNew.save
        },
        
        dialog () {
            return this.$store.state.cashNew.dialog_new
        },
        
        sa () {
            return this.$store.state.cashNew.sa
        }
    },

    methods : {
        setObject(x, y) {
            return this.$store.commit('cashNew/set_object', 
                    [x, y])
        },

        setObjects(x, y) {
            for (let v of y)
                this.setObject(v, x[v])
        },

        change_payment_date(x) {
            this.setObject("cash_date", x.new_date)
        },

        amountValidation(e) {
            if (!(e.key.match(/[0-9\,\be]/) || [8,37,39].indexOf(e.keyCode) > -1))
                e.preventDefault()
        },

        pickFile () {
            this.$refs.image.click ()
        },

        onFilePicked (e) {
            const files = e.target.files
            if(files[0] !== undefined) {
                this.imageName = files[0].name
                
                if(this.imageName.lastIndexOf('.') <= 0) {
                    return
                }
                const fr = new FileReader ()
                fr.readAsDataURL(files[0])
                fr.addEventListener('load', () => {
                    this.imageUrl = fr.result
                    this.imageFile = files[0] // this is an image file that can be sent to server...
                    this.getBase64(files[0])
                })
            } else {
                this.imageName = ''
                this.imageFile = ''
                this.imageUrl = ''
            }
        },

        getBase64(file) {
            let vue = this
            let photo_uri = ''
            var reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = function () {
            //   console.log(reader.result);
                photo_uri = reader.result
                vue.item_img = photo_uri
                reader.onerror = function (error) {
                    console.log('Error: ', error);
                };
            }

            
        },

        save () {
            this.$store.dispatch('cashNew/save').then(res => {
                if (!!this.$store.state.cashNew.dialog_new) {
                    this.$store.commit("cashNew/set_common", ["dialog_new", false])

                    // IF POSITION STILL IN ACCOUNT LIST == TAB.01
                    if (this.$store.state.cash.selected_tab == "TAB.01")
                        this.$store.commit("cash/set_object", ["selected_tab", "TAB.02"])
                    this.$store.dispatch("cash/search")
                } else {
                    window.location = "../list/" + this.selected_account.account_id
                }
            })
        },

        cancel () {
            if (!!this.$store.state.cashNew.dialog_new) {
                this.$store.commit("cashNew/set_common", ["dialog_new", false])
            } else {
                window.location = "../list/" + this.selected_account.account_id
            }
        }
    },

    mounted () {
        let id = window.getParameter('id')
        if (id)
            this.setObject('cash_id', id)

        if (this.$store.state.cashNew.cash_id) {
            // this.setObject('cash_id', id)
            this.setObject("edit", true)

            this.$store.dispatch('cashNew/search_id').then(res => {
                this.setObjects(res, [
                    "cash_date",
                    "cash_memo",
                    "cash_note",
                    "cash_date",
                    "cash_from",
                    "cash_number",
                    "cash_amount",
                    "cash_disc",
                    "cash_discrp",
                    "cash_img"
                ])

                this.$store.commit("tag/set_object", ['selected_tagnames', JSON.parse(res.cash_tags)])

                if (res.tax_id != 0) {
                    this.setObject("selected_tax", {tax_id:res.tax_id,tax_name:res.tax_name,tax_amount:res.tax_amount})
                }
                this.setObject("selected_account", {account_id:res.from_account_id,account_name:res.from_account_name})
                this.setObject("selected_account_to", {account_id:res.to_account_id,account_name:res.to_account_name})
            })
        }
    },

    watch : {
        // amount (v) {
        //     let re = /[^0-9]/gi;
        //     console.log(re)
        //     this.$set(this, 'amount', "vb");
        // }
    }
}