<template>
    <v-dialog
        v-model="dialog"
        persistent
        max-width="800px"
        transition="dialog-transition"
        content-class="zalfa-dialog-print"
        scrollable
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pb-2 pt-2">
                <h3 class="ml-2">
                    DETAIL PENERIMAAN
                </h3>
                <v-spacer></v-spacer>
                <v-btn color="red" dark @click="dialog=!dialog" class="ma-0" small style="min-width:0px">
                    <v-icon>clear</v-icon>
                </v-btn>
            </v-card-title>

            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs6 v-for="(d, n) in details" :key="n">
                        <v-list>
                            <v-list-tile
                            @click=""
                            class="mb-2 xs6"
                            >

                                <v-list-tile-content>
                                    <v-list-tile-sub-title v-html="n"></v-list-tile-sub-title>
                                    <v-list-tile-title v-html="d"></v-list-tile-title>
                                    
                                </v-list-tile-content>

                                </v-list-tile>
                            <!-- </template> -->
                        </v-list>
                    </v-flex>
                </v-layout>

                <v-layout row wrap>
                    <v-flex v-for="(itm, n) in prices" :class="[itm.width, itm.align?'text-xs-'+itm.align:null]" class="pa-2 cyan white--text">{{ itm.header }}</v-flex>
                </v-layout>
                <v-layout row wrap>
                    <v-flex v-for="(itm, n) in prices" :class="[itm.width, itm.align?'text-xs-'+itm.align:null]" class="pa-2">{{ itm.content }}</v-flex>
                </v-layout>
                <v-divider></v-divider>

            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="red" dark @click="dialog=!dialog">Tutup</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<style>
.zalfa-dialog-print {
    /* margin: 12px !important;
    max-height: 95% !important; */
}
</style>

<script>
let t = "?t=" + Math.round(Math.random() * 1e10)
module.exports = {
    props : ['data', 'report_url'],
    components: {
    },

    data () {
        return {

        }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.purchase004.dialog_detail },
            set (v) { 
                this.$store.commit('purchase004/set_object', ['dialog_detail', v])
            }
        },

        selected_item () {
            if (this.$store.state.purchase004.selected_item)
                return this.$store.state.purchase004.selected_item
            return {}
        },

        details () {
            return {
                'NAMA ITEM' : this.selected_item.item_name,
                'KODE ITEM' : this.selected_item.item_code,
                'TANGGAL / NOMOR PENERIMAAN' : this.selected_item.receive_date + ' / ' + this.selected_item.receive_number,
                'SALES' : this.selected_item.sales_name,
                'VENDOR ASAL' : this.$store.state.purchase004.selected_bill.vendor_name,
                'GUDANG TUJUAN' : this.selected_item.warehouse_name
            }
        },

        prices () { 
            return [
                { header:'QTY', 
                    content:this.oneMoney(this.selected_item.detail_qty) + ' ' + this.selected_item.unit_name, width:'xs2', align:'right' },
                { header:'HARGA', 
                    content:'Rp ' + this.oneMoney(this.selected_item.detail_price), width:'xs2', align:'right' },
                { header:'POTONGAN', 
                    content:'Rp ' + this.oneMoney(this.discTotal), width:'xs2', align:'right' },
                { header:'PPN', 
                    content:'Rp ' + this.oneMoney(this.selected_item.detail_ppnamount / this.selected_item.detail_qty), width:'xs2', align:'right' },
                { header:'TOTAL', 
                    content:'Rp ' + this.oneMoney(this.selected_item.detail_total), width:'xs4', align:'right' }
            ] 
        },

        discTotal () {
            if (!this.selected_item) return 0
            let si = this.selected_item
            return parseFloat(si.detail_disc * si.detail_price / 100) + parseFloat(si.detail_discrp)
        }
    },

    methods : {
        oneMoney (x) {
            return window.one_money(x)
        }
    },

    mounted () {
    }
}
</script>