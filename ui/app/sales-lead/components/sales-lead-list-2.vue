<template>
    <v-card>
        <v-card-title primary-title class="pt-2 pb-0" v-show="!custom_title">
            <v-layout row wrap>
                <v-flex xs5>
                    <h3 class="display-1 font-weight-light zalfa-text-title">{{title}}</h3>
                </v-flex>
                
                <v-flex xs1 pr-1>
                    <common-datepicker
                        label="Dari Tanggal"
                        :date="s_date"
                        data="0"
                        @change="change_s_date"
                        classs=""
                        hints=" "
                        :details="false"
                        :solo="true"
                    ></common-datepicker>
                </v-flex>
                <v-flex xs1 pl-1>
                    <common-datepicker
                        label="Dari Tanggal"
                        :date="e_date"
                        data="0"
                        @change="change_e_date"
                        classs=""
                        hints=" "
                        :details="false"
                        :solo="true"
                    ></common-datepicker>
                </v-flex>

                <v-flex xs2 pl-2>
                    <v-select
                        :items="staffs"
                        v-model="selected_staff"
                        return-object
                        item-text="staff_name"
                        item-value="staff_id"
                        label="Sales"
                        solo
                        clearable
                        hide-details
                    ></v-select>
                </v-flex>

                <v-flex xs3 class="text-xs-right" pl-2>
                    <!-- <v-btn color="success" class="ma-0 btn-icon" @click="add">
                        <v-icon>add</v-icon>
                    </v-btn> -->

                    <v-text-field
                        solo
                        hide-details
                        placeholder="Pencarian" v-model="query"
                        @change="search"
                    >
                        <template v-slot:append-outer>
                            <v-btn color="primary" class="ma-0 btn-icon" @click="search">
                                <v-icon>search</v-icon>
                            </v-btn>

                            <v-btn color="orange" class="ma-0 ml-2 btn-icon" @click="print" dark>
                                <v-icon>print</v-icon>
                            </v-btn>

                            <v-btn color="success" class="ma-0 ml-2 btn-icon" @click="add" v-show="!is_sales&&!view">
                                <v-icon>add</v-icon>
                            </v-btn>  
                        </template>
                    </v-text-field>
                </v-flex>
            </v-layout>
        </v-card-title>
        <v-card-text class="pt-2">
            <table class="v-datatable v-table theme--light" style="table-layout:fixed">
                <thead>
                    <tr>
                        <th role="columnheader" scope="col" width="8%" aria-label="TANGGAL: Not sorted." aria-sort="none" class="column text-xs-left pa-2 zalfa-bg-purple lighten-3 white--text">TANGGAL</th>
                        <th role="columnheader" scope="col" width="16%" aria-label="NOMOR: Not sorted." aria-sort="none" class="column text-xs-left pa-2 zalfa-bg-purple lighten-3 white--text">NOMOR</th>
                        <!-- <th role="columnheader" scope="col" width="30%" aria-label="SALES: Not sorted." aria-sort="none" class="column text-xs-left pa-2 zalfa-bg-purple lighten-3 white--text">SALES</th> -->
                        <!-- <th role="columnheader" scope="col" width="44%" aria-label="CATATAN: Not sorted." aria-sort="none" class="column text-xs-left pa-2 zalfa-bg-purple lighten-3 white--text">CATATAN</th> -->
                        
                        <th role="columnheader" scope="col" width="6%" aria-label="CATATAN: Not sorted." aria-sort="none" class="column text-xs-right pa-2 blue lighten-1 white--text" v-for="(p, n) in prospects" :key="'p'+n">
                            {{p.prospect_name.toUpperCase()}}
                        </th>
                        <th role="columnheader" scope="col" width="6%" aria-label="CATATAN: Not sorted." aria-sort="none" class="column text-xs-right pa-2 zalfa-bg-purple lighten-3 white--text rotate-90" v-for="(c, n) in categories" :key="'c'+n" style="overflow-x:hidden" :title="c.category_name">
                            {{c.category_acronym.toUpperCase()}}
                        </th>

                        <th role="columnheader" scope="col" width="10%" aria-label="ACTION: Not sorted." aria-sort="none" class="column text-xs-center pa-2 zalfa-bg-purple lighten-3 white--text rotate-90">ACTION</th>
                    </tr>
                    <tr class="v-datatable__progress">
                        <th colspan="5" class="column"></th>
                    </tr>
                </thead>
                <tbody>
                    <template v-for="(l, n) in leads">
                        <tr :key="n">
                            <td class="text-xs-left pa-2" @click="select(l)" rowspan="2">
                                <v-layout row wrap>
                                    <v-flex xs12>{{ l.lead_date }}</v-flex>
                                    <v-flex xs12><i>— {{ l.lead_day }}</i></v-flex>
                                </v-layout>
                            </td>
                            <td class="text-xs-left pa-2" @click="select(l)" rowspan="2">
                                {{ l.lead_number }}
                                <div><b>{{ l.staff_name }}</b></div>
                            </td> 
                            <!-- <td class="text-xs-left pa-2" @click="select(l)" v-show="!is_sales">
                                {{ l.staff_name }}
                            </td>  -->
                            <!-- <td class="text-xs-left pa-2" @click="select(l)" v-show="!is_sales">
                                {{ l.lead_note }}
                            </td>  -->

                            <td class="text-xs-right pa-0" @click="select(l)" v-show="!is_sales" v-for="(p, n) in prospects" :key="'pd'+n">
                                <v-layout row wrap>
                                    <v-flex xs12 v-for="(ct, m) in customer_types" :key="ct.val" pa-2 :class="ctbgs.P[m]">
                                        {{l.values['Px'+p.prospect_id][ct.val]}}
                                    </v-flex>
                                </v-layout>
                            </td>

                            <td class="text-xs-right pa-0" @click="select(l)" v-show="!is_sales" v-for="(c, n) in categories" :key="'cd'+n">
                                <v-layout row wrap>
                                    <v-flex xs12 v-for="(ct, m) in customer_types" :key="ct.val" pa-2 :class="ctbgs.C[m]">
                                        {{l.values['Cx'+c.category_id][ct.val]}}
                                    </v-flex>
                                </v-layout>
                            </td>

                            <td class="text-xs-center pa-0" @click="select(l)" rowspan="2">
                                <v-btn color="primary" class="btn-icon ma-0" small @click="edit(l)"><v-icon>create</v-icon></v-btn>
                                <v-btn color="red" 
                                    :dark="!view"
                                    class="btn-icon ma-0" small @click="del(l)"
                                    :disabled="!!view"><v-icon>delete</v-icon></v-btn>
                                
                            </td>
                        </tr>

                        <tr :key="'x'+n">
                            <td class="text-xs-left py-1 px-2" @click="select(l)" v-show="!is_sales" :colspan="categories.length+prospects.length+1">
                                <i>{{l.lead_note}}</i></td> 
                        </tr>
                    </template>
                </tbody>

                <tfoot>
                    <tr>
                        <th role="columnheader" scope="col" width="8%" aria-label="TANGGAL: Not sorted." aria-sort="none" class="column text-xs-left pa-2 zalfa-bg-purple lighten-3 white--text">TANGGAL</th>
                        <th role="columnheader" scope="col" width="16%" aria-label="NOMOR: Not sorted." aria-sort="none" class="column text-xs-left pa-2 zalfa-bg-purple lighten-3 white--text">NOMOR</th>
                        <!-- <th role="columnheader" scope="col" width="30%" aria-label="SALES: Not sorted." aria-sort="none" class="column text-xs-left pa-2 zalfa-bg-purple lighten-3 white--text">SALES</th> -->
                        <!-- <th role="columnheader" scope="col" width="44%" aria-label="CATATAN: Not sorted." aria-sort="none" class="column text-xs-left pa-2 zalfa-bg-purple lighten-3 white--text">CATATAN</th> -->
                        
                        <th role="columnheader" scope="col" width="6%" aria-label="CATATAN: Not sorted." aria-sort="none" class="column text-xs-right pa-2 blue lighten-1 white--text" v-for="(p, n) in prospects" :key="'p'+n">
                            {{p.prospect_name.toUpperCase()}}
                        </th>
                        <th role="columnheader" scope="col" width="6%" aria-label="CATATAN: Not sorted." aria-sort="none" class="column text-xs-right pa-2 zalfa-bg-purple lighten-3 white--text rotate-90" v-for="(c, n) in categories" :key="'c'+n" style="overflow-x:hidden" :title="c.category_name">
                            {{c.category_acronym.toUpperCase()}}
                        </th>

                        <th role="columnheader" scope="col" width="10%" aria-label="ACTION: Not sorted." aria-sort="none" class="column text-xs-center pa-2 zalfa-bg-purple lighten-3 white--text rotate-90">ACTION</th>
                    </tr>
                    <tr class="v-datatable__progress">
                        <th colspan="5" class="column"></th>
                    </tr>
                </tfoot>
            </table>

            <!-- <v-data-table 
                :headers="headers"
                :items="leads"
                :loading="false"
                hide-actions
                class="elevation-1">
                <template slot="items" slot-scope="props">
                    
                    <td class="text-xs-left pa-2" @click="select(props.item)">
                        <v-layout row wrap>
                            <v-flex xs12>{{ props.item.lead_date }}</v-flex>
                            <v-flex xs12><i>— {{ props.item.lead_day }}</i></v-flex>
                        </v-layout>
                        
                    </td>
                    <td class="text-xs-left pa-2" @click="select(props.item)">{{ props.item.lead_number }}</td> 
                    <td class="text-xs-left pa-2" @click="select(props.item)" v-show="!is_sales">
                        {{ props.item.staff_name }}
                    </td> 
                    <td class="text-xs-left pa-2" @click="select(props.item)" v-show="!is_sales">
                        {{ props.item.lead_note }}
                    </td> 
                    <td class="text-xs-center pa-0" @click="select(props.item)">
                        <v-btn color="primary" class="btn-icon ma-0" small @click="edit(props.item)"><v-icon>create</v-icon></v-btn>
                        <v-btn color="red" 
                            :dark="!view"
                            class="btn-icon ma-0" small @click="del(props.item)"
                            :disabled="!!view"><v-icon>delete</v-icon></v-btn>
                        
                    </td>

                </template>
            </v-data-table> -->
            <v-divider></v-divider>
            <v-pagination
                style="margin-top:10px;margin-bottom:10px"
                v-model="curr_page"
                :length="xtotal_page"
                @input="change_page"
            ></v-pagination>
        </v-card-text>
        
        <common-dialog-print :report_url="report_url" v-if="dialog_report"></common-dialog-print>
        <common-dialog-delete :data="lead_id" @confirm_del="confirm_del" v-if="dialog_delete"></common-dialog-delete>
        <common-dialog-confirm :data="lead_id" @confirm="confirm_post" v-if="dialog_confirm" :text="text_post"></common-dialog-confirm>
    </v-card>
</template>

<style scoped>
.v-text-field.v-text-field--solo .v-input__control {
    min-height: 36px;
}
.v-text-field.v-text-field--solo .v-input__append-outer {
    margin-top: 0px;
    margin-left: 0px;
}

tfoot > tr th { 
    font-weight: normal; 
    font-size: 12px;
}

.rotate-90 {
    writing-mode: vertical-rl;
}
</style>

<script>
module.exports = {
    components : {
        "common-dialog-delete" : httpVueLoader("../../common/components/common-dialog-delete.vue"),
        "common-dialog-confirm" : httpVueLoader("../../common/components/common-dialog-confirm.vue"),
        "common-dialog-print" : httpVueLoader("../../common/components/common-dialog-print.vue"),
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue')
    },

    data () {
        return {
            headers: [
                {
                    text: "TANGGAL",
                    align: "left",
                    sortable: false,
                    width: "8%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "NOMOR",
                    align: "left",
                    sortable: false,
                    width: "8%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "SALES",
                    align: "left",
                    sortable: false,
                    width: "30%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "CATATAN",
                    align: "left",
                    sortable: false,
                    width: "44%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                },
                {
                    text: "ACTION",
                    align: "center",
                    sortable: false,
                    width: "10%",
                    class: "pa-2 zalfa-bg-purple lighten-3 white--text"
                }
            ],

            ctbgs : {P:['blue lighten-1 white--text', 'amber lighten-1 white--text'],C:['blue lighten-3', 'amber lighten-3']}
        }
    },

    computed : {
        view () {
            return this.$store.state.view
        },
        
        leads () {
            return this.$store.state.lead.leads
        },

        dialog_delete () {
            return this.$store.state.dialog_delete
        },

        dialog_confirm () {
            return this.$store.state.dialog_confirm
        },

        lead_id () {
            return this.$store.state.lead.selected_lead.M_JournalID
        },

        query : {
            get () { return this.$store.state.lead.search },
            set (v) { this.$store.commit('lead/update_search', v) }
        },

        curr_page : {
            get () { return this.$store.state.lead.current_page },
            set (v) { this.$store.commit('lead/update_current_page', v) }
        },

        xtotal_page () {
            return this.$store.state.lead.total_lead_page
        },

        text_post () {
            let j = this.$store.state.lead.selected_lead
            return "Apakah anda yakin akan melakukan Posting Jurnal tersebut ?"
        },

        s_date : {
            get () { return this.$store.state.lead.s_date },
            set (v) { this.$store.commit('lead/set_common', ['s_date', v]) }
        },

        e_date : {
            get () { return this.$store.state.lead.e_date },
            set (v) { this.$store.commit('lead/set_common', ['e_date', v]) }
        },

        title() {
            return "SALES LEAD"
        },

        is_sales() {
            if (this.$store.state.filter.indexOf("J.03")>-1)
                return true
            return false
        },

        custom_title () {
            return this.$store.state.custom_title?this.$store.state.custom_title:false
        },

        dialog_report : {
            get () { return this.$store.state.dialog_print },
            set (v) { this.$store.commit('set_dialog_print', v) }
        },

        prospects () {
            return this.$store.state.lead_new.prospects
        },

        categories () {
            return this.$store.state.lead_new.leadcategories
        },

        values : {
            get () { return this.$store.state.lead_new.values },
            set (v) { this.$store.commit('lead_new/set_values', v) }
        },

        customer_types () {
            return this.$store.state.lead_new.customer_types
        },

        report_url () {
            return this.$store.state.report_url
        },

        staffs () {
            return this.$store.state.lead_new.staffs
        },

        selected_staff : {
            get () { return this.$store.state.lead.selected_staff },
            set (v) { 
                this.$store.commit('lead/set_selected_staff', v) 
                this.search()
            }
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        add () {
            this.$store.commit('lead_new/set_common', ['edit', false])
            this.$store.commit('lead_new/set_common', ['lead_date', this.$store.state.lead_new.current_date])
            this.$store.commit('lead_new/set_common', ['lead_note', ''])
            this.$store.commit('lead_new/set_common', ['lead_number', ''])
            this.$store.commit('lead_new/set_selected_staff', null)

            // SET ZERO
            let values = {}
            for (let ctg of this.$store.state.lead_new.leadcategories)
                values['Cx'+ctg.category_id] = {b2b:0,b2c:0,total:0}
            for (let ppt of this.$store.state.lead_new.prospects)
                values['Px'+ppt.prospect_id] = {b2b:0,b2c:0,total:0}
            this.$store.commit('lead_new/set_values', values)

            this.$store.commit('lead_new/set_common', ['dialog_new', true])
        },

        edit (x) {
            this.select(x)
            let sc = x
            this.$store.commit('lead_new/set_common', ['edit', true])
            this.$store.commit('lead_new/set_common', ['lead_id', sc.lead_id])
            this.$store.commit('lead_new/set_common', ['lead_date', sc.lead_date])
            this.$store.commit('lead_new/set_common', ['lead_note', sc.lead_note])
            this.$store.commit('lead_new/set_common', ['lead_number', sc.lead_number])

            this.$store.commit('lead_new/set_selected_staff', null)
            for (let v of this.$store.state.lead_new.staffs)
                if (v.staff_id == sc.staff_id)
                    this.$store.commit('lead_new/set_selected_staff', v)

            // SET ZERO
            this.$store.commit('lead_new/set_jooo', [1,2,3,45])
            let values = {}
            for (let ctg of this.$store.state.lead_new.leadcategories)
                values['Cx'+ctg.category_id] = {b2b:0,b2c:0,total:0}
                
            for (let ppt of this.$store.state.lead_new.prospects)
                values['Px'+ppt.prospect_id] = {b2b:0,b2c:0,total:0}

            for (let d of sc.details) {
                
                if (d.d_type=='P') {
                    if (values[d.d_type+'x'+d.d_pid]) {
                        values[d.d_type+'x'+d.d_pid] = {b2b:Math.round(d.d_b2b),b2c:Math.round(d.d_b2c),total:Math.round(d.d_b2b)+Math.round(d.d_b2c)}
                    }
                        
                } else {
                    if (values[d.d_type+'x'+d.d_lcid])
                        values[d.d_type+'x'+d.d_lcid] = {b2b:Math.round(d.d_b2b),b2c:Math.round(d.d_b2c),total:Math.round(d.d_b2b)+Math.round(d.d_b2c)}
                }
            }
            
            this.$store.commit('lead_new/set_values', values)
            this.$store.commit('lead_new/set_common', ['dialog_new', true])
        },

        del (x) {
            this.select(x)
            this.$store.commit('set_dialog_delete', true)
        },

        confirm_del (x) {
            this.$store.dispatch('lead/del', {id:x.data})
        },

        select (x) {
            this.$store.commit('lead/set_selected_lead', x)
        },

        search () {
            return this.$store.dispatch('lead/search', {})
        },

        change_page(x) {
            this.curr_page = x
            this.$store.dispatch('lead/search', {})
        },

        change_s_date(x) {
            this.$store.commit('lead/set_common', ['s_date', x.new_date])
            this.$store.dispatch('lead/search')
        },

        change_e_date(x) {
            this.$store.commit('lead/set_common', ['e_date', x.new_date])
            this.$store.dispatch('lead/search')
        },

        print () {
            this.$store.commit('set_report_url', this.$store.state.lead.URL+'report/one_sales_009?sdate='+
                this.s_date+'&edate='+this.e_date)
            this.$store.commit('set_dialog_print', true)
        }
    },

    mounted () {
        // this.$store.dispatch('lead_new/search_category')
        // this.$store.dispatch('lead_new/search_prospect')
        
        if (this.is_sales)
            this.headers[2].text="NOMOR INVOICE"
    }
}
</script>