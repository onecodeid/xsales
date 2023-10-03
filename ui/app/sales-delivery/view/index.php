<!DOCTYPE html>
<html lang="en">

   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta http-equiv="X-UA-Compatible" content="ie=edge">
      <title>One :: Pengiriman (DO - View)</title>
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
                        <delivery-order-new-form></delivery-order-new-form>
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

    this.$store.commit('delivery_new/set_object', ['delivery_id', '<?=$_GET['id'];?>'])
    this.$store.dispatch('delivery_new/search_id').then((a) => {
      console.log(a)
      this.$store.commit('delivery_new/set_object', ['search', a.customer_name])
      this.$store.dispatch('delivery_new/search_customer').then((x) => {
         this.$store.dispatch('delivery_new/search_warehouse').then((y) => {
            this.$store.dispatch('sales_new/search_expedition').then((z) => {
               
               
      //             console.log(a)
                  let sc = a

                  this.$store.commit('delivery_new/set_common', ['edit', true])
                //   this.$store.commit('delivery_new/set_common', ['view', true])
                  this.$store.commit('delivery_new/set_common', ['delivery_id', sc.delivery_id])
            this.$store.commit('delivery_new/set_common', ['delivery_date', sc.delivery_date])
            this.$store.commit('delivery_new/set_common', ['delivery_note', sc.delivery_note])
            this.$store.commit('delivery_new/set_common', ['delivery_send_note', sc.delivery_send_note])
            this.$store.commit('delivery_new/set_common', ['delivery_memo', sc.delivery_memo])
            this.$store.commit('delivery_new/set_common', ['delivery_number', sc.delivery_number])
            this.$store.commit('delivery_new/set_common', ['delivery_ref_number', sc.delivery_ref_number])
            this.$store.commit('delivery_new/set_common', ['delivery_proforma', sc.delivery_proforma])

            this.$store.commit('delivery_new/set_object', ['addresses', [sc.delivery_address]])
            this.$store.commit('delivery_new/set_object', ['selected_address', sc.delivery_address])

            this.$store.commit('delivery_new/set_selected_customer', null)
            this.$store.commit('delivery_new/set_selected_warehouse', null)
            this.$store.commit('delivery_new/set_selected_staff', null)
            for (let v of this.$store.state.delivery_new.customers)
                if (v.customer_id == sc.customer_id)
                    this.$store.commit('delivery_new/set_selected_customer', v)
            for (let v of this.$store.state.delivery_new.staffs)
                if (v.staff_id == sc.delivery_staff)
                    this.$store.commit('delivery_new/set_selected_staff', v)
            for (let v of this.$store.state.delivery_new.warehouses)
                if (v.warehouse_id == sc.warehouse_id)
                    this.$store.commit('delivery_new/set_selected_warehouse', v)

            for (let vv of this.$store.state.delivery_new.deliverytypes)
                if (vv.deliverytype_id == sc.delivery_type)
                    this.$store.commit('delivery_new/set_object', ['selected_deliverytype', vv])

            for (let vv of this.$store.state.sales_new.expeditions)
                if (vv.expedition_id == sc.delivery_expedition)
                    this.$store.commit('delivery_new/set_object', ['selected_expedition', vv])

            let details = sc.items
            this.$store.commit('delivery_new/set_details', details)
            this.$store.dispatch('delivery_new/search_item')
      //             // this.$store.commit('delivery_new/set_delivery_dps', sc.delivery_dps) 

      //             this.$store.commit('delivery_new/set_selected_customer', null)
      //             // this.$store.commit('delivery_new/set_selected_warehouse', null)
      //             for (let v of this.$store.state.delivery_new.customers)
      //                if (v.customer_id == sc.customer_id)
      //                   this.$store.commit('delivery_new/set_selected_customer', v)
                  
      //             for (let v of this.$store.state.delivery_new.terms)
      //                if (v.term_id == sc.invoice_term)
      //                   this.$store.commit('delivery_new/set_selected_term', v)
                  
      //             let details = sc.details
      //             let dfl = JSON.parse(JSON.stringify(this.$store.state.delivery_new.detail_default))

      //             for (let d of details)
      //                d.delivery.items = d.items

      //             this.$store.commit('delivery_new/set_details', details)
                  this.$store.commit('delivery_new/set_common', ['sa', true])
                  document.title += (' ' + sc.delivery_number)
               })
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
        "delivery-order-new-form" : httpVueLoader("../components/delivery-order-new-form.vue?t=<?=date('YmdHis')?>"),
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
