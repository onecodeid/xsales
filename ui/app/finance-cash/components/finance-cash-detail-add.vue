<template>
    <v-layout row wrap>
        <v-flex xs12>
            <table class="v-datatable v-table theme--light" style="table-layout:fixed">
                <thead>
                    <tr>
                        <th role="columnheader" scope="col" width="65%" aria-label="AKUN: Not sorted." aria-sort="none" class="column text-xs-left py-2 px-3 zalfa-bg-purple lighten-3 white--text">AKUN / REKENING {{ cashTypeCode == 'CASH.PAY' ? 'ASAL' : 'TUJUAN' }}</th>
                        <th role="columnheader" scope="col" width="35%" aria-label="DEBIT: Not sorted." aria-sort="none" class="column text-xs-right py-2 px-3 zalfa-bg-purple lighten-3 white--text">{{ side }}</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-for="(d, n) in details_add">
                        <tr>
                            <td class="text-xs-left pa-2">
                                <v-autocomplete
                                    :items="accounts"
                                    return-object clearable solo hide-details
                                    v-show="!d.account"
                                    item-text="account_name"
                                    item-value="account_id"
                                    placeholder="Pilih akun ..."
                                    item-disabled="parent"
                                    @change="changeAccount($event, n)"
                                    :value="d.account"
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
                                    solo readonly hide-details
                                    :value="d.account.account_name"
                                    v-if="!!d.account"
                                    @click:clear="changeAccount(null, n)"
                                    :clearable="n>0"
                                ></v-text-field>
                            </td>
                            <td class="text-xs-left pa-1">
                                <v-text-field
                                    solo hide-details reverse dense
                                    :value="d.debit"
                                    @input="changeAmount($event, n, side.toLowerCase())"
                                ></v-text-field>
                            </td>
                        </tr>
                    </template>
                </tbody>
                <tfoot>
                    <tr class="title">
                        <td>TOTAL {{ side }}</td>
                        <td class="text-xs-right pr-2 pl-0 pt-2"  v-show="cashTypeCode=='CASH.PAY'">
                            <v-text-field
                                :value="one_money(totals.d)"
                                reverse
                                dense
                                solo
                                readonly
                            >
                                <template slot="append"><span class="grey--text">Rp</span></template>
                            </v-text-field>
                            </td>
                        <td class="text-xs-right pr-2 pl-0 pt-2"  v-show="cashTypeCode=='CASH.RECEIVE'">
                            <v-text-field
                                :value="one_money(totals.c)"
                                reverse
                                dense
                                solo
                                readonly
                            >
                                <template slot="append"><span class="grey--text">Rp</span></template>
                            </v-text-field></td>
                    </tr>
                </tfoot>
            </table>
        </v-flex>
    </v-layout>
    

    
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
        accounts () {
            return this.$store.state.cash.accounts
        },

        details : {
            get () { return this.$store.state.cashNew.details },
            set (v) { this.setObject('details', v) }
        },

        details_add : {
            get () { return this.$store.state.cashNew.details_add },
            set (v) { this.setObject('details_add', v) }
        },

        totals () {
            let totals = {d:0, c:0}
            for (let d of this.details) {
                totals.d += parseFloat(d.debit)
                totals.c += parseFloat(d.credit)
            }

            return totals
        },

        cashTypeCode() {
            return this.$store.state.cashNew.cash_type_code
        },

        side () { return this.cashTypeCode == 'CASH.PAY' ? 'KREDIT' : 'DEBIT' },
        otherSide () { return this.cashTypeCode == 'CASH.PAY' ? 'DEBIT' : 'KREDIT' }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        setObject(x, y) {
            return this.$store.commit('cashNew/set_object', 
                    [x, y])
        },

        setObjects(x, y) {
            for (let v of y)
                this.setObject(v, x[v])
        },

        amountValidation(e) {
            if (!(e.key.match(/[0-9\,\b]/) || [8,37,39].indexOf(e.keyCode) > -1))
                e.preventDefault()
        },

        changeAmount(x, idx, side) {
            let details = JSON.parse(JSON.stringify(this.details))
            details[idx][side] = x

            this.details = details
        },

        changeAccount(x, idx) {
            let details = JSON.parse(JSON.stringify(this.details))
            details[idx].account = x

            this.details = details
        }
    },

    mounted () {
        let detail_default = this.$store.state.cashNew.detail_default
        let details_add = []
        for (let n = 0; n < this.$store.state.cashNew.detail_add_cnt; n++) { 
            details_add.push(detail_default)
        }
        this.details_add = details_add
    },

    watch : {
    }
}