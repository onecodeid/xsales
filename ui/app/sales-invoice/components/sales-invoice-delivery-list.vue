<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="800px"
        transition="dialog-transition"
        content-class="dialog-delivery"
        
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <h3>
                    PENDING DELIVERY ORDER (DO)
                </h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs6>
                        <v-pagination
                            style="margin-top:10px;margin-bottom:10px"
                            v-model="curr_page"
                            :length="xtotal_page"
                            @input="change_page"
                        ></v-pagination>
                    </v-flex>
                    <v-flex xs6 pt-2>
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
                    <v-flex xs12>
                        <v-data-table 
                            :headers="headers"
                            :items="deliverys"
                            :loading="false"
                            hide-actions
                            class="elevation-1">
                            <template slot="items" slot-scope="props">
                                <tr :class="{'cyan lighten-5':is_selected(props.item)}">
                                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.delivery_date }}</td>
                                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.delivery_number }}</td>
                                    <td class="text-xs-left pa-2" @click="select(props.item)">
                                        {{ props.item.customer_name }}
                                    </td>
                                    <td class="text-xs-right pa-2" @click="select(props.item)">
                                        <b>{{ one_money(props.item.sales_grandtotal) }}</b>
                                    </td>
                                </tr>
                                <tr :class="{'cyan lighten-5':is_selected(props.item)}" v-show="is_selected(props.item)">
                                    <td colspan="4" class="text-xs-left pa-2" @click="select(props.item)">
                                        <v-layout row wrap>
                                            <v-flex xs6 v-for="(d, n) in props.item.items" :key="d.item.item_id+n" class="pa-1">
                                                <v-layout row wrap>
                                                    <v-flex xs9 class="cyan lighten-4 py-2 px-2">
                                                        {{d.item.item_name}}        
                                                    </v-flex>
                                                    <v-flex xs3 class="cyan lighten-1 white--text font-weight-bold py-2 px-2 text-xs-right">
                                                        {{one_money(d.qty)}} {{d.item.item_unit}}
                                                    </v-flex>
                                                </v-layout>
                                            </v-flex>
                                        </v-layout>
                                    </td>
                                </tr>
                            </template>
                        </v-data-table>   
                        <v-divider></v-divider> 
                        
                    </v-flex>
                </v-layout>
            </v-card-text>

            <v-card-actions>
                <v-btn color="primary" @click="dialog=!dialog" flat>Tutup</v-btn>
                <v-spacer></v-spacer>
                <v-btn color="primary" @click="choose" :disabled="!selected_delivery||selected_delivery=={}" :dark="!!selected_delivery">Pilih</v-btn>
            </v-card-actions>

        </v-card>
    </v-dialog>
</template>

<style>
.dialog-delivery .v-input__append-outer {
    margin-top: 0px !important;
    margin-left: 0px !important;
}

.dialog-delivery .v-text-field.v-text-field--solo .v-input__control {
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
            headers: [
                {
                    text: "TANGGAL DO",
                    align: "left",
                    sortable: false,
                    width: "15%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "NOMOR DO",
                    align: "left",
                    sortable: false,
                    width: "25%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "CUSTOMER",
                    align: "left",
                    sortable: false,
                    width: "45%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "TOTAL",
                    align: "right",
                    sortable: false,
                    width: "15%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                }
            ]
        }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.invoice_new.dialog_delivery },
            set (v) { this.$store.commit('invoice_new/set_common', ['dialog_delivery', v]) }
        },

        deliverys () {
            return this.$store.state.delivery.deliverys
        },

        selected_delivery : {
            get () { return this.$store.state.delivery.selected_delivery },
            set (v) { this.$store.commit('delivery/set_selected_delivery', v) }
        },

        curr_page : {
            get () { return this.$store.state.delivery.current_page },
            set (v) { this.$store.commit('delivery/update_current_page', v) }
        },

        xtotal_page () {
            return this.$store.state.delivery.total_delivery_page
        },

        query : {
            get () { return this.$store.state.delivery.search },
            set (v) { this.$store.commit('delivery/update_search', v) }
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        select (x) {
            this.$store.commit('delivery/set_selected_delivery', x)
        },

        is_selected (x) {
            if (!this.selected_delivery)
                return false

            if (this.selected_delivery.delivery_id == x.delivery_id)
                return true

            return false
        },

        choose () {
            this.$store.commit('invoice_new/set_common', ['dialog_new', true])
            this.$store.commit('invoice_new/set_common', ['dialog_delivery', false])

            this.$store.commit('invoice_new/set_common', ['invoice_shipping', this.selected_delivery.sales_shipping])
            this.$store.commit('invoice_new/set_common', ['invoice_discrp', this.selected_delivery.sales_discrp])
            this.$store.commit('invoice_new/set_common', ['invoice_disc', this.selected_delivery.sales_disc])
            this.$store.commit('invoice_new/set_common', ['sales_name', this.selected_delivery.sales_name])

            let current_date = this.$store.state.invoice_new.current_date
            this.$store.commit('invoice_new/set_common', ['invoice_date', current_date])
            // this.$store.commit('invoice_new/set_common', ['invoice_due_date', 
            //     moment(current_date, "YYYY-MM-DD").add(Math.round(sc.invoice_term), 'days').format('DD-MM-YYYY')])
            this.$store.commit('invoice_new/set_common', ['invoice_disctype', 
                parseFloat(this.selected_delivery.sales_discrp)==0?'P':'R'])
            for (let v of this.$store.state.invoice_new.customers)
                if (v.customer_id == this.selected_delivery.customer_id) {
                    this.$store.commit('invoice_new/set_selected_customer', v)
                    this.$store.dispatch('invoice_new/search_item')
                    this.$store.dispatch('invoice_new/search_dp')
                }
                    
            // address
            let scd = this.selected_delivery.delivery_address
            let phones = []
            for (let p of scd.phones)
                phones.push(window.phone_format(p.no))
            
            let address = scd.address_desc + "<br />" +
                (scd.village_name!=''?scd.village_name+', ':'') +
                (scd.district_name!=''?scd.district_name+', ':'') +
                (scd.city_name!=''?scd.city_name+' - ':'') +
                (scd.province_name!=''?scd.province_name:'') + "<br />Phone : " + phones.join(", ")
            this.$store.commit('invoice_new/set_common', ['invoice_address', address])

            return
        },

        change_page(x) {
            this.curr_page = x
            this.$store.dispatch('delivery/search', {})
        },

        search () {
            return this.$store.dispatch('delivery/search', {done:'N'})
        }
    },

    mounted () {
        this.$store.dispatch('delivery/search', {})
        this.$store.commit('delivery/set_common', ['s_date', '2022-01-01'])
        // this.$store.commit('invoice_new/set_selected_customer', null)
        // this.$store.dispatch('invoice_new/search_item')
    }
}
</script>