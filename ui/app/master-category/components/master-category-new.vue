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
                <h3 v-show="!edit">ENTRY KATEGORI BARU</h3>
                <h3 v-show="edit">UBAH DATA KATEGORI</h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                        <v-layout row wrap>
                            <v-flex xs12>
                                <v-text-field
                                    label="Kode Category"
                                    v-model="category_code"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs12 :class="`mt-`+v_space">
                                <v-text-field
                                    label="Nama Category"
                                    v-model="category_name"
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
            get () { return this.$store.state.category_new.dialog_new },
            set (v) { this.$store.commit('category_new/set_common', ['dialog_new', v]) }
        },

        category_name : {
            get () { return this.$store.state.category_new.category_name },
            set (v) { this.$store.commit('category_new/set_common', ['category_name', v]) }
        },

        category_code : {
            get () { return this.$store.state.category_new.category_code },
            set (v) { this.$store.commit('category_new/set_common', ['category_code', v]) }
        },

        edit () {
            return this.$store.state.category_new.edit
        },

        v_space () {
            return 0
        }
    },

    methods : {
        save () {
            this.$store.dispatch('category_new/save')
        }
    },

    mounted () {
    },

    watch : {
        
    }
}
</script>