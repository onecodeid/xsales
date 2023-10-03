<template>
    <v-card class="text-xs-center">
        <v-card-title primary-title class="py-2 px-3 teal lighten-1 white--text title justify-center" title="sales-customer-filter">
            FILTER
        </v-card-title>
        <v-card-text class="py-2 px-2 teal lighten-5">
            <v-layout row wrap>
                <v-flex xs12 lg-4 offset-lg8>
                    <v-select
                        :items="endyears"
                        item-text="label"
                        item-value="edate"
                        return-object
                        v-model="selected_endyear"
                        label="Periode"
                        
                    >
                    </v-select>

                    <v-layout row wrap>
                        <v-flex xs6>
                            <v-select
                                :items="staffs"
                                v-model="selected_staff"
                                return-object
                                item-text="staff_name"
                                item-value="staff_id"
                                label="Sales"
                                clearable
                            >
                                <template slot="selection" slot-scope="data">
                                    <span class="body-1">{{ data.item.staff_name }}</span>
                                </template>
                            </v-select>        
                        </v-flex>
                        <v-flex xs6 class="pl-2">
                            <v-btn color="success" class="mb-0 mt-3 mx-0" block @click="search">TERAPKAN</v-btn>
                        </v-flex>
                    </v-layout>
                </v-flex>
            </v-layout>
            
            
        </v-card-text>
    </v-card>        
</template>

<script>
module.exports = {
    computed : {
        data () {
            return this.$store.state.dashboardSales.customer001
        },

        endyears () {
            let x = JSON.parse(JSON.stringify(this.$store.state.dashboardSales.months2))
            // x.unshift({header:"PER PERIODE"})
            // x.unshift({sdate:"1971-01-01",edate:"2023-12-12",label:"PER TANGGAL"})
            return x
        },

        selected_endyear : {
            get () { return this.$store.state.dashboardSales.selected_month2 },
            set (v) { 
                this.$store.commit('dashboardSales/set_object', ['selected_month2', v])
            }
        },

        staffs () {
            return this.$store.state.dashboardSales.staffs
        },

        selected_staff : {
            get () { return this.$store.state.dashboardSales.selected_staff },
            set (v) { 
                this.$store.commit('dashboardSales/set_object', ['selected_staff', v])
            }
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        search () {
            this.$store.dispatch("dashboardSales/searchSalesCustomer001").then((x) => {
                this.$store.dispatch("dashboardSales/searchSales002")
            })
        }
    },

    mounted () {
        // this.$store.dispatch("dashboardSales/search_months").then((x) => {
        //     this.$store.dispatch("dashboardSales/search_staff")
        // })
    }
}
</script>