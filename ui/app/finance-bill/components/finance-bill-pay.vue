<template>
    <v-card>
        <v-card-title primary-title class="teal lighten-5">
            <h3 class="display-1 font-weight-light zalfa-text-title">PEMBAYARAN HUTANG</h3>
        </v-card-title>
        <v-card-text>
            <v-card class="mb-4" elevation="1">
                <v-card-text>
                    <v-layout row wrap>
                        <v-flex xs6>
                            <h3 class="display-1">INVOICE <span class="blue--text font-weight-regular">#{{bill_number}}</span></h3>
                            <span>Tanggal <b>{{bill_date}}</b></span>
                        </v-flex>
                        
                        <v-flex xs6>
                            <v-layout row wrap class="headline text-xs-right">
                                <v-flex xs8><h3 class="font-weight-regular">TOTAL HUTANG : </h3></v-flex>
                                <v-flex xs4><h3 class="font-weight-regular blue--text">Rp. {{one_money(bill_grand_total)}}</h3></v-flex>
                            </v-layout>
                            <v-layout row wrap class="text-xs-right mt-2">
                                <v-flex xs8><h3 class="font-weight-regular">TOTAL PEMBAYARAN SEBELUMNYA : </h3></v-flex>
                                <v-flex xs4><h3 class="font-weight-regular">( Rp. {{one_money(bill_paid-(!!edit?grandTotal:0))}} )</h3></v-flex>
                                <v-flex xs6 offset-xs6><v-divider class="my-1"></v-divider></v-flex>
                            </v-layout>
                            <v-layout row wrap class="text-xs-right mt-2">
                                <v-flex xs8><h3 class="font-weight-regular">SISA HUTANG : </h3></v-flex>
                                <v-flex xs4><h3 class="font-weight-bold">Rp. {{one_money(parseFloat(bill_unpaid)+(!!edit?parseFloat(grandTotal):0))}}</h3></v-flex>
                            </v-layout>
                            
                        </v-flex>
                    </v-layout>        
                </v-card-text>
            </v-card>
            
            <v-layout row wrap>
                <v-flex xs6 pr-4>
                    <v-text-field
                        label="Vendor"
                        :value="selected_vendor?selected_vendor.vendor_name:''"
                        readonly
                    ></v-text-field>
                </v-flex>
                <v-flex xs2 pr-4 offset-xs2>
                    <common-datepicker
                        label="Tanggal Pembayaran"
                        :date="pay_date" data="0" hints=" "
                        @change="change_pay_date"
                        :details="true" :solo="false"
                        v-if="(pay_id!=0&&!!edit)||pay_id==0"
                    ></common-datepicker>
                </v-flex>
                <v-flex xs2>
                    <v-text-field
                        label="Nomor Transaksi"
                        :value="bill_number"
                        placeholder="(Otomatis)"
                        readonly
                    ></v-text-field>
                </v-flex>
            </v-layout>

            <v-card flat>
                <v-card-title primary-title class="orange white--text py-2">
                    <v-layout row wrap>
                        <v-flex xs6>PEMBAYARAN MELALUI KAS & BANK</v-flex><v-flex xs6 class="text-xs-right pr-2">JUMLAH PEMBAYARAN</v-flex>
                    </v-layout>
                    
                </v-card-title>
                <v-card-text>
                    <v-layout row wrap>
                        <v-flex xs3>
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
                                label="Akun / Rekening Debit"
                            >
                                <template slot="item" slot-scope="data">
                                    <div class="v-list__tile__content">
                                        <div class="v-list__tile__title">{{data.item.account_name}}</div> 
                                    </div>
                                    
                                </template>

                                <template slot="selection" slot-scope="data">
                                    <v-layout row wrap>
                                        <div class="v-list__tile__title">{{data.item.account_name}}</div>
                                    </v-layout>
                                </template>
                            </v-autocomplete>

                            <v-text-field
                                label="Akun / Rekening Debit"
                                :value="selected_account?selected_account.account_name:''"
                                v-show="!!selected_account"
                                clearable readonly
                                @click:clear="selected_account=null"
                            >
                            </v-text-field>
                        </v-flex>
                        <v-flex xs7 pl-4 pr-4>
                            <v-text-field
                                hide-details
                                v-model="pay_note"
                                label="Catatan"
                                placeholder="Catatan Penerimaan"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs2>
                            <v-text-field
                                    solo hide-details reverse
                                    v-model="pay_amount"
                                ></v-text-field>
                        </v-flex>
                    </v-layout>
                    
                </v-card-text>
            </v-card>

            <disc default_disc="R"></disc>
            <memo></memo>

            <v-layout row wrap>
                <v-flex xs5 offset-xs7><v-divider class="mt-2 mb-1"></v-divider><v-divider class="mt-0 mb-4"></v-divider></v-flex>
                <v-flex xs10><h3 class="text-xs-right headline">TOTAL PEMBAYARAN</h3></v-flex>
                <v-flex xs2><h3 class="text-xs-right headline"><span class="font-weight-regular">Rp </span>{{one_money(grandTotal)}}</h3></v-flex>
                <v-flex xs5 offset-xs7><v-divider class="mt-5 mb-2"></v-divider></v-flex>
                <v-flex xs10 mt-2><h3 class="text-xs-right font-weight-regular">SISA HUTANG</h3></v-flex>
                <v-flex xs2 mt-2><h3 class="text-xs-right font-weight-regular"><span class="">Rp </span>{{one_money(bill_unpaid-grandTotal+(edit?grandTotal:0))}}</h3></v-flex>
            </v-layout>
               
            
        </v-card-text>
        <v-card-actions v-show="!dialog_pay">
            <v-layout row wrap>
                <v-flex xs12 class="text-xs-center">
                    <v-btn color="success" @click="cancel" outline>Batal</v-btn>
                    <v-btn color="success" @click="save" :disabled="!btn_save_enabled">Simpan</v-btn>
                </v-flex>
            </v-layout>
        </v-card-actions>
        <v-card-actions v-show="!!dialog_pay">
            <v-btn color="success" @click="cancel" outline>Batal</v-btn>
            <v-spacer></v-spacer>
            <v-btn color="success" @click="save" :disabled="!btn_save_enabled">Simpan</v-btn>
        </v-card-actions>
    </v-card>
</template>

<script>
let rnd = Math.random() * 1e10
module.exports = {
    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        'common-tag' : httpVueLoader('../../common/components/common-tag.vue?t='+rnd),
        'disc' : httpVueLoader('./finance-bill-pay-disc.vue?t='+rnd),
        'memo' : httpVueLoader('./finance-bill-pay-memo.vue?t='+rnd)
    },

    data () {
        return {
            imageName: '',
            imageUrl: '',
            imageFile: ''
        }
    },

    computed : {
        state () {
            return this.$store.state.billPay
        },

        edit () {
            return this.state.edit
        },

        dialog_pay () {
            return this.state.dialog_pay
        },

        cash_accounts () {
            return this.$store.state.bill.cash_accounts
        },

        bill_id () { return this.state.bill_id },
        bill_date () { return this.state.bill_date },
        bill_number () { return this.state.bill_number },
        bill_total () { return this.state.bill_total },
        bill_grand_total () { return this.state.bill_grand_total },
        bill_paid () { return this.state.bill_paid },
        bill_unpaid () { return this.state.bill_unpaid },

        selected_account : {
            get () { return this.state.selected_account },
            set (v) { this.setObject('selected_account', v) }
        },

        bill_number () {
            return this.state.bill_number
        },

        bill_amount () {
            return this.state.bill_amount
        },

        bill_used () {
            return this.state.bill_used
        },

        bill_payed () {
            return this.state.bill_payed
        },

        pay_date () { return this.state.pay_date },

        pay_amount : {
            get () { return this.state.pay_amount },
            set (v) { this.setObject('pay_amount', v) }
        },

        pay_note : {
            get () { return this.state.pay_note },
            set (v) { this.setObject('pay_note', v) }
        },

        selected_vendor () {
            return this.state.selected_vendor
        },

        bill_id : {
            get () { return this.state.bill_id },
            set (v) { this.setObject('bill_id', v) }
        },

        pay_id : {
            get () { return this.state.pay_id },
            set (v) { this.setObject('pay_id', v) }
        },
        
        btn_save_enabled () {
            if (!this.selected_vendor || 
                !this.selected_account ||
                !this.pay_amount)
                return false

            if (this.state.use_disc) {
                if (!this.state.selected_disc_account || (!this.$store.state.disc.disc && !this.$store.state.disc.discrp))
                    return false
            }

            if (this.state.use_memo) {
                for (let m of this.state.selected_memos) {
                    if (!!m.selected_memo && (!m.amount || parseFloat(m.amount) <= 0)) return false
                }
            }
            return true
        },

        grandTotal () {
            let discTotal = this.state.use_disc?
                                (parseFloat(this.bill_unpaid*(this.$store.state.disc.disc/100))+this.$store.state.disc.discrp):0
            this.setObject("disc_amount", discTotal)
            
            let memoTotal = 0
            for (let m of this.state.selected_memos) memoTotal += parseFloat(m.amount)

            return parseFloat(this.pay_amount) + parseFloat(discTotal) + parseFloat(memoTotal)
        }
    },

    methods : {
        one_money(x) {
            return window.one_money(x)
        },

        setObject(x, y) {
            return this.$store.commit('billPay/set_object', 
                    [x, y])
        },

        setObjects(x, y) {
            for (let v of y)
                this.setObject(v, x[v])
        },

        change_pay_date(x) {
            this.setObject("pay_date", x.new_date)
        },

        save() {
            this.$store.dispatch("billPay/save").then(res => {
                if (res) {
                    if (this.state.dialog_pay) {
                        this.setObject("dialog_pay", false)
                        this.$store.dispatch("bill/search")
                    } else {
                        window.location.replace('../list')
                    }
                }
            })
        },

        cancel () {
            if (this.state.dialog_pay)
                this.setObject("dialog_pay", false)
            else
                window.location.replace('../list')
        }
    },

    mounted () {
        this.$store.dispatch("bill/search_account_cash")
        this.$store.dispatch("bill/search_account")
        if (this.pay_id != 0 && !!this.state.sa) {
            this.$store.dispatch("billPay/search_pay_id").then(res => {
                this.setObjects(res, [
                    "bill_number",
                    "bill_date",
                    "bill_amount",
                    "pay_id",
                    "pay_amount",
                    "pay_number",
                    "pay_date",
                    "pay_note",
                    "bill_paid",
                    "bill_unpaid",
                    "bill_grand_total",
                    "disc_amount"
                ])
                this.setObject("selected_vendor", res.vendor)
                this.setObject("selected_account", res.credit_account)
                this.setObject("selected_disc_account", res.disc_account)

                this.$store.commit('disc/set_object', ['disc', res.pay_disc])
                this.$store.commit('disc/set_object', ['discrp', res.pay_discrp])
                this.setObject("use_disc", parseFloat(res.pay_discamount)>0?true:false)
                if (parseFloat(res.pay_disc) > 0)
                    this.$store.commit('disc/set_object', ['disctype', 'P'])
                else
                    this.$store.commit('disc/set_object', ['disctype', 'R'])
                this.setObject("edit", true)
                this.setObject("sa", true)
                document.title = 'One :: Pembayaran Hutang'
            })
        } else if (this.bill_id != 0 && !!this.state.sa) {
            this.$store.dispatch("billPay/search_bill_id").then(res => {
                this.setObjects(res, [
                    "bill_number",
                    "bill_date",
                    "bill_total",
                    "bill_grand_total",
                    "bill_paid",
                    "bill_unpaid"
                ])
                this.setObject("selected_vendor", {customer_id:res.customer_id,customer_name:res.customer_name})

                this.$store.dispatch("billPay/search_memo")
            })
        }
    }
}