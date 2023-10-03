<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="500px"
        transition="dialog-transition"
        content-class="dialog-new"
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <h3>DATA AKUN</h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                        <v-layout row wrap>
                            <v-flex xs12>
                                <v-select
                                    :items="groups"
                                    v-model="selected_group"
                                    label="Grup"
                                    return-object
                                    item-text="group_name"
                                    item-value="group_id"
                                >
                                    <template slot="item" slot-scope="data">
                                        {{ data.item.group_code }} - {{ data.item.group_name }}
                                    </template>
                                    <template slot="selection" slot-scope="data">
                                        {{ data.item.group_code }} - {{ data.item.group_name }}
                                    </template>
                                </v-select>    
                            </v-flex>

                            <v-flex xs6 pr-2>
                                <v-select
                                    :items="actions"
                                    v-model="selected_action"
                                    item-text="text"
                                    item-value="id"
                                    return-object
                                    label="Tambahkan"
                                ></v-select>
                            </v-flex>

                            <v-flex xs6 pl-2>
                                <v-select
                                    :items="accounts"
                                    v-model="selected_account"
                                    item-text="account_name"
                                    item-value="account_id"
                                    return-object
                                    label=""
                                ></v-select>
                            </v-flex>

                            <v-flex xs12>
                                <v-text-field
                                    label="Kode Akun Perkiraan"
                                    v-model="account_code"
                                    readonly
                                    v-show="false"
                                >
                                    <!-- <template slot="prepend-inner">
                                        <span class="mt-1 blue--text">{{ code_prefix }}</span></template> -->
                                </v-text-field>
                            </v-flex>

                            <v-flex xs12>
                                <v-text-field
                                    label="Nama Akun Perkiraan"
                                    v-model="account_name"
                                ></v-text-field>
                            </v-flex>

                        </v-layout>
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

.dialog-new .v-text-field__prefix {
    width: auto;
}

.dialog-new .v-text-field__prefix::after {
    width: auto;
}

.dialog-new .v-text-field__slot input {
    width: auto;
}
</style>
<script>
module.exports = {
    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue')
    },

    data () {
        return { }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.account_new.dialog_new },
            set (v) { this.$store.commit('account_new/set_common', ['dialog_new', v]) }
        },

        account_name : {
            get () { return this.$store.state.account_new.account_name },
            set (v) { this.$store.commit('account_new/set_common', ['account_name', v]) }
        },

        account_code : {
            get () { return this.$store.state.account_new.account_code },
            set (v) { this.$store.commit('account_new/set_common', ['account_code', v]) }
        },

        groups () {
            return this.$store.state.account_new.groups
        },

        selected_group : {
            get () { return this.$store.state.account_new.selected_group },
            set (v) { 
                this.$store.commit('account_new/set_object', ['selected_group', v]) 
                this.$store.dispatch('account_new/search_account')
            }
        },

        code_prefix () {
            return this.$store.state.account_new.account_code_prefix
        },

        accounts () {
            return this.$store.state.account_new.accounts
        },

        selected_account : {
            get () { return this.$store.state.account_new.selected_account },
            set (v) { this.$store.commit('account_new/set_object', ['selected_account',v]) }
        },

        actions () {
            return this.$store.state.account_new.actions
        },

        selected_action : {
            get () { return this.$store.state.account_new.selected_action },
            set (v) { this.$store.commit('account_new/set_object', ['selected_action',v]) }
        }


    },

    methods : {
        save () {
            this.$store.dispatch('account_new/save')
        }
    },

    mounted () {},

    watch : {}
}
</script>