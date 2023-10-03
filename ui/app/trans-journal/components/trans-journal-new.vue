<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="800px"
        transition="dialog-transition"
        content-class="dialog-new"
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <h3>
                    <span v-show="!is_sales&&!edit&&!view">INPUT MEMORIAL BARU</span>
                    <span v-show="!is_sales&&!!edit&&!view">UBAH MEMORIAL</span>
                    <span v-show="is_sales||view">DETAIL JURNAL</span>
                </h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                        <v-layout row wrap>

                            <v-flex xs3>
                                <common-datepicker
                                    label="Tanggal Transaksi"
                                    :date="journal_date"
                                    data="0"
                                    @change="change_journal_date"
                                    classs=""
                                    hints=" "
                                    :details="true"
                                    :solo="false"
                                    v-if="dialog&&!journal_post&&!is_sales&&!view"
                                ></common-datepicker>
                                <v-text-field
                                    label="Tanggal Transaksi"
                                    :value="journal_date"
                                    v-show="!!journal_post||!!is_sales||!!view"
                                    readonly
                                    :disabled="view"
                                ></v-text-field>

                                <v-text-field
                                    label="Nomor Bukti"
                                    v-model="journal_receipt"
                                    :readonly="journal_post||is_sales"
                                    :disabled="view"
                                ></v-text-field>
                            </v-flex>
                            <v-flex xs9 pl-5>
                                <v-textarea
                                    label="Catatan"
                                    rows="4"
                                    v-model="journal_note"
                                    outline
                                    :readonly="journal_post||is_sales"
                                    :disabled="view"
                                ></v-textarea>
                            </v-flex>


                            <v-flex xs12>
                                <v-card>
                                    <v-card-title primary-title class="py-2 px-3 cyan white--text">
                                        <v-layout row wrap>
                                            <v-flex xs7><h3 class="subheading">PERKIRAAN</h3></v-flex>
                                            <v-flex xs5>
                                                <v-layout row wrap>
                                                    <v-flex xs6 pr-2>
                                                        <h3 class="subheading text-xs-right">DEBIT</h3>
                                                    </v-flex>
                                                    <v-flex xs6>
                                                        <h3 class="subheading text-xs-right">KREDIT</h3>
                                                    </v-flex>
                                                </v-layout>
                                            </v-flex>
                                        </v-layout>
                                    </v-card-title>
                                    <v-card-text class="px-3 py-2">
                                        <v-layout row wrap v-for="(d, n) in details" :key="n" :class="{'mt-2':n>0}">
                                            <v-flex xs7 pr-3>
                                                <v-autocomplete
                                                    :items="accounts"
                                                    :value="d.account"
                                                    return-object
                                                    solo
                                                    hide-details
                                                    :clearable="d.post!='Y'&&!is_sales&&!view"
                                                    :readonly="!!d.account"
                                                    @change="update_account(n, $event)"
                                                    item-text="account_name"
                                                    item-value="account_id"
                                                    placeholder="Pilih..."
                                                >
                                                    <template slot="item" slot-scope="data">
                                                        <span class="blue--text mr-2">{{data.item.account_code}}</span> {{data.item.account_name}}
                                                    </template>

                                                    <template slot="selection" slot-scope="data">
                                                        <span class="blue--text mr-2">{{data.item.account_code}}</span> {{data.item.account_name}}
                                                    </template>

                                                    <template slot="prepend" v-if="!is_sales">
                                                        <v-btn color="red" class="ma-0 mr-1" icon :dark="false" flat @click="del_detail(n)" :disabled="d.post=='Y'||is_sales||view"><v-icon>delete</v-icon></v-btn>
                                                    </template>
                                                </v-autocomplete>
                                            </v-flex>
                                            <v-flex xs5>
                                                <v-layout row wrap>
                                                    <v-flex xs6 pr-2>
                                                        <v-text-field
                                                            label=""
                                                            solo
                                                            hide-details
                                                            :value="d.debit"
                                                            reverse
                                                            dense
                                                            @change="set_details"
                                                            @input="update_amount(n, 'debit', $event)"
                                                            :mask="one_mask_money(d.debit)"
                                                            suffix="Rp"
                                                            :readonly="d.post=='Y'||is_sales||view"
                                                        ></v-text-field>
                                                    </v-flex>
                                                    <v-flex xs6 pl-2>
                                                        <v-text-field
                                                            label=""
                                                            solo
                                                            hide-details
                                                            :value="d.credit"
                                                            reverse
                                                            dense
                                                            @change="set_details"
                                                            @input="update_amount(n, 'credit', $event)"
                                                            :mask="one_mask_money(d.credit)"
                                                            suffix="Rp"
                                                            :readonly="d.post=='Y'||is_sales||view"
                                                        ></v-text-field>
                                                    </v-flex>
                                                </v-layout>    
                                            </v-flex>
                                            
                                        </v-layout>

                                        <v-layout row wrap v-show="!is_sales&&!view">
                                            <v-flex xs12 class="text-xs-center" pt-2>
                                                <v-btn color="primary btn-icon" @click="add_detail" :disabled="!btn_add_enabled"><v-icon>add</v-icon></v-btn>
                                            </v-flex>
                                        </v-layout>
                                        
                                    </v-card-text>
                                </v-card>

                                
                                <v-layout row wrap>
                                    <v-flex xs12 px-3 pt-3>
                                        <v-layout row wrap>
                                            <v-flex xs7 py-3>
                                                <h3 class="title">TOTAL</h3>
                                            </v-flex>
                                            <v-flex xs5>
                                                <v-layout row wrap>
                                                    <v-flex xs6 pr-2>
                                                        <v-text-field
                                                            label=""
                                                            solo
                                                            hide-details
                                                            :value="one_money(total.debit)"
                                                            reverse
                                                            dense
                                                            flat
                                                            suffix="Rp"
                                                            readonly
                                                        ></v-text-field>
                                                    </v-flex>
                                                    <v-flex xs6 pl-2>
                                                        <v-text-field
                                                            label=""
                                                            solo
                                                            hide-details
                                                            :value="one_money(total.credit)"
                                                            reverse
                                                            dense
                                                            flat
                                                            suffix="Rp"
                                                            readonly
                                                        ></v-text-field>
                                                    </v-flex>
                                                </v-layout>
                                            </v-flex>    
                                        </v-layout>
                                    </v-flex>
                                    
                                </v-layout>
                                
                                <v-layout row wrap>
                                    <v-flex xs12>
                                        <v-chip
                                            label
                                            small
                                            class="blue--text mr-2"
                                            v-for="t in tag_names"
                                            :key="t"
                                            >
                                            <span class="pr-2">
                                                {{ t }}
                                            </span>
                                        </v-chip>
                                    </v-flex>
                                </v-layout>
                            </v-flex>
                        </v-layout>
                    </v-flex>
                </v-layout>
            </v-card-text>

            <v-card-actions>
                <v-btn color="primary" flat @click="dialog=!dialog" v-show="!view">Batal</v-btn>
                <v-spacer></v-spacer>
                <!-- <v-btn color="primary" @click="add_detail">Add</v-btn>                 -->
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
        dialog : {
            get () { return this.$store.state.journal_new.dialog_new },
            set (v) { this.$store.commit('journal_new/set_common', ['dialog_new', v]) }
        },

        journal_note : {
            get () { return this.$store.state.journal_new.journal_note },
            set (v) { this.$store.commit('journal_new/set_common', ['journal_note', v]) }
        },

        journal_receipt : {
            get () { return this.$store.state.journal_new.journal_receipt },
            set (v) { this.$store.commit('journal_new/set_common', ['journal_receipt', v]) }
        },

        details () {
            return this.$store.state.journal_new.details
        },

        total () {
            let x = 0
            let y = 0
            for (let j of this.details) {
                x = x + Math.round(j.debit)
                y = y + Math.round(j.credit)
            }
            return {debit:x,credit:y}
        },

        journal_date () {
            return this.$store.state.journal_new.journal_date
        },

        journal_post () {
            let x = this.$store.state
            if (!x.journal_new.edit) return false
            if (x.journal.selected_journal.journal_post == 'N') return false
            return true
        },

        accounts () {
            return this.$store.state.journal_new.accounts
        },

        btn_save_enabled () {
            let ttl = this.total
            if (ttl.debit == 0 && ttl.credit == 0) return false
            if (ttl.debit != ttl.credit) return false
            if (this.journal_note == '') return false
            if (!!this.$store.state.journal_new.save) return false
            if (!!this.journal_post) return false
            if (this.view) return false

            for (let d of this.details)
                if (!d.account && (Math.round(d.credit) > 0 || Math.round(d.debit) > 0))
                    return false

            return true
        },

        btn_add_enabled () {
            let d = this.details[this.details.length-1]
            if (!d) return false
            if (!d.account) return false
            if (this.is_sales) return false
            if (this.view) return false

            if (this.$store.state.journal_new.edit) {
                let j = this.$store.state.journal.selected_journal
                if (j.journal_post=='Y') return false
            }
            
            return true
        },

        is_sales() {
            // if (this.$store.state.filter.indexOf("J.03")>-1)
            //     return true
            return false
        },

        edit() {
            return this.$store.state.journal_new.edit
        },

        view () {
            return this.$store.state.journal_new.view
        },

        tags () {
            return this.$store.state.journal_new.tags
        },

        tag_names () {
            let tags = []
            for (let t of this.tags) tags.push(t.tag_name)

            return tags
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
            this.$store.dispatch('journal_new/save')
        },

        update_amount (idx, side, amount) {
            let d = this.details            
            d[idx][side] = amount
            // this.$store.commit('journal_new/set_details', d)
        },

        add_detail () {
            let d = this.details
            let dfl = JSON.parse(JSON.stringify(this.$store.state.journal_new.detail_default))
            d.push(dfl)
            this.$store.commit('journal_new/set_details', d)
        },

        set_details () {
            this.$store.commit('journal_new/set_details', this.details)
        },

        del_detail (x) {
            let d = this.details
            d.splice(x, 1)
            this.$store.commit('journal_new/set_details', d)
        },

        change_journal_date(x) {
            this.$store.commit('journal_new/set_common', ['journal_date', x.new_date])
        },

        update_account(idx, v) {
            let d = this.details
            d[idx].account = v
            this.$store.commit('journal_new/set_details', d)
        }
    },

    mounted () {
        this.$store.dispatch('journal_new/search_account')
    },

    watch : {}
}
</script>