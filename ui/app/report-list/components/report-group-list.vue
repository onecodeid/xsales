<template>
    <v-card>
        <v-card-title primary-title class="pt-3 pb-2 primary white--text">
            <h3>GRUP LAPORAN</h3>
        </v-card-title>
        <v-card-text class="pa-0">
            <v-list two-line>
          

          <!-- <v-divider inset></v-divider> -->

                <v-list-tile @click="select(g)" v-for="(g, n) in groups" v-bind:key="n" :class="{'blue lighten-3':is_selected(g)}">
                    <v-list-tile-action>
                    <v-icon color="indigo">{{ g.report_icon }}</v-icon>
                    </v-list-tile-action>

                    <v-list-tile-content>
                    <v-list-tile-title>{{g.report_name}}</v-list-tile-title>
                    <v-list-tile-sub-title>{{g.report_desc}}</v-list-tile-sub-title>
                    </v-list-tile-content>
                </v-list-tile>

          
            </v-list>
        </v-card-text>
    </v-card>
            
        
</template>

<style scoped>
    .v-list--two-line .v-list__tile {
        height: 64px;
    }

</style>

<script>
module.exports = {
    computed : {
        groups () {
            return this.$store.state.report.groups
        },

        selected_group () {
            return this.$store.state.report.selected_group
        }
    },

    methods : {
        select (x) {
            this.$store.commit('report/set_selected_group', x)
        },

        is_selected (x) {
            if (!this.selected_group)
                return false
            if (x.report_id == this.selected_group.report_id)
                return true
            return false
        }
    },

    mounted () {
        this.$store.dispatch('report/search_groups')
    }
}
</script>