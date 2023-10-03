<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="1200px"
        transition="dialog-transition"
        content-class="dialog-new"
        persistent
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <h3>
                    <span v-show="!edit">DATA LEAD BARU</span>
                    <span v-show="!!edit&&!view">UBAH DATA LEAD</span>
                    <span v-show="!!edit&&!!view">VIEW DETAIL LEAD</span>
                </h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                        <v-layout row wrap>

                            <v-flex xs2 pr-4>
                                <common-datepicker
                                    label="Tanggal Transaksi"
                                    :date="lead_date"
                                    data="0"
                                    @change="change_lead_date"
                                    classs=""
                                    hints=" "
                                    :details="true"
                                    :solo="false"
                                    v-if="dialog"
                                ></common-datepicker>
                                <!-- <v-text-field
                                    label="Tanggal Transaksi"
                                    :value="lead_date"
                                    v-show="!!lead_post||!!is_sales||!!view"
                                    readonly
                                    :disabled="view"
                                ></v-text-field> -->

                                <v-text-field
                                    label="Nomor"
                                    v-model="lead_number"
                                    :readonly="true"
                                    :disabled="view"
                                    placeholder="( kosongkan saja )"
                                ></v-text-field>
                            </v-flex>
                            <v-flex xs10 pl-4>
                                <v-layout row wrap>
                                    <v-flex xs4>
                                        <v-select
                                            :items="staffs"
                                            v-model="selected_staff"
                                            return-object
                                            item-text="staff_name"
                                            item-value="staff_id"
                                            label="Sales"
                                            :disabled="!!view"
                                        ></v-select>
                                    </v-flex>
                                </v-layout>
                                <v-layout row wrap>
                                    <v-flex xs12>
                                        <v-text-field
                                            label="Catatan"
                                            v-model="lead_note"
                                            :readonly="!!view"
                                        ></v-text-field>
                                    </v-flex>
                                </v-layout>
                                
                            </v-flex>
                            
                            <v-flex xs12>
                                <v-card>
                                    <!-- <v-card-title primary-title class="py-2 px-3 cyan white--text">
                                    </v-card-title> -->
                                    <v-card-text class="px-3 py-2">
                                        <v-layout row wrap>
                                            <v-flex xs4>
                                                <v-layout row wrap>
                                                    <v-flex xs4>
                                                        &nbsp;
                                                    </v-flex>
                                                    <v-flex xs8>
                                                        <v-layout row wrap>
                                                            <v-flex xs4 v-for="(p, m) in prospects" :key="p.prospect_id" class="subheading text-xs-right cyan py-2 px-3 white--text">
                                                                {{p.prospect_name}}
                                                            </v-flex>        
                                                        </v-layout>
                                                    </v-flex>
                                                </v-layout>
                                                <v-layout row wrap v-for="(ct, n) in customer_types" :key="ct.val">
                                                    <v-flex xs4 class="cyan py-2 px-3 white--text">
                                                        {{ct.text}}    
                                                    </v-flex>
                                                    <v-flex xs8>
                                                        <v-layout row wrap>
                                                            <v-flex xs4 v-for="(p, m) in prospects" :key="p.prospect_id" pa-1>
                                                                <v-text-field
                                                                    solo
                                                                    hide-details
                                                                    reverse
                                                                    :value="values['Px'+p.prospect_id][ct.val]"
                                                                    @input="set_value('Px'+p.prospect_id, ct.val, $event)"
                                                                    :readonly="!!view"
                                                                ></v-text-field>
                                                            </v-flex>
                                                        </v-layout>
                                                    </v-flex>
                                                </v-layout>
                                                <!-- TOTAL -->
                                                <v-layout row wrap><v-flex xs8 offset-xs4><v-divider class="my-1"></v-divider></v-flex></v-layout>
                                                <v-layout row wrap>
                                                    <v-flex xs4 class="py-2 px-3 font-weight-bold">
                                                        T O T A L
                                                    </v-flex>
                                                    <v-flex xs8>
                                                        <v-layout row wrap>
                                                            <v-flex xs4 v-for="(p, m) in prospects" :key="p.prospect_id" pa-1>
                                                                <v-text-field
                                                                    solo
                                                                    hide-details
                                                                    reverse
                                                                    :value="values['Px'+p.prospect_id].total"
                                                                    readonly
                                                                    class="font-weight-bold"
                                                                ></v-text-field>
                                                            </v-flex>
                                                        </v-layout>
                                                    </v-flex>
                                                </v-layout>
                                                <!-- END Of TOTAL -->
                                            </v-flex>

                                            <v-flex xs8>
                                                <v-layout row wrap>
                                                    <v-flex xs4>
                                                        <v-layout row wrap>
                                                            <v-flex xs4>&nbsp;</v-flex>
                                                            <template v-for="x in 2">
                                                                <v-flex xs4 :key="x" class="subheading text-xs-right cyan py-2 px-3 white--text">{{categories[x-1].category_name}}</v-flex>
                                                            </template>
                                                        </v-layout>
                                                    </v-flex>
                                                    <v-flex xs8>
                                                        <v-layout row wrap>
                                                            <template v-for="x in 8">
                                                                <v-flex xs2 :key="x" v-if="x>2" class="subheading text-xs-right cyan py-2 px-3 white--text">{{categories[x-1].category_name}}</v-flex>
                                                            </template>
                                                            <!-- <v-flex xs2>&nbsp;</v-flex> -->
                                                        </v-layout>
                                                    </v-flex>
                                                </v-layout>

                                                <v-layout row wrap v-for="(ct, n) in customer_types" :key="ct.val">
                                                    <v-flex xs4>
                                                        <v-layout row wrap>
                                                            <v-flex xs4>&nbsp;</v-flex>
                                                            <template v-for="x in 2">
                                                                <v-flex xs4 :key="x" pa-1>
                                                                    <v-text-field 
                                                                        solo 
                                                                        hide-details 
                                                                        reverse 
                                                                        :value="values['Cx'+categories[x-1].category_id][ct.val]"
                                                                        @input="set_value('Cx'+categories[x-1].category_id, ct.val, $event)"
                                                                        :readonly="!!view"></v-text-field></v-flex>
                                                            </template>
                                                        </v-layout>
                                                    </v-flex>
                                                    <v-flex xs8>
                                                        <v-layout row wrap>
                                                            <template v-for="x in 8">
                                                                <v-flex xs2 :key="x" v-if="x>2" pa-1><v-text-field solo hide-details reverse :value="values['Cx'+categories[x-1].category_id][ct.val]"
                                                                @input="set_value('Cx'+categories[x-1].category_id, ct.val, $event)"></v-text-field></v-flex>
                                                            </template>
                                                            <!-- <v-flex xs2>&nbsp;</v-flex> -->
                                                        </v-layout>
                                                    </v-flex>
                                                </v-layout>

                                                <!-- TOTAL -->
                                                <v-layout row wrap><v-flex xs10 offset-xs1><v-divider class="my-1"></v-divider></v-flex></v-layout>
                                                <v-layout row wrap>
                                                    <v-flex xs4>
                                                        <v-layout row wrap>
                                                            <v-flex xs4>&nbsp;</v-flex>
                                                            <template v-for="x in 2">
                                                                <v-flex xs4 :key="x" pa-1><v-text-field solo hide-details reverse :value="values['Cx'+categories[x-1].category_id].total" readonly class="font-weight-bold"
                                                                ></v-text-field></v-flex>
                                                            </template>
                                                        </v-layout>
                                                    </v-flex>
                                                    <v-flex xs8>
                                                        <v-layout row wrap>
                                                            <template v-for="x in 8">
                                                                <v-flex xs2 :key="x" v-if="x>2" pa-1><v-text-field solo hide-details reverse :value="values['Cx'+categories[x-1].category_id].total" readonly class="font-weight-bold"></v-text-field></v-flex>
                                                            </template>
                                                            <!-- <v-flex xs2>&nbsp;</v-flex> -->
                                                        </v-layout>
                                                    </v-flex>
                                                </v-layout>
                                                <!-- END OF TOTAL -->

                                            </v-flex>
                                        </v-layout>                                        
                                    </v-card-text>
                                </v-card>                                
                            </v-flex>
                        </v-layout>
                    </v-flex>
                </v-layout>
            </v-card-text>

            <v-card-actions>
                <v-btn color="primary" flat @click="dialog=!dialog" v-show="!view">Batal</v-btn>
                <v-spacer></v-spacer>
                <v-btn color="primary" @click="save" :disabled="!btn_save_enabled" :dark="btn_save_enabled" v-show="!view">Simpan</v-btn>
                <v-btn color="primary" @click="dialog=!dialog" v-show="view">Tutup</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<style>
.input-dense .v-input__control {
    min-height: 36px !important;
}

.dialog-new .v-input__prepend-outer {
    margin: 0px !important;
}

.dialog-new .v-text-field.v-text-field--solo .v-input__control {
    min-height: 36px;
    padding: 0;
}

.dialog-new .v-input__append-outer {
    margin: 0px !important;
}

.dialog-new .v-input__append-outer button {
    min-height: 36px;
}

.dialog-new .subheading {
    min-height: 64px;
}
</style>
<script>
module.exports = {
    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue')
    },

    data () {
        return { }
    },

    computed : {
        view () {
            return this.$store.state.view
        },

        dialog : {
            get () { return this.$store.state.lead_new.dialog_new },
            set (v) { this.$store.commit('lead_new/set_common', ['dialog_new', v]) }
        },

        lead_note : {
            get () { return this.$store.state.lead_new.lead_note },
            set (v) { this.$store.commit('lead_new/set_common', ['lead_note', v]) }
        },

        lead_number : {
            get () { return this.$store.state.lead_new.lead_number },
            set (v) { this.$store.commit('lead_new/set_common', ['lead_number', v]) }
        },

        details () {
            return this.$store.state.lead_new.details
        },

        lead_date () {
            return this.$store.state.lead_new.lead_date
        },

        btn_save_enabled () {
            // let ttl = this.total
            // if (ttl.debit == 0 && ttl.credit == 0) return false
            // if (ttl.debit != ttl.credit) return false
            // if (this.lead_note == '') return false
            // if (!!this.$store.state.lead_new.save) return false
            // if (!!this.lead_post) return false
            // if (this.view) return false

            // for (let d of this.details)
            //     if (!d.account && (Math.round(d.credit) > 0 || Math.round(d.debit) > 0))
            //         return false
            
            if (!this.selected_staff) return false
            if (!!this.view) return false

            return true
        },

        is_sales() {
            if (this.$store.state.filter.indexOf("J.03")>-1)
                return true
            return false
        },

        edit() {
            return this.$store.state.lead_new.edit
        },

        // view () {
        //     return this.$store.state.lead_new.view
        // },

        categories () {
            return this.$store.state.lead_new.categories
        },

        selected_category : {
            get () { return this.$store.state.lead_new.selected_category },
            set (v) { this.$store.commit('lead_new/set_selected_category', v) }
        },

        prospects () {
            return this.$store.state.lead_new.prospects
        },

        selected_prospect : {
            get () { return this.$store.state.lead_new.selected_prospect },
            set (v) { this.$store.commit('lead_new/set_selected_prospect', v) }
        },

        customer_types () {
            return this.$store.state.lead_new.customer_types
        },

        staffs () {
            return this.$store.state.lead_new.staffs
        },

        selected_staff : {
            get () { return this.$store.state.lead_new.selected_staff },
            set (v) { this.$store.commit('lead_new/set_selected_staff', v) }
        },

        values : {
            get () { return this.$store.state.lead_new.values },
            set (v) { this.$store.commit('lead_new/set_values', v) }
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        one_mask_money (x) {
            return window.one_mask_money(x)
        },

        save () {
            this.$store.dispatch('lead_new/save')
        },

        change_lead_date(x) {
            this.$store.commit('lead_new/set_common', ['lead_date', x.new_date])
        },

        set_value (idx, idxx, v) {            
            let x = this.values
            x[idx][idxx] = v
            x[idx].total = Math.round(x[idx].b2c)+Math.round(x[idx].b2b)
            this.$store.commit('lead_new/set_values', x)
        }
    },

    mounted () {
        this.$store.dispatch('lead_new/search_staff')
    },

    watch : {}
}
</script>