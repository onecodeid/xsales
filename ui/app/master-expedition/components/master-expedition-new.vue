<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="500px"
        transition="dialog-transition"
        persistent
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <h3 v-show="!edit">ENTRY DATA EKSPEDISI</h3>
                <h3 v-show="edit">UBAH DATA EKSPEDISI</h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                        <v-layout row wrap>
                            

                            <v-flex xs4>
                                <v-text-field
                                    label="Kode Ekspedisi"
                                    v-model="expedition_code"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs8 :class="`mt-`+v_space" pl-2>
                                <v-text-field
                                    label="Nama Ekspedisi"
                                    v-model="expedition_name"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs12>
                                <v-text-field
                                    label="Alamat Ekspedisi"
                                    v-model="expedition_address"
                                    prepend-inner-icon="place"
                                    :error="expedition_address.length<10"
                                    :error-count="expedition_address.length<10?1:0"
                                    :error-messages="expedition_address.length<10?['Wajib diisi, Minimal 10 karakter']:[]"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs6 :class="`mt-`+v_space">
                                <v-text-field
                                    label="Telp"
                                    v-model="expedition_phone1"
                                    prepend-inner-icon="phone"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs6 :class="`mt-`+v_space" pl-2>
                                <v-text-field
                                    label="HP"
                                    v-model="expedition_phone2"
                                    prepend-inner-icon="phone"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs12>
                                <v-text-field
                                    label="Tujuan Pengiriman"
                                    v-model="expedition_destination"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs12>
                                <v-text-field
                                    label="Website"
                                    v-model="expedition_website"
                                    prepend-inner-icon="public"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs12>
                                <v-textarea
                                    label="Catatan"
                                    v-model="expedition_note"
                                ></v-textarea>
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
            get () { return this.$store.state.expedition_new.dialog_new },
            set (v) { this.$store.commit('expedition_new/set_common', ['dialog_new', v]) }
        },

        expedition_name : {
            get () { return this.$store.state.expedition_new.expedition_name },
            set (v) { this.$store.commit('expedition_new/set_common', ['expedition_name', v]) }
        },

        expedition_code : {
            get () { return this.$store.state.expedition_new.expedition_code },
            set (v) { this.$store.commit('expedition_new/set_common', ['expedition_code', v]) }
        },

        expedition_address : {
            get () { return this.$store.state.expedition_new.expedition_address },
            set (v) { this.$store.commit('expedition_new/set_object', ['expedition_address', v]) }
        },

        expedition_website : {
            get () { return this.$store.state.expedition_new.expedition_website },
            set (v) { this.$store.commit('expedition_new/set_object', ['expedition_website', v]) }
        },

        expedition_destination : {
            get () { return this.$store.state.expedition_new.expedition_destination },
            set (v) { this.$store.commit('expedition_new/set_object', ['expedition_destination', v]) }
        },

        expedition_phone1 : {
            get () { return this.$store.state.expedition_new.expedition_phone1 },
            set (v) { this.$store.commit('expedition_new/set_object', ['expedition_phone1', v]) }
        },

        expedition_phone2 : {
            get () { return this.$store.state.expedition_new.expedition_phone2 },
            set (v) { this.$store.commit('expedition_new/set_object', ['expedition_phone2', v]) }
        },

        expedition_note : {
            get () { return this.$store.state.expedition_new.expedition_note },
            set (v) { this.$store.commit('expedition_new/set_object', ['expedition_note', v]) }
},

        edit () {
            return this.$store.state.expedition_new.edit
        },

        v_space () {
            return 0
        }
    },

    methods : {
        save () {
            this.$store.dispatch('expedition_new/save')
        }
    },

    mounted () {
    },

    watch : {
        
    }
}
</script>