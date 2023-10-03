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
                <h3 v-show="!edit">ENTRY DATA STAFF BARU</h3>
                <h3 v-show="edit">UBAH DATA STAFF</h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                        <v-layout row wrap>
                            <v-flex xs12 :class="`mt-`+v_space">
                                <v-text-field
                                    label="Nama Staff"
                                    v-model="staff_name"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs12>
                                <v-text-field
                                    label="Kode Staff"
                                    v-model="staff_code"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs12>
                                <v-select
                                    :items="positions"
                                    v-model="selected_position"
                                    item-value="position_id"
                                    item-text="position_name"
                                    return-object
                                    label="Posisi"
                                ></v-select>
                            </v-flex>
                        </v-layout>
                    </v-flex>
                </v-layout>
            </v-card-text>

            <v-card-actions>
                <v-btn color="primary" flat @click="dialog=!dialog">Batal</v-btn>
                <v-spacer></v-spacer>
                <v-btn color="primary" @click="save" :disabled="staff_name==''||!selected_position">Simpan</v-btn>                
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
            get () { return this.$store.state.staff_new.dialog_new },
            set (v) { this.$store.commit('staff_new/set_common', ['dialog_new', v]) }
        },

        staff_name : {
            get () { return this.$store.state.staff_new.staff_name },
            set (v) { this.$store.commit('staff_new/set_common', ['staff_name', v]) }
        },

        staff_code : {
            get () { return this.$store.state.staff_new.staff_code },
            set (v) { this.$store.commit('staff_new/set_common', ['staff_code', v]) }
        },

        positions () {
            return this.$store.state.staff.positions
        },

        selected_position : {
            get () { return this.$store.state.staff_new.selected_position },
            set (v) { this.$store.commit('staff_new/set_object', ['selected_position', v]) }
        },

        edit () {
            return this.$store.state.staff_new.edit
        },

        v_space () {
            return 0
        }
    },

    methods : {
        save () {
            this.$store.dispatch('staff_new/save').then((x) => {
                this.$store.dispatch('staff/search')
            })
        }
    },

    mounted () {
        this.$store.dispatch('staff/search_position')
    },

    watch : {
        
    }
}
</script>