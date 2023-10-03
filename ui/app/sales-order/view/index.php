<!DOCTYPE html>
<html lang="en">

   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta http-equiv="X-UA-Compatible" content="ie=edge">
      <title>One :: Penjualan (SO - View)</title>
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
                        <sales-order-new-form></sales-order-new-form>
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

methods : {
   loadData (sc) {
      this.$store.commit('sales_new/set_common', ['edit', true])
      this.$store.commit('sales_new/set_common', ['sa', true])
      this.$store.commit('sales_new/set_common', ['view', sc.sales_done == 'Y' ? true : false])
      this.$store.commit('sales_new/set_common', ['proforma', sc.proforma?sc.proforma:false])
      this.$store.commit('sales_new/set_common', ['sales_date', sc.sales_date])
      this.$store.commit('sales_new/set_common', ['sales_note', sc.sales_note])
      this.$store.commit('sales_new/set_common', ['sales_memo', sc.sales_memo])
      this.$store.commit('sales_new/set_common', ['sales_number', sc.sales_number])
      this.$store.commit('sales_new/set_common', ['sales_disc', sc.sales_disc])
      this.$store.commit('sales_new/set_common', ['sales_discrp', sc.sales_discrp])
      this.$store.commit('sales_new/set_common', ['sales_ref', sc.sales_ref])
      this.$store.commit('sales_new/set_common', ['sales_shipping', sc.sales_shipping])
      this.$store.commit('sales_new/set_common', ['sales_dp', sc.sales_dp])
      this.$store.commit('sales_new/set_common', ['sales_proforma', sc.sales_proforma])
      this.$store.commit('sales_new/set_common', ['sales_ppn', sc.sales_ppn])
      this.$store.commit('sales_new/set_common', ['proforma_number', sc.proforma_number])
      this.$store.commit('sales_new/set_common', ['proforma_duedate', sc.proforma_duedate])

      this.$store.commit('sales_new/set_selected_customer', null)
      for (let v of this.$store.state.sales_new.customers)
         if (v.customer_id == sc.customer_id)
            this.$store.commit('sales_new/set_selected_customer', v)

      this.$store.commit('sales_new/set_selected_staff', null)
      for (let v of this.$store.state.sales_new.staffs)
         if (sc.sales_staff == v.staff_id)
            this.$store.commit('sales_new/set_selected_staff', v)

      this.$store.commit('sales_new/set_selected_offer', 
            {sales_id:sc.offer_id,sales_number:sc.offer_number,sales_date:sc.offer_date})
      this.$store.commit('sales_new/set_common', ['edits', sc.offer_id])
      this.$store.dispatch('sales_new/search_offer')

      this.$store.dispatch('sales_new/search_address').then((z5) => {
         for (let add of this.$store.state.sales_new.addresses)
            if (add.address_id == sc.address_id)
               this.$store.commit('sales_new/set_object', ['selected_address', add])
      })
      
      this.$store.commit('sales_new/set_selected_term', null)
      for (let v of this.$store.state.sales_new.terms)
            if (sc.term_id == v.term_id)
               this.$store.commit('sales_new/set_selected_term', v)

      this.$store.commit('sales_new/set_selected_expedition', null)
      for (let v of this.$store.state.sales_new.expeditions)
         if (sc.expedition_id == v.expedition_id)
            this.$store.commit('sales_new/set_selected_expedition', v)
      this.$store.commit('sales_new/set_common', ['expedition_mode', 'E'])
      this.$store.commit('sales_new/set_common', ['expedition_name', ''])

      if (sc.affiliate_id != 0) {
            for (let a of this.$store.state.sales_new.affiliates)
               if (a.affiliate_id == sc.affiliate_id)
                  this.$store.commit('sales_new/set_selected_affiliate', a)
      }

      this.$store.commit('sales_new/set_details', sc.details)
   }
},

mounted: function() {
    if (!this.$store.state.system.one_token)
        window.location.replace('../system-login/')

    this.$store.dispatch('system/search_menu_group')
    this.$store.dispatch('system/get_conf')

    this.$store.commit('sales/set_object', ['sales_id', <?=$_GET['id'];?>])
    this.$store.dispatch('sales/search_id').then((a) => {
      let sc = a
      
      this.$store.commit('sales_new/set_object', ['search', a.customer_name])
      this.$store.dispatch('sales_new/search_customer').then((x) => {
         this.$store.dispatch('sales_new/search_staff').then((y) => {
            this.$store.dispatch('sales_new/search_offer').then((z) => {
               this.$store.dispatch('sales_new/search_paymentplan').then((z1) => {
                  this.$store.dispatch('sales_new/search_expedition').then((z2) => {
                     this.$store.dispatch('sales_new/search_item').then((z3) => {
                        this.$store.dispatch('sales_new/search_item').then((z4) => {
                           this.$store.dispatch('sales_new/search_affiliate').then((z5) => {
                              this.loadData(sc)

                              document.title += (' ' + this.sales_number)
                           })
                        })
                     })
                  })
               })
            })
         })
      })
    })
},
computed : {
    one_token () {
        return this.$store.state.system.one_token
    },

   sales_number () {
      return this.$store.state.sales_new.sales_number
   }
},

   el: '#app',
   components: {
        "common-navbar" : httpVueLoader("../../common/components/common-navbar.vue?r=1"),
        "common-toolbar" : httpVueLoader("../../common/components/common-toolbar.vue"),
        "sales-order-new-form" : httpVueLoader("../components/sales-order-new-form.vue?t=<?=date('YmdHis')?>"),
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
