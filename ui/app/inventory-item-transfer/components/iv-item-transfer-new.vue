<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="1000px"
        transition="dialog-transition"
        content-class="dialog-new"
    >
        <new-form></new-form>
    </v-dialog>
</template>

<style>
.zalfa-input-super-dense .v-input__control {
    min-height: 36px !important;
}

.zalfa-font-12 {
    font-size: 1.2em;
}

.dialog-new table.v-table thead tr {
    height: auto !important;
}
</style>

<script>
let t = Math.random()
module.exports = {
    components: {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue?t='+t),
        'new-form' : httpVueLoader('./iv-item-transfer-new-form.vue?t='+t)
    },

    data () {
        return {
            headers: [
                {
                    text: "KODE",
                    align: "center",
                    sortable: false,
                    width: "20%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "NAMA ITEM",
                    align: "center",
                    sortable: false,
                    width: "40%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "QTY SBLM",
                    align: "left",
                    sortable: false,
                    width: "15%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "QTY ADJUSTMENT",
                    align: "left",
                    sortable: false,
                    width: "15%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "QTY SSDH",
                    align: "left",
                    sortable: false,
                    width: "15%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "ACTION",
                    align: "center",
                    sortable: false,
                    width: "15%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                }
            ]
        }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.transfer_new.dialog_new },
            set (v) { this.$store.commit('transfer_new/set_common', ['dialog_new', v]) }
        },

        transfer_number : {
            get () { return this.$store.state.transfer_new.transfer_number },
            set (v) { this.$store.commit('transfer_new/set_common', ['transfer_number', v]) }
        },

        transfer_date : {
            get () { return this.$store.state.transfer_new.transfer_date },
            set (v) { this.$store.commit('transfer_new/set_common', ['transfer_date', v]) }
        },

        transfer_note : {
            get () { return this.$store.state.transfer_new.transfer_note },
            set (v) { this.$store.commit('transfer_new/set_common', ['transfer_note', v]) }
        },

        items () { 
            return this.$store.state.transfer_new.items
        },

        edit () {
            return this.$store.state.transfer_new.edit
        },

        btn_save_enable () {
            if (this.transfer_note != '' && this.items.length > 0)
                return true

            return false
        },

        warehouses () {
            return this.$store.state.transfer_new.warehouses
        },

        selected_warehouse : {
            get () { return this.$store.state.transfer_new.selected_warehouse },
            set (v) { 
                this.$store.commit('transfer_new/set_selected_warehouse', v)
            }
        },

        selected_to_warehouse : {
            get () { return this.$store.state.transfer_new.selected_to_warehouse },
            set (v) { 
                this.$store.commit('transfer_new/set_selected_to_warehouse', v)
            }
        },

        selected_transfer () {
            return this.$store.state.transfer.selected_transfer
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        select (x) {
            this.$store.commit('transfer_new/set_selected_item', x)
        },

        save () {
            this.$store.dispatch('transfer_new/save')
        },

        add_item () {
            this.$store.dispatch('transfer_new/search_item', {})
            this.$store.commit('transfer_new/set_common', ['dialog_item', true])
        },

        change_qty(i, v) {
            let x = this.items
            x[i][`item_qty`] = v
            x[i][`item_af_qty`] = parseFloat(x[i][`item_bf_qty`]) - parseFloat(v)
            x[i][`item_to_af_qty`] = parseFloat(x[i][`item_to_bf_qty`]) + parseFloat(v)

            this.$store.commit('transfer_new/set_items', x)
        },

        del (idx) {
            this.items.splice(idx, 1)
        }
    },

    watch : {
    },

    mounted () {
        
    }
}
</script>