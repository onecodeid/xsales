<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="400px"
        transition="dialog-transition"
        content-class="dialog-new"
        
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <h3>
                    PILIH GUDANG TUJUAN
                </h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                        <v-autocomplete
                            :items="warehouses"
                            v-model="selected_warehouse"
                            return-object
                            item-text="warehouse_name"
                            item-value="warehouse_id"
                            label="Diterima di Gudang"
                        ></v-autocomplete>   
                    </v-flex>
                </v-layout>
            </v-card-text>

            <v-card-actions>
                <v-btn color="primary" @click="dialog=!dialog" flat>Tutup</v-btn>
                <v-spacer></v-spacer>
                <v-btn color="primary" @click="choose" :disabled="!selected_purchase" :dark="!!selected_purchase">Pilih</v-btn>
            </v-card-actions>

        </v-card>
    </v-dialog>
</template>

<script>
module.exports = {
    components : {
    },

    data () {
        return {
        }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.receive_new.dialog_warehouse },
            set (v) { this.$store.commit('receive_new/set_common', ['dialog_warehouse', v]) }
        },

        warehouses () {
            return this.$store.state.receive_new.warehouses
        },

        selected_warehouse : {
            get () { return this.$store.state.receive_new.selected_warehouse },
            set (v) { 
                this.$store.commit('receive_new/set_selected_warehouse', v)
                this.$store.dispatch('receive_new/search_item')
            }
        },

        selected_purchase : {
            get () { return this.$store.state.purchase.selected_purchase },
            set (v) { this.$store.commit('purchase/set_selected_purchase', v) }
        }
    },

    methods : {
        choose () {
            this.$store.commit('receive_new/set_common', ['dialog_warehouse', false])
            this.$store.commit('receive_new/set_common', ['dialog_new', true])
        }
    }
}
</script>