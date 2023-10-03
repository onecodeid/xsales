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
                  <v-layout row wrap>
                     <v-flex xs12>
                        <v-tabs
                           centered
                           color="cyan"
                           dark
                           @change="selectTab"
                           v-model="active_tab"
                        >
                           <v-tabs-slider color="yellow"></v-tabs-slider>

                           <v-tab :href="'#tab-'+t.id" v-for="(t,n) in tabs" :key="t.id">
                              <a :href="'./?tab='+t.id">{{ t.text }}</a>
                           </v-tab>

                           <v-tab-item
                              v-for="(t,n) in tabs"
                              :key="t.id"
                              :value="'tab-' + t.id"
                           >
                           </v-tab-item>
                        </v-tabs>
                     </v-flex>
                  </v-layout>
                  <v-layout row>
                     <v-flex xs12>
                        <an-inventory-pareto-list v-if="selected_tab.id==0"></an-inventory-pareto-list>
                        <an-inventory-pareto-nominal-list v-if="selected_tab.id==1"></an-inventory-pareto-nominal-list>
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
      <script src="../../assets/js/moment.min.js"></script>
      <script src="../../assets/js/numeral.min.js"></script>
      <script src="../../assets/js/moment-locale-id.js"></script>
      <script src="../../assets/js/lodash.js"></script>
      <script src="../../assets/js/axios.min.js"></script>
      <script src="../../assets/js/vue.js"></script>
      <script src="../../assets/js/vuex.js"></script>
      <script src="../../assets/js/vuetify.js"></script>
      <script src="../../assets/js/httpVueLoader.js"></script>
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

.v-tabs a {
   color: inherit !important;
   text-decoration: inherit !important;
}
</style>

<script type="module">

import { store } from './store.js<?php echo $ts ?>';
//for testing
window.store = store;
new Vue({
store,
data : {
   tmp : 0
},
mounted: function() {
    if (!this.$store.state.system.one_token)
        window.location.replace('../system-login/')

      <?php if (isset($_GET['tab'])) { ?>
         this.$store.commit('inventory/set_object', ['selected_tab', this.tabs[<?=$_GET['tab'];?>]])
      <?php } ?>

      
         this.$store.dispatch('inventory/search_warehouse') .then((x) => {
            this.$store.commit('inventory/set_selected_warehouse', x.records[0])
            this.$store.dispatch('inventory/search').then((y) => {
               this.$store.dispatch('inventory/searchNominal')
            })
         })  
      
         
      
      
    
    this.$store.dispatch('system/search_menu_group')
},
computed : {
    one_token () {
        return this.$store.state.system.one_token
    },

    tabs () {
      return this.$store.state.inventory.tabs
    },

    selected_tab : {
      get () { return this.$store.state.inventory.selected_tab },
      set (v) { 
         this.$store.commit('inventory/set_object', ['selected_tab', v])
      }
    },

    active_tab : {
      get () { 
         if (!this.selected_tab) return 'tab-0'
         else return 'tab-'+this.selected_tab.id 
      },
      set (v) { this.tmp = v }
    }
},

methods : {
    selectTab (x) {
      let tabId = x.replace(/(tab\-)/, "")
      for (let t of this.tabs)
         if (t.id == tabId) {
            this.selected_tab = t
         }
    }
},
   el: '#app',
   components: {
        "common-navbar" : httpVueLoader("../common/components/common-navbar.vue?t=123"),
        "common-toolbar" : httpVueLoader("../common/components/common-toolbar.vue"),
        "an-inventory-pareto-list" : httpVueLoader("./components/an-inventory-pareto-graph.vue?t=123"),
        "an-inventory-pareto-nominal-list" : httpVueLoader("./components/an-inventory-pareto-graph-nominal.vue?t=123")
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
