<template>
    
    <v-card>
        <v-card-title class="pb-0">
            <v-layout row wrap>
                <v-flex xs6>
                    <h3 class="display-1 font-weight-light zalfa-text-pink">Profile</h3>
                </v-flex>
                <v-flex xs6 class="text-xs-right">
                    <v-icon class="blue--text" x-large style="float:right">account_box</v-icon>
                    <h4 class="caption">Tipe Akun</h4>
                    <h3 class="title blue--text mb-4">{{ user_level }}</h3>
                </v-flex>
                
            </v-layout>
            
        </v-card-title>

        <v-card-text class="pt-0">

            <!-- <v-tabs v-model="selected_tab" dark slider-color="yellow" color="cyan">
                <v-tab v-for="(i, n) of tabs" :key="i">
                    {{ i }}
                </v-tab>
                <v-tab-item v-for="(i, n) of tabs" :key="i">
                    <v-card flat>
                    <v-card-text>{{ i }}</v-card-text>
                    </v-card>
                </v-tab-item>
            </v-tabs> -->

            <v-layout row wrap class="cyan">
                <v-flex xs2 pr-1>
                    <v-btn class="ma-0 mt-2 ml-2" light :color="selected_tab!=1?'cyan':'white'" block depressed @click="selected_tab=1">Profile</v-btn>
                </v-flex>
                <v-flex xs2>
                    <v-btn class="ma-0 mt-2 mr-2" light :color="selected_tab!=2?'cyan':'white'" block depressed @click="selected_tab=2">Password</v-btn>
                </v-flex>
            </v-layout>

            <v-layout row wrap v-show="selected_tab == 1">
                <v-flex xs3 class="pa-2">
                    
                    <!-- <v-text-field
                        label="Tipe Akun"
                        v-model="user_level"
                        readonly
                        prepend-inner-icon="account_box"
                    >
                    </v-text-field> -->

                    <v-text-field
                        label="Nama Lengkap"
                        v-model="user_fullname"
                    ></v-text-field>

                    <v-text-field
                        label="Alamat Lengkap"
                        v-model="user_fulladdress"
                        multiline
                    ></v-text-field>

                    <v-autocomplete
                        label="Propinsi"
                        v-model="selected_province"
                        :items="provinces"
                        auto-select-first
                        return-object
                        clearable
                        item-text="M_ProvinceName"
                        item-value="M_ProvinceID"
                        placeholder="Pilih Propinsi"
                        >
                        <template
                            slot="item"
                            slot-scope="{ item }"
                            >
                            <v-list-tile-content>
                            <v-list-tile-title v-text="item.M_ProvinceName"></v-list-tile-title>
                            <!-- <v-list-tile-sub-title v-text="getAddress(item)"></v-list-tile-sub-title> -->
                            </v-list-tile-content>
                        </template>

                    </v-autocomplete>
                

                
                    <v-autocomplete
                        label="Kota"
                        v-model="selected_city"
                        :items="cities"
                        auto-select-first
                        return-object
                        clearable
                        item-text="M_CityName"
                        item-value="M_CityID"
                        placeholder="Pilih Kota"
                        :disabled="selected_province == null"
                        >
                        <template
                            slot="item"
                            slot-scope="{ item }"
                            >
                            <v-list-tile-content>
                            <v-list-tile-title v-text="item.M_CityName"></v-list-tile-title>
                            <!-- <v-list-tile-sub-title v-text="getAddress(item)"></v-list-tile-sub-title> -->
                            </v-list-tile-content>
                        </template>

                    </v-autocomplete>
                
                    <v-autocomplete
                        label="Kecamatan"
                        v-model="selected_district"
                        :items="districts"
                        auto-select-first
                        return-object
                        clearable
                        item-text="M_DistrictName"
                        item-value="M_DistrictID"
                        placeholder="Pilih Kecamatan"
                        :disabled="selected_city == null"
                        >
                        <template
                            slot="item"
                            slot-scope="{ item }"
                            >
                            <v-list-tile-content>
                            <v-list-tile-title v-text="item.M_DistrictName"></v-list-tile-title>
                            <!-- <v-list-tile-sub-title v-text="getAddress(item)"></v-list-tile-sub-title> -->
                            </v-list-tile-content>
                        </template>

                    </v-autocomplete>
                
                    <v-autocomplete
                        label="Kelurahan"
                        v-model="selected_village"
                        :items="villages"
                        auto-select-first
                        return-object
                        clearable
                        item-text="M_KelurahanName"
                        item-value="M_KelurahanID"
                        placeholder="Pilih Kelurahan"
                        :disabled="selected_district == null"
                        >
                        <template
                            slot="item"
                            slot-scope="{ item }"
                            >
                            <v-list-tile-content>
                            <v-list-tile-title v-text="item.M_KelurahanName"></v-list-tile-title>
                            <!-- <v-list-tile-sub-title v-text="getAddress(item)"></v-list-tile-sub-title> -->
                            </v-list-tile-content>
                        </template>
                    </v-autocomplete>

                    <v-text-field
                        label="Kode Pos"
                        v-model="user_postcode"
                    ></v-text-field>
                </v-flex>

                <v-flex xs3 class="pa-2 pl-5">
                    <v-text-field
                        label="No HP"
                        v-model="user_phone"
                    ></v-text-field>

                    <v-text-field
                        label="Email"
                        v-model="user_email"
                    ></v-text-field>

                    <v-layout row wrap>
                        <v-flex xs12 class="text-xs-right">
                            <v-btn color="primary" class="mr-0" @click="save_profile">Simpan</v-btn>
                        </v-flex>
                    </v-layout>
                </v-flex>
            </v-layout>

            <v-layout row wrap v-show="selected_tab==2">
                

                <v-flex xs3 class="pa-2">
                    <v-alert
                        :value="alert_password"
                        type="error"
                        dismissible
                        >
                        Password lama tidak sesuai !
                    </v-alert>

                    <v-text-field
                        label="Password Lama"
                        type="password"
                        hint="Masukkan password lama anda"
                        persistent-hint
                        v-model="user_old_password"
                    ></v-text-field>
                    <h4 class="subheading blue--text mt-5">Ganti Password</h4>
                    <v-divider class="mb-2"></v-divider>
                    <v-text-field
                        label="Password Baru"
                        type="password"
                        v-model="user_password"
                        :error="error_password"
                        :error-count="error_password ? 1 : 0"
                        :error-messages="error_password ? ['Minimal 5 huruf, HARUS terdapat angka DAN perpaduan huruf besar kecil'] : []"
                    ></v-text-field>
                    <v-text-field
                        label="Konfirmasi"
                        type="password"
                        v-model="user_password_confirm"
                        :error="error_password_confirm"
                        :error-count="error_password_confirm ? 1 : 0"
                        :error-messages="error_password_confirm ? ['Password tidak sama'] : []"
                    ></v-text-field>

                    <v-layout row wrap>
                        <v-flex xs12 class="text-xs-right">
                            <v-btn color="primary" class="mr-0" @click="save_password">Simpan</v-btn>
                        </v-flex>
                    </v-layout>
                </v-flex>
            </v-layout>
        </v-card-text>

        <v-snackbar
            v-model="snackbar"
            multi-line
            :timeout="6000"
            top
            vertical
            >
            {{ snackbar_text }}
            <v-btn
                color="pink"
                flat
                @click="snackbar = false"
            >
                Tutup
            </v-btn>
        </v-snackbar>
    </v-card>
</template>

<script>
module.exports = {
    computed : {
        user_fullname : {
            get () { return this.$store.state.profile.user_fullname },
            set (v) { this.$store.commit('profile/set_common', ['user_fullname', v]) }
        },

        user_fulladdress : {
            get () { return this.$store.state.profile.user_fulladdress },
            set (v) { this.$store.commit('profile/set_common', ['user_fulladdress', v]) }
        },

        user_phone : {
            get () { return this.$store.state.profile.user_phone },
            set (v) { this.$store.commit('profile/set_common', ['user_phone', v]) }
        },

        user_email : {
            get () { return this.$store.state.profile.user_email },
            set (v) { this.$store.commit('profile/set_common', ['user_email', v]) }
        },

        user_postcode : {
            get () { return this.$store.state.profile.user_postcode },
            set (v) { this.$store.commit('profile/set_common', ['user_postcode', v]) }
        },

        user_level () {
            return this.$store.state.profile.user_level
        },

        user_password : {
            get () { return this.$store.state.profile.user_password },
            set (v) { this.$store.commit('profile/set_common', ['user_password', v]) }
        },

        user_password_confirm : {
            get () { return this.$store.state.profile.user_password_confirm },
            set (v) { this.$store.commit('profile/set_common', ['user_password_confirm', v]) }
        },

        user_old_password : {
            get () { return this.$store.state.profile.user_old_password },
            set (v) { this.$store.commit('profile/set_common', ['user_old_password', v]) }
        },

        tabs () {
            return this.$store.state.profile.tabs
        },
        
        selected_tab : {
            get () { return this.$store.state.profile.selected_tab },
            set (v) { this.$store.commit('profile/set_common', ['selected_tab', v]) }
        },

        error_password_confirm () {
            if (this.user_password != this.user_password_confirm)
                return true
            return false
        },

        error_password () {
            if (this.user_password == '' && this.user_password_confirm == '')
                return false

            if (this.user_password.length < 5)
                return true

            if (!this.user_password.match(/[a-z]/) || 
                !this.user_password.match(/[A-Z]/) ||
                !this.user_password.match(/[0-9]/))
                return true

            return false
        },

        selected_city : {
            get () { return this.$store.state.profile.selected_city },
            set (v) { 
                this.$store.commit('profile/set_selected_city', v) 
                this.$store.dispatch('profile/search_district', {})
            }
        },

        cities () {
            return this.$store.state.profile.cities
        },

        provinces () {
            return this.$store.state.profile.provinces
        },

        selected_province : {
            get () { return this.$store.state.profile.selected_province },
            set (v) { 
                this.$store.commit('profile/set_selected_province', v)
                this.$store.dispatch('profile/search_city', {})
            }
        },

        districts () {
            return this.$store.state.profile.districts
        },

        selected_district : {
            get () { return this.$store.state.profile.selected_district },
            set (v) { 
                this.$store.commit('profile/set_selected_district', v)
                this.$store.dispatch('profile/search_village', {})
            }
        },

        villages () {
            return this.$store.state.profile.villages
        },

        selected_village : {
            get () { return this.$store.state.profile.selected_village },
            set (v) { 
                this.$store.commit('profile/set_selected_village', v)
            }
        },

        snackbar : {
            get () { return this.$store.state.profile.snackbar },
            set (v) { this.$store.commit('profile/set_common', ['snackbar', v]) }
        },

        snackbar_text () {
            return this.$store.state.profile.snackbar_text
        },

        alert_password () {
            return this.$store.state.profile.alert_password
        }
    },

    methods : {
        save_profile () {
            this.$store.dispatch('profile/save_profile')
        },

        save_password () {
            this.$store.dispatch('profile/save_password')
        }
    },

    mounted () {
        this.$store.dispatch('profile/search')
        // this.$store.dispatch('profile/search_province', {})
    }
}
</script>
