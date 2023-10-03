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
         <v-app id="oneApp">
            
            <v-content class="one"  color="transparent">
               <v-container pt-1 pl-1 pr-1 fluid>
                  <v-layout row>
                     <v-flex xs12>
                        <system-login-form></system-login-form>
                     </v-flex>
                  </v-layout>
               </v-container>
            </v-content>
            
           
            
            
         </v-app>
      </div>

      <!-- Vendor -->
      <!-- <script src="../../assets/js/lodash.custom.min.js"></script> -->
      <script src="../../assets/js/axios.min.js"></script>
      <script src="../../assets/js/vue.min.js"></script>
      <script src="../../assets/js/vuex.min.js"></script>
      <script src="../../assets/js/vuetify.min.js"></script>
      <!-- <script src="../../assets/js/vuezify.min.js"></script> -->
      <script src="../../assets/js/httpVueLoader.min.js"></script>
      
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

body {
    background: url('../assets/img/water-treatment.jpg');
    background-size: cover;
}

.theme--light.application {
    background: none !important;
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
    if (this.$store.state.system.one_token)
        window.location.replace('../dashboard/')
    // var url_string = window.location.href
    // var url = new URL(url_string);
    // var c = url.searchParams.get("noreg");

    // if (c != null) {
    //     this.$store.commit('patient/update_noreg', c)
    //     this.$store.dispatch('patient/search', {use:true,use_idx:0})
    // }

    
},
computed : {
   
},

methods : {
    
},
   el: '#app',
   components: {
        "common-navbar" : httpVueLoader("../common/components/common-navbar.vue?t=123"),
        "common-toolbar" : httpVueLoader("../common/components/common-toolbar.vue"),
        "system-login-form" : httpVueLoader("./components/system-login-form.vue")
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
