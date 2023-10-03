<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0">
            <v-layout row wrap>
                <v-flex xs6>
                    <h3 class="display-1 font-weight-light zalfa-text-title">IMPORT SALES :: CUSTOMER</h3>
                </v-flex>
                <v-flex xs3>
                    <v-checkbox label="Tampilkan yang belum di mapping" v-model="unmap" value="N" true-value="Y" false-value="N" hide-details></v-checkbox>
                </v-flex>
                <v-flex xs3 class="text-xs-right">
                    <v-text-field
                        solo
                        hide-details
                        placeholder="Pencarian" v-model="query"
                        @change="search"
                    >
                        <template v-slot:append-outer>
                            <v-btn color="primary" class="ma-0 btn-icon" @click="search">
                                <v-icon>search</v-icon>
                            </v-btn>      
                        </template>
                    </v-text-field>
                </v-flex>
            </v-layout>
        </v-card-title>
        <v-card-text class="pt-2">
            <v-data-table 
                :headers="headers"
                :items="customers"
                :loading="false"
                hide-actions
                class="elevation-1">
                <template slot="items" slot-scope="props">
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.a }}</td>
                    <td class="text-xs-left pa-2 border-left" @click="select(props.item)">{{ props.item.b }}</td>
                    <td class="text-xs-left pa-2 border-left" @click="select(props.item)">
                        <v-autocomplete
                            :items="xcustomers"
                            return-object
                            item-text="customer_name"
                            item-value="customer_id"
                            label="Customer"
                            :loading="xcustomers.length<1"
                            v-if="props.item.c==0"
                            hide-details
                            clearable
                            @change="setCustomer(props.item, $event)"
                        >
                            <template slot="selection" slot-scope="data">
                                <span class="blue--text">{{data.item.customer_code}} | {{data.item.customer_name}}</span>
                            </template>
                            <template slot="item" slot-scope="data">
                                <v-layout row wrap>
                                    <v-flex xs12>
                                        <div class="blue--text">{{data.item.customer_name}}</div>
                                    </v-flex>
                                    <v-flex xs12>
                                        <div class="grey--text caption">{{data.item.customer_code}} | {{data.item.city_name}}</div>
                                    </v-flex>
                                </v-layout>
                            </template>
                        </v-autocomplete>
                    </td>
                    <td class="text-xs-center pa-0 border-left" @click="select(props.item)">
                        <v-btn color="primary" class="btn-icon ma-0" small @click="save(props.item)" v-show="props.item.c==0"><v-icon>save</v-icon></v-btn>
                    </td>
                </template>
            </v-data-table>
            <v-divider></v-divider>
            <v-pagination
                style="margin-top:10px;margin-bottom:10px"
                v-model="curr_page"
                :length="xtotal_page"
                @input="change_page"
            ></v-pagination>
        </v-card-text>
        

    </v-card>
</template>

<style scoped>
.v-text-field.v-text-field--solo .v-input__control {
    min-height: 36px;
}
.v-text-field.v-text-field--solo .v-input__append-outer {
    margin-top: 0px;
    margin-left: 0px;
}

.border-left {
    border-left: 1px solid rgba(0,0,0,.12);
}

.border-right {
    border-left: 1px solid rgba(0,0,0,.12);
}

td:not(:first-child) {
    border-left: 1px solid rgba(0,0,0,.12);
}
</style>

<script>
module.exports = {
    components : {
    },

    data () {
        return {
            headers: [
                {
                    text: "JURNAL",
                    align: "left",
                    sortable: false,
                    width: "30%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "ERP",
                    align: "left",
                    sortable: false,
                    width: "30%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "ERP",
                    align: "left",
                    sortable: false,
                    width: "30%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "ACTION",
                    align: "left",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                }
            ]
        }
    },

    computed : {
        customers () {
            return this.$store.state.salesCustomer.customers
        },

        dialog_delete () {
            return this.$store.state.dialog_delete
        },

        customer_id () {
            return this.$store.state.salesCustomer.selected_customer.customer_id
        },

        query : {
            get () { return this.$store.state.salesCustomer.search },
            set (v) { this.$store.commit('salesCustomer/update_search', v) }
        },

        curr_page : {
            get () { return this.$store.state.salesCustomer.current_page },
            set (v) { this.$store.commit('salesCustomer/update_current_page', v) }
        },

        xtotal_page () {
            return this.$store.state.salesCustomer.total_customer_page
        },

        dialog_report : {
            get () { return this.$store.state.dialog_print },
            set (v) { this.$store.commit('set_dialog_print', v) }
        },

        report_url () {
            return this.$store.state.report_url
        },

        xcustomers () {
            return this.$store.state.salesCustomer.xcustomers
        },

        selected_xcustomer : {
            get () { return this.$store.state.salesCustomer.selected_xcustomer },
            set (v) { 
                this.$store.commit('salesCustomer/set_object', ['selected_xcustomer',v])
            }
        },

        unmap : {
            get () { return this.$store.state.salesCustomer.unmap },
            set (v) { 
                this.$store.commit('salesCustomer/set_object', ['unmap', v]) 
                this.search()
            }
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        save(x) {
            this.$store.dispatch('salesCustomer/save')
        },

        del (x) {
            this.select(x)
            this.$store.commit('set_dialog_delete', true)
        },

        confirm_del (x) {
            this.$store.dispatch('salesCustomer/del')
        },

        select (x) {
            this.$store.commit('salesCustomer/set_selected_customer', x)
        },

        search () {
            return this.$store.dispatch('salesCustomer/search')
        },

        change_page(x) {
            this.curr_page = x
            this.$store.dispatch('salesCustomer/search')
        },

        setCustomer(a, b) {
            this.select(a)
            this.$store.commit('salesCustomer/set_object', ['selected_xcustomer', b])
            // this.$store.dispatch('salesCustomer/save')
        }
    }
}
</script>