<!DOCTYPE html>
<html lang="en">

   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta http-equiv="X-UA-Compatible" content="ie=edge">
      <title>One</title>
      <link rel="stylesheet" href="../../assets/css/google-fonts.css">
      <link rel="stylesheet" href="../../assets/css/vuetify.min.css">
      <link rel="stylesheet" href="../assets/css/global.min.css">
      <link rel="icon" type="image/x-icon" href="../assets/favicon/favicon.ico">
      <!-- <script src="../../../libs/one_global_clinic.js"></script> -->
   </head>

   <body>
      <div v-cloak id="app">
         <v-app id="oneApp" v-if="one_token" >
            <common-toolbar></common-toolbar>
            <v-content class="blue lighten-5 one" >
            <common-navbar></common-navbar>
               <v-container pt-1 pl-1 pr-1 fluid>
                  <v-layout row>
                     <v-flex xs12>
                        <v-card>
                            <v-card-text>
                                <v-layout row wrap>
                                    <v-flex xs3>
                                        <user-group-list></user-group-list>
                                    </v-flex>
                                    <v-flex xs9 pl-2>
                                        <user-list></user-list>
                                    </v-flex>
                                </v-layout>
                                       
                            </v-card-text>
                        </v-card>
                        
                     </v-flex>
                  </v-layout>
               </v-container>
            </v-content>
            
            <!-- <v-footer class="mb-5 footer" app color="transparent">
                <v-spacer></v-spacer>
                
            </v-footer> -->

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
        
         </v-app>
      </div>

      <!-- Vendor -->
      <script src="../../assets/js/moment.min.js"></script>
      <script src="../../assets/js/numeral.min.js"></script>
      <script src="../../assets/js/moment-locale-id.js"></script>
      <script src="../../assets/js/lodash.js"></script>
      <script src="../../assets/js/axios.min.js"></script>
      <script src="../../assets/js/vue.js"></script>
      <script src="../../assets/js/vuex.js"></script>
      <script src="../../assets/js/vuetify.js"></script>
      <script src="../../assets/js/httpVueLoader.js"></script>
      <script src="../../assets/js/webcam.min.js"></script>
      <script src="../assets/js/window_functions.min.js"></script>
      
      <!-- App Script -->
<?php
$ts = "?ts=" . Date("ymdhis");
?>

<style scoped>
.footer {
    width: 100px;
    right: 0px;
    left: auto;
}

.v-content__wrap {
    /* padding-left: 80px; */
    padding-left: 0px
}
</style>

<script type="module">

import { store } from './store.js<?php echo $ts ?>';
//for testing
window.store = store;
new Vue({
store,
data : {
   
},
mounted: function() {
    if (!this.$store.state.system.one_token)
        window.location.replace('../system-login/')

    this.$store.dispatch('system/search_menu_group')
},
computed : {
   one_token () {
        return this.$store.state.system.one_token
    },

    snackbar : {
        get () { return this.$store.state.user.snackbar },
        set (v) { this.$store.commit('user/set_common', ['snackbar', v]) }
    },

    snackbar_text () {
        return this.$store.state.user.snackbar_text
    }
},

methods : {
    
},
   el: '#app',
   components: {
        "common-navbar" : httpVueLoader("../common/components/common-navbar.vue?t=123"),
        "common-toolbar" : httpVueLoader("../common/components/common-toolbar.vue"),
        "common-dialog-print" : httpVueLoader("../common/components/common-dialog-print.vue?t=<?=date('YmdHis');?>"),
        "user-group-list" : httpVueLoader("./components/user-group-list.vue?t=<?=date('YmdHis');?>"),
        "user-list" : httpVueLoader("./components/user-list.vue?t=<?=date('YmdHis');?>")
      }
    })
    </script>
    <style>
[v-cloak] {
   display: none
}
    .v-content.one {
       /* padding:64px 0px 0px !important; */
    }
    </style>
   </body>

</html>
