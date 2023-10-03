<template>
    <v-card>
        <v-card-title primary-title class="py-2 px-3 orange white--text">
            <v-layout row wrap>
                <v-flex xs8><h3 class="subheading text-xs-center">BIAYA TETAP</h3></v-flex>
                <v-flex xs4>
                    <h3 class="subheading text-xs-right">JUMLAH</h3>
                </v-flex>
            </v-layout>
        </v-card-title>

        <v-card-text>
            <v-layout row wrap v-for="(d, n) in costs" :key="n">
                <v-flex xs8 pr-3>
                    <v-autocomplete
                        :items="accounts"
                        :value="d.account"
                        return-object
                        solo
                        hide-details
                        clearable
                        :readonly="!!d.account"
                        @change="update_account(n, $event)"
                        item-text="account_name"
                        item-value="account_id"
                        placeholder="Pilih..."
                        v-show="!d.account"
                    >
                        <template slot="item" slot-scope="data">
                            <v-layout row wrap>
                                <v-flex xs12>{{data.item.account_name}}</v-flex>
                            </v-layout> 
                        </template>

                        <template slot="selection" slot-scope="data">
                            <span class="blue--text">{{data.item.account_code}}</span> &nbsp; {{data.item.account_name}}
                        </template>

                        <template slot="prepend">
                            <v-btn color="red" class="ma-0 mr-1" icon :dark="false" flat @click="del_cost(n)" :disabled="!!view" ><v-icon>delete</v-icon></v-btn>
                        </template>
                    </v-autocomplete>

                    <v-layout row wrap v-if="!!d.account">
                        <v-flex xs12>
                            <v-text-field
                                solo
                                :value="d.account.account_name"
                                hide-details
                                :clearable="!view"
                                readonly
                                @click:clear="update_account(n, null)"
                                >
                                <!-- <template slot="prepend">
                                    <v-btn color="red" class="ma-0 mr-1" icon :dark="false" flat @click="del_cost(n)" :disabled="!!view" ><v-icon>delete</v-icon></v-btn>
                                </template> -->
                            </v-text-field>
                        </v-flex>                        
                    </v-layout>
                </v-flex>
                <v-flex xs4>
                    <v-text-field
                        solo
                        hide-details
                        :value="d.amount"
                        reverse
                        dense
                        @input="update_amount(n, $event)"
                        suffix="Rp"
                        :readonly="!!view"
                    >
                    </v-text-field>
                </v-flex>
            </v-layout>
            <v-layout row wrap>
                <v-flex xs12>
                    <v-btn color="primary btn-icon" @click="add_cost" :disabled="!!view"><v-icon>add</v-icon></v-btn>
                </v-flex>
            </v-layout>
        </v-card-text>
    </v-card>
</template>

<script>
module.exports = {
    components: {
        "common-dialog-confirm" : httpVueLoader("../../common/components/common-dialog-confirm.vue")
    },

    data () {
        return {
            headers: [
                {
                    text: "NAMA ITEM",
                    align: "center",
                    sortable: false,
                    width: "35%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "QTY",
                    align: "left",
                    sortable: false,
                    width: "15%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "CATATAN",
                    align: "left",
                    sortable: false,
                    width: "27%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "ACTION",
                    align: "center",
                    sortable: false,
                    width: "8%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                }
            ]
        }
    },

    computed : {
        accounts () { 
            return this.$store.state.assembly_new.accounts
        },

        edit () {
            return this.$store.state.assembly_new.edit
        },

        view () {
            return this.$store.state.assembly_new.view
        },

        costs () { 
            return this.$store.state.assembly_new.costs
        },

        accounts () { 
            return this.$store.state.assembly_new.accounts
        },
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        // select (x) {
        //     this.$store.commit('assembly_new/set_selected_item', x)
        // },

        add_cost () {
            let d = this.costs
            let dfl = JSON.parse(JSON.stringify(this.$store.state.assembly_new.cost_default))
            d.push(dfl)
            this.$store.commit('assembly_new/set_costs', d)
        },

        del_cost (x) {
            let d = this.costs
            d.splice(x, 1)
            this.$store.commit('assembly_new/set_costs', d)
        },

        update_account (n, v) {
            let d = this.costs
            d[n].account = v
            
            this.$store.commit('assembly_new/set_costs', d)
        },

        set_costs (d) {
            this.$store.commit('assembly_new/set_costs', d?d:this.costs)
        },

        update_amount (idx, amount) {
            let d = this.costs            
            d[idx].amount = amount

            this.set_costs(d)
        }
    },

    watch : {
    },

    mounted () {
        this.$store.dispatch('assembly_new/search_account')
    }
}
</script>