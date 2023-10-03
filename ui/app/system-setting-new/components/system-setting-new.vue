<template>

    <v-card>
        <v-card-title primary-title>
            <v-layout row wrap>
                <v-flex md2 sm3 xs6 v-for="(t, n) in tabs" :key="n" pr-1>
                    <v-btn large color="primary" block depressed @click="select(t)" :outline="!is_selected(t)" mb-0 mt-2 class="fill-height"
                    :large="$vuetify.breakpoint.xsOnly">{{t.label}}</v-btn>
                </v-flex>
            </v-layout>
            
        </v-card-title>
        <v-card-text class="pt-2">
            <v-layout row wrap>
                <v-flex xs12>
                    <system-setting-tab-global v-show="selected_tab.id=='SETTING.GLOBAL'"></system-setting-tab-global>
                </v-flex>
                
                <v-flex xs12>
                    
                </v-flex>
            </v-layout>
        </v-card-text>

        <v-snackbar
            v-model="snackbar"
            multi-line
            :timeout="6000"
            top
            vertical
            >
            {{ snackbar_text }}
            <v-btn
                color="pink"
                flat
                @click="snackbar = false"
            >
                Tutup
            </v-btn>
        </v-snackbar>
    </v-card>
</template>

<style>
.v-card__title.cyan.lighten-4 {
    border-bottom: solid 2px cyan !important;
    border-bottom-color: cyan !important;
}
</style>

<script>
module.exports = {
    components : {
        "system-setting-tab-global" : httpVueLoader("./system-setting-tab-global.vue")
    },

    computed : {
        money_mask () {
            let y = []
            for (let i=0;i<this.target_weekly.length;i++) {
                y.push('#')
                if (i%3==2)
                    y.push(',')
            }
            y.reverse()

            if (y[0]==',')
                y.shift()
            
            return y.join('')+'#'
        },

        snackbar : {
            get () { return this.$store.state.setting.snackbar },
            set (v) { this.$store.commit('setting/set_common', ['snackbar', v]) }
        },

        snackbar_text () {
            return this.$store.state.setting.snackbar_text
        },

        selected_tab () {
            return this.$store.state.setting.selected_tab
        },

        tabs () {
            return this.$store.state.setting.tabs
        }
    },

    methods : {
        save_setting () {
            this.$store.dispatch('setting/save_setting')
        },

        select (t) {
            this.$store.commit('setting/set_selected_tab', t)
        },

        is_selected (t) {
            if (!this.selected_tab)
                return false
            if (t.id == this.selected_tab.id)
                return true
            return false
        }
    },

    mounted () {
        this.$store.dispatch('setting/search')
    }
}
</script>
