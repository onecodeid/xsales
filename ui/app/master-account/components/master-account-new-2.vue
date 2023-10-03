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
                <h3><span v-show="!!edit">UBAH </span>DATA AKUN<span v-show="!edit"> BARU</span></h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                        <v-select
                            :items="groups"
                            v-model="selected_group"
                            label="Grup"
                            return-object
                            item-text="group_name"
                            item-value="group_id"
                            :disabled="!!edit"
                        >
                            <template slot="item" slot-scope="data">
                                {{ data.item.group_code }} - {{ data.item.group_name }}
                            </template>
                            <template slot="selection" slot-scope="data">
                                {{ data.item.group_code }} - {{ data.item.group_name }}
                            </template>
                        </v-select>    
                    </v-flex>

                    <v-flex xs6>
                        <v-select
                            :items="accounts"
                            v-model="selected_account"
                            item-text="account_name"
                            item-value="account_id"
                            return-object
                            label="Parent"
                            clearable
                            v-show="!edit"
                            placeholder="TOP LEVEL"
                        >
                            <!-- <template slot="item" slot-scope="data">
                                <v-layout row wrap>
                                    <v-flex xs12>{{ data.item.account_name }}</v-flex>
                                    <v-flex xs12>└─ {{ account_name == ''? 'XXX' : account_name }}</v-flex>
                                </v-layout>
                                
                            </template> -->
                        </v-select>
                    </v-flex><v-flex xs6>&nbsp;</v-flex>
                    <v-flex xs12>
                        <v-text-field
                            label="Nama Akun Perkiraan"
                            v-model="account_name"
                            placeholder="XXX"
                        >
                            <template v-slot:prepend v-if="!!selected_account">
                                <span v-show="!!selected_account">└─</span>
                            </template>
                        </v-text-field>
                    </v-flex>

                    <v-flex xs12>
                        <v-radio-group v-model="account_pos" row>
                            <v-radio label="DEBIT" value="D"></v-radio>
                            <v-radio label="KREDIT" value="C"></v-radio>
                        </v-radio-group>
                    </v-flex>

                    <v-flex xs12>
                        <v-divider class="my-1"></v-divider>
                    </v-flex>
                </v-layout>
            </v-card-text>
            <v-card-actions>
                <v-btn color="primary" flat @click="dialog=!dialog">Batal</v-btn>
                <v-spacer></v-spacer>
                <v-btn color="primary" @click="save" :disabled="!btn_save_enabled">Simpan</v-btn>                
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
module.exports = {
    components : {
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
            set (v) { 
                this.$store.commit('account_new/set_common', ['account_name', v]) 
            }
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

        accounts () {
            return this.$store.state.account_new.accounts
        },

        selected_account : {
            get () { return this.$store.state.account_new.selected_account },
            set (v) { this.$store.commit('account_new/set_object', ['selected_account',v]) }
        },

        btn_save_enabled () {
            if (!this.selected_group || this.account_name.length < 3)
                return false
            return true
        },

        edit () {
            return this.$store.state.account_new.edit
        },

        account_pos : {
            get () { return this.$store.state.account_new.account_pos },
            set (v) { 
                this.$store.commit('account_new/set_common', ['account_pos', v]) 
            }
        }
    
    },

    methods : {
        save () {
            this.$store.dispatch('account_new/save_new')
        }
    }
}
</script>