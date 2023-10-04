<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="1000px"
        :fullscreen="$vuetify.breakpoint.smAndDown"
        transition="dialog-transition"
        content-class="dialog-new"
    >
        <v-card>
            <v-card-title primary-title class="py-2 blue">
                <v-btn flat color="primary" class="ma-0 btn-icon mr-2 hidden-md-and-up" @click="dialog=!dialog" style="float:left">
                    <v-icon class="white--text" medium>arrow_back</v-icon>
                </v-btn>

                <h3 class="headline white--text pt-1" v-show="!edit">{{vendor_prospect=='Y'?'PROSPEK':'CUSTOMER'}} BARU</h3>
                <h3 class="headline white--text pt-1" v-show="edit">UBAH DATA {{vendor_prospect=='Y'?'PROSPEK':'CUSTOMER'}}</h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12 sm6 md6 :class="{'pr-4':$vuetify.breakpoint.mdAndUp,'pr-2':$vuetify.breakpoint.smOnly}">
                        <v-layout row wrap>
                            <v-flex xs12>
                                <v-text-field
                                    label="Nama Vendor"
                                    v-model="vendor_name"
                                    :error="vendor_name==''"
                                    :error-count="vendor_name==''?1:0"
                                    :error-messages="vendor_name==''?['Wajib diisi']:[]"
                                >
                                    <template slot="append-outer">
                                        <v-btn color="primary" small class="btn-icon" v-show="show_similar" @click="show_me_similar"><v-icon>search</v-icon></v-btn>
                                    </template>
                                </v-text-field>
                            </v-flex>                            

                            <v-flex xs12>
                                <v-text-field
                                    label="Alamat Vendor"
                                    v-model="vendor_address"
                                    prepend-inner-icon="place"
                                    :error="false"
                                    :error-count="0"
                                    :error-messages="[]"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs12 sm6 md6 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
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
                                    :error="!selected_province"
                                    :error-count="!selected_province?1:0"
                                    :error-messages="!selected_province?['Pilih salah satu']:[]"
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

                            <v-flex xs12 sm6 md6 :class="{'pl-2':$vuetify.breakpoint.smAndUp}">
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

                            <v-flex xs12 sm6 md6 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
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

                            <v-flex xs12 sm6 md6 :class="{'pl-2':$vuetify.breakpoint.smAndUp}">
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
                            
                            <v-flex xs12 sm4 md4 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
                                <v-text-field
                                    label="Kode Pos"
                                    v-model="vendor_postcode"
                                    placeholder=""
                                ></v-text-field>

                            </v-flex>

                            <!-- <v-flex xs12 sm6 md6>
                                <v-text-field
                                    label="Telepon"
                                    v-model="vendor_phone"
                                    prepend-inner-icon="phonelink_ring"
                                    placeholder="08X-"
                                ></v-text-field>

                            </v-flex> -->

                            <v-flex xs12 sm8 md8>
                                <v-text-field
                                    label="Email"
                                    v-model="vendor_email"
                                    prepend-inner-icon="email"
                                    placeholder="ex : someone@gmail.com"
                                    :error="!email_validate"
                                    :error-count="email_validate?0:1"
                                    :error-messages="['Email harus diisi, Format email harus benar']"
                                ></v-text-field>
                            </v-flex>

                            <v-flex xs12 sm12 md12>
                                <master-vendor-new-addresses></master-vendor-new-addresses>
                            </v-flex>

                            <!-- <v-flex xs12 sm12 md12>
                                <v-select
                                    label="Sales"
                                    v-model="selected_staff"
                                    :items="staffs"
                                    return-object
                                    clearable
                                    item-text="staff_name"
                                    item-value="staff_id"
                                    placeholder="Pilih Sales, atau kosongi saja"
                                    prepend-inner-icon="person"
                                    >
                                </v-select>
                            </v-flex> -->

                            <!-- <v-flex xs4>
                                <common-datepicker
                                    label="Tanggal Bergabung"
                                    :date="vendor_join_date"
                                    data="0"
                                    @change="change_join_date"
                                    classs="ml-1"
                                    hints=""
                                    :details="true"
                                    v-if="dialog"
                                    :solo="false"
                                ></common-datepicker>
                            </v-flex> -->

                        </v-layout>
                    </v-flex>
                    <v-flex xs12 sm6 md6 :class="{'pl-4':$vuetify.breakpoint.mdAndUp,'pl-2':$vuetify.breakpoint.smOnly}">
                        <v-layout row wrap>
                            <!-- <v-flex xs4>
                                <v-select
                                    :items="vendor_types"
                                    v-model="selected_vendor_type"
                                    label="Tipe Vendor"
                                    return-object
                                    item-text="text"
                                    item-id="id"
                                ></v-select>
                            </v-flex> -->
                            <v-flex xs12>
                                <v-text-field
                                    label="Nomor NPWP"
                                    v-model="vendor_npwp"
                                ></v-text-field>
                            </v-flex>
                            <v-flex xs8 pr-3>
                                <v-text-field
                                    label="Nama PIC"
                                    v-model="vendor_pic_name"
                                    :error="vendor_pic_name==''"
                                    :error-count="vendor_pic_name==''?1:0"
                                    :error-messages="vendor_pic_name==''?['Wajib diisi']:[]"
                                ></v-text-field>
                            </v-flex>
                            <v-flex xs4>
                                <v-text-field
                                    :disabled="selected_vendor_type_id=='N'"
                                    label="No HP PIC"
                                    v-model="vendor_pic_phone"
                                    prefix="+62"
                                ></v-text-field>
                            </v-flex>
                            <!-- <v-flex xs12 sm6 md6 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
                                <v-select
                                    :items="vendor_levels"
                                    v-model="selected_vendor_level"
                                    return-object
                                    item-text="M_VendorLevelName"
                                    item-value="M_VendorLevelID"
                                    label="Jenis Vendor"
                                >
                                </v-select>
                            </v-flex> -->

                            <v-flex xs12>
                                <v-textarea
                                    label="Catatan"
                                    placeholder=" "
                                    rows="2"
                                    v-model="vendor_note"
                                ></v-textarea>
                            </v-flex>

                        </v-layout>

                        <!-- PHONES SECTION -->
                        <master-vendor-new-phones></master-vendor-new-phones>
                        <div class="mb-2"></div>
                        <master-vendor-new-banks></master-vendor-new-banks>
                        <!-- END OF PHONES SECTION -->

                    </v-flex>
                </v-layout>
                
            </v-card-text>

            <v-card-actions>
                <v-btn color="primary" flat @click="dialog=!dialog">Batal</v-btn>                
                <v-spacer></v-spacer>
                <v-btn color="primary" @click="save" 
                    :disabled="vendor_name == ''">Simpan</v-btn>                
            </v-card-actions>
        </v-card>
        <vendor-mp></vendor-mp>
        <master-vendor-similar></master-vendor-similar>
    </v-dialog>
</template>
<style>
.dialog-new {
    max-height: 98% !important;
}
.dialog-new .input-dense .v-input__control {
    min-height: 36px !important;
}
.dialog-new .v-text-field.v-text-field--solo .v-input__control {
    min-height: 36px;
    padding: 0;
}


</style>
<script>
module.exports = {
    components : {
        'vendor-mp' : httpVueLoader('./master-vendor-mp.vue'),
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        "master-vendor-similar" : httpVueLoader("./master-vendor-similar.vue?t=as1d"),
        "master-vendor-new-phones" : httpVueLoader("./master-vendor-new-phones.vue?t=a1sd"),
        "master-vendor-new-banks" : httpVueLoader("./master-vendor-new-banks.vue?t=a1sd"),
        "master-vendor-new-addresses" : httpVueLoader("./master-vendor-new-addresses.vue?t=as1dasdd")
    },

    data () {
        return {
            
        }
    },

    computed : {
        URL () {
            return this.$store.state.vendor_new.URL
        },

        edit () {
            return this.$store.state.vendor_new.edit
        },

        dialog : {
            get () { return this.$store.state.vendor_new.dialog_new },
            set (v) { this.$store.commit('vendor_new/set_common', ['dialog_new', v]) }
        },

        search_city : {
            get () { return this.$store.state.vendor_new.search_city },
            set (v) { this.$store.commit('vendor_new/set_common', ["search_city", v]) }
        },

        selected_city : {
            get () { return this.$store.state.vendor_new.selected_city },
            set (v) { 
                this.$store.commit('vendor_new/set_selected_city', v) 
                this.$store.dispatch('vendor_new/search_district', {})
            }
        },

        cities () {
            return this.$store.state.vendor_new.cities
        },

        vendor_name : {
            get () { return this.$store.state.vendor_new.vendor_name },
            set (v) { this.$store.commit('vendor_new/set_common', ['vendor_name', v]) }
        },

        vendor_address : {
            get () { return this.$store.state.vendor_new.vendor_address },
            set (v) { this.$store.commit('vendor_new/set_common', ['vendor_address', v]) }
        },

        vendor_phone : {
            get () { return this.$store.state.vendor_new.vendor_phone },
            set (v) { this.$store.commit('vendor_new/set_common', ['vendor_phone', v]) }
        },

        vendor_note : {
            get () { return this.$store.state.vendor_new.vendor_note },
            set (v) { this.$store.commit('vendor_new/set_common', ['vendor_note', v]) }
        },

        vendor_email : {
            get () { return this.$store.state.vendor_new.vendor_email },
            set (v) { this.$store.commit('vendor_new/set_common', ['vendor_email', v]) }
        },

        vendor_postcode : {
            get () { return this.$store.state.vendor_new.vendor_postcode },
            set (v) { this.$store.commit('vendor_new/set_common', ['vendor_postcode', v]) }
        },

        vendor_pic_name : {
            get () { return this.$store.state.vendor_new.vendor_pic_name },
            set (v) { this.$store.commit('vendor_new/set_common', ['vendor_pic_name', v]) }
        },

        vendor_pic_phone : {
            get () { return this.$store.state.vendor_new.vendor_pic_phone },
            set (v) { this.$store.commit('vendor_new/set_common', ['vendor_pic_phone', v]) }
        },

        vendor_npwp : {
            get () { return this.$store.state.vendor_new.vendor_npwp },
            set (v) { this.$store.commit('vendor_new/set_common', ['vendor_npwp', v]) }
        },

        vendor_types () {
            return this.$store.state.vendor_new.vendor_types
        },

        selected_vendor_type : {
            get () { return this.$store.state.vendor_new.selected_vendor_type },
            set (v) { this.$store.commit('vendor_new/set_selected_vendor_type', v) }
        },

        provinces () {
            return this.$store.state.vendor_new.provinces
        },

        selected_province : {
            get () { return this.$store.state.vendor_new.selected_province },
            set (v) { 
                this.$store.commit('vendor_new/set_selected_province', v)
                this.$store.dispatch('vendor_new/search_city', {})
            }
        },

        districts () {
            return this.$store.state.vendor_new.districts
        },

        selected_district : {
            get () { return this.$store.state.vendor_new.selected_district },
            set (v) { 
                this.$store.commit('vendor_new/set_selected_district', v)
                this.$store.dispatch('vendor_new/search_village', {})
            }
        },

        villages () {
            return this.$store.state.vendor_new.villages
        },

        selected_village : {
            get () { return this.$store.state.vendor_new.selected_village },
            set (v) { 
                this.$store.commit('vendor_new/set_selected_village', v)
            }
        },

        email_validate (x) {
            return true
            const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
            return re.test(String(this.vendor_email).toLowerCase())
        },

        staffs () {
            return this.$store.state.vendor_new.staffs
        },

        selected_staff : {
            get () { return this.$store.state.vendor_new.selected_staff },
            set (v) { 
                this.$store.commit('vendor_new/set_selected_staff', v)
            }
        },

        vendor_prospect () {
            return this.$store.state.vendor_new.vendor_prospect
        },

        show_similar : {
            get () { 
                if (this.edit) return false
                if (this.vendor_name.length < 5) return false
                if (this.$store.state.vendor_new.similars.length<1) return false
                return this.$store.state.vendor_new.show_similar 
            },
            set (v) { this.$store.commit('vendor_new/set_common', ['show_similar', v]) }
        },

        selected_vendor_type_id () {
            if (this.selected_vendor_type)
                return this.selected_vendor_type.id
            return ''
        }
    },

    methods : {
        thr_search: _.debounce( function () {            
            this.$store.commit("vendor_new/set_common", ['search_city_status', 1]) // LOADING
            this.$store.dispatch("vendor_new/search_city", [])
        }, 700),

        thr_search2: _.debounce( function () {            
            this.$store.dispatch("vendor_new/search_similar")
        }, 700),

        save () {
            this.$store.dispatch('vendor_new/save')
        },

        show_me_similar () {
            this.$store.commit('vendor_new/set_common', ['dialog_similar', true])
        }
    },

    mounted () {
        this.$store.dispatch('vendor_new/search_province', {})
        this.$store.dispatch('vendor_new/search_staff', {})
        this.$store.dispatch('vendor_new/search_bank', {})
        // this.$store.dispatch('vendor_new/search_vendor_level', {})
        // this.$store.dispatch('vendor_new/search_mp')
    },

    watch : {
        search_city(val, old) { 
            
            if (this.$store.state.vendor_new.edit) return
            if (val == null || typeof val == 'undefined') val = ""
            if (val == old ) return
            if (this.$store.state.vendor_new.search_city_status == 1 ) return  
            
            this.$store.commit("vendor_new/set_common", ["search_city", val])
            this.thr_search()
        },

        vendor_name(val, old) {
            this.show_similar = false
            if (this.edit) return
            if (val == null || typeof val == 'undefined') val = ""
            if (val == old ) return
            if (this.$store.state.system.search_status == 1 ) return  
            if (val.length < 5) return

            this.thr_search2()
        }
    }
}
</script>