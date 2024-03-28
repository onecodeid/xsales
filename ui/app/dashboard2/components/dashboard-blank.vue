<template>
    <v-layout row wrap>
        <v-flex xs12 sm6 md6 mb-2 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
            <v-checkbox
      v-model="checkbox"
      :label="`Checkbox 1: ${checkbox.toString()}`"
    ></v-checkbox>
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