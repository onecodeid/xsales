<template>
    <v-card flat>
        <v-card-text class="my-0 py-0">
            <v-layout row wrap>
                <v-flex xs3 offset-xs0>
                    <v-checkbox label="POTONGAN PEMBAYARAN" v-model="use_memo" :value="use_memo" hide-details>
                        <template slot="label"><h3 class="cyan--text">GUNAKAN MEMO</h3></template>
                    </v-checkbox>
                </v-flex>
                <v-flex xs6 class="text-xs-right pt-4">
                    NOMINAL MEMO
                </v-flex>
            </v-layout>
            <v-layout row wrap>
                <v-flex xs9 offset-xs0>
                    <v-divider class="mb-2 mt-1"></v-divider>        
                </v-flex>
            </v-layout>

            <v-layout row wrap v-for="(memo,i) in selected_memos" :key="i">
                <v-flex xs3 offset-xs0>
                    <v-select
                        :items="memos"
                        return-object
                        clearable
                        v-show="!memo.selected_memo"
                        item-text="memo_number"
                        item-value="memo_id"
                        placeholder="Pilih..."
                        @change="update_item(i, 'selected_memo', $event)"
                        label="Kredit Memo"
                        :value="memo.selected_memo"
                        :disabled="!use_memo"
                    >
                        <template slot="item" slot-scope="data">
                            <div class="v-list__tile__content">
                                <div class="v-list__tile__title">{{data.item.memo_number}}</div> 
                            </div>
                            
                        </template>

                        <template slot="selection" slot-scope="data">
                            <v-layout row wrap>
                                <div class="v-list__tile__title">{{data.item.memo_number}}</div>
                            </v-layout>
                        </template>
                    </v-select>

                    <v-text-field
                        label="Kredit Memo"
                        :value="memo.selected_memo?memo.selected_memo.memo_number:''"
                        v-show="!!memo.selected_memo"
                        clearable readonly
                        @click:clear="update_item(i, 'selected_memo', null)"
                    >
                    </v-text-field>
                </v-flex>
                <v-flex xs4 pl-4 pr-4>
                    <div v-if="memo.selected_memo && use_memo">
                        <div>Invoice #{{memo.selected_memo.invoice_number}}</div>
                        <div>Total Memo : Rp. {{one_money(memo.selected_memo.memo_amount)}} | 
                            Terpakai : Rp. {{one_money(parseFloat(memo.selected_memo.memo_used)+parseFloat(memo.selected_memo.memo_refunded))}} | 
                            Tersedia : Rp. {{one_money(+memo.selected_memo.memo_amount-memo.selected_memo.memo_used-memo.selected_memo.memo_refunded)}}</div>
                    </div>
                    
                </v-flex>
                <v-flex xs2>
                    <v-text-field
                            hide-details reverse
                            :value="memo.amount"
                            @change="update_item(i, 'amount', $event)"
                            suffix="Rp"
                            :disabled="!use_memo||!memo.selected_memo"
                        ></v-text-field>
                </v-flex>
            </v-layout>

            <v-layout row wrap>
                <v-flex xs10 offset-xs0>
                    <v-divider class="mb-2 mt-1"></v-divider>        
                    
                </v-flex>
                <v-flex xs10><h3 class="font-weight-regular text-xs-right">TOTAL MEMO</h3></v-flex><v-flex xs2><h3 class="text-xs-right cyan--text">Rp. {{one_money(total)}}</h3></v-flex>
            </v-layout>
        </v-card-text>
    </v-card>
</template>

<script>
let rnd = Math.random() * 1e10
module.exports = {
    components : {
    },

    data () {
        return {}
    },

    computed : {
        state () {
            return this.$store.state.payablePay
        },

        cash_accounts () {
            return this.$store.state.payable.cash_accounts
        },

        use_memo : {
            get () { return this.state.use_memo },
            set (v) { this.setObject('use_memo', v) }
        },

        memos () {
            return this.state.memos
        },

        selected_memos : {
            get () { return this.state.selected_memos },
            set (v) { this.setObject('selected_memos', v) }
        },

        total () {
            let total = 0
            for (let m of this.selected_memos) total += parseFloat(m.amount)

            return total
        }
    },

    methods : {
        one_money(x) {
            return window.one_money(x)
        },

        setObject(x, y) {
            return this.$store.commit('payablePay/set_object', 
                    [x, y])
        },

        setObjects(x, y) {
            for (let v of y)
                this.setObject(v, x[v])
        },

        update_item(idx, y, v) {
            let d = JSON.parse(JSON.stringify(this.selected_memos))
            d[idx][y] = v

            if (y == 'selected_memo') {
                if (v != null) {
                    if (d[idx].amount == 0) d[idx].amount = v.memo_amount - v.memo_used - v.memo_refunded
                } else d[idx].amount = 0
            }

            this.selected_memos = d
        }
    },

    mounted () {
        
    }
}
</script>