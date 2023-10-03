<template>
    <div>
        <v-data-table 
            :headers="headers"
            :items="aliases"
            :loading="false"
            hide-actions
            class="elevation-1">
            <template slot="items" slot-scope="props">
                <td class="text-xs-center pa-0">
                    <v-btn color="red" class="ma-0 mr-1" icon :dark="false" flat @click="del(props.index)" small :disabled="!!view"><v-icon>delete</v-icon></v-btn>
                    <v-btn color="primary" class="ma-0 mr-1" icon :dark="false" flat @click="edit_alias(props.item)" small :disabled="!!view"><v-icon>edit</v-icon></v-btn>
                </td>
                <td class="text-xs-left pa-2">
                    {{ props.item.customer_name }}
                </td>
                <td class="text-xs-left pa-2">
                    {{ props.item.item_alias }}
                </td>
            </template>
            <template slot="footer">
                <td colspan="3" class="text-xs-center" v-show="!view">
                    <v-btn color="primary btn-icon ma-1" @click="add_alias" small><v-icon>add</v-icon></v-btn>
                </td>
            </template>
        </v-data-table>   
        <master-item-alias></master-item-alias>
    </div>
</template>

<style scoped src="../assets/css/table.css"></style>
<style scoped>
.input-dense .v-input__control {
    min-height: 36px !important;
}
</style>
<script>
var t = Math.ceil(Math.random() * 1e10)
module.exports = {
    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        "master-item-alias" : httpVueLoader("./master-item-alias.vue?t="+t)
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
                    text: "ALIAS",
                    align: "left",
                    sortable: false,
                    width: "45%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                }
            ]
        }
    },

    computed : {
        aliases () {
            return this.$store.state.item_new.aliases
        },

        selected_alias () {
            return this.$store.state.item_new.selected_alias
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

        select_alias (x) {
            this.$store.commit('item_new/set_selected_alias', x)
        },

        add_alias () {
            this.$store.commit('item_new/set_common', ['dialog_alias', true])
            this.$store.commit('item_new/set_common', ['edit_alias', false])
            this.$store.commit('item_new/set_common', ['item_alias', ''])
            this.$store.commit('item_new/set_selected_customer', null)
        },

        del (x) {
            let aliases = this.$store.state.item_new.aliases
            aliases.splice(x, 1)
            this.$store.commit('item_new/set_aliases', aliases)
        },

        edit_alias (x) {
            this.select_alias(x)
            this.$store.commit('item_new/set_common', ['dialog_alias', true])
            this.$store.commit('item_new/set_common', ['edit_alias', true])
            this.$store.commit('item_new/set_common', ['item_alias', x.item_alias])
            
            this.$store.commit('item_new/set_selected_customer', null)
            for (let c of this.$store.state.item_new.customers)
                if (c.customer_id == x.customer_id)
                    this.$store.commit('item_new/set_selected_customer', c)
        },

        select_alias (x) {
            this.$store.commit('item_new/set_selected_alias', x)
        }
    },

    mounted () {
    },

    watch : {
        
    }
}
</script>