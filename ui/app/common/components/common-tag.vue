<template>
    <v-layout row wrap>
        <v-flex xs12>
            <v-combobox
                v-model="selected"
                :items="tagss"
                :search-input.sync="search"
                hide-selected
                :label="label ? label : 'Ketikkan sesuatu'"
                multiple
                small-chips
                deletable-chips
                clearable
                :delimiters="[',']"
                
                :allow-overflow="false"
                :solo="modeSolo"
                :outline="modeOutline"
                >
                <template v-slot:no-data>
                    <v-list-tile>
                    <v-list-tile-content>
                        <v-list-tile-title>
                        No results matching "<strong>{{ search }}</strong>". Press <kbd>enter</kbd> to create a new one
                        </v-list-tile-title>
                    </v-list-tile-content>
                    </v-list-tile>
                </template>
            
                <template v-slot:selection="{ attrs, item, parent, selected }">
                    <v-chip
                    v-bind="attrs"
                    :input-value="selected"
                    label
                    small
                    close
                    class="blue--text mr-2"
                    >
                    <span class="pr-2">
                        {{ item }}
                    </span>
                    <v-icon
                        small
                        @click="parent.selectItem(item)"
                    >mdi-close</v-icon>
                    </v-chip>
                </template>
      
                <!-- <template v-slot:prepend-item>
                    <v-container class="py-0">
                    <span class="subtitle-1">Suggested</span>
                    <v-chip class="mx-1" v-for="tag in suggestions" v-text="tag" label small color="primary"/>
                    </v-container>
                </template> -->
      
            </v-combobox>

        </v-flex>
    </v-layout>
</template>

<script>
module.exports = {
    props : ['mode', 'label'],
    components : {
    },

    data () {
        return { 
            search: null,
            suggestions: ['Vuetify', 'Vue', 'Php'],
            modeSolo: this.mode == "solo" || !this.mode ? true : false,
            modeOutline: this.mode == "outline" ? true : false
        }

    },

    computed : {
        tags () {
            return this.$store.state.tag.tags
        },

        selected_tags : {
            get () { return this.$store.state.tag.selected_tags },
            set (v) {
                this.$store.commit('tag/set_object', ['selected_tags', v]) 
            }
        },

        tagss () {
            let tags = []
            for (let t of this.tags) tags.push(t.tag_name)

            return tags
        },

        selected : {
            get () { return this.$store.state.tag.selected_tagnames },
            set (v) {
                this.$store.commit('tag/set_object', ['selected_tagnames', v]) 
            }
        }
    },

    methods : {
        remove(x) {
            let y = JSON.parse(JSON.stringify(this.selected_tags))
            y.splice(x, 1)

            this.selected_tags = y
        }
    },

    watch : {
        selected (n, o) {
            console.log(n)
        }
    },

    mounted () {
        this.$store.dispatch("tag/search_tag")
    }
}
</script>