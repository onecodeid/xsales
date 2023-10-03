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
                <h3>ITEM ALIAS</h3>
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
                        <v-text-field
                            label="Alias"
                            v-model="item_alias"
                        ></v-text-field>
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
            get () { return this.$store.state.item_new.dialog_alias },
            set (v) { this.$store.commit('item_new/set_common', ['dialog_alias', v]) }
        },

        customers () {
            return this.$store.state.item_new.customers
        },

        selected_customer : {
            get () { return this.$store.state.item_new.selected_customer },
            set (v) { this.$store.commit('item_new/set_selected_customer', v) }
        },

        item_alias : {
            get () { return this.$store.state.item_new.item_alias },
            set (v) { this.$store.commit('item_new/set_common', ['item_alias', v]) }
        },

        edit () {
            return this.$store.state.item_new.edit_alias
        }
    },

    methods : {
        save () {
            let aliases = this.$store.state.item_new.aliases

            for (let a of aliases) {
                if (a.customer_id == this.selected_customer.customer_id) {
                    if (!this.edit) {
                        alert('Alias untuk customer tersebut sudah ada !')
                        return
                    } else {
                        a.item_alias = this.item_alias
                    }
                }
            }

            if (!this.edit) {
                aliases.push({
                    customer_id:this.selected_customer.customer_id,
                    customer_name:this.selected_customer.customer_name,
                    item_alias:this.item_alias
                })
            }
                
            this.$store.commit('item_new/set_aliases', aliases)
            this.dialog = false
        }
    },

    mounted () {
    },

    watch : {
        
    }
}
</script>