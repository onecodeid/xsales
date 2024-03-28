// State
//  data ...
// Mutations
//
//
// Actions
// import Chart from "../../assets/js/apache-echarts/src/core/echarts.ts"
import dashboard from "./modules/dashboard.js?t=ss12s32";
import dashboardSales from "./modules/dashboard_sales.js?t=ss12s3";
import dashboardWarehouse from "./modules/dashboard_warehouse.js?t=ss12s3";
import system from "../assets/js/system.js";

export const store = new Vuex.Store({
    state: {
        dialog_delete: false,
        dialog_print: false,
        report_url: '',
        // Chart: Chart
    },

    mutations: {
        set_dialog_delete(state, v) {
            state.dialog_delete = v
        },

        set_dialog_print(state, v) {
            state.dialog_print = v
        },

        set_report_url(state, v) {
            state.report_url = v
        }
    },

    modules: {
        dashboard: dashboard,
        dashboardSales: dashboardSales,
        dashboardWarehouse: dashboardWarehouse,
        system: system
    }
});