<template>
    <v-dialog
        v-model="dialog"
        persistent
        max-width="1000px"
        transition="dialog-transition"
    >
        <v-card>
            <v-card-title primary-title class="blue white--text pt-2 pb-2">
                <h3 class="headline">{{ edit ? 'UBAH' : 'TAMBAH' }} DATA USER</h3>
                <v-spacer></v-spacer>
                <v-icon large class="white--text">group</v-icon>
            </v-card-title>

            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs6 pr-4>
                        <v-layout row wrap>
                            <v-flex xs12>
                                <v-text-field
                                    label="Username"
                                    v-model="user_name"
                                    :disabled="edit"
                                    :hide-details="user_check"
                                    :error="(error_check || user_name.length == 0) && !edit"
                                    :error-count="error_check || user_name.length == 0?1:0"
                                    :error-messages="user_name.length > 0 ? ['Ooops, aku udah ada yang punya, silahkan pilih yang lain :)'] : ['Jangan lupa diisi ya username nya..']"
                                >
                                    <template slot="append">
                                        <v-icon class="green--text" v-show="!error_check">how_to_reg</v-icon>
                                        <v-icon class="red--text" v-show="error_check">person</v-icon>
                                    </template>

                                    
                                </v-text-field>
                                <v-progress-linear :indeterminate="true" v-show="user_check" class="mt-1 mb-1"></v-progress-linear>
                            </v-flex>

                            <v-flex xs12>
                                <v-text-field
                                    label="Nama Lengkap"
                                    v-model="user_full_name"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs12>
                                <v-text-field
                                    label="Alamat Lengkap"
                                    v-model="user_address"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs6 pr-1>
                                <v-text-field
                                    label="HP"
                                    v-model="user_phone"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs6 pl-1>
                                <v-text-field
                                    label="Email"
                                    v-model="user_email"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs6>
                                <v-text-field
                                    label="Tanggal Gabung"
                                    v-model="user_join"
                                ></v-text-field>
                            </v-flex>
                            <v-flex xs6>
                                &nbsp;
                            </v-flex>
                        </v-layout>
                    </v-flex>

                    <v-flex xs6 pl-4>
                        <v-layout row wrap>
                            <v-flex xs4 mt-2 mb-2>
                                <v-checkbox v-model="change_password" label="Ubah Password" hide-details class="pt-0 mt-0" v-show="edit"></v-checkbox>
                                <div v-show="!edit" class="blue--text">Masukkan Password</div>
                            </v-flex>
                            <v-flex xs8 mt-2 mb-2>
                                <v-divider class="pt-2 mt-2"></v-divider>
                            </v-flex>

                            <v-flex xs6>
                                <v-text-field
                                    label="Password"
                                    type="password"
                                    :disabled="(!change_password && edit)"
                                    v-model="user_pass"
                                    :error="error_password"
                                    :error-count="error_password ? 1 : 0"
                                    :error-messages="error_password ? ['Minimal 5 huruf, HARUS terdapat angka DAN perpaduan huruf besar kecil'] : []"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs6 pl-2>
                                <v-text-field
                                    label="Password (konfirmasi)"
                                    type="password"
                                    :disabled="(!change_password && edit)"
                                    v-model="conf_pass"
                                    :error="error_password_confirm"
                                    :error-count="error_password_confirm ? 1 : 0"
                                    :error-messages="error_password_confirm ? ['Password tidak sama'] : []"
                                ></v-text-field>
                            </v-flex>

                            <!-- <v-flex xs4 mt-2>
                                <div class="subheading">Password Lama</div>
                            </v-flex>
                            <v-flex xs8 mt-2>
                                <v-divider class="pt-2 mt-2"></v-divider>
                            </v-flex>

                            <v-flex xs6>
                                <v-text-field
                                    label="Password Baru (konfirmasi)"
                                    type="password"
                                ></v-text-field>
                            </v-flex> -->
                        </v-layout>
                    </v-flex>
                </v-layout>
                
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="primary" @click="dialog=!dialog" flat>Tutup</v-btn>
                
                <v-btn color="primary" @click="save" :disabled="!btn_save_enabled">Simpan</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
module.exports = {
    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue')
    },

    data () {
        return {
            change_password: false,
            conf_pass: ''
        }
    },

    computed : {
        edit () {
            return this.$store.state.user_new.edit
        },

        dialog : {
            get () { return this.$store.state.user_new.dialog_user },
            set (v) { this.$store.commit('user_new/set_common', ['dialog_user', v]) }
        },

        user_name: {
            get () { return this.$store.state.user_new.user_name },
            set (v) { this.$store.commit('user_new/set_common', ['user_name', v]) }
        },

        user_full_name: {
            get () { return this.$store.state.user_new.user_full_name },
            set (v) { this.$store.commit('user_new/set_common', ['user_full_name', v]) }
        },

        user_address: {
            get () { return this.$store.state.user_new.user_address },
            set (v) { this.$store.commit('user_new/set_common', ['user_address', v]) }
        },

        user_phone: {
            get () { return this.$store.state.user_new.user_phone },
            set (v) { this.$store.commit('user_new/set_common', ['user_phone', v]) }
        },

        user_email: {
            get () { return this.$store.state.user_new.user_email },
            set (v) { this.$store.commit('user_new/set_common', ['user_email', v]) }
        },

        user_join: {
            get () { return this.$store.state.user_new.user_join },
            set (v) { this.$store.commit('user_new/set_common', ['user_join', v]) }
        },

        user_pass: {
            get () { return this.$store.state.user_new.user_pass },
            set (v) { this.$store.commit('user_new/set_common', ['user_pass', v]) }
        },

        old_pass: {
            get () { return this.$store.state.user_new.old_pass },
            set (v) { this.$store.commit('user_new/set_common', ['old_pass', v]) }
        },

        error_password_confirm () {
            if (this.user_pass != this.conf_pass)
                return true
            return false
        },

        error_password () {
            if (this.user_pass == '' && this.conf_pass == '')
                return false

            if (this.user_pass < 5)
                return true

            if (!this.user_pass.match(/[a-z]/) || 
                !this.user_pass.match(/[A-Z]/) ||
                !this.user_pass.match(/[0-9]/))
                return true

            return false
        },

        btn_save_enabled () {
            if (this.user_check)
                return false
            if (this.error_password || this.error_password_confirm || this.error_check)
                return false
            if (this.user_name == '' || this.user_full_name == '')
                return false
            return true
        },

        append_user_icon () {
            return 'person'
        },

        user_check : {
            get () { return this.$store.state.user_new.user_check },
            set (v) { this.$store.commit('user_new/set_common', ['user_check', v]) }
        },

        error_check : {
            get () { return this.$store.state.user_new.error_check },
            set (v) { this.$store.commit('user_new/set_common', ['error_check', v]) }
        }
    },

    methods : {
        save () {
            this.$store.dispatch('user_new/save')
        },

        thr_search: _.debounce( function () {            
            // this.$store.commit("user_new/set_common", ['search_status', 1]) // LOADING
            this.$store.dispatch("user_new/check_user", [])
        }, 700)
    },

    watch : {
        change_password (v, o) {                    
        },

        user_name(val, old) {
            if (this.edit) return
            if (val == null || typeof val == 'undefined') val = ""
            if (val == old ) return
            if (this.$store.state.user_new.search_status == 1 ) return  
            
            // this.$store.commit("user_new/set_common", ["user_name", val])
            this.thr_search()
        }
    }
}
</script>