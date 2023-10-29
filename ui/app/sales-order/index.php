<?php
$ts = "?ts=" . Date("ymdhis");
?>

<!DOCTYPE html>
<html lang="en">

   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta http-equiv="X-UA-Compatible" content="ie=edge">
      <title>One :: Penjualan (SO)</title>
      <link rel="stylesheet" href="../../assets/css/google-fonts.css">
      <link rel="stylesheet" href="../../assets/css/vuetify.min.css">
      <link rel="stylesheet" href="../assets/css/global.min.css">
      <link rel="icon" type="image/x-icon" href="../assets/favicon/favicon.ico">
      <!-- <script src="../../../libs/one_global_clinic.js"></script> -->
   </head>

   <body>
      <div v-cloak id="app">
         <v-app id="oneApp"  v-if="one_token">
            <common-toolbar></common-toolbar>
            <v-content class="blue lighten-5 one" >
            <common-navbar></common-navbar>
               <v-container pt-1 pl-1 pr-1 fluid>
                  <v-layout row>
                     <v-flex xs12>
                        <sales-order-list></sales-order-list>
                     </v-flex>
                  </v-layout>
               </v-container>
            </v-content>
            
            <v-footer class="mb-5 footer" app color="transparent">
                <v-spacer></v-spacer>
                
            </v-footer>

            <sales-order-new></sales-order-new>
            <sales-invoice-new></sales-invoice-new>
            <master-customer-new></master-customer-new>
            <master-expedition-new></master-expedition-new>
            <!-- <common-dialog-print :report_url="report_url" v-if="dialog_report"></common-dialog-print> -->
            <common-dialog-progress text="Sedang menyiapkan data Customer, mohon ditunggu... 😉"></common-dialog-progress>
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
      <script src="../assets/js/window_functions.min.js<?php echo $ts ?>"></script>
      
      <!-- App Script -->


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
    this.$store.dispatch('system/get_conf')
},
computed : {
    one_token () {
        return this.$store.state.system.one_token
    },

   dialog_report : {
      get () { return this.$store.state.dialog_print },
      set (v) { this.$store.commit('set_dialog_print', v) }
   },

   report_url : {
      get () { return this.$store.state.report_url },
      set (v) { this.$store.commit('set_report_url', v) }
   }
},

methods : {
    
},
   el: '#app',
   components: {
        "common-navbar" : httpVueLoader("../common/components/common-navbar.vue?r=1"),
        "common-toolbar" : httpVueLoader("../common/components/common-toolbar.vue"),
        "sales-order-list" : httpVueLoader("./components/sales-order-list.vue?t=<?=date('YmdHis');?>"),
        "sales-order-new" : httpVueLoader("./components/sales-order-new.vue?t=<?=date('YmdHis');?>"),
        "sales-invoice-new" : httpVueLoader("../sales-invoice/components/sales-invoice-proforma.vue?t=<?=date('YmdHis');?>"),
        "master-customer-new" : httpVueLoader("../master-customer/components/master-customer-new.vue?t=<?=date('YmdHis');?>"),
        "master-expedition-new" : httpVueLoader("../master-expedition/components/master-expedition-new.vue"),
      //   "common-dialog-print" : httpVueLoader("../common/components/common-dialog-print-size.vue?t=<?=date('YmdHis');?>"),
        "common-dialog-progress" : httpVueLoader("../common/components/common-dialog-progress.vue?t=<?=date('YmdHis');?>")
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
