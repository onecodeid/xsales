<template>
    <v-card class="drawer-right" :class="{'hide':!show}" fill-height style="display:flex;flex-direction:column">
        <v-card-title class="pa-0">
            <v-layout row wrap class="grey">
                <v-flex xs9 pa-3 style="position: relative">
                    <v-btn color="red" class="btn-icon white--text btn-close ma-0"  :class="{'hide':!show}" depressed @click="close()">
                        <v-icon>arrow_right</v-icon>
                    </v-btn>
                    <h3>LAPORAN</h3>
                </v-flex>
                <v-flex xs3 class="text-xs-right">
                    <!-- <v-btn color="red" class="btn-icon white--text">X</v-btn> -->
                </v-flex>
            </v-layout>
        </v-card-title>
        <v-card-text grow style="flex:1">
            <object :data="init_report_url" type="application/pdf" width="100%" height="100%"></object>
        </v-card-text>
    </v-card>
</template>

<style scoped>
.drawer-right {
    background-color:#fff;
    position: fixed;
    width: 66%;
    top: 0px;
    right: 0px;
    height: 100vh;

    transition:all 500ms ease;
    -moz-transition:all 500ms ease;
    -webkit-transition:all 500ms ease;
    -ms-transition:all 500ms ease;
    -o-transition:all 500ms ease;
}

.btn-close {
    position: absolute;
    top: 0px;
    left: -35px;
    height: 100%;
}

.show {

}

.hide {
    -webkit-transform:translateX(100%);
    -ms-transform:translateX(100%);
    transform:translateX(100%);
}
</style>
<script>
module.exports = {
    props : ['data', 'report_url'],
    data () {
        return {
            init_report_url: this.report_url ? this.report_url : ''
        }
    },

    computed : {
        show : {
            get () { return this.$store.state.drawer_right },
            set (v) { this.$store.commit('set_drawer_right', v) }
        }
    },

    methods : {
        close() {
            this.show = false
        }
    }
}
</script>