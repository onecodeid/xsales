<template>
    <v-layout row wrap>
        <v-flex xs12 sm12 md6 mb-2 :class="{'pr-2':$vuetify.breakpoint.mdAndUp}">
            <item-profile></item-profile>
        </v-flex>

        <v-flex xs12 sm12 md6 mb-2>
            <item-stat-capaian></item-stat-capaian>
        </v-flex>

        <v-flex xs12 sm4 md3 mb-2 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
            <item-comission></item-comission>
        </v-flex>

        <v-flex xs12 sm4 md3 mb-2 :class="{'pr-2':$vuetify.breakpoint.smAndUp}">
            <item-admin-trans></item-admin-trans>
        </v-flex>

        <v-flex xs12 sm4 md3 mb-2 :class="{'pr-2':$vuetify.breakpoint.smAndUp}" class="hidden-xs-only">
            <item-admin-omzet height="125"></item-admin-omzet>
        </v-flex>

        <v-flex xs12 sm4 md3 mb-2 class="hidden-xs-only">
            <item-blank height="125"></item-blank>
        </v-flex>
    </v-layout>
</template>

<script>
module.exports = {
    components : {
        "item-profile": httpVueLoader("./dashboard-item-profile.vue"),
        "item-stat-capaian": httpVueLoader("./dashboard-item-stat-capaian.vue"),
        "item-comission": httpVueLoader("./dashboard-item-comission.vue"),
        "item-admin-trans": httpVueLoader("./dashboard-item-admin-trans.vue"),
        "item-admin-omzet": httpVueLoader("./dashboard-item-admin-omzet.vue"),
        "item-blank": httpVueLoader("./dashboard-item-blank.vue")
    },

    computed : {
        
    }
}
</script>