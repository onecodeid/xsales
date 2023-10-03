<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="1000px"
        transition="dialog-transition"
        content-class="dialog-new"
    >
        <v-card>
            <v-card-title primary-title class="teal lighten-5">
                <h3 class="display-1 font-weight-light zalfa-text-title">{{ edit?'UBAH':'ENTRI' }} JURNAL UMUM <b>{{ edit?journal_number:'BARU' }}</b></h3>
            </v-card-title>
            <v-card-text>
                <new></new>
            </v-card-text>

            <v-card-actions>

                <v-btn color="amber" class="white--text" @click="calculator">Calculator</v-btn>
                <v-spacer></v-spacer>
                <v-btn color="primary" @click="cancel" outline>Batal</v-btn>
                <v-btn color="primary" @click="save">Simpan</v-btn>

            </v-card-actions>
        </v-card>
    </v-dialog>
    
</template>
<style scoped>
.v-text-field.v-text-field--solo .v-input__control {
    /* min-height: 36px; */
    padding: 0;
}
</style>
<script>
let rnd = Math.random() * 1e10
module.exports = {
    components : {
        'new' : httpVueLoader('./finance-journal-general-new.vue?t='+rnd)
    },

    data () {
        return {
            
        }
    },

    computed : {
        journal_number () {
            return this.$store.state.generalNew.journal_number
        },

        dialog : {
            get () { return this.$store.state.generalNew.dialog_new },
            set (v) { this.setObject('dialog_new', v) }
        },

        edit () {
            return this.$store.state.generalNew.edit
        }
    },

    methods : {
        setObject(x, y) {
            return this.$store.commit('generalNew/set_object', 
                    [x, y])
        },

        save () {
            this.$store.dispatch('generalNew/save').then(res => {
                if (res.status && res.status == 'ERR') {
                    alert(res.message)
                } else {
                    this.dialog = false
                    this.$store.dispatch("general/search")
                }
            })
        },

        cancel () {
            if (!!this.$store.state.generalNew.dialog_new) {
                this.$store.commit("generalNew/set_object", ["dialog_new", false])
            } else {
                window.location = "../list/" + this.selected_account.account_id
            }
        },

        calculator () {
            this.$store.commit('set_dialog_calculator', true)
        }
    },

    mounted () {
       
    },

    watch : {
    }
}