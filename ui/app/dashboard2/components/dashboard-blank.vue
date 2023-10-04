<template>
    <v-layout row wrap>
        <v-flex xs12 sm6 md6 mb-2 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
            H_E_L_L_O
        </v-flex>
    </v-layout>
</template>

<script>
let t = '?t='+Math.random()
module.exports = {
    components : {
    },

    mounted () {
    }
}
</script>