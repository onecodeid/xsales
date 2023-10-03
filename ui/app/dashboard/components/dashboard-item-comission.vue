<template>
    <v-card>
        <v-card-text class="pb-2">
            <v-layout row wrap>
                <v-flex xs4>
                    <v-card color="zalfa-icon-box" flat>
                        <v-card-text class="pa-0">
                            <v-icon size="60" color="green" light>attach_money</v-icon>        
                        </v-card-text>
                    </v-card>
                    
                </v-flex>
                <v-flex xs8 class="text-xs-right">
                    <div class="subheading font-weight-thin">Komisi Bulan Ini</div>
                    <h4 class="headline">Rp {{one_money(admin_total_fee)}}</h4>
                </v-flex>
                <v-flex xs12>
                    <v-divider class="mt-2 mb-2"></v-divider>
                    <v-layout row wrap>
                        <v-flex xs9>
                            <div class="body-1 font-weight-light"><v-icon size="20">history</v-icon> {{admin_fee_sdate}} s/d {{admin_fee_edate}}</div>
                        </v-flex>
                        <v-flex xs3 class="text-xs-right">
                            <!-- <v-btn color="success" small class="ma-0 btn-icon" @click="admin_fee_print">Detail</v-btn> -->
                        </v-flex>
                    </v-layout>
                    
                </v-flex>
            </v-layout>
            
            
        </v-card-text>
    </v-card>
</template>

<script>
module.exports = {
    computed : {
        admin_total_fee () {
            return 0
        },

        admin_fee_sdate () {
            return "2020-07-01"
        },

        admin_fee_edate () {
            return "2020-07-31"
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        }
    }
}
</script>