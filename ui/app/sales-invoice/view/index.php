<!DOCTYPE html>
<html lang="en">

   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta http-equiv="X-UA-Compatible" content="ie=edge">
      <title>One :: Piutang Customer (View)</title>
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
                        <sales-invoice-new-form></sales-invoice-new-form>
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

    this.$store.commit('invoice/set_object', ['invoice_id', <?=$_GET['id'];?>])
    this.$store.dispatch('invoice/search_id').then((a) => {
      
      this.$store.commit('invoice_new/set_object', ['search', a.customer_name])
      this.$store.dispatch('invoice_new/search_customer').then((x) => {
         this.$store.dispatch('invoice_new/search_warehouse').then((y) => {
            this.$store.dispatch('invoice_new/search_term').then((z) => {
               
               
                  console.log(a)
                  let sc = a

                  this.$store.commit('invoice_new/set_common', ['edit', true])
                  // this.$store.commit('invoice_new/set_common', ['view', true])
                  this.$store.commit('invoice_new/set_common', ['invoice_id', sc.invoice_id])
                  this.$store.commit('invoice_new/set_common', ['invoice_date', sc.invoice_date])
                  // this.$store.commit('invoice_new/set_common', ['invoice_due_date', 
                  //     moment(sc.invoice_date, "YYYY-MM-DD").add(Math.round(sc.invoice_term), 'days').format('DD-MM-YYYY')])
                  this.$store.commit('invoice_new/set_common', ['invoice_due_date', sc.invoice_due_date.split('-').reverse().join('-')])
                  this.$store.commit('invoice_new/set_common', ['invoice_note', sc.invoice_note])
                  this.$store.commit('invoice_new/set_common', ['invoice_memo', sc.invoice_memo])
                  this.$store.commit('invoice_new/set_common', ['invoice_number', sc.invoice_number])
                  this.$store.commit('invoice_new/set_common', ['invoice_disc', sc.invoice_disc])
                  this.$store.commit('invoice_new/set_common', ['invoice_discrp', sc.invoice_discrp])
                  this.$store.commit('invoice_new/set_common', ['invoice_dp', sc.invoice_dp])
                  this.$store.commit('invoice_new/set_common', ['invoice_shipping', sc.invoice_shipping])
                  this.$store.commit('invoice_new/set_common', ['invoice_proforma', sc.invoice_proforma])
                  this.$store.commit('invoice_new/set_common', ['sales_name', sc.sales.staff_short])
                  // this.$store.commit('invoice_new/set_invoice_dps', sc.invoice_dps) 

                  this.$store.commit('invoice_new/set_selected_customer', null)
                  // this.$store.commit('invoice_new/set_selected_warehouse', null)
                  for (let v of this.$store.state.invoice_new.customers)
                     if (v.customer_id == sc.customer_id)
                        this.$store.commit('invoice_new/set_selected_customer', v)
                  
                  for (let v of this.$store.state.invoice_new.terms)
                     if (v.term_id == sc.invoice_term)
                        this.$store.commit('invoice_new/set_selected_term', v)
                  
                  let details = sc.details
                  let dfl = JSON.parse(JSON.stringify(this.$store.state.invoice_new.detail_default))

                  for (let d of details)
                     d.delivery.items = d.items
                  //     for (let a of acc)
                  //         if (a.account_id == d.account)
                  //             d.account = a

                  this.$store.commit('invoice_new/set_details', details)
                  // this.$store.commit('invoice_new/set_common', ['dialog_new', true])
                  this.$store.commit('invoice_new/set_common', ['sa', true])

                  document.title += " " + this.invoice_number
               })
            })
         })
      
    })
   
   
   //  this.$store.commit('invoice/set_object', ['invoice_id', <?=$_GET['id'];?>])
},
computed : {
    one_token () {
        return this.$store.state.system.one_token
    },

   invoice_number () {
      return this.$store.state.invoice_new.invoice_number
   }
},

methods : {
    
},
   el: '#app',
   components: {
        "common-navbar" : httpVueLoader("../../common/components/common-navbar.vue?r=1"),
        "common-toolbar" : httpVueLoader("../../common/components/common-toolbar.vue"),
        "sales-invoice-list" : httpVueLoader("./components/sales-invoice-list.vue?t=<?=date('YmdHis')?>"),
        "sales-invoice-new-form" : httpVueLoader("../components/sales-invoice-new-form.vue?t=<?=date('YmdHis')?>"),
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
