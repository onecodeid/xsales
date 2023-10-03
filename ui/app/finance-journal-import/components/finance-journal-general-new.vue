<template>
    
            <v-layout row wrap>
                <v-flex xs6 pr-4>
                    <v-layout row wrap>
                        <v-flex xs6 pr-4>
                            <common-datepicker
                                label="Tanggal Transaksi"
                                :date="journal_date" data="0" hints=" "
                                @change="change_journal_date"
                                :details="true" :solo="false"
                                v-if="dialog || sa"
                            ></common-datepicker>
                        </v-flex>
                        <v-flex xs6>
                            <v-text-field
                                label="Nomor Transaksi"
                                :value="journal_number"
                                readonly
                            ></v-text-field>
                        </v-flex>
                    </v-layout>

                    <v-textarea
                        label="Deskripsi"
                        v-model="journal_note"
                        outline
                        rows="2"
                    >
                    </v-textarea>

                    <common-tag mode="outline" label="Tag(s)"></common-tag>

                    <!-- <v-autocomplete
                        :items="accounts"
                        return-object
                        clearable
                        v-show="!selected_account"
                        item-text="account_name"
                        item-value="account_id"
                        placeholder="Pilih..."
                        item-disabled="parent"
                        v-model="selected_account"
                        label="Akun / Rekening KREDIT"
                    >
                        <template slot="item" slot-scope="data">
                            <div class="v-list__tile__content">
                                <div class="v-list__tile__title"><span class="blue--text mr-2">{{data.item.account_code}}</span> {{data.item.account_name}}</div> 
                            </div>
                            
                        </template>

                        <template slot="selection" slot-scope="data">
                            <v-layout row wrap>
                                <div class="v-list__tile__title"><span class="blue--text mr-2">{{data.item.account_code}}</span> {{data.item.account_name}}</div>
                            </v-layout>
                        </template>
                    </v-autocomplete> -->
                </v-flex>

                <v-flex xs6 pl-4>
                    <detail></detail>
                </v-flex>
            </v-layout>
        
</template>
<style scoped>
.v-text-field.v-text-field--solo .v-input__control {
    /* min-height: 36px; */
    padding: 0;
}
</style>
<script>
let rnd = Math.random() * 1e10
module.exports = {
    components : {
        'common-datepicker' : httpVueLoader('../../common/components/common-datepicker.vue'),
        'common-tag' : httpVueLoader('../../common/components/common-tag.vue?t='+rnd),
        'common-disc' : httpVueLoader('../../common/components/common-disc.vue?t='+rnd),
        'detail' : httpVueLoader('./finance-journal-general-new-detail.vue?t='+rnd)
    },

    data () {
        return {
            imageName: '',
            imageUrl: '',
            imageFile: ''
        }
    },

    computed : {
        journal_date () {
                return this.$store.state.generalNew.journal_date
        },

        accounts () {
            return this.$store.state.generalNew.accounts
        },

        selected_account : {
            get () { return this.$store.state.generalNew.selected_account },
            set (v) { this.setObject('selected_account', v) }
        },

        details : {
            get () { return this.$store.state.generalNew.details },
            set (v) { this.setObject('details', v) }
        },

        journal_number () {
            return this.$store.state.generalNew.journal_number
        },

        journal_note : {
            get () { return this.$store.state.generalNew.journal_note },
            set (v) { this.setObject('journal_note', v) }
        },

        // cash_note : {
        //     get () { return this.$store.state.cashNew.cash_note },
        //     set (v) { this.setObject('cash_note', v) }
        // },

        // cash_memo : {
        //     get () { return this.$store.state.cashNew.cash_memo },
        //     set (v) { this.setObject('cash_memo', v) }
        // },

        // item_img : {
        //     get () {
        //         if (this.$store.state.cash.cash_img)
        //             return this.$store.state.cash.cash_img
        //         return ''
        //     },
        //     set (v) { this.setObject('cash_img', v) }
        // },

        edit () {
            return this.$store.state.generalNew.edit
        },

        dialog () {
            return this.$store.state.generalNew.dialog_new
        },

        sa () {
            return this.$store.state.generalNew.sa
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        setObject(x, y) {
            return this.$store.commit('generalNew/set_object', 
                    [x, y])
        },

        setObjects(x, y) {
            for (let v of y)
                this.setObject(v, x[v])
        },

        change_journal_date(x) {
            this.setObject("journal_date", x.new_date)
        },

        amountValidation(e) {
            if (!(e.key.match(/[0-9\,\b]/) || [8,37,39].indexOf(e.keyCode) > -1))
                e.preventDefault()
        },

        pickFile () {
            this.$refs.image.click ()
        },

        onFilePicked (e) {
            const files = e.target.files
            if(files[0] !== undefined) {
                this.imageName = files[0].name
                
                if(this.imageName.lastIndexOf('.') <= 0) {
                    return
                }
                const fr = new FileReader ()
                fr.readAsDataURL(files[0])
                fr.addEventListener('load', () => {
                    this.imageUrl = fr.result
                    this.imageFile = files[0] // this is an image file that can be sent to server...
                    this.getBase64(files[0])
                })
            } else {
                this.imageName = ''
                this.imageFile = ''
                this.imageUrl = ''
            }
        },

        getBase64(file) {
            let vue = this
            let photo_uri = ''
            var reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = function () {
            //   console.log(reader.result);
                photo_uri = reader.result
                vue.item_img = photo_uri
                reader.onerror = function (error) {
                    console.log('Error: ', error);
                };
            }
        },

        save () {
            this.$store.dispatch('generalNew/save').then(res => {
                // console.log(res)
                // if (!!this.$store.state.cashNew.dialog_new) {
                //     this.$store.commit("cashNew/set_common", ["dialog_new", false])

                //     // IF POSITION STILL IN ACCOUNT LIST == TAB.01
                //     if (this.$store.state.cash.selected_tab == "TAB.01")
                //         this.$store.commit("cash/set_object", ["selected_tab", "TAB.02"])
                //     this.$store.dispatch("cash/search")
                // } else {
                //     window.location = "../list/" + this.selected_account.account_id
                // }
            })
        },

        cancel () {
            if (!!this.$store.state.cashNew.dialog_new) {
                this.$store.commit("cashNew/set_common", ["dialog_new", false])
            } else {
                window.location = "../list/" + this.selected_account.account_id
            }
        }
    },

    mounted () {
        // let detail_default = this.$store.state.generalNew.detail_default
        // let details = []
        // for (let n = 0; n < this.$store.state.generalNew.detail_cnt; n++) { 
        //     details.push(detail_default)
        // }
        // this.details = details
        // let id = window.getParameter('id')
        // if (id)
        //     this.setObject('cash_id', id)

        //     if (this.$store.state.cashNew.cash_id) {
        //     // this.setObject('cash_id', id)
        //     this.setObject("edit", true)

        //     this.$store.dispatch('cashNew/search_id').then(res => {
        //         this.setObjects(res, [
        //             "cash_date",
        //             "cash_memo",
        //             "cash_note",
        //             "cash_date",
        //             "cash_from",
        //             "cash_number",
        //             "cash_amount",
        //             "cash_disc",
        //             "cash_discrp",
        //             "cash_img"
        //         ])

        //         this.$store.commit("tag/set_object", ['selected_tagnames', JSON.parse(res.cash_tags)])

        //         if (res.tax_id != 0) {
        //             this.setObject("selected_tax", {tax_id:res.tax_id,tax_name:res.tax_name,tax_amount:res.tax_amount})
        //         }
        //         this.setObject("selected_account", {account_id:res.from_account_id,account_name:res.from_account_name})
        //         this.setObject("selected_account_to", {account_id:res.to_account_id,account_name:res.to_account_name})
        //     })
        // }
    },

    watch : {
    }
}