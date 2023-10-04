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
         <v-app id="oneApp" v-if="one_token">
         <common-navbar></common-navbar>
            <common-toolbar></common-toolbar>
            <v-content class="blue lighten-5 one" >
            
               <v-container fluid :class="{'pa-3':$vuetify.breakpoint.mdAndUp,'pa-1':$vuetify.breakpoint.smAndDown}">
                    <dashboard-super-admin></dashboard-super-admin>
                    
                    <!-- <div id="mainx" style="width: 600px;height:400px;" class="cyan white--text"></div> -->
               </v-container>
            </v-content>
            
            <!-- <v-footer class="mb-5 footer" app color="transparent">
                <v-spacer></v-spacer>
                
            </v-footer> -->
            
            <common-dialog-print v-if="dialog_print" :report_url="report_url"></common-dialog-print>
         </v-app>
      </div>

      <!-- Vendor -->
      <script src="../../assets/js/moment.min.js"></script>
      <script src="../../assets/js/numeral.min.js"></script>
      <script src="../../assets/js/axios.min.js"></script>
      <script src="../../assets/js/vue.min.js"></script>
      <script src="../../assets/js/vuex.min.js"></script>
      <script src="../../assets/js/vuetify.min.js"></script>
      <script src="../../assets/js/httpVueLoader.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.1/dist/echarts.min.js"></script>
      <script src="https://d3js.org/d3.v5.min.js"></script>
      <script src="../assets/js/window_functions.min.js"></script>
        
      <script type="text/javascript">
      // Initialize the echarts instance based on the prepared dom
      // var myChart = echarts.init(document.getElementById('mainx'));

      // // Specify the configuration items and data for the chart
      // var option = {
      //   title: {
      //     text: 'ECharts Getting Started Example'
      //   },
      //   tooltip: {},
      //   legend: {
      //     data: ['sales']
      //   },
      //   xAxis: {
      //     data: ['Shirts', 'Cardigans', 'Chiffons', 'Pants', 'Heels', 'Socks']
      //   },
      //   yAxis: {},
      //   series: [
      //     {
      //       name: 'sales',
      //       type: 'bar',
      //       data: [5, 20, 36, 10, 10, 20]
      //     }
      //   ]
      // };

      // // Display the chart using the configuration items and data just specified.
      // myChart.setOption(option);
    </script>
      
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
},
computed : {
    one_token () {
        return this.$store.state.system.one_token
    },

    dialog_print : {
        get () { return this.$store.state.dialog_print },
        set (v) { this.$store.commit('set_dialog_print', v) }
    },

    report_url () {
        return this.$store.state.report_url
    },
    
    user () {
        return this.$store.state.dashboard.user
    }
},

methods : {
    
},
   el: '#app',
   components: {
        "common-navbar" : httpVueLoader("../common/components/common-navbar.vue?t=123"),
        "common-toolbar" : httpVueLoader("../common/components/common-toolbar.vue"),
        "dashboard-stat-01" : httpVueLoader("./components/dashboard-stat-01.vue"),
        "dashboard-profile" : httpVueLoader("./components/dashboard-profile.vue"),
        "common-dialog-print" : httpVueLoader("../common/components/common-dialog-print.vue"),

        "dashboard-super-admin" : httpVueLoader("./components/dashboard-blank.vue<?=$ts;?>"),
        "dashboard-admin" : httpVueLoader("./components/dashboard-admin.vue<?=$ts;?>")
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
