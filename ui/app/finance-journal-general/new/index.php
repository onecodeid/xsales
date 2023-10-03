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
                        <finance-journal-general-new></finance-journal-general-new>
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
   //  this.$store.commit("cashNew/set_object", ["cash_type_code", "CASH.PAY"])

   // GET ID
   <?php if (isset($_GET['id'])) { ?>
      this.$store.commit("generalNew/set_object", ["journal_id", <?= $_GET['id']; ?>])
   <?php } ?>

   // SET DETAILS
   let detail_default = this.$store.state.generalNew.detail_default
   let details = []
   for (let n = 0; n < this.$store.state.generalNew.detail_cnt; n++) { 
      details.push(detail_default)
   }
   this.setNewObject("details", details)

   this.$store.dispatch('generalNew/search_account').then(resx => {
      this.$store.dispatch('generalNew/search_id').then(resy => {
         let x = resy
         this.$store.commit('general/set_object', ['selected_journal', x])

         this.setNewObject("edit", true)
         this.setNewObjects(x, [
               "journal_id",
               "journal_date",
               "journal_note",
               "journal_number"
         ])

         let details = JSON.parse(JSON.stringify(this.$store.state.generalNew.details))
         for (let n in details) {
               if (x.details[n]) {
                  x.details[n].account = x.details[n].accountx
                  details[n] = x.details[n]
               }
               else {
                  details[n].account = null
                  details[n].debit = 0
                  details[n].credit = 0
               }
         }
         this.setNewObject("details", details)
         this.setNewObject("sa", true)
      })
   })
},
computed : {
    one_token () {
        return this.$store.state.system.one_token
    }
},

methods : {
   setNewObject(x, y) {
      this.$store.commit('generalNew/set_object', [x, y])
   },

   setNewObjects(x, y) {
      for (let v of y)
         this.setNewObject(v, x[v])
   }
},
   el: '#app',
   components: {
        "common-navbar" : httpVueLoader("../../common/components/common-navbar.vue?t=123"),
        "common-toolbar" : httpVueLoader("../../common/components/common-toolbar.vue"),
        "finance-journal-general-new" : httpVueLoader("../components/finance-journal-general-new-sa.vue<?= $ts; ?>")
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
