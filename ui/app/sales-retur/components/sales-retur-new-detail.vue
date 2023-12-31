<template>
    <v-layout row wrap>
        <v-flex xs12>
            <v-data-table 
                :headers="headers"
                :items="items"
                :loading="false"
                hide-actions
                class="elevation-1">
                <template slot="items" slot-scope="props">
                    
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.item_name }}</td>
                    <td class="text-xs-right pa-2" @click="select(props.item)">
                        <v-layout row wrap>
                            <v-flex xs12>{{ props.item.qty }} {{ props.item.unit_name }}</v-flex>
                        </v-layout>
                    </td>
                    <td class="text-xs-right pa-2" @click="select(props.item)">
                        <span class="caption">Rp</span> {{ one_money(props.item.price) }}<br>
                        <span class="caption red--text" v-show="props.item.disc>0"><i>diskon {{props.item.disc}}%</i></span>
                    </td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">
                        <v-text-field
                            solo
                            dense
                            :value="props.item.retur_qty"
                            hide-details
                            @input="update_amount(props.index, 'retur_qty', $event)"
                            reverse
                        ></v-text-field>
                    </td>
                    <td class="text-xs-right pa-2" @click="select(props.item)">
                        <span class="caption">Rp</span> {{ one_money(props.item.price * props.item.retur_qty * (100-props.item.disc) / 100) }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">
                        <v-text-field
                            solo
                            dense
                            :value="props.item.retur_note"
                            hide-details
                            @input="update_amount(props.index, 'retur_note', $event)"
                        ></v-text-field>
                    </td>
                </template>
                <template slot="footer">
                    <tr v-show="total_ppn>0"><td colspan="4" class="text-xs-right"><b>SUBTOTAL SEBELUM PPN</b></td>
                    <td class="text-xs-right pa-2"><span class="caption">Rp</span> <b>{{one_money(total_before_ppn)}}</b></td><td></td></tr>

                    <tr v-show="total_ppn>0"><td colspan="4" class="text-xs-right">PPN</td>
                    <td class="text-xs-right pa-2"><span class="caption">Rp</span> <b>{{one_money(total_ppn)}}</b></td><td></td></tr>

                    <tr><td colspan="4" class="text-xs-right"><b>TOTAL</b></td>
                    <td class="text-xs-right pa-2"><span class="caption">Rp</span> <b class="cyan--text">{{one_money(total)}}</b></td><td></td></tr>
                    
                    <tr v-show="memo_used>0||memo_refunded>0"><td colspan="4" class="text-xs-right"><b>DIGUNAKAN / DIKEMBALIKAN</b></td>
                    <td class="text-xs-right pa-2"><span class="caption">Rp</span> <b class="red--text">{{one_money(memo_used+memo_refunded)}}</b></td><td></td></tr>

                    <tr v-show="memo_used>0||memo_refunded>0"><td colspan="4" class="text-xs-right"><b>SISA MEMO</b></td>
                    <td class="text-xs-right pa-2"><span class="caption">Rp</span> <b class="cyan--text">{{one_money(total-memo_used-memo_refunded)}}</b></td><td></td></tr>
                </template>
            </v-data-table>
        </v-flex>
    </v-layout>
</template>

<script>
let rnd = Math.random() * 1e10
module.exports = {
    components : {
        
    },

    data () {
        return {
            headers: [
                {
                    text: "NAMA ITEM",
                    align: "left",
                    sortable: false,
                    width: "30%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "INVOICE QTY",
                    align: "right",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "HARGA",
                    align: "right",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "RETUR QTY",
                    align: "right",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "NOMINAL RETUR",
                    align: "right",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "CATATAN",
                    align: "left",
                    sortable: false,
                    width: "30%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                // {
                //     text: "PESAN",
                //     align: "left",
                //     sortable: false,
                //     width: "27%",
                //     class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                // },
                // {
                //     text: "TOTAL TAGIHAN",
                //     align: "right",
                //     sortable: false,
                //     width: "15%",
                //     class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                // },
                // {
                //     text: "ACTION",
                //     align: "center",
                //     sortable: false,
                //     width: "10%",
                //     class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                // }
            ]
        }
    },

    computed : {
        items () {
            return this.$store.state.retur.items
        },

        total () {
            let total = 0
            for (let item of this.items)
                total += item.retur_qty * item.hpp

            return total
        },

        total_before_ppn () {
            let total = 0
            for (let item of this.items)
                total += item.retur_qty * item.hpp_non_ppn

            return total
        },

        total_ppn () {
            let total = 0
            for (let item of this.items)
                total += item.retur_qty * (item.hpp - item.hpp_non_ppn)

            return total
        },

        memo_id () {
            return this.$store.state.retur.memo_id
        },

        memo_used () {
            return this.$store.state.retur.memo_used
        },

        memo_refunded () {
            return this.$store.state.retur.memo_refunded
        }
       
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        select(x) {
            return
        },

        update_amount (idx, side, amount) {
            let d = JSON.parse(JSON.stringify(this.items))
            d[idx][side] = amount
            // this.retotal(idx)

            this.$store.commit('retur/set_object', ['items', d])
        }
    },

    mounted () {
    },

    watch : {
    }
}