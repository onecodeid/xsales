<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="500px"
        transition="dialog-transition"
        content-class="dialog-new"
        
    >
    <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <h3>
                    <span v-show="!edit">PENERIMAAN PEMBAYARAN</span>
                    <span v-show="!!edit">UBAH DATA PENERIMAAN PEMBAYARAN</span>
                </h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs8>
                        <v-text-field readonly :value="selected_sales.customer_name+(selected_sales.customer_code=='C.UMUM'?(' - '+selected_sales.sales_customer_name+' / '):' / ')+selected_sales.sales_number" label="Customer / Order"></v-text-field>
                    </v-flex>
                    <v-flex xs4 pl-2>
                        <v-text-field readonly :value="payment_total" label="Total Tagihan" reverse suffix="Rp"></v-text-field>
                    </v-flex>
                    <v-flex xs4>
                        <common-datepicker
                            label="Tanggal Penerimaan"
                            :date="payment_date"
                            data="0"
                            @change="change_payment_date"
                            classs=""
                            hints=" "
                            :details="true"
                            :solo="false"
                            v-if="dialog"
                        ></common-datepicker>
                    </v-flex>
                    <v-flex xs8 pl-4>
                        <v-select :items="payment_types" v-model="selected_payment_type" item-value="paymenttype_id" item-text="paymenttype_name" 
                            return-object label="Metode Pembayaran"></v-select>
                    </v-flex>
                    <v-flex xs6>
                        <v-textarea v-model="payment_note" rows="3" outline label="Catatan"></v-textarea>
                    </v-flex>
                    <v-flex xs6 pl-4>
                        <!-- <v-text-field label="Total Tagihan" reverse readonly :value="payment_total" outline></v-text-field> -->
                        <v-text-field label="Pembayaran Sebelumnya" reverse readonly :value="payment_paid" v-show="payment_paid>0"></v-text-field>
                        <v-text-field label="Jumlah Pembayaran" reverse v-model="payment_amount" outline></v-text-field>
                    </v-flex>
                </v-layout>
            </v-card-text>
            <v-card-actions>
                
                <v-btn color="red" dark @click="del">Hapus</v-btn>
                <v-spacer></v-spacer>
                <v-btn color="primary" @click="dialog=!dialog" text flat>Tutup</v-btn>
                <v-btn color="primary" @click="save">Simpan</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
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

/* .dialog-new .v-input__append-outer {
    margin: 0px !important;
} */

.dialog-new .v-input__prepend-outer button {
    min-height: 36px;
}
</style>
<script>
module.exports = {
    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
    },

    data () {
        return {
            tempo: true
         }
    },

    computed : {
        __s () { return this.$store.state.payment },

        dialog : {
            get () { return this.__s.dialog },
            set (v) { this.__c("dialog", v) }
        },

        edit () { return this.__s.edit },

        payment_date () {
            return this.__s.payment_date
        },

        selected_sales () {
            return this.$store.state.sales.selected_sales
        },

        payment_types () {
            return this.__s.payment_types
        },

        selected_payment_type : {
            get () { return this.__s.selected_payment_type },
            set (v) { this.__c("selected_payment_type", v) }
        },

        payment_total () { return this.__s.payment_total },
        payment_paid () { return this.__s.payment_paid },
        payment_unpaid () { return this.__s.payment_unpaid },

        payment_amount : {
            get () { return this.__s.payment_amount },
            set (v) { this.__c("payment_amount", v) }
        },

        payment_note : {
            get () { return this.__s.payment_note },
            set (v) { this.__c("payment_note", v) }
        }
    },

    methods : {
        __c (a, b) { this.$store.commit("payment/set_object", [a, b]) },
        __d (a) { return this.$store.dispatch("payment/"+a) },

        change_payment_date(x) {
            this.__c('payment_date', x.new_date)
        },

        save() {
            this.__d("save").then((d) => {
                this.dialog = false
                this.$store.dispatch("sales/search")
            })
        },

        del () {
            this.__d("del").then((d) => {
                this.dialog = false
                this.$store.dispatch("sales/search")
            })
        }
    },

    mounted () {
        this.__d("search_paymenttype")
        .then((x)=>{
            console.log(x)
        })
    }
}