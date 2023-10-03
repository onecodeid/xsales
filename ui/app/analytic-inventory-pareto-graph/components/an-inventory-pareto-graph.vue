<template>
    <v-card>
        <v-card-text primary-title class="pt-2 pb-0">
            <v-layout row wrap>
                <v-flex xs12>
                    <v-layout row wrap>
                        <v-flex xs7>
                            <h3 class="display-1 font-weight-light zalfa-text-title">PRODUK PARETO PER GUDANG</h3> 
                            <v-layout row wrap class="legends caption text-xs-center pb-1">
                                <!-- <v-flex xs2 :class="status_class('A')" py-1 px-2>FAST</v-flex>
                                <v-flex xs2 :class="status_class('B')" py-1 px-2>MEDIUM</v-flex>
                                <v-flex xs2 :class="status_class('C')" py-1 px-2>SLOW</v-flex>
                                <v-flex xs2 :class="status_class('D')" py-1 px-2>HIGH SLOW</v-flex> -->
                            </v-layout>       
                        </v-flex>
                        <v-flex xs3>
                            <v-layout row wrap>
                                <v-flex xs6 pl-5 pr-1>
                                    <common-datepicker
                                        label="Tanggal Awal"
                                        :date="sdate"
                                        data="0"
                                        @change="change_sdate"
                                        hints=" "
                                        :details="true"
                                        :solo="false"
                                    ></common-datepicker>
                                </v-flex>
                                <v-flex xs6 pl-1 pr-5>
                                    <common-datepicker
                                        label="Tanggal Akhir"
                                        :date="edate"
                                        data="0"
                                        @change="change_edate"
                                        
                                        hints=" "
                                        :details="true"
                                        :solo="false"
                                    ></common-datepicker>
                                </v-flex>
                            </v-layout>
                        </v-flex>
                        <v-flex xs2>
                            <v-select
                                :items="warehouses"
                                v-model="selected_warehouse"
                                label="Gudang"
                                return-object
                                item-text="warehouse_name"
                                item-value="warehouse_id"
                                hint=" "
                                persistent-hint
                                clearable
                                placeholder="Semua Gudang"
                            ></v-select>
                        </v-flex>
                    </v-layout>
                    
                </v-flex>

                <v-flex xs12>
                    <v-layout row wrap>
                        <v-layout row wrap>
                                <v-flex xs12 v-for="(i, n) in items" :key="n">
                                    <v-layout row wrap>
                                        <v-flex xs6 class="text-xs-right pr-3 align-center d-flex justify-end">
                                            <div>{{ i.item_name }} (<span class="font-weight-bold orange--text px-1">{{ one_money(i.item_qty) }} {{ i.unit_name }}</span>)</div>
                                        </v-flex>
                                        <v-flex xs5>
                                            <v-progress-linear :value="i.item_percent" :color="'green '+color(i.item_percent)"></v-progress-linear>        
                                        </v-flex>
                                    </v-layout>
                                    
                                </v-flex>        
                            </v-layout>
                        
                    </v-layout>
                </v-flex>
            </v-layout>

            
        </v-card-text>
        <!-- <v-card-text class="pt-2">
            
        </v-card-text> -->
        
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

.v-select__selection, .legends > div {
    width: 100%;
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: pre;
}
</style>

<script>
module.exports = {
    components : {
        "common-dialog-delete" : httpVueLoader("../../common/components/common-dialog-delete.vue"),
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        "common-dialog-print" : httpVueLoader("../../common/components/common-dialog-print-size.vue")
    },

    data () {
        return {
            curr_page: 1,
            xtotal_page: 1,
            report_url: ''
        }
    },

    computed : {
        items () {
            return this.$store.state.inventory.items
        },

        item_id () {
            return this.$store.state.inventory.selected_inventory.I_AdjustID
        },

        edate : {
            get () { return this.$store.state.inventory.edate },
            set (v) { this.$store.commit('inventory/set_edate', v) }
        },

        sdate : {
            get () { return this.$store.state.inventory.sdate },
            set (v) { this.$store.commit('inventory/set_sdate', v) }
        },

        query : {
            get () { return this.$store.state.inventory.query },
            set (v) { this.$store.commit('inventory/set_common', ['query', v]) }
        },

        warehouses () {
            return this.$store.state.inventory.warehouses
        },

        selected_warehouse : {
            get () { return this.$store.state.inventory.selected_warehouse },
            set (v) { 
                this.$store.commit('inventory/set_selected_warehouse', v)
                this.search()
            }
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        add () {
            return false
        },

        edit (x) {
            return x
        },

        del (x) {
            this.select(x)
            this.$store.commit('set_dialog_delete', true)
        },

        confirm_del (x) {
            this.$store.dispatch('inventory/del', {id:x.data})
        },

        select (x) {
            this.$store.commit('inventory/set_selected_item', x)
        },

        reverse_date(x) {
            return ""
        },

        change_edate(x) {
            this.edate = x.new_date
            this.search()
        },

        change_sdate(x) {
            this.sdate = x.new_date
            this.search()
        },

        search () {
            this.$store.dispatch('inventory/search', {})
        },

        color (x) {
            if (x > 90) return 'darken-4'
            if (x > 80) return 'darken-3'
            if (x > 60) return 'darken-2'
            if (x > 50) return 'darken-1'
            if (x > 40) return ''
            if (x > 30) return 'lighten-1'
            if (x > 20) return 'lighten-2'
            return 'lighten-3'
        }
    },

    mounted () {
        // this.$store.dispatch('item_new/search_warehouse')
    }
}
</script>