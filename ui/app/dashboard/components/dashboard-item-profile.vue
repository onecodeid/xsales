<template>
    <v-card height="192px">
        <v-card-text class="pa-2">
            <v-layout row wrap>
                <v-flex xs4 sm2 md2 pr-2>
                    <v-card>
                        <v-card-text class="pa-1">
                            <v-img
                                src="../assets/img/noPhotoAvailable.jpg"
                                aspect-ratio="0.8"
                                class="grey lighten-2"
                            >
                        </v-card-text>
                    </v-card>
                </v-flex>
                <v-flex xs8 sm4 md3>
                    <div class="headline">{{ profile.user_fullname }} <span class="hidden-sm-and-up body-2">— {{ profile.level_name }}</span></div>
                    <div class="hidden-xs-only">— {{ profile.level_name }}</div>

                    <v-layout row wrap>
                        <v-flex xs12 class="hidden-sm-and-up ">
                            <v-divider class="mt-1 mb-1"></v-divider>
                            <h4>Alamat</h4>
                            <div>{{ profile.user_fulladdress }}</div>
                            <div>Kel. {{ profile.kelurahan_name }}, Kec. {{ profile.district_name }}</div>
                            <div>Kota {{ profile.city_name }} — {{ profile.province_name }}</div>
                            <!-- <div><a href="#" @click="edit_profile">edit</a></div> -->
                        </v-flex>
                    </v-layout>
                </v-flex>
                <v-flex md7 class="hidden-xs-only text-xs-right">
                    <h4>Alamat</h4>
                    <div>{{ profile.user_fulladdress }}</div>
                    <div>Kel. {{ profile.kelurahan_name }}, Kec. {{ profile.district_name }}</div>
                    <div>Kota {{ profile.city_name }} — {{ profile.province_name }}</div>
                    
                </v-flex>

                <v-flex xs6 md2 :class="{'text-xs-right':!$vuetify.breakpoint.xsOnly}">
                    <!-- <v-divider class="mb-1 mt-2"></v-divider>
                    <div class="subheading font-weight-thin">On proses</div>
                    <h4 class="headline blue--text">{{ total_on_process }} Transaksi</h4> -->
                </v-flex>

                <v-flex xs6 md2 class="text-xs-right pr-1">
                    <!-- <v-divider class="mb-1 mt-2"></v-divider>
                    <div class="subheading font-weight-thin">Transaksi bulan ini</div>
                    <h4 class="headline blue--text">Rp {{ one_money(total_transaction) }}</h4>
                    <v-btn color="default" small class="mr-0 mt-4">Detail</v-btn> -->
                </v-flex>
            </v-layout>
            
        </v-card-text>
    </v-card>
</template>

<script>
module.exports = {
    computed : {
        profile () {
            return this.$store.state.dashboard.profile
        }
    },

    mounted () {
        this.$store.dispatch('dashboard/profile')
    }
}
</script>