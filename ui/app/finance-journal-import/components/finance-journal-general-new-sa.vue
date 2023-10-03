<template>
    <v-card>
        <v-card-title primary-title class="teal lighten-5">
            <h3 class="display-1 font-weight-light zalfa-text-title">{{ edit?'UBAH':'ENTRI' }} JURNAL UMUM <b>{{ journal_number }}</b></h3>
        </v-card-title>
        <v-card-text>
            <new></new>
        </v-card-text>

        <v-card-actions>
            <v-flex xs12 class="text-xs-right">
                <v-btn color="primary" @click="cancel" outline>Batal</v-btn>
                <v-btn color="primary" @click="save">Simpan</v-btn>
            </v-flex>
        </v-card-actions>

        <v-snackbar
            v-model="snackbar"
            color="success"
            multi-line
            :timeout="6000"
            top
            >
            Jurnal berhasil disimpan
            <v-btn
                dark
                flat
                @click="closeMe"
            >
                Tutup
            </v-btn>
        </v-snackbar>
    </v-card>
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
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        'common-tag' : httpVueLoader('../../common/components/common-tag.vue?t='+rnd),
        'common-disc' : httpVueLoader('../../common/components/common-disc.vue?t='+rnd),
        'new' : httpVueLoader('./finance-journal-general-new.vue?t='+rnd)
    },

    data () {
        return {
            imageName: '',
            imageUrl: '',
            imageFile: ''
        }
    },

    computed : {
        journal_number () {
            return this.$store.state.generalNew.journal_number
        },

        edit () {
            return this.$store.state.generalNew.edit
        },

        snackbar : {
            get () { return this.$store.state.generalNew.snackbar },
            set (v) { this.$store.commit('generalNew/set_object', ['snackbar', v]) }
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        setObject(x, y) {
            return this.$store.commit('generalNew/set_object', 
                    [x, y])
        },

        setObjects(x, y) {
            for (let v of y)
                this.setObject(v, x[v])
        },

        save () {
            this.$store.dispatch('generalNew/save').then(res => {
                this.snackbar = true
            })
        },

        cancel () {
            if (!!this.$store.state.generalNew.dialog_new) {
                this.$store.commit("generalNew/set_common", ["dialog_new", false])
            } else {
                window.location = "../list"
            }
        },

        closeMe () {
            this.snackbar = false 
            window.close()
        }
    },

    mounted () {
        
    },

    watch : {
    }
}