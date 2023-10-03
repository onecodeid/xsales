<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="300px"
        transition="dialog-transition"
        persistent
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <h3 v-show="!edit">ENTRY DISKON BARU</h3>
                <h3 v-show="edit">UBAH DATA DISKON</h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                        <v-layout row wrap>
                            <v-flex xs12 :class="`mt-`+v_space">
                                <v-text-field
                                    label="Nama Diskon"
                                    v-model="disc_name"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs12>
                                <v-text-field
                                    label="Kode Diskon"
                                    v-model="disc_code"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs12>
                                <v-text-field
                                    label="Besar Diskon"
                                    v-model="disc_amount"
                                    suffix="%"
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
</style>

<script>
module.exports = {
    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue')
    },

    data () {
        return {
        }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.disc_new.dialog_new }, // Mengganti 'affiliate_new' menjadi 'disc_new'
            set (v) { this.$store.commit('disc_new/set_common', ['dialog_new', v]) } // Mengganti 'affiliate_new' menjadi 'disc_new'
        },

        disc_name : { // Mengganti 'affiliate_name' menjadi 'disc_name'
            get () { return this.$store.state.disc_new.disc_name }, // Mengganti 'affiliate_new' menjadi 'disc_new'
            set (v) { this.$store.commit('disc_new/set_common', ['disc_name', v]) } // Mengganti 'affiliate_new' menjadi 'disc_new'
        },

        disc_code : { // Mengganti 'affiliate_number' menjadi 'disc_code'
            get () { return this.$store.state.disc_new.disc_code }, // Mengganti 'affiliate_new' menjadi 'disc_new'
            set (v) { this.$store.commit('disc_new/set_common', ['disc_code', v]) } // Mengganti 'affiliate_new' menjadi 'disc_new'
        },

        disc_amount : { // Mengganti 'affiliate_number' menjadi 'disc_code'
            get () { return this.$store.state.disc_new.disc_amount }, // Mengganti 'affiliate_new' menjadi 'disc_new'
            set (v) { this.$store.commit('disc_new/set_common', ['disc_amount', v]) } // Mengganti 'affiliate_new' menjadi 'disc_new'
        },

        edit () {
            return this.$store.state.disc_new.edit // Mengganti 'affiliate_new' menjadi 'disc_new'
        },

        v_space () {
            return 0
        }
    },

    methods : {
        save () {
            this.$store.dispatch('disc_new/save') // Mengganti 'affiliate_new' menjadi 'disc_new'
        }
    },

    mounted () {
    },

    watch : {
        
    }
}
</script>
