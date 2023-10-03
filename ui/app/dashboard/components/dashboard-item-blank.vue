<template>
    <v-card :height="h">
        <v-card-text>
            &nbsp;
        </v-card-text>
    </v-card>
</template>

<script>
module.exports = {
    props : ['height'],

    computed : {
        h () {
            if (this.height)
                return this.height
            return 133
        }
    }
}
</script>