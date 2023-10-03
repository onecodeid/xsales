<template>
    <v-card>
        <v-card-text>
            <v-layout row wrap>
                <v-flex xs2>
                    <v-layout row wrap v-for="(g, n) in groups" :key="n" class="pb-1" @click="selectGroup(g)">
                        <v-flex xs12 px-3 py-3  class="primary white--text" :class="{'lighten-2':(g.id!=selected_group.id)}">
                            {{ g.label }}
                        </v-flex>
                        <v-divider></v-divider>
                    </v-layout>

                    <v-layout row wrap mt-4>
                        <v-flex xs12>
                            <v-btn color="primary" block @click="save" :disabled="!selected_group">Simpan</v-btn>
                        </v-flex>

                    </v-layout>

                    
                </v-flex>
                <v-flex xs6 pl-2>
                    <v-data-table 
                        :headers="headers"
                        :items="accountreports"
                        :loading="false"
                        hide-actions
                        class="elevation-1">
                        <template slot="items" slot-scope="props">
                            <tr>
                                <td colspan="2">{{ props.item.account_title != '' ? props.item.account_title : props.item.account_type }}</td>
                                <td><v-btn color="success" class="ma-0 ml-2 btn-icon" @click="add(props.index)" small>
                                    <v-icon>add</v-icon>
                                </v-btn></td>
                            </tr>

                            <tr v-for="(d, n) in props.item.details" :key="props.index+'-d-'+n">
                                <td class="text-xs-left pa-1">&nbsp;</td>
                                <td class="text-xs-left pa-1" colspan="2">
                                    <v-autocomplete
                                        :items="accounts"
                                        item-text="account_name"
                                        item-value="account_code"
                                        return-object
                                        label="Parent"
                                        clearable
                                        :value="d.account"
                                        @click:clear="changeAccount(props.index, n, null)"
                                        @change="changeAccount(props.index, n, $event)"
                                        solo
                                        hide-details
                                    >
                                        <!-- <template
                                            slot="item"
                                            slot-scope="data"> -->

                                            <!-- Divider and Header-->
                                            <!-- <template v-if="!!data.item.header">
                                                <v-list-tile-content v-text="data.item.header" class="blue"></v-list-tile-content>
                                            </template> -->

                                            <!-- Normal item -->
                                            <!-- <template v-else>
                                                <v-list-tile-content>
                                                <v-list-tile-title v-html="data.item.account_name" class="ml-3" v-show="data.item.is_group=='Y'"></v-list-tile-title>
                                                <v-list-tile-title class="ml-3" v-show="data.item.is_group=='N'">
                                                    <span class="blue--text">{{ data.item.account_code }}</span> {{ data.item.account_name }}
                                                </v-list-tile-title>
                                                </v-list-tile-content>
                                            </template> -->
                                        <!-- </template> -->

                                        <template slot="selection" slot-scope="data">
                                            <span v-show="data.item.is_group=='Y'" class=""><span class="orange--text">{{ data.item.account_code }}</span> {{data.item.account_name }}</span>
                                            <span v-show="data.item.is_group=='N'"><span class="blue--text">{{ data.item.account_code }}</span> {{ data.item.account_name }}</span>
                                        </template>

                                        <template slot="item" slot-scope="data">
                                            <span v-show="data.item.is_group=='Y'" class=""><span class="orange--text">{{ data.item.account_code }}</span> {{data.item.account_name }}</span>
                                            <span v-show="data.item.is_group=='N'"><span class="blue--text ml-4">{{ data.item.account_code }}</span> {{ data.item.account_name }}</span>
                                        </template>

                                        <template slot="prepend">
                                            <v-btn color="red" class="ma-0" icon :dark="false" flat @click="del_detail(props.index, n)" ><v-icon>delete</v-icon></v-btn>
                                        </template>
                                    </v-autocomplete>
                                </td>
                            </tr>
                            <!-- <tr v-show="props.index==0">
                                <td colspan="2">{{ props.item.account_title != '' ? props.item.account_title : props.item.account_type }}</td>
                                <td><v-btn color="success" class="ma-0 ml-2 btn-icon" @click="add" small>
                                    <v-icon>add</v-icon>
                                </v-btn></td>
                            </tr>
                            <tr v-if="props.index>0 && props.item.account_type!=accountreports[props.index-1].account_type">
                                <td colspan="2">{{ props.item.account_title != '' ? props.item.account_title : props.item.account_type }}</td>
                                <td><v-btn color="success" class="ma-0 ml-2 btn-icon" @click="add" small>
                                    <v-icon>add</v-icon>
                                </v-btn></td>
                            </tr> -->
                            
                            
                            
                            
                            <!-- <td class="text-xs-center pa-0" @click="select(props.item)">
                                <v-btn color="primary" class="btn-icon ma-0" small @click="edit(props.item)" v-show="props.item.M_AccountRemovable=='Y'"><v-icon>create</v-icon></v-btn>
                                <v-btn color="red" dark class="btn-icon ma-0" small @click="del(props.item)" v-show="props.item.M_AccountRemovable=='Y'"><v-icon>delete</v-icon></v-btn>
                            </td> -->
                        </template>
                    </v-data-table>
                </v-flex>
            </v-layout>
        </v-card-text>  
    </v-card>
</template>

<style scoped>
.v-text-field.v-text-field--solo .v-input__control {
    min-height: 36px;
}

.v-input__prepend-outer {
    margin: 0px !important;
}
</style>

<script>
module.exports = {
    data () {
        return {
            activeTab: 0,
            headers: [
                {
                    text: "NO",
                    align: "left",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "AKUN / GRUP AKUN",
                    align: "left",
                    sortable: false,
                    width: "80%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "X",
                    align: "left",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                }
            ]
        }
    },

    computed : {
        groups () {
            return this.$store.state.accountreport.groups
        },

        selected_group : {
            get () { return this.$store.state.accountreport.selected_group },
            set (v) { 
                this.$store.commit('accountreport/set_object', ['selected_group', v]) 
                this.$store.dispatch('accountreport/search').then((x) => {
                    let details = []
                    for(let xx of x) {
                        let xxx = JSON.parse(JSON.stringify(xx))
                        for (let d of xxx.details) {
                            for (let a of this.accounts)
                                if (d.account.account_id == a.account_id && d.account.is_group == a.is_group)
                                    d.account = a
                        }
                        details.push(xxx)
                    }

                    console.log(details)
                    this.$store.commit('accountreport/set_object', ['accountreports', details])
                })
            }
        },

        accountreports () {
            return this.$store.state.accountreport.accountreports
        },

        accounts () {
            return this.$store.state.accountreport.accounts
        }
    },

    methods : {
        selectGroup (v) {
            this.selected_group = v
        },

        changeAccount (idx1, idx2, v) {
            let x = JSON.parse(JSON.stringify(this.accountreports))
            x[idx1].details[idx2].account = v
            if (!v) {
                x[idx1].details[idx2].account_id = 0
                x[idx1].details[idx2].account_type = ''
                x[idx1].details[idx2].account_title = ''
                x[idx1].details[idx2].is_group = 'N'
            } else {
                x[idx1].details[idx2].account_id = v.account_id
                x[idx1].details[idx2].account_type = x[idx1].account_type
                x[idx1].details[idx2].account_title = x[idx1].account_title
                x[idx1].details[idx2].is_group = v.is_group
            }
            this.$store.commit('accountreport/set_object', ['accountreports', x])
        },

        add (x) {
            let y = JSON.parse(JSON.stringify(this.accountreports))
            y[x].details.push({account_type:y[x].account_type, account_name:y[x].account_name, account:null})

            this.$store.commit('accountreport/set_object', ['accountreports', y])
            return
        },

        del_detail (x, z) {
            let y = JSON.parse(JSON.stringify(this.accountreports))
            y[x].details.splice(z, 1)

            this.$store.commit('accountreport/set_object', ['accountreports', y])
            return
        },

        save () {
            this.$store.dispatch('accountreport/save')
        }
    },

    watch : {
        activeTab : function(v, o) {
            this.selected_group = this.groups[v]
        }
    }
}
</script>