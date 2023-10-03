<template>
   
        
                <v-layout row wrap>
                    <v-flex xs4 v-for="(w,n) in warehouses" mb-2 class="pr-1">
                        <v-card :class="{'orange lighten-3':selected_warehouse?(selected_warehouse.warehouse_id==w.warehouse_id?true:false):false}"
                            @click="selected_warehouse=w">
                            <v-card-text class="text-xs-center py-4">
                                {{ w.warehouse_name }}
                            </v-card-text>
                        </v-card>
                        
                    </v-flex>
                    <!-- <v-flex xs12>
                        <v-autocomplete
                            :items="warehouses"
                            v-model="selected_warehouse"
                            return-object
                            item-text="warehouse_name"
                            item-value="warehouse_id"
                            label="Diterima di Gudang"
                        ></v-autocomplete>   
                    </v-flex> -->
                </v-layout>
            

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
                // this.$store.dispatch('receive_new/search_item')
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