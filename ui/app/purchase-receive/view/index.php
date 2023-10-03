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
               <v-container pt-1 pl-1 pr-1 fluid>
                  <v-layout row>
                     <v-flex xs12>
                        <receive-order-new-form></receive-order-new-form>
                     </v-flex>
                  </v-layout>
               </v-container>
            </v-content>
            
            <v-footer class="mb-5 footer" app color="transparent">
                <v-spacer></v-spacer>
                
            </v-footer>
            
            <!-- <trans-journal-view></trans-journal-view> -->
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
    this.$store.dispatch('system/get_conf')

    this.$store.commit('receive_new/set_object', ['receive_id', '<?=$_GET['id'];?>'])
    this.$store.dispatch('receive_new/search_id').then((a) => {
      
   //    this.$store.commit('invoice_new/set_object', ['search', a.customer_name])
      this.$store.dispatch('receive_new/search_vendor').then((x) => {
         this.$store.dispatch('receive_new/search_warehouse').then((y) => {
//          this.$store.dispatch('invoice_new/search_term').then((z) => {
               
            let sc = a

            this.$store.commit('receive_new/set_common', ['edit', true])
            this.$store.commit('receive_new/set_common', ['receive_id', sc.receive_id])
            this.$store.commit('receive_new/set_common', ['receive_date', sc.receive_date])
            this.$store.commit('receive_new/set_common', ['receive_note', sc.receive_note])
            this.$store.commit('receive_new/set_common', ['receive_memo', sc.receive_memo])
            this.$store.commit('receive_new/set_common', ['receive_number', sc.receive_number])
            this.$store.commit('receive_new/set_common', ['receive_ref_number', sc.receive_ref_number])

            this.$store.commit('receive_new/set_selected_vendor', null)
            this.$store.commit('receive_new/set_selected_warehouse', null)
            for (let v of this.$store.state.receive_new.vendors)
                if (v.vendor_id == sc.vendor_id)
                    this.$store.commit('receive_new/set_selected_vendor', v)
            for (let v of this.$store.state.receive_new.warehouses)
                if (v.warehouse_id == sc.warehouse_id)
                    this.$store.commit('receive_new/set_selected_warehouse', v)

            let details = sc.items
            this.$store.commit('receive_new/set_details', details)

//             })
         })
      })
      
   })
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
        "common-navbar" : httpVueLoader("../../common/components/common-navbar.vue?r=1"),
        "common-toolbar" : httpVueLoader("../../common/components/common-toolbar.vue"),
        "receive-order-new-form" : httpVueLoader("../components/receive-order-new-form.vue?t=<?=date('YmdHis')?>"),
      //   "trans-journal-view" : httpVueLoader("../trans-journal/components/trans-journal-new.vue?t=<?=date('YmdHis')?>"),
      //   "common-dialog-progress" : httpVueLoader("../common/components/common-dialog-progress.vue?t=<?=date('YmdHis');?>")
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
