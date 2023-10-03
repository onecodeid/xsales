<template>
    <v-card flat>
        <v-card-text class="py-0">
            <v-layout row wrap>
                <v-flex xs3 offset-xs0>
                    <v-checkbox label="POTONGAN PEMBAYARAN" v-model="use_disc" :value="use_disc" hide-details>
                        <template slot="label"><h3 class="red--text">POTONGAN PEMBAYARAN</h3></template>
                    </v-checkbox>
                </v-flex>
                <v-flex xs6 class="text-xs-right pt-4">
                    NOMINAL POTONGAN
                </v-flex>
                <!-- <v-flex xs3 class="text-xs-right pt-4">
                    <h3 class="caption text-xs-right">TOTAL POTONGAN</h3>
                </v-flex> -->
            </v-layout>
            <v-layout row wrap>
                <v-flex xs9 offset-xs0>
                    <v-divider class="mb-2 mt-1"></v-divider>        
                </v-flex>
            </v-layout>
            
            <v-layout row wrap>
                <v-flex xs3 offset-xs0>
                    <v-autocomplete
                        :items="accounts"
                        return-object
                        clearable
                        v-show="!selected_disc_account"
                        item-text="account_name"
                        item-value="account_id"
                        placeholder="Pilih..."
                        item-disabled="parent"
                        v-model="selected_disc_account"
                        label="Akun / Rekening Potongan"
                        :disabled="!use_disc"
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
                        label="Akun / Rekening Potongan"
                        :value="selected_disc_account?selected_disc_account.account_name:''"
                        v-show="!!selected_disc_account"
                        clearable readonly
                        @click:clear="selected_disc_account=null"
                        :disabled="!use_disc"
                    >
                    </v-text-field>
                </v-flex>
                <v-flex xs4 pl-4 pr-4>
                    <v-text-field
                        hide-details
                        v-model="disc_note"
                        label="Catatan"
                        placeholder="Catatan Potongan"
                        :disabled="!use_disc"
                    ></v-text-field>
                </v-flex>
                <v-flex xs2>
                        <v-text-field
                            hide-details reverse disabled
                            suffix="Rp"
                            v-show="!use_disc"
                            label="Potongan"
                            :value="0"
                        ></v-text-field>
                        <common-disc hide_details v-show="use_disc"></common-disc>
                </v-flex>

                <!-- <v-flex xs3 class="text-xs-right">
                    <h3 class="text-xs-right">Rp. 90.000</h3>
                </v-flex> -->
            </v-layout>

            <v-layout row wrap>
                <v-flex xs10 offset-xs0>
                    <v-divider class="mb-2 mt-1"></v-divider>        
                    
                </v-flex>
                <v-flex xs10><h3 class="font-weight-regular text-xs-right">TOTAL POTONGAN</h3></v-flex><v-flex xs2><h3 class="text-xs-right red--text">Rp. {{one_money(this.disc_amount)}}</h3></v-flex>
            </v-layout>
        </v-card-text>
    </v-card>
</template>

<script>
let rnd = Math.random() * 1e10
module.exports = {
    components : {
        'common-disc' : httpVueLoader('../../common/components/common-disc.vue?t='+rnd)
    },

    data () {
        return {}
    },

    computed : {
        state () {
            return this.$store.state.billPay
        },

        accounts () {
            return this.$store.state.bill.accounts
        },

        selected_disc_account : {
            get () { return this.state.selected_disc_account },
            set (v) { this.setObject('selected_disc_account', v) }
        },

        disc_amount : {
            get () { return this.state.disc_amount },
            set (v) { this.setObject('disc_amount', v) }
        },

        disc_note : {
            get () { return this.state.disc_note },
            set (v) { this.setObject('disc_note', v) }
        },

        use_disc : {
            get () { return this.state.use_disc },
            set (v) { this.setObject('use_disc', v) }
        },

        total () {
            if (!this.use_disc) return 0
            return this.disc_amount
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
        }
    },

    mounted () {
        
    }
}