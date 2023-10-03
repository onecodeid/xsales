<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="300px"
        transition="dialog-transition"
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <h3>KEMASAN ITEM</h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                        <v-autocomplete
                            :items="customers"
                            v-model="selected_customer"
                            return-object
                            item-text="customer_name"
                            item-value="customer_id"
                            label="Customer"
                            :disabled="edit"
                        ></v-autocomplete>
                    </v-flex>

                    <v-flex xs12>
                        <v-select
                            :items="packs"
                            v-model="selected_pack"
                            return-object
                            label="Kemasan"
                            item-key="pack_id"
                            item-text="pack_name"
                            :disabled="!!view"
                        >
                        </v-select>
                    </v-flex>
                </v-layout>
            </v-card-text>

            <v-card-actions>
                <v-btn color="primary" flat @click="dialog=!dialog">Batal</v-btn>
                <v-spacer></v-spacer>
                <v-btn color="primary" @click="save">Simpan</v-btn>                
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<style>
.input-dense .v-input__control {
    min-height: 36px !important;
}
</style>
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
            get () { return this.$store.state.item_new.dialog_pack },
            set (v) { this.$store.commit('item_new/set_common', ['dialog_pack', v]) }
        },

        customers () {
            return this.$store.state.item_new.customers
        },

        selected_customer : {
            get () { return this.$store.state.item_new.selected_customer },
            set (v) { this.$store.commit('item_new/set_selected_customer', v) }
        },

        packs () {
            return this.$store.state.item_new.packs
        },

        selected_pack : {
            get () { return this.$store.state.item_new.selected_itempack_pack },
            set (v) { this.$store.commit('item_new/set_selected_itempack_pack', v) }
        },

        edit () {
            return this.$store.state.item_new.edit_pack
        },

        view () { return this.$store.state.view }
    },

    methods : {
        save () {
            let itempacks = this.$store.state.item_new.itempacks

            for (let a of itempacks) {
                if (a.customer_id == this.selected_customer.customer_id) {
                    if (!this.edit) {
                        alert('Kemasan untuk customer tersebut sudah ada !')
                        return
                    } else {
                        a.pack_id = this.selected_pack.pack_id
                        a.pack_name = this.selected_pack.pack_name
                    }
                }
            }

            if (!this.edit) {
                itempacks.push({
                    customer_id:this.selected_customer.customer_id,
                    customer_name:this.selected_customer.customer_name,
                    pack_id:this.selected_pack.pack_id,
                    pack_name:this.selected_pack.pack_name
                })
            }
                
            this.$store.commit('item_new/set_itempacks', itempacks)
            this.dialog = false
        }
    },

    mounted () {
    },

    watch : {
        
    }
}
</script>