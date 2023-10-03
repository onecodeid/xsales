<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="1000px"
        transition="dialog-transition"
        >
            <v-card>
                <v-card-title primary-title class="cyan white--text pt-3">
                    <h3>
                        <v-icon class="mr-2" dark small>event</v-icon><span> KALENDER PENGIRIMAN</span>
                    </h3>
                    <v-spacer></v-spacer>
                    <span class="py-1 px-2 mr-1">Legend : </span>
                    <span class="grey white--text py-1 px-2 mr-1">SALES ORDER</span>
                    <span class="orange white--text py-1 px-2 mr-1">SEBAGIAN</span>
                    <span class="green white--text py-1 px-2">SELESAI</span>
                </v-card-title>
                <v-card-text>
                    <v-layout row wrap>
                        <v-flex xs12>
                            <v-sheet height="500">
                                <v-calendar
                                ref="calendar"
                                :now="today"
                                v-model="today"
                                color="primary"
                                @change="cal_change($event)"
                                locale="id-ID"
                                :short-weekdays="false"
                                >
                                <template v-slot:day="{ present, past, date }">
                                    <v-layout
                                    >
                                        <v-layout v-if="items[date]" row wrap>
                                            <v-flex xs12 v-for="(item, n) in items[date]" :key="n" class="caption" :class="colors[item.sales_done]" 
                                                pa-1 mb-1
                                                @click="view_order(item)">
                                                {{item.customer_name}}
                                            </v-flex>
                                        </v-layout>
                                    </v-layout>
                                </template>
                                </v-calendar>
                            </v-sheet>
                        </v-flex>
                    </v-layout>
                </v-card-text>

                <v-card-actions class="primary lighten-3">
                    <v-btn @click="$refs.calendar.prev()">
                        <v-icon dark left>
                            keyboard_arrow_left
                        </v-icon>
                        Prev
                    </v-btn>

                    <v-btn @click="$refs.calendar.next()">
                        Next
                        <v-icon right dark>
                            keyboard_arrow_right
                        </v-icon>
                    </v-btn>

                    <h3 class="white--text ml-4 display-1">BULAN <span id="month_name">{{month_name}}</span></h3>

                    <v-spacer></v-spacer>
                    <v-btn color="primary" @click="dialog=!dialog">Tutup</v-btn>
                </v-card-actions>
            </v-card>

            <sales-order-new @deliver="deliver"></sales-order-new>
    </v-dialog>

    
</template>

<script>
module.exports = {
    components: {
        "sales-order-new" : httpVueLoader("../../sales-order/components/sales-order-new.vue?t=<?=date('YmdHis');?>")
    },
    data: () => ({
      colors: {'Y':'green white--text', 'N':'grey white--text', 'X':'orange white--text'},
      category: ['Development', 'Meetings', 'Slacking']
    }),

    computed : {
        dialog : {
            get () { return this.$store.state.delivery.dialog_calendar },
            set (v) { this.$store.commit('delivery/set_common', ['dialog_calendar', v]) }
        },

        today : {
            get () { return this.$store.state.delivery_calendar.cal_date },
            set (v) { this.$store.commit('delivery_calendar/set_common', ['cal_date', v])}
        },

        items () {
            return this.$store.state.delivery_calendar.cal_items
        },

        month_name () {
            return moment(this.today).locale("id").format("MMMM YYYY").toUpperCase()
        }
    },

    methods : {
        cal_change (v) {
            this.$store.dispatch('delivery_calendar/search')
        },

        view_order (x) {
            let sc = x
            this.$store.commit('sales/set_selected_sales', sc)
            this.$store.commit('delivery_new/set_selected_proforma', sc)
            
            this.$store.commit('sales_new/set_common', ['edit', true])
            this.$store.commit('sales_new/set_common', ['sales_id', sc.sales_id])
            this.$store.commit('sales_new/set_common', ['sales_date', sc.sales_date])
            this.$store.commit('sales_new/set_common', ['sales_note', sc.sales_note])
            this.$store.commit('sales_new/set_common', ['sales_memo', sc.sales_memo])
            this.$store.commit('sales_new/set_common', ['sales_number', sc.sales_number])
            this.$store.commit('sales_new/set_common', ['sales_ref', sc.sales_ref])
            this.$store.commit('sales_new/set_common', ['sales_shipping', sc.sales_shipping])
            this.$store.commit('sales_new/set_common', ['sales_proforma', sc.sales_proforma])

            this.$store.commit('sales_new/set_common', ['delivery', false])
            if (x.sales_done == 'N')
                this.$store.commit('sales_new/set_common', ['delivery', true])

            this.$store.commit('sales_new/set_selected_customer', null)
            for (let v of this.$store.state.sales_new.customers)
                if (v.customer_id == sc.customer_id)
                    this.$store.commit('sales_new/set_selected_customer', v)

            this.$store.commit('sales_new/set_selected_staff', null)
            for (let v of this.$store.state.sales_new.staffs)
                if (sc.sales_staff == v.staff_id)
                    this.$store.commit('sales_new/set_selected_staff', v)

            this.$store.commit('sales_new/set_selected_offer', 
                {sales_id:x.offer_id,sales_number:x.offer_number,sales_date:x.offer_date})
            this.$store.commit('sales_new/set_common', ['edits', x.offer_id])
            this.$store.dispatch('sales_new/search_offer')

            this.$store.commit('sales_new/set_selected_paymentplan', null)
            for (let v of this.$store.state.sales_new.paymentplans)
                if (sc.payment_id == v.paymentplan_id)
                    this.$store.commit('sales_new/set_selected_paymentplan', v)

            this.$store.commit('sales_new/set_selected_term', null)
            for (let v of this.$store.state.sales_new.terms)
                if (sc.term_id == v.term_id)
                    this.$store.commit('sales_new/set_selected_term', v)
            
            this.$store.commit('sales_new/set_selected_expedition', null)
            for (let v of this.$store.state.sales_new.expeditions)
                if (sc.expedition_id == v.expedition_id)
                    this.$store.commit('sales_new/set_selected_expedition', v)
            this.$store.commit('sales_new/set_common', ['expedition_mode', 'E'])
            this.$store.commit('sales_new/set_common', ['expedition_name', ''])

            this.$store.commit('sales_new/set_selected_address', null)
            this.$store.dispatch('sales_new/search_address')

            let details = sc.details
            
            this.$store.commit('sales_new/set_details', details)
            this.$store.commit('sales_new/set_common', ['dialog_new', true])
            this.$store.commit('sales_new/set_common', ['view', true])
        },

        deliver () {
            let sales = this.$store.state.sales_new


            this.$store.commit('sales_new/set_common', ['dialog_new', false])
            this.$store.commit('delivery_new/set_common', ['dialog_new', true])

            this.$store.commit('delivery_new/set_common', ['edit', false])
            this.$store.commit('delivery_new/set_common', ['single', true])
            this.$store.commit('delivery_new/set_common', ['delivery_id', 0])
            this.$store.commit('delivery_new/set_common', ['delivery_date', this.$store.state.delivery_new.current_date])
            this.$store.commit('delivery_new/set_common', ['delivery_note', ''])
            this.$store.commit('delivery_new/set_common', ['delivery_send_note', ''])
            this.$store.commit('delivery_new/set_common', ['delivery_memo', ''])
            this.$store.commit('delivery_new/set_common', ['delivery_number', ''])
            this.$store.commit('delivery_new/set_common', ['delivery_ref_number', ''])
            this.$store.commit('delivery_new/set_common', ['delivery_proforma', sales.sales_proforma])
            this.$store.commit('delivery_new/set_object', ['invoices', []])

            // this.$store.commit('delivery_new/set_tmp', {selected_customer:sales.selected_customer})
            this.$store.commit('delivery_new/set_selected_customer', sales.selected_customer)
            this.$store.commit('delivery_new/set_addresses', sales.addresses)
            this.$store.commit('delivery_new/set_selected_address', sales.selected_address)
            this.$store.commit('delivery_new/set_selected_expedition', sales.selected_expedition)

            let details = []
            for (let detail of sales.details) {
                details.push(
                    {
                        "item":{
                            "detail_done":"N",
                            "detail_id":0,
                            "detail_qty":detail.qty,
                            "detail_sent":0,
                            "detail_unsent":detail.qty,
                            "item_id":detail.item.item_id,
                            "item_name":detail.item.item_name,
                            "item_unit":"-",
                            "stock":0,
                            "sales_date":sales.sales_date,
                            "sales_number":sales.sales_number,
                            "sales_memo":sales.sales_memo,
                            "customer_name":sales.selected_customer.customer_name
                        },
                        "note":"",
                        "qty":detail.qty,
                        "total":0
                    }
                )
            }

            this.$store.commit('delivery_new/set_details', details)
            this.$store.commit('delivery/set_common', ['dialog_calendar', false])
            // this.$store.commit('delivery_new/set_selected_warehouse', null)
            // this.$store.commit('delivery_new/set_selected_staff', null)
            // for (let v of this.$store.state.delivery_new.customers)
            //     if (v.customer_id == sc.customer_id)
            //         this.$store.commit('delivery_new/set_selected_customer', v)
            // for (let v of this.$store.state.delivery_new.staffs)
            //     if (v.staff_id == sc.delivery_staff)
            //         this.$store.commit('delivery_new/set_selected_staff', v)
            // for (let v of this.$store.state.delivery_new.warehouses)
            //     if (v.warehouse_id == sc.warehouse_id)
            //         this.$store.commit('delivery_new/set_selected_warehouse', v)
            // for (let v of this.$store.state.delivery_new.deliverytypes)
            //     if (v.deliverytype_id == sc.delivery_type)
            //         this.$store.commit('delivery_new/set_selected_deliverytype', v)
        }
    },

    mounted () {
        this.$store.dispatch('delivery_calendar/search', {})
    }
}
</script>