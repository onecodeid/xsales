<template>
    <table class="v-datatable v-table theme--light" style="table-layout:fixed">
        <thead>
            <tr>
                <th role="columnheader" scope="col" width="50%" aria-label="AKUN: Not sorted." aria-sort="none" class="column text-xs-left pa-2 zalfa-bg-purple lighten-3 white--text">AKUN</th>
                <th role="columnheader" scope="col" width="25%" aria-label="DEBIT: Not sorted." aria-sort="none" class="column text-xs-left pa-2 zalfa-bg-purple lighten-3 white--text">DEBIT</th>
                    <th role="columnheader" scope="col" width="25%" aria-label="KREDIT: Not sorted." aria-sort="none" class="column text-xs-left pa-2 zalfa-bg-purple lighten-3 white--text">CREDIT</th>
            </tr>
        </thead>
        <tbody>
            <template v-for="(d, n) in details">
                <tr>
                    <td class="text-xs-left pa-2">
                        <v-autocomplete
                            :items="accounts"
                            return-object
                            clearable
                            v-show="!d.account"
                            item-text="account_name"
                            item-value="account_id"
                            placeholder="Pilih..."
                            item-disabled="parent"
                            label="Akun / Rekening"
                            solo
                            hide-details
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
                            solo readonly
                            :value="d.account.account_name"
                            v-if="!!d.account"
                            hide-details
                            clearable
                            @click:clear="changeAccount(null, n)"
                        ></v-text-field>
                    </td>
                    <td class="text-xs-left pa-2">
                        <v-text-field
                            solo
                            hide-details
                            reverse
                            dense
                            :value="d.debit"
                            @input="changeAmount($event, n, 'debit')"
                        ></v-text-field>
                    </td>
                    <td class="text-xs-left pa-2">
                        <v-text-field
                            solo
                            hide-details
                            reverse
                            dense
                            :value="d.credit"
                            @input="changeAmount($event, n, 'credit')"
                        ></v-text-field>
                    </td>
                </tr>
            </template>
        </tbody>
        <tfoot>
            <tr class="title">
                <td>TOTAL</td>
                <td class="text-xs-right px-3"><span class="caption">Rp </span>{{ one_money(totals.d) }}</td>
                <td class="text-xs-right px-3"><span class="caption">Rp </span>{{ one_money(totals.c) }}</td>
            </tr>
        </tfoot>
    </table>
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
            return this.$store.state.generalNew.accounts
        },

        selected_account : {
            get () { return this.$store.state.generalNew.selected_account },
            set (v) { this.setObject('selected_account', v) }
        },

        details : {
            get () { return this.$store.state.generalNew.details },
            set (v) { this.setObject('details', v) }
        },

        totals () {
            let totals = {d:0, c:0}
            for (let d of this.details) {
                totals.d += parseFloat(d.debit)
                totals.c += parseFloat(d.credit)
            }

            return totals
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        setObject(x, y) {
            return this.$store.commit('generalNew/set_object', 
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
        if (!this.$store.state.generalNew.sa) {
            let detail_default = this.$store.state.generalNew.detail_default
            let details = []

            if (this.details.length < 1) {
                for (let n = 0; n < this.$store.state.generalNew.detail_cnt; n++) { 
                    details.push(detail_default)
                }
                this.details = details
            }
        }
    },

    watch : {
    }
}