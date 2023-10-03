<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="500px"
        transition="dialog-transition"
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <h3>
                    <span>KONFIRMASI PENERIMAAN BARANG KE GUDANG</span>
                </h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                        <v-text-field
                            label="Nama Vendor"
                            :value="vendor_name"
                            readonly
                        ></v-text-field>
                    </v-flex>

                    <v-flex xs12>
                        <v-text-field
                            label="Gudang Tujuan"
                            :value="warehouse_name"
                            readonly
                        ></v-text-field>
                    </v-flex>
                </v-layout>

                <v-divider class="mt-2"></v-divider>
                <v-layout row wrap class="white--text font-weight-bold">
                    <v-flex xs1 class="green lighten-2 pa-2">
                        NO
                    </v-flex>
                    <v-flex xs9 class="green lighten-2 pa-2 white--text">
                        NAMA ITEM
                    </v-flex>
                    <v-flex xs2 class="text-xs-right green lighten-2 pa-2 white--text">
                        QTY
                    </v-flex>
                </v-layout>
                <v-divider class="mb-2"></v-divider> 
                <v-layout row wrap v-for="(item, n) in items" :key="n">
                    
                    
                    
                    <v-flex xs1 class="pa-2">
                        {{n+1}}
                    </v-flex>
                    <v-flex xs9 class="pa-2">
                        {{item.item.item_name}}
                    </v-flex>
                    <v-flex xs2 class="pa-2 text-xs-right">
                        {{item.qty}}
                    </v-flex>
                </v-layout>
                <v-divider class="my-2"></v-divider> 
            </v-card-text>

            <v-card-actions>
                <v-btn color="primary" flat @click="dialog=!dialog" v-show="!view">Batal</v-btn>
                <v-spacer></v-spacer>
                <!-- <v-btn color="primary" @click="add_detail">Add</v-btn>                 -->
                <v-btn color="primary" @click="save" :disabled="!btn_save_enabled" :dark="btn_save_enabled" v-show="!view">Konfirmasi</v-btn>
            </v-card-actions>

        </v-card>
    </v-dialog>
</template>

<script>
module.exports = {
    computed : {
        dialog : {
            get () { return this.$store.state.receive.dialog_confirm },
            set (v) { this.$store.commit('receive/set_common', ['dialog_confirm', v]) }
        },

        vendor_name () {
            return this.$store.state.receive.selected_receive.vendor_name
        },

        warehouse_name () {
            return this.$store.state.receive.selected_receive.warehouse_name
        },

        bill_note : {
            get () { return this.$store.state.receive.bill_note },
            set (v) { this.$store.commit('receive/set_common', ['bill_note', v]) }
        },

        btn_save_enabled () {
            return !this.$store.state.receive.bill_save
        },

        items () {
            return this.$store.state.receive.selected_receive.items
        },

        view () {
            return false
        }
    },

    methods : {
        save () {
            return this.$store.dispatch('receive/confirm')
        }
    }
}
</script>