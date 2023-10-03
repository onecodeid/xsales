<template>
    <v-layout row wrap>
        
        <v-flex xs12>
            <v-data-table 
                :headers="headers"
                :items="users"
                :loading="false"
                hide-actions
                class="elevation-1">
                <template slot="items" slot-scope="props">
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.user_name }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.user_full_name }}</td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">
                        -
                        </td>
                    <td class="text-xs-center pa-2" @click="select(props.item)">-</td>
                    <td class="text-xs-center pa-0 pr-1 pl-1" @click="select(props.item)">
                        <v-btn color="primary" class="btn-icon ma-0" small @click="edit(props.item)" :disabled="props.item.user_id == me.user_id">
                            <v-icon>edit</v-icon>
                        </v-btn>
                        
                        <v-btn color="red" :dark="props.item.user_id != me.user_id" class="btn-icon ma-0" small @click="del(props.item)" :disabled="props.item.user_id == me.user_id"><v-icon>delete</v-icon></v-btn>
                    </td>
                    <!-- <td class="text-xs-center pa-2" v-bind:class="{'amber lighten-4':isSelected(props.item)}" @click="selectMe(props.item)">{{ props.item.M_DoctorHP}}</td>
                    <td class="text-xs-left pa-2" v-bind:class="{'amber lighten-4':isSelected(props.item)}" @click="selectMe(props.item)">{{ props.item.status}}</td> -->
                </template>
            </v-data-table>
        </v-flex>

        <user-new></user-new>
        <v-snackbar
            v-model="snackbar"
            multi-line
            :timeout="6000"
            top
            vertical
            >
            {{ snackbar_text }}
            <v-btn
                color="pink"
                flat
                @click="snackbar = false"
            >
                Tutup
            </v-btn>
        </v-snackbar>

        <common-dialog-confirm data="1" :text="'Hapus data user ' + (selected_user?selected_user.user_name:'') + ' ?'" @confirm="do_del" v-if="dialog_confirm"></common-dialog-confirm>
    </v-layout>
</template>

<style scoped>
table.v-table thead tr {
    height: 44px;
}
</style>

<script>
module.exports = {
    components : {
        'user-new' : httpVueLoader('./user-new.vue'),
        'common-dialog-confirm' : httpVueLoader('../../common/components/common-dialog-confirm.vue')
    },

    data () {
        return {
            headers: [
                {
                    text: "USERNAME",
                    align: "left",
                    width: "15%",
                    sortable:false,
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "NAMA LENGKAP",
                    align: "left",
                    sortable: false,
                    width: "20%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "ALAMAT",
                    align: "left",
                    sortable: false,
                    width: "48%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "J. KELAMIN",
                    align: "center",
                    sortable: false,
                    width: "7%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "ACTION",
                    align: "center",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                }
            ],

            me: this.$store.state.user.one_user
        }
    },

    computed : {
        users () {
            return this.$store.state.user.users
        },

        selected_user : {
            get () { return this.$store.state.user.selected_user },
            set (v) { this.$store.commit('user/set_selected_user', v) }
        },

        snackbar : {
            get () { return this.$store.state.user_new.snackbar },
            set (v) { this.$store.commit('user_new/set_common', ['snackbar', v]) }
        },

        snackbar_text : {
            get () { return this.$store.state.user_new.snackbar_text },
            set (v) { this.$store.commit('user_new/set_common', ['snackbar_text', v]) }
        },

        dialog_confirm () {
            return this.$store.state.dialog_confirm
        }
    },

    methods : {
        select (x) {
            this.$store.commit('user/set_selected_user', x)
        },

        edit (x) {
            this.select(x)
            this.$store.commit('user_new/set_common', ['edit', true])
            this.$store.commit('user_new/set_common', ['dialog_user', true])
            this.$store.commit('user_new/set_common', ['error_check', false])

            this.$store.commit('user_new/set_common', ['user_name', x.user_name])
            this.$store.commit('user_new/set_common', ['user_full_name', x.user_full_name])
            this.$store.commit('user_new/set_common', ['user_address', x.user_address])
            this.$store.commit('user_new/set_common', ['user_phone', x.user_phone])
            this.$store.commit('user_new/set_common', ['user_email', x.user_email])
            
            // this.$store.commit('user/set_common', ['user_name', x.user_name])
        },

        add (x) {
            this.select(x)
            this.$store.commit('user_new/set_common', ['edit', false])
            this.$store.commit('user_new/set_common', ['dialog_user', true])
            this.$store.commit('user_new/set_common', ['error_check', true])

            this.$store.commit('user_new/set_common', ['user_name', ''])
            this.$store.commit('user_new/set_common', ['user_full_name', ''])
            this.$store.commit('user_new/set_common', ['user_address', ''])
            this.$store.commit('user_new/set_common', ['user_phone', ''])
            this.$store.commit('user_new/set_common', ['user_email', ''])
            // this.$store.commit('user/set_common', ['user_name', x.user_name])
        },

        del (x) {
            this.select(x)
            this.$store.commit('set_dialog_confirm', true)
            
        },

        do_del () {
            this.$store.dispatch('user/del')
        }
    }
}
</script>