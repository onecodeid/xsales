<template>
    <v-dialog
        v-model="dialog"
        max-width="1000px"
        transition="dialog-transition"
        scrollable
    >
        <v-card>
            <v-card-title primary-title class="blue white--text">
                <h3 class="headline">GROUP SETTING</h3>
                <v-spacer></v-spacer>
                <v-icon large class="white--text">group</v-icon>
            </v-card-title>

            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs4>
                        <v-text-field
                            label="Nama Group"
                            :value="selected_group?selected_group.group_name:''"
                        ></v-text-field>
                    </v-flex>

                    <v-flex xs4 pl-2>
                        <v-text-field
                            label="Kode Group"
                            :value="selected_group?selected_group.group_code:''"
                            readonly
                        ></v-text-field>
                    </v-flex>
                </v-layout>
                <v-layout row wrap>
                    <v-flex xs8>
                        <v-layout row wrap>

                            <v-flex xs12 v-for="(m, i) in menus[0]" :key="i" class="mb-2 pr-1 pl-1">
                                <v-card>
                                    <v-card-title primary-title class="primary white--text pt-2 pb-1">
                                        {{ i.toUpperCase() }}
                                    </v-card-title>
                                    <v-card-text v-if="typeof(m)!='string'" class="pa-2 pt-0">
                                        <v-layout row wrap>
                                            <v-flex xs6 v-for="(mm, ii) in m" v-bind:key="ii">
                                                <v-checkbox :label="ii" :value="Math.round(mm)" v-model="privileges" hide-details class="mt-2"></v-checkbox>
                                            </v-flex>
                                        </v-layout>
                                    </v-card-text>

                                    <v-card-text v-if="typeof(m)=='string'" class="pa-2 pt-0">
                                        <v-checkbox :label="i" :value="Math.round(m)" hide-details class="mt-2" v-model="privileges"></v-checkbox>
                                    </v-card-text>
                                </v-card>  
                            </v-flex>
                                  

                        </v-layout>
                        
                        
                    </v-flex>

                    <v-flex xs4>
                        <v-layout row wrap>

                            <v-flex xs12 v-for="(m, i) in reports" :key="i" class="mb-2 pr-1 pl-1">
                                <v-card>
                                    <v-card-title primary-title class="orange white--text pt-2 pb-1">
                                        {{ m.report_name.toUpperCase() }}
                                    </v-card-title>
                                    <v-card-text class="pa-2 pt-0">
                                        <v-layout row wrap>
                                            <v-flex xs12 v-for="(mm, ii) in m.childs" v-bind:key="ii">
                                                <v-checkbox :label="mm.report_name" :value="Math.round(mm.report_id)" v-model="report_privileges" hide-details class="mt-0">
                                                    <template slot="label">
                                                        <v-layout row wrap>
                                                            <v-flex xs12>{{ mm.report_name }}</v-flex>
                                                            <v-flex xs12 class="caption cyan--text">
                                                                {{ mm.report_code }}
                                                            </v-flex>
                                                        </v-layout></template>
                                                </v-checkbox>
                                            </v-flex>
                                        </v-layout>
                                    </v-card-text>
                                </v-card>  
                            </v-flex>

                        </v-layout>
                        
                        
                    </v-flex>
                    
                </v-layout>
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="primary" @click="dialog=!dialog" flat>Tutup</v-btn>
                
                <v-btn color="primary" @click="save">Simpan</v-btn>
            </v-card-actions>
        </v-card>

    </v-dialog>
</template>

<script>
module.exports = {
    computed : {
        dialog : {
            get () { return this.$store.state.user.dialog_group },
            set (v) { this.$store.commit('user/set_common', ['dialog_group', v]) }
        },

        selected_group () {
            return this.$store.state.user.selected_group
        },

        menus () {
            return this.$store.state.user.menus
        },

        reports () {
            return this.$store.state.user.reports
        },

        privileges : {
            get () { return this.$store.state.user.privileges },
            set (v) { this.$store.commit('user/set_privileges',v) }
        },

        report_privileges : {
            get () { return this.$store.state.user.report_privileges },
            set (v) { this.$store.commit('user/set_report_privileges',v) }
        }
    },

    methods : {
        save () {
            this.$store.dispatch('user/save')
        }
    },

    mounted () {
        this.$store.dispatch('user/search_menus')
    }
}
</script>