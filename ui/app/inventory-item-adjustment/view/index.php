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
                        <new-form></new-form>
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

    this.$store.commit('adjustment_new/set_object', ['adjustment_id', <?=$_GET['id'];?>])
    this.$store.dispatch('adjustment_new/search_id').then((a) => {
      

      // // this.$store.dispatch('receive_new/search_vendor').then((x) => {
      this.$store.dispatch('adjustment_new/search_warehouse').then((y) => {

         let sc = a
         this.$store.commit('adjustment_new/set_common', ['edit', true])
            this.$store.commit('adjustment_new/set_common', ['adjustment_note', sc.I_AdjustNote])
            this.$store.commit('adjustment_new/set_common', ['adjustment_number', sc.I_AdjustNumber])

            this.$store.commit('adjustment/set_selected_adjustment', sc)
            this.$store.dispatch('adjustment_new/search_detail', {})
            // this.$store.commit('adjustment_new/set_common', ['adjustment_address', sc.I_AdjustAddress])
            // this.$store.commit('adjustment_new/set_common', ['search_city', sc.full_address])
            // this.$store.commit('adjustment_new/set_selected_city', {kelurahan_id:sc.kelurahan_id,full_address:sc.full_address})

            this.$store.commit('adjustment_new/set_selected_warehouse', null)
            for (let w of this.warehouses) {
               if (w.warehouse_id == sc.I_AdjustM_WarehouseID)
                    this.$store.commit('adjustment_new/set_selected_warehouse', w)
            }
                

            
      //    let sc = a

      //    this.$store.commit('transfer_new/set_common', ['edit', true])
      //    this.$store.commit('transfer_new/set_common', ['transfer_date', sc.I_TransferDate.substr(0,10)])
      //    this.$store.commit('transfer_new/set_common', ['transfer_note', sc.I_TransferNote])
      //    this.$store.commit('transfer_new/set_common', ['transfer_number', sc.I_TransferNumber])

      //    // patch
      //    this.$store.commit('transfer/set_selected_transfer', sc)
      //    this.$store.dispatch('transfer_new/search_detail', {})
      //    this.$store.commit('transfer_new/set_selected_warehouse', null)
      //    for (let w of this.warehouses)
      //          if (w.warehouse_id == sc.I_TransferM_WarehouseID)
      //             this.$store.commit('transfer_new/set_selected_warehouse', w)

      //    this.$store.commit('transfer_new/set_selected_to_warehouse', null)
      //    for (let w of this.warehouses)
      //          if (w.warehouse_id == sc.I_TransferToM_WarehouseID)
      //             this.$store.commit('transfer_new/set_selected_to_warehouse', w)

      })
      // // })
      
   })
},
computed : {
    one_token () {
        return this.$store.state.system.one_token
    },

   warehouses () {
      return this.$store.state.adjustment_new.warehouses
   }
},

methods : {
    
},
   el: '#app',
   components: {
        "common-navbar" : httpVueLoader("../../common/components/common-navbar.vue?r=1"),
        "common-toolbar" : httpVueLoader("../../common/components/common-toolbar.vue"),
        "new-form" : httpVueLoader("../components/iv-item-adjustment-new-form.vue?t=<?=date('YmdHis')?>"),
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
