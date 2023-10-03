<template>
    <v-layout row wrap>
        <v-flex xs12>
            <v-card>
                <v-card-text>
                    
                </v-card-text>
            </v-card>
        </v-flex>
    </v-layout>
</template>

<script>
module.exports = {
    data () {
        return {
            user: this.$store.state.dashboard.user
        }
    },

    mounted () {
        this.$store.dispatch('dashboard/profile')
    }
}
</script>