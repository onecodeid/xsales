<!DOCTYPE html>
<html lang="en">

   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta http-equiv="X-UA-Compatible" content="ie=edge">
      <title>One</title>
      <link rel="stylesheet" href="../../../assets/css/google-fonts.css">
      <link rel="stylesheet" href="../../../assets/css/vuetify.min.css">
      <link rel="stylesheet" href="../../assets/css/global.min.css">
      <link rel="icon" type="image/x-icon" href="../../assets/favicon/favicon.ico">
      <!-- <script src="../../../libs/one_global_clinic.js"></script> -->
   </head>

   <body>
      <div v-cloak id="app">
         <v-app id="oneApp"  v-if="one_token">
            <common-toolbar></common-toolbar>
            <v-content class="blue lighten-5 one" >
            <common-navbar></common-navbar>
            <!-- <common-notification></common-notification> -->
               <v-container pt-1 pl-1 pr-1 fluid>
                  <v-layout row>
                     <v-flex xs12>
                        <!-- <finance-memo-account-list v-show="selected_tab=='TAB.01'"></finance-memo-account-list> -->
                        <finance-memo-list v-show="selected_tab=='TAB.01'"></finance-memo-list>
                        <finance-memo-new-dialog></finance-memo-new-dialog>
                        <finance-memo-refund-dialog></finance-memo-refund-dialog>
                     </v-flex>
                  </v-layout>
               </v-container>
            </v-content>
            
            <v-footer class="mb-5 footer" app color="transparent">
                <v-spacer></v-spacer>
                
            </v-footer>
         </v-app>
      </div>

      <!-- Vendor -->
      <script src="../../../assets/js/moment.min.js"></script>
      <script src="../../../assets/js/numeral.min.js"></script>
      <script src="../../../assets/js/moment-locale-id.js"></script>
      <script src="../../../assets/js/lodash.js"></script>
      <script src="../../../assets/js/axios.min.js"></script>
      <script src="../../../assets/js/vue.js"></script>
      <script src="../../../assets/js/vuex.js"></script>
      <script src="../../../assets/js/vuetify.js"></script>
      <script src="../../../assets/js/httpVueLoader.js"></script>
      <script src="../../../assets/js/webcam.min.js"></script>
      <script src="../../assets/js/window_functions.min.js"></script>
      
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

import { store } from '../store.js<?php echo $ts ?>';
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

    <?php if (isset($_GET['id'])) { ?>
      this.tmp_acc_id = <?= $_GET['id']; ?>
   <?php } ?>

   <?php if (isset($_GET['search'])) { ?>
      this.$store.commit('memo/set_object', ['search', '<?=$_GET['search']?>'])
   <?php } ?>
},
computed : {
    one_token () {
        return this.$store.state.system.one_token
    },

    tabs () {
      return this.$store.state.memo.tabs
    },

    selected_tab : {
      get () { return this.$store.state.memo.selected_tab },
      set (v) { this.$store.commit('memo/set_object', ['selected_tab', v]) }
    },

    tmp_acc_id : {
      get () { return this.$store.state.memo.tmp_acc_id },
      set (v) { this.$store.commit('memo/set_object', ['tmp_acc_id', v]) }
    }
},

methods : {
    
},
   el: '#app',
   components: {
        "common-navbar" : httpVueLoader("../../common/components/common-navbar.vue?t=123"),
        "common-toolbar" : httpVueLoader("../../common/components/common-toolbar.vue"),
        "finance-memo-list" : httpVueLoader("../components/finance-memo-list.vue<?php echo $ts ?>"),
      //   "finance-memo-account-list" : httpVueLoader("../components/finance-memo-account-list.vue<?php echo $ts ?>"),
        "finance-memo-new-dialog" : httpVueLoader("../components/finance-memo-new-dialog.vue<?= $ts; ?>"),
        "finance-memo-refund-dialog" : httpVueLoader("../components/finance-memo-refund-dialog.vue<?= $ts; ?>")
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
