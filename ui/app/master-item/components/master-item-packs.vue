<template>
    <div>
    <v-data-table 
        :headers="headers"
        :items="itempacks"
        :loading="false"
        hide-actions
        class="elevation-1">
        <template slot="items" slot-scope="props">
            <td class="text-xs-center pa-0">
                <v-btn color="red" class="ma-0 mr-1" icon :dark="false" flat @click="del(props.index)" small :disabled="!!view"><v-icon>delete</v-icon></v-btn>
                <v-btn color="primary" class="ma-0 mr-1" icon :dark="false" flat @click="edit_itempack(props.item)" small :disabled="!!view"><v-icon>edit</v-icon></v-btn>
            </td>
            <td class="text-xs-left pa-2">
                {{ props.item.customer_name }}
            </td>
            <td class="text-xs-left pa-2">
                {{ props.item.pack_name }}
            </td>
        </template>
        <template slot="footer">
            <td colspan="3" class="text-xs-center" v-show="!view">
                <v-btn color="primary btn-icon" @click="add_itempack" class="ma-1" small><v-icon>add</v-icon></v-btn>
            </td>
        </template>

        
    </v-data-table>    
    <master-item-pack></master-item-pack>
    </div>
</template>

<style scoped src="../assets/css/table.css"></style>
<style scoped>
.input-dense .v-input__control {
    min-height: 36px !important;
}
</style>
<script>
module.exports = {
    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        "master-item-pack" : httpVueLoader("./master-item-pack.vue?t=123")
    },

    data () {
        return {
            headers: [
                {
                    text: "#",
                    align: "center",
                    sortable: false,
                    width: "15%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "CUSTOMER",
                    align: "left",
                    sortable: false,
                    width: "40%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "KEMASAN",
                    align: "left",
                    sortable: false,
                    width: "45%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                }
            ]
        }
    },

    computed : {
        itempacks () {
            return this.$store.state.item_new.itempacks
        },

        selected_itempack () {
            return this.$store.state.item_new.selected_itempack
        },

        edit () {
            return this.$store.state.item_new.edit
        },

        view () { return this.$store.state.view }
    },

    methods : {
        save () {
            this.$store.dispatch('item_new/save')
        },

        select_itempack (x) {
            this.$store.commit('item_new/set_selected_itempack', x)
        },

        add_itempack () {
            this.$store.commit('item_new/set_common', ['dialog_pack', true])
            this.$store.commit('item_new/set_common', ['edit_pack', false])
            this.$store.commit('item_new/set_selected_itempack_pack', null)
            this.$store.commit('item_new/set_selected_customer', null)
        },

        del (x) {
            let itempacks = this.$store.state.item_new.itempacks
            itempacks.splice(x, 1)
            this.$store.commit('item_new/set_itempacks', itempacks)
        },

        edit_itempack (x) {
            this.select_pack(x)
            this.$store.commit('item_new/set_common', ['dialog_pack', true])
            this.$store.commit('item_new/set_common', ['edit_pack', true])
            
            this.$store.commit('item_new/set_selected_customer', null)
            for (let c of this.$store.state.item_new.customers)
                if (c.customer_id == x.customer_id)
                    this.$store.commit('item_new/set_selected_customer', c)

            this.$store.commit('item_new/set_selected_itempack_pack', null)
            for (let c of this.$store.state.item_new.packs)
                if (c.pack_id == x.pack_id)
                    this.$store.commit('item_new/set_selected_itempack_pack', c)
        },

        select_pack (x) {
            this.$store.commit('item_new/set_selected_itempack_pack', x)
        }
    },

    mounted () {
    },

    watch : {
        
    }
}
</script>