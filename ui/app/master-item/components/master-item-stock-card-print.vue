<template>
    <v-dialog
        v-model="dialog"
        scrollable
        persistent :overlay="false"
        max-width="400px"
        transition="dialog-transition"
    >
        <v-card>
            <v-card-title primary-title class="blue white--text pt-2 pb-2">
                <h3 class="headline">Cetak Mutasi Stok</h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                        <v-text-field
                            label="Nama Item"
                            readonly
                            :value="item_name"
                        ></v-text-field>    
                    </v-flex>

                    <v-flex xs12>
                        <v-text-field
                            label="Gudang"
                            readonly
                            :value="warehouse_name"
                        ></v-text-field>    
                    </v-flex>

                    <v-flex xs12>
                        <common-datepicker
                            label="Dari Tanggal"
                            :date="sdate"
                            data="0"
                            @change="change_sdate"
                            classs="mt-0"
                            hints=" "
                            :details="true"
                            :solo="false"
                        ></common-datepicker>
                    </v-flex>

                    <v-flex xs12>
                        <common-datepicker
                            label="Sampai Tanggal"
                            :date="edate"
                            data="0"
                            @change="change_edate"
                            classs="mt-0"
                            hints=" "
                            :details="true"
                            :solo="false"
                        ></common-datepicker>
                    </v-flex>
                </v-layout>
            </v-card-text>

            <v-card-actions>
                <v-btn color="primary" outline dark @click="dialog=!dialog">Tutup</v-btn>
                <v-spacer></v-spacer>
                <v-btn color="primary" dark @click="print_me">Cetak</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
module.exports = {
    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue')
    },

    computed : {
        dialog: {
            get () { return this.$store.state.item.dialog_stock_card },
            set (v) { this.$store.commit('item/set_common', ['dialog_stock_card', v]) }
        },

        edate : {
            get () { return this.$store.state.item.edate },
            set (v) { this.$store.commit('item/set_common', ['edate', v]) }
        },

        sdate : {
            get () { return this.$store.state.item.sdate },
            set (v) { this.$store.commit('item/set_common', ['sdate', v]) }
        },

        item_name () {
            return this.$store.state.item.selected_item.item_name
        },

        warehouse_name () {
            if (!!this.$store.state.item_new.selected_stock)
                return this.$store.state.item_new.selected_stock.warehouse_name
            return ""
        }
    },

    methods : {
        change_sdate (x) {
            this.sdate = x.new_date
        },

        change_edate (x) {
            this.edate = x.new_date
        },

        print_me () {
            let r = `${this.$store.state.item.URL}report/one_iv_002?itemid=\
                        ${this.$store.state.item.selected_item.item_id}&sdate=\
                        ${this.sdate}&edate=${this.edate}&warehouseid=\
                        ${this.$store.state.item_new.selected_stock.warehouse_id}`
            this.$store.commit('set_report_url', r)
            this.$store.commit('set_dialog_print', true)
            this.$store.commit('item/set_common', ['dialog_stock_card', false])
        }
    }
}
</script>