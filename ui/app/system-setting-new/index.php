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
         <v-app id="oneApp" >
            <common-toolbar></common-toolbar>
            <v-content class="blue lighten-5 one" >
            <common-navbar></common-navbar>
               <v-container pt-1 pl-1 pr-1 fluid>
                  <v-layout row wrap>
                    <v-flex xs12>
                        <v-layout row wrap>
                            <v-flex md8 sm6 xs12 pt-2>
                                <h3 class="display-1 font-weight-light zalfa-text-pink ml-3">

                            </h3>
                            </v-flex>

                            
                        </v-layout>
                    </v-flex>
                     <v-flex xs12>
                        <system-setting></system-setting>
                        <!-- <customer-omzet-setting v-if="selected_tab.id == 2"></customer-omzet-setting> -->
                     </v-flex>
                  </v-layout>
               </v-container>
            </v-content>
            
            <!-- <v-footer class="mb-5 footer" app color="transparent">
                <v-spacer></v-spacer>
                
            </v-footer> -->
            
            
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
    padding-left: 0px
    /* padding-left: 80px */
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
    this.$store.dispatch('system/search_menu_group')
    this.$store.commit('setting/set_selected_tab', this.tabs[0])
    this.$store.dispatch('setting/get_conf')
},
computed : {
   tabs () {
       return this.$store.state.setting.tabs
   },

   selected_tab () {
    return this.$store.state.setting.selected_tab
   }
},

methods : {
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
   el: '#app',
   components: {
        "common-navbar" : httpVueLoader("../common/components/common-navbar.vue?t=123"),
        "common-toolbar" : httpVueLoader("../common/components/common-toolbar.vue"),
        "master-item-list" : httpVueLoader("./components/master-item-list.vue"),
        "system-setting" : httpVueLoader("./components/system-setting-new.vue")
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
