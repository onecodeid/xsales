<template>
    <v-layout row wrap>
        <v-flex xs12>
            <v-card>
                <v-card-title primary-title class="cyan py-2 px-3 white--text">

                    <v-layout row wrap>
                        <!-- <v-flex xs12 class="text-xs-center">
                            <b>DISKON KHUSUS</b>
                        </v-flex> -->
                        <v-divider class="my-1 white"></v-divider>
                        <v-flex xs6 class="text-xs-center">
                            NAMA DISKON
                        </v-flex>
                        <v-flex xs6 class="text-xs-center">
                            DISKON KHUSUS
                        </v-flex>
                    </v-layout>
                </v-card-title>
                <v-card-text class="pt-2">
                    <div v-show="cdiscs.length<1" class="text-xs-center">Belum ada data diskon</div>
                    <v-layout row wrap v-for="(cdisc, n) in cdiscs" :key="n" class="mb-1">
                        <!-- <v-flex xs1 class="text-xs-left" pt-2>{{n+1}}</v-flex> -->
                        <v-flex xs6>
                            <v-select
                                :items="discs"
                                item-value="disc_id"
                                item-text="disc_name"
                                return-object
                                solo
                                @change="update_cdisc(n, 'disc', $event)" 
                                v-if="!cdisc.disc"
                                hide-details
                            >
                                <template slot="item" slot-scope="data">{{ data.item.disc_name }} - {{ data.item.disc_amount }}%</template>
                                <template slot="selection" slot-scope="data">{{ data.item.disc_name }} - {{ data.item.disc_amount }}%</template>
                            </v-select>
                            <v-text-field
                                :value="cdisc.disc.disc_name + ' - ' + cdisc.disc.disc_amount + '%'"
                                v-if="!!cdisc.disc"
                                readonly
                                clearable
                                solo
                                @click:clear="clear_disc(n)"
                                hide-details
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs5 pl-2>
                            <v-text-field
                                solo
                                @input="update_cdisc(n, 'amount', $event)"
                                :value="cdisc.amount"
                                suffix="%"
                                hide-details
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs1>
                            <v-btn color="red" class="ma-0 mr-1" icon :dark="false" flat @click="del_cdisc(n)"><v-icon>delete</v-icon></v-btn>
                        </v-flex>
                        <!-- <v-flex xs11>
                            <v-text-field
                                prefix="a/n"
                                solo
                                hide-details
                                @input="update_cdisc(n, 'name', $event)"
                                :value="cdisc.name"
                            ></v-text-field>
                        </v-flex> -->
                        <v-flex xs12>
                            <v-divider class="mb-1 mt-2"></v-divider>
                        </v-flex>
                    </v-layout>
                </v-card-text>
                <v-card-actions>
                    <v-layout row wrap>
                        <v-flex xs12 class="text-xs-center">
                            <v-btn color="primary btn-icon" @click="add_cdisc" small><v-icon>add</v-icon></v-btn>
                        </v-flex>
                    </v-layout>
                    
                </v-card-actions>
            </v-card>
        </v-flex>
        
    </v-layout>
</template>
<style>
.dialog-new .input-dense .v-input__control {
    min-height: 36px !important;
}
.dialog-new .v-text-field.v-text-field--solo .v-input__control {
    min-height: 36px;
    padding: 0;
}
</style>
<script>
module.exports = {
    components : {
    },

    data () {
        return {
        }
    },

    computed : {
        edit () {
            return this.$store.state.customer_new.edit
        },

        discs () {
            return this.$store.state.customer_new.discs
        },

        cdiscs () {
            return this.$store.state.customer_new.cdiscs
        },

        selected_customer_type : {
            get () { return this.$store.state.customer_new.selected_customer_type },
            set (v) { this.$store.commit('customer_new/set_selected_customer_type', v) }
        }
    },

    methods : {
        add_cdisc () {
            let x = this.cdiscs
            x.push(JSON.parse(JSON.stringify(this.$store.state.customer_new.template_cdisc)))

            this.$store.commit('customer_new/set_cdiscs', x)
        },

        update_cdisc (i, t, v) {
            let x = this.cdiscs
            x[i][t] = v
            this.$store.commit('customer_new/set_cdiscs', x)
        },

        del_cdisc (n) {
            let x = this.cdiscs
            x.splice(n, 1)
            this.$store.commit('customer_new/set_cdiscs', x)
        },

        clear_disc (n) {
            let x = this.cdiscs
            x[n]['disc'] = null
            this.$store.commit('customer_new/set_cdiscs', x)
        }
    },

    mounted () {
    },

    watch : {
    }
}
</script>