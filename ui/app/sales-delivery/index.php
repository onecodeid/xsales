<!DOCTYPE html>
<html lang="en">

   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta http-equiv="X-UA-Compatible" content="ie=edge">
      <title>One :: Pengiriman (DO)</title>
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
                        <delivery-order-list></delivery-order-list>
                     </v-flex>
                  </v-layout>
               </v-container>
            </v-content>
            
            <v-footer class="mb-5 footer" app color="transparent">
                <v-spacer></v-spacer>
                
            </v-footer>
            
            <delivery-order-new></delivery-order-new>
            <delivery-order-new-sales></delivery-order-new-sales>
            <delivery-order-proforma></delivery-order-proforma>
            <delivery-order-invoice></delivery-order-invoice>
            <delivery-order-confirm></delivery-order-confirm>
            <delivery-order-calendar></delivery-order-calendar>
            <common-dialog-progress text="Sedang menyiapkan data Customer, mohon ditunggu... 😉"></common-dialog-progress>
            <delivery-order-item-name></delivery-order-item-name>
            
         </v-app>
      </div>

      <!-- Customer -->
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
    this.$store.dispatch('delivery/search', {})
},
computed : {
    one_token () {
        return this.$store.state.system.one_token
    }
},

methods : {
    
},
   el: '#app',
   components: {
        "common-navbar" : httpVueLoader("../common/components/common-navbar.vue?r=1"),
        "common-toolbar" : httpVueLoader("../common/components/common-toolbar.vue"),
        "delivery-order-list" : httpVueLoader("./components/delivery-order-list.vue?t=<?=date('YmdHis');?>"),
        "delivery-order-new" : httpVueLoader("./components/delivery-order-new.vue?t=<?=date('YmdHis');?>"),
        "delivery-order-new-sales" : httpVueLoader("./components/delivery-order-new-sales.vue?t=<?=date('YmdHis');?>"),
        "delivery-order-proforma" : httpVueLoader("./components/delivery-order-proforma.vue?t=<?=date('YmdHis');?>"),
        "delivery-order-invoice" : httpVueLoader("./components/delivery-order-invoice.vue?t=<?=date('YmdHis');?>"),
        "delivery-order-confirm" : httpVueLoader("./components/delivery-order-confirm.vue?t=<?=date('YmdHis');?>"),
        "delivery-order-calendar" : httpVueLoader("./components/delivery-order-calendar.vue?t=<?=date('YmdHis');?>"),
        "sales-order-new" : httpVueLoader("../sales-order/components/sales-order-new.vue?t=<?=date('YmdHis');?>"),
        "common-dialog-progress" : httpVueLoader("../common/components/common-dialog-progress.vue?t=<?=date('YmdHis');?>"),
        "delivery-order-item-name" : httpVueLoader("./components/delivery-order-item-name.vue?t=<?=date('YmdHis');?>")
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
