<template>
    <v-card>
        <!-- <v-card-title class="cyan lighten-4 py-2">
            <v-layout row wrap>
                <v-flex xs6>
                    <h3 class="display-1 font-weight-light zalfa-text-pink">SYSTEM SETTINGS</h3>
                </v-flex>
                <v-flex xs6 class="text-xs-right">
                    <v-btn color="primary" @click="save_setting" class="ma-0">Simpan</v-btn>
                </v-flex>
            </v-layout>
        </v-card-title> -->

        <v-card-text class="pt-2">
            <v-layout row wrap>
                <v-flex xs12 sm4 md3 :class="{'pr-4':$vuetify.breakpoint.smAndUp}">
                    <v-layout row wrap>
                        <v-flex xs12>
                            <v-text-field
                                label="Nama Perusahaan"
                                v-model="company_name"
                            ></v-text-field>
                            <v-text-field
                                label="Nama Perusahaan"
                                v-model="company_address"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12>
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
                        </v-flex>

                        <v-flex xs12>
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
                        </v-flex>

                        <v-flex xs12>
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
                        </v-flex>

                        <v-flex xs12>
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
                        </v-flex>

                        <v-flex xs12>
                            <v-text-field
                                label="Telepon Perusahaan"
                                v-model="company_phone"
                            ></v-text-field>
                        </v-flex>
                    </v-layout>
                </v-flex>
                
                <v-flex xs12 sm4 md3 :class="{'pr-4':$vuetify.breakpoint.smAndUp}">
                    <v-layout row wrap>
                        <v-flex xs12>
                            <v-text-field
                                label="Target Omzet Mingguan"
                                suffix="Rp"
                                reverse
                                v-model="target_weekly"
                                :mask="money_mask"
                            ></v-text-field>

                            <v-text-field
                                label="Jangka Waktu Pembayaran"
                                prefix="Jam"
                                reverse
                                v-model="payment_expired"
                                persistent-hint
                            >
                            </v-text-field>
                        </v-flex>
                        <v-flex xs12>
                            <v-btn color="primary" @click="save_setting" class="ma-0" 
                            :block="$vuetify.breakpoint.smAndDown"
                            :large="$vuetify.breakpoint.smAndDown">Simpan</v-btn>
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

<style>
.v-card__title.cyan.lighten-4 {
    border-bottom: solid 2px cyan !important;
    border-bottom-color: cyan !important;
}
</style>

<script>
module.exports = {
    computed : {
        
        company_name : {
            get () { return this.$store.state.setting.company_name },
            set (v) { this.$store.commit('setting/set_common', ['company_name', v]) }
        },

        company_address : {
            get () { return this.$store.state.setting.company_address },
            set (v) { this.$store.commit('setting/set_common', ['company_address', v]) }
        },

        company_phone : {
            get () { return this.$store.state.setting.company_phone },
            set (v) { this.$store.commit('setting/set_common', ['company_phone', v]) }
        },

        payment_expired : {
            get () { return this.$store.state.setting.payment_expired },
            set (v) { this.$store.commit('setting/set_common', ['payment_expired', v]) }
        },

        target_weekly : {
            get () { return this.$store.state.setting.target_weekly },
            set (v) { this.$store.commit('setting/set_common', ['target_weekly', v]) }
        },

        money_mask () {
            let y = []
            for (let i=0;i<this.target_weekly.length;i++) {
                y.push('#')
                if (i%3==2)
                    y.push(',')
            }
            y.reverse()

            if (y[0]==',')
                y.shift()
            
            return y.join('')+'#'
        },

        selected_city : {
            get () { return this.$store.state.setting.selected_city },
            set (v) { 
                this.$store.commit('setting/set_selected_city', v) 
                this.$store.dispatch('setting/search_district')
            }
        },

        cities () {
            return this.$store.state.setting.cities
        },

        provinces () {
            return this.$store.state.setting.provinces
        },

        selected_province : {
            get () { return this.$store.state.setting.selected_province },
            set (v) { 
                this.$store.commit('setting/set_selected_province', v)
                this.$store.dispatch('setting/search_city')
            }
        },

        districts () {
            return this.$store.state.setting.districts
        },

        selected_district : {
            get () { return this.$store.state.setting.selected_district },
            set (v) { 
                this.$store.commit('setting/set_selected_district', v)
                this.$store.dispatch('setting/search_village')
            }
        },

        villages () {
            return this.$store.state.setting.villages
        },

        selected_village : {
            get () { return this.$store.state.setting.selected_village },
            set (v) { 
                this.$store.commit('setting/set_selected_village', v)
            }
        },

        snackbar : {
            get () { return this.$store.state.setting.snackbar },
            set (v) { this.$store.commit('setting/set_common', ['snackbar', v]) }
        },

        snackbar_text () {
            return this.$store.state.setting.snackbar_text
        }
    },

    methods : {
        save_setting () {
            this.$store.dispatch('setting/save_setting')
        }
    },

    mounted () {
        this.$store.dispatch('setting/search')
    }
}
</script>
