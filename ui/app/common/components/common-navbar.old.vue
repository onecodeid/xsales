<template>
    <v-navigation-drawer
        v-model="drawer"
        :mini-variant.sync="mini"
        absolute
        permanent
        :value="true"
        stateless
    >
        <v-list>
            <!-- LEVEL 1 -->
            <template v-for="(i, n) in menus">
                <v-list-tile v-if="typeof(i) == 'string'" v-bind:key="n" @click="goto(i)">
                    <v-list-tile-action>
                        <v-icon>{{ icon_me(n) }}</v-icon>
                    </v-list-tile-action>
                    <v-list-tile-title>{{ n }}</v-list-tile-title>
                    
                </v-list-tile>

                <v-list-group
                no-action
                :prepend-icon="icon_me(n)"
                :value="c"
                v-bind:key="n"
                v-if="typeof(i) != 'string'"
                >
                    <template v-slot:activator>
                        <v-list-tile>
                            <v-list-tile-title>{{ n }}</v-list-tile-title>
                        </v-list-tile>
                    </template>

                    <template v-for="(ii, nn) in i">
                        <v-list-tile v-bind:key="nn" @click="goto(ii)"
                        >
                            <v-list-tile-title v-text="nn"></v-list-tile-title>
                                <!-- <v-list-tile-action>
                                <v-icon v-text="crud[1]"></v-icon>
                            </v-list-tile-action> -->
                        </v-list-tile>

                        <!-- <v-list-tile-title v-text="nn" v-if="typeof(ii) == 'string'" v-bind:key="nn">
                            
                            <v-list-tile-title>{{ nn }}</v-list-tile-title>
                            
                        </v-list-tile-title> -->
                    </template>
                </v-list-group>
            </template>
            <!--/ LEVEL 1 -->

            <v-list-tile>
                <v-list-tile-action>
                    <v-icon>home</v-icon>
                </v-list-tile-action>
                <v-list-tile-title>Home</v-list-tile-title>
                <v-list-tile-action>
                    <v-btn
                    icon
                    @click.stop="mini = !mini"
                    >
                    <v-icon>chevron_left</v-icon>
                    </v-btn>
                </v-list-tile-action>
            </v-list-tile>

            
            <v-list-group
                prepend-icon="account_circle"
                :value="c"
            >
                <template v-slot:activator>
                    <v-list-tile>
                        <v-list-tile-title>Users</v-list-tile-title>
                    </v-list-tile>
                </template>

                <!-- LEVEL 2 -->
                <v-list-group
                no-action
                sub-group
                :value="!collapse"
                >
                    <template v-slot:activator>
                        <v-list-tile>
                        <v-list-tile-title>Admin</v-list-tile-title>
                        </v-list-tile>
                    </template>

                    <!-- LEVEL 3 -->
                    <v-list-tile
                        v-for="(admin, i) in admins"
                        :key="i"
                        @click=""
                    >
                        <v-list-tile-title v-text="admin[0]"></v-list-tile-title>
                            <v-list-tile-action>
                            <v-icon v-text="admin[1]"></v-icon>
                        </v-list-tile-action>
                    </v-list-tile>
                    <!-- END OF LEVEL 3 -->

                </v-list-group>
                <!-- END OF LEVEL 2 -->

                <!-- LEVEL 2 -->
                <v-list-group
                sub-group
                no-action
                :value="c"
                >
                    <template v-slot:activator>
                        <v-list-tile>
                        <v-list-tile-title>Actions</v-list-tile-title>
                        </v-list-tile>
                    </template>
                    
                    <!-- LEVEL 3 -->
                    <v-list-tile
                        v-for="(crud, i) in cruds"
                        :key="i"
                        @click=""
                    >
                        <v-list-tile-title v-text="crud[0]"></v-list-tile-title>
                            <v-list-tile-action>
                            <v-icon v-text="crud[1]"></v-icon>
                        </v-list-tile-action>
                    </v-list-tile>
                    <!-- END OF LEVEL 3 -->

                </v-list-group>
                <!-- END OF LEVEL 2 -->

            </v-list-group>
            

        </v-list>
    </v-navigation-drawer>
</template>

<script>
module.exports = {
    data () {
        return {
            drawer: true,
            items: [
            { title: 'Home', icon: 'dashboard' },
            { title: 'About', icon: 'question_answer' }
            ],
            mini: true,
            right: null,

            admins: [
                ['Management', 'people_outline'],
                ['Settings', 'settings']
            ],
            cruds: [
                ['Create', 'add'],
                ['Read', 'insert_drive_file'],
                ['Update', 'update'],
                ['Delete', 'delete']
            ],
            collapse: true
        }
    },

    computed : {
        c () {
            console.log(`c : ${!this.mini}`)
            return false
            return !this.mini
        },

        menus () {
            return this.$store.state.system.menus
        }
    },

    methods : {
        icon_me (x) {
            let icons = this.$store.state.system.icons
            if (icons[x])
                return icons[x]
            return ''
        },

        goto (x) {
            window.location = '../' + x
        }
    },

    watch : {
        mini (v, o) {
            console.log(v)
            this.collapse = true
            console.log(this.collapse)
        }
    }
}
</script>