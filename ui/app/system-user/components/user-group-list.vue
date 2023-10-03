<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-2 primary white--text">
            <h3>GRUP USER</h3><v-spacer></v-spacer>
            <v-btn color="zalfa-bg-purple lighten-3" dark small class="ma-0" v-if="selected_group != null" :title="'Tambah User baru dalam Group '+selected_group.group_name" @click="add_user">
                <v-icon class="mr-1">add_circle_outline</v-icon> User</v-btn>
        </v-card-title>
        <v-card-text class="pa-0">
            <v-list two-line class="pt-0 pb-0">
          

          <!-- <v-divider inset></v-divider> -->

                <v-list-tile @click="select(g)" v-for="(g, n) in groups" v-bind:key="n" v-bind:class="is_selected(g)?'cyan lighten-4':''">
                    <v-list-tile-action>
                    <v-icon color="indigo">group</v-icon>
                    </v-list-tile-action>

                    <v-list-tile-content>
                    <v-list-tile-title>{{g.group_name}}</v-list-tile-title>
                    <!-- <v-list-tile-sub-title>{{g.group_code}}</v-list-tile-sub-title> -->
                    </v-list-tile-content>

                    <v-list-tile-action>
                        <v-btn icon ripple>
                            <v-icon color="grey lighten-1" @click="edit(g)">edit</v-icon>
                        </v-btn>
                    </v-list-tile-action>
                </v-list-tile>

          
            </v-list>
        </v-card-text>

        <user-group-edit></user-group-edit>
    </v-card>
            
        
</template>

<style scoped>
    .v-list--two-line .v-list__tile {
        height: 64px;
    }

    .v-list__tile__action, .v-list__tile__avatar {
        min-width: 40px;
    }
</style>

<script>
let t = Math.round(Math.random() * 1e10)
module.exports = {
    components : {
        'user-group-edit' : httpVueLoader('./user-group-edit.vue?t='+t)
    },

    computed : {
        groups () {
            return this.$store.state.user.groups
        },

        selected_group () {
            return this.$store.state.user.selected_group
        }
    },

    methods : {
        select (x) {
            this.$store.commit('user/set_selected_group', x)
            this.$store.commit('user/set_privileges', x.privilege)
            this.$store.commit('user/set_report_privileges', x.report_privilege)
            this.$store.dispatch('user/search_users')
        },

        is_selected (x) {
            if (!this.selected_group)
                return false
            if (this.selected_group.group_id == x.group_id)
                return true
            return false
        },

        edit (x) {
            this.select(x)
            this.$store.commit('user/set_common', ['dialog_group', true])
        },

        add_user () {
            this.$store.commit('user_new/set_common', ['edit', false])
            this.$store.commit('user_new/set_common', ['dialog_user', true])

            this.$store.commit('user_new/set_common', ['user_name', ''])
            this.$store.commit('user_new/set_common', ['user_full_name', ''])
            this.$store.commit('user_new/set_common', ['user_address', ''])
            this.$store.commit('user_new/set_common', ['user_phone', ''])
            this.$store.commit('user_new/set_common', ['user_email', ''])
        }
    },

    mounted () {
        this.$store.dispatch('user/search_groups')
        this.$store.dispatch('user/search_reports')
    }
}
</script>