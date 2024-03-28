<template>
    <v-card class="fill-height">
        <v-card-text>
            <v-layout row wrap>
                <v-flex xs12>
                    <common-datepicker
                        label="Dari Tanggal"
                        :date="sdate"
                        data="0"
                        @change="change_sdate"
                        classs="mb-2"
                        hints=" "
                        :details="false"
                        :solo="false"
                    ></common-datepicker>
                    <common-datepicker
                        label="Sampai Tanggal"
                        :date="edate"
                        data="0"
                        @change="change_edate"
                        classs=""
                        hints=" "
                        :details="false"
                        :solo="false"
                    ></common-datepicker>
                </v-flex>
            </v-layout>  
        </v-card-text>
    </v-card>
</template>
<script>
module.exports = {
    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        SheetFooter: {
            functional: true,

            render (h, { children }) {
                return h('v-sheet', {
                    staticClass: 'mt-auto align-center justify-center d-flex',
                    props: {
                    color: 'rgba(0, 0, 0, .36)',
                    dark: true,
                    height: 50
                    }
                }, children)
            }
        },

        SheetHeader: {
            functional: true,

            render (h, { children }) {
                return h('v-sheet', {
                    staticClass: 'mt-auto align-center justify-center d-flex',
                    props: {
                    color: 'rgba(0, 0, 0, .36)',
                    dark: true,
                    height: 50
                    }
                }, children)
            }
        }
    },

    methods : {
        __c (x, y) {
            return this.$store.commit("dashboardWarehouse/set_object", [x, y])
        },

        one_money (x) {
            return window.one_money(x)
        },

        change_sdate(x) {
            this.__c("sdate", x.new_date), this.search()
        },

        change_edate(x) {
            this.__c("edate", x.new_date), this.search()
        },

        search () {
            this.$store.dispatch("dashboardWarehouse/searchWarehouse001").then((x) => {
                this.$store.dispatch("dashboardWarehouse/searchWarehouse002").then((y) => { 
                    this.$store.dispatch("dashboardWarehouse/searchWarehouse003").then((z) => { })
                 })
            })
        }
    },

    computed : {
        __s () {
            return this.$store.state.dashboardWarehouse
        },

        sdate () {
            return this.__s.sdate
        },

        edate () {
            return this.__s.edate
        }
    }
}