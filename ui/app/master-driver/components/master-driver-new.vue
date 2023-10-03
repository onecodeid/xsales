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
                            <v-flex xs12 :class="`mt-`+v_space">
                                <v-text-field
                                    label="Nama Sopir"
                                    v-model="driver_name"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs4>
                                <v-text-field
                                    label="Plat Nomor"
                                    v-model="driver_plat"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs8 pl-2>
                                <v-text-field
                                    label="Kendaraan"
                                    v-model="driver_vehicle"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs4>
                                <v-text-field
                                    label="Berat Muatan"
                                    v-model="driver_weight"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs8>
                                <v-text-field
                                    label="Pangkalan"
                                    v-model="driver_pool"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs6 :class="`mt-`+v_space">
                                <v-text-field
                                    label="Telp"
                                    v-model="driver_phone1"
                                    prepend-inner-icon="phone"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs6 :class="`mt-`+v_space" pl-2>
                                <v-text-field
                                    label="HP"
                                    v-model="driver_phone2"
                                    prepend-inner-icon="phone"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs12>
                                <v-textarea
                                    label="Catatan"
                                    v-model="driver_note"
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
            get () { return this.$store.state.driver_new.dialog_new },
            set (v) { this.$store.commit('driver_new/set_common', ['dialog_new', v]) }
        },

        driver_name : {
            get () { return this.$store.state.driver_new.driver_name },
            set (v) { this.$store.commit('driver_new/set_common', ['driver_name', v]) }
        },

        driver_plat : {
            get () { return this.$store.state.driver_new.driver_plat },
            set (v) { this.$store.commit('driver_new/set_common', ['driver_plat', v]) }
        },

        driver_vehicle : {
            get () { return this.$store.state.driver_new.driver_vehicle },
            set (v) { this.$store.commit('driver_new/set_object', ['driver_vehicle', v]) }
        },

        driver_pool : {
            get () { return this.$store.state.driver_new.driver_pool },
            set (v) { this.$store.commit('driver_new/set_object', ['driver_pool', v]) }
        },

        driver_weight : {
            get () { return this.$store.state.driver_new.driver_weight },
            set (v) { this.$store.commit('driver_new/set_object', ['driver_weight', v]) }
        },

        driver_phone1 : {
            get () { return this.$store.state.driver_new.driver_phone1 },
            set (v) { this.$store.commit('driver_new/set_object', ['driver_phone1', v]) }
        },

        driver_phone2 : {
            get () { return this.$store.state.driver_new.driver_phone2 },
            set (v) { this.$store.commit('driver_new/set_object', ['driver_phone2', v]) }
        },

        driver_note : {
            get () { return this.$store.state.driver_new.driver_note },
            set (v) { this.$store.commit('driver_new/set_object', ['driver_note', v]) }
},

        edit () {
            return this.$store.state.driver_new.edit
        },

        v_space () {
            return 0
        }
    },

    methods : {
        save () {
            this.$store.dispatch('driver_new/save')
        }
    },

    mounted () {
    },

    watch : {
        
    }
}
</script>