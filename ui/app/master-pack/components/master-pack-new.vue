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
                <h3 v-show="!edit">ENTRY DATA KEMASAN BARU</h3>
                <h3 v-show="edit">UBAH DATA KEMASAN</h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                        <v-layout row wrap>
                            <v-flex xs12 :class="`mt-`+v_space">
                                <v-text-field
                                    label="Nama Pack"
                                    v-model="pack_name"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs12>
                                <v-text-field
                                    label="Kode Pack"
                                    v-model="pack_code"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs6 pr-4>
                                <v-text-field
                                    label="Konversi"
                                    v-model="pack_conversion"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs6>
                                <v-select
                                    :items="units"
                                    v-model="selected_unit"
                                    return-object
                                    label="Satuan"
                                    item-key="unit_id"
                                    item-text="unit_name"
                                >
                                </v-select>
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
            get () { return this.$store.state.pack_new.dialog_new },
            set (v) { this.$store.commit('pack_new/set_common', ['dialog_new', v]) }
        },

        pack_name : {
            get () { return this.$store.state.pack_new.pack_name },
            set (v) { this.$store.commit('pack_new/set_common', ['pack_name', v]) }
        },

        pack_code : {
            get () { return this.$store.state.pack_new.pack_code },
            set (v) { this.$store.commit('pack_new/set_common', ['pack_code', v]) }
        },

        edit () {
            return this.$store.state.pack_new.edit
        },

        v_space () {
            return 0
        },

        pack_conversion : {
            get () { return this.$store.state.pack_new.pack_conversion },
            set (v) { this.$store.commit('pack_new/set_common', ['pack_conversion', v]) }
        },

        units () {
            return this.$store.state.pack_new.units
        },

        selected_unit : {
            get () { return this.$store.state.pack_new.selected_unit },
            set (v) { this.$store.commit('pack_new/set_selected_unit', v) }
        }
    },

    methods : {
        save () {
            this.$store.dispatch('pack_new/save')
        }
    },

    mounted () {
        this.$store.dispatch('pack_new/search_unit')
    },

    watch : {
        
    }
}
</script>