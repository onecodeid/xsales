// State
//  data ...
// Mutations
//
//
// Actions

// const report = () =>
//     import ('./modules/report.js?t=asd');
// const report_param = () =>
//     import ('./modules/report_param.js?a1sdsdwasseassss')
import report from "./modules/report.js?t=afsd"
import report_param from "./modules/report_param.js?ss";
import sales015 from "./modules/sales-015.js?t=123s"
import sales016 from "./modules/sales-016.js?t=123s"
import sales017 from "./modules/sales-017.js?t=123ws"
import purchase002 from "./modules/purchase-002.js?t=12ss3s"
import purchase003 from "./modules/purchase-003.js?t=123s"
import purchase004 from "./modules/purchase-004.js?t=123s"
import purchase005 from "./modules/purchase-005.js?t=123s"
import purchase006 from "./modules/purchase-006.js?t=123s"
import iv003 from "./modules/iv-003.js?t=123ss"
import iv004 from "./modules/iv-004.js?t=123ss"
import iv005 from "./modules/iv-005.js?t=123ss"
import iv006 from "./modules/iv-006.js?t=123ss"
import iv007 from "./modules/iv-007.js?t=123ss"
import iv008 from "./modules/iv-008.js?t=123ss"
import iv009 from "./modules/iv-009.js?t=123ss"
import iv011 from "./modules/iv-011.js?t=123ss"
import iv012 from "./modules/iv-012.js?t=123ss"
import fin009 from "./modules/fin-009.js?t=123sss"
import fin008 from "./modules/fin-008.js?t=123sss"
import fin006 from "./modules/fin-006.js?t=123sss"
import fin001 from "./modules/fin-001.js?t=123ssss"
import fin002 from "./modules/fin-002.js?t=12s3ssssss"
import fin003 from "./modules/fin-003.js?t=123zxcsssss"
import fin005 from "./modules/fin-005.js?t=123"
import fin007 from "./modules/fin-007.js?t=12s3s"
import fin010 from "./modules/fin-010.js?t=123"
import log001 from "./modules/log-001.js?t=12s3s"
import invoice_new from "../sales-invoice/modules/invoice_new.js?t=asd"
import bill_new from "../finance-bill/modules/bill_new.js?t=asd"
import bill from "../finance-bill/modules/bill.js?t=asd"
// import journal from "../trans-journal/modules/journal.js?1234"
import journal_new from "../trans-journal/modules/journal_new.js?t=123"
import system from "../assets/js/system.js";

export const store = new Vuex.Store({
    state: {
        dialog_delete: false,
        dialog_confirm: false,
        dialog_print: false,
        dialog_progress: false,
        filter: [],
        jtype: ["J.01"],
        title: "JURNAL UMUM / MEMORIAL"
    },

    mutations: {
        set_dialog_delete(state, v) {
            state.dialog_delete = v
        },

        set_dialog_confirm(state, v) {
            state.dialog_confirm = v
        },

        set_dialog_print(state, v) {
            state.dialog_print = v
        },

        set_dialog_progress(state, v) {
            state.dialog_progress = v
        }
    },

    modules: {
        report: report,
        report_param: report_param,
        sales015: sales015,
        sales016: sales016,
        sales017: sales017,
        purchase002: purchase002,
        purchase003: purchase003,
        purchase004: purchase004,
        purchase005: purchase005,
        purchase006: purchase006,
        iv003: iv003,
        iv004: iv004,
        iv005: iv005,
        iv006: iv006,
        iv007: iv007,
        iv008: iv008,
        iv009: iv009,
        iv011: iv011,
        iv012: iv012,
        fin006: fin006,
        fin002: fin002,
        fin001: fin001,
        fin003: fin003,
        fin005: fin005,
        fin007: fin007,
        fin008: fin008,
        fin009: fin009,
        fin010: fin010,
        log001: log001,

        // misc
        invoice_new: invoice_new,
        bill_new: bill_new,
        bill: bill,
        // journal: journal,
        journal_new: journal_new,
        system: system
    }
});