<template>
    <v-layout row wrap>
        <v-flex xs6 pr-1>
            <calc-rop></calc-rop>
        </v-flex>
        <v-flex xs6 pl-1>
            <calc-ma></calc-ma>
        </v-flex>
    </v-layout>
    
</template>

<style scoped>
.v-text-field.v-text-field--solo .v-input__control {
    min-height: 36px;
}
.v-text-field.v-text-field--solo .v-input__prepend-outer {
    margin-top: 0px;
    margin-left: 0px;
}
</style>

<script>
module.exports = {
    components : {
        "calc-rop" : httpVueLoader("./inventory-calculator-rop.vue?t=123"),
        "calc-ma" : httpVueLoader("./inventory-calculator-ma.vue?t=123")
    },

    data () {
        return {
            curr_page: 1,
            xtotal_page: 1,
        }
    },

    computed : {

    },

    methods : {
        one_money (x) {
            return window.one_money(x, "0,000.00")
        }
    },

    mounted () {
        
    }
}
</script>