<template>
    <v-card>
        <v-card-title primary-title class="orange py-2 white--text">
            PROFORMA
        </v-card-title>
        <v-card-text>
            <v-text-field
                label="Nomor Proforma"
                v-model="proforma_number"
                :readonly="true"
                placeholder="( kosongkan saja )"
            ></v-text-field>

            <common-datepicker
                label="Tanggal Jatuh Tempo"
                :date="due_date"
                data="0"
                @change="change_due_date"
                classs=""
                hints=" "
                :details="true"
                :solo="false"
                v-if="dialog"
            ></common-datepicker>
        </v-card-text>
    </v-card>
</template>
<script>
module.exports = {
    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue')
    },

    data () {
        return { }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.sales_new.dialog_new },
            set (v) { this.$store.commit('sales_new/set_common', ['dialog_new', v]) }
        },

        due_date () {
            return this.$store.state.sales_new.proforma_duedate
        },

        proforma_number : {
            get () { return this.$store.state.sales_new.proforma_number },
            set (v) { this.$store.commit('sales_new/set_common', ['proforma_number', v]) }
        }
    },

    methods : {
        change_due_date(x) {
            this.$store.commit('sales_new/set_common', ['proforma_duedate', x.new_date])
        }
    },

    mounted () {
    },

    watch : {}
}
</script>