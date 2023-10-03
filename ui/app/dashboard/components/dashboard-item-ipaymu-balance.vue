<template>
    <v-card>
        <v-card-text>
            <v-layout row wrap>
                <v-flex xs4>
                    <v-card color="zalfa-icon-box" flat>
                        <v-card-text class="pa-0">
                            <v-icon size="60" color="orange" light>account_balance</v-icon>        
                        </v-card-text>
                    </v-card>
                    
                </v-flex>
                <v-flex xs8 class="text-xs-right">
                    <div class="subheading font-weight-thin">Saldo iPaymu</div>
                    <h4 class="headline">Rp {{ one_money(ipaymu_balance) }}</h4>
                </v-flex>
                <v-flex xs12>
                    <v-divider class="mt-2 mb-2"></v-divider>
                    <div class="body-1 font-weight-light"><v-icon size="20">history</v-icon> Last updated : 2020-04-01 09:00</div>
                </v-flex>
            </v-layout>
            
            
        </v-card-text>
    </v-card>
</template>

<script>
module.exports = {
    computed : {
        ipaymu_balance () {
            return this.$store.state.dashboard.ipaymu_balance
        }
    },
    
    methods : {
        one_money (x) {
            return window.one_money(x)
        }
    },

    mounted () {
        this.$store.dispatch('dashboard/ipaymu_balance')
    }
}
</script>