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
         <v-app id="oneApp"  v-if="one_token">
            <common-toolbar></common-toolbar>
            <v-content class="blue lighten-5 one" >
            <common-navbar></common-navbar>
               <v-container pt-1 pl-1 pr-1 fluid>
                  <v-layout row>
                     <v-flex xs12>
                        <sales-duplicate-offer v-show="source_from=='OFFER'"></sales-duplicate-offer>
                        <sales-duplicate-order v-if="source_from=='SALES'"></sales-duplicate-order>
                        <sales-duplicate-delivery v-if="source_from=='DELIVERY'"></sales-duplicate-delivery>
                        <sales-duplicate-invoice v-if="source_from=='INVOICE'"></sales-duplicate-invoice>
                     </v-flex>
                  </v-layout>
               </v-container>
            </v-content>
            
            <v-footer class="mb-5 footer" app color="transparent">
                <v-spacer></v-spacer>
                
            </v-footer>
            
            <master-customer-new></master-customer-new>
            <master-unit-new></master-unit-new>
            <master-pack-new></master-pack-new>
            
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
    this.$store.dispatch('system/get_conf')

    <?php 
    $from = 'OFFER';
    if (isset($_GET['id'])) {
      if (isset($_GET['from'])) $from = $_GET['from'];
      echo 'this.$store.commit("duplicateNew/set_object", ["source_id", '.$_GET['id'].']);';
      echo 'this.$store.commit("duplicateNew/set_object", ["source_from", "'.$from.'"]);';
    }
    ?>
},
computed : {
    one_token () {
        return this.$store.state.system.one_token
    },

    source_id () {
      return this.$store.state.duplicateNew.source_id
    },

   source_from () {
   return this.$store.state.duplicateNew.source_from
   }
},

methods : {
    
},
   el: '#app',
   components: {
        "common-navbar" : httpVueLoader("../common/components/common-navbar.vue?r=1"),
        "common-toolbar" : httpVueLoader("../common/components/common-toolbar.vue"),
        "sales-duplicate-offer" : httpVueLoader("./components/sales-duplicate-offer.vue?t=<?=date('YmdHis');?>"),
        "sales-duplicate-order" : httpVueLoader("./components/sales-duplicate-order.vue?t=<?=date('YmdHis');?>"),
        "sales-duplicate-delivery" : httpVueLoader("./components/sales-duplicate-delivery.vue?t=<?=date('YmdHis');?>"),
        "sales-duplicate-invoice" : httpVueLoader("./components/sales-duplicate-invoice.vue?t=<?=date('YmdHis');?>"),
        "master-customer-new" : httpVueLoader("../master-customer/components/master-customer-new.vue?t=<?=date('YmdHis');?>"),
        "master-unit-new" : httpVueLoader("../master-unit/components/master-unit-new.vue"),
        "master-pack-new" : httpVueLoader("../master-pack/components/master-pack-new.vue")
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
