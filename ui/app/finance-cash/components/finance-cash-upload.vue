<template>
    <v-card>
        <v-card-title primary-title class="teal lighten-5">
            <h3 class="display-1 font-weight-light zalfa-text-title">UPLOAD FILE 
                <span class="primary--text">EXCEL</span></h3>
        </v-card-title>
        <v-card-text>
            <v-text-field 
            label="Select..."
            @click='onPickFile'
            v-model='fileName'
            prepend-icon="attach_file"
            >
                <template v-slot:append-outer>     
                    <v-btn
                    color="primary"
                    class="ma-0 btn-icon"
                    @click.stop="onUploadSelectedFileClick"
                    :loading="loading"
                    ><v-icon>file_upload</v-icon> UPLOAD</v-btn>
                </template>
            </v-text-field>
            <!-- Hidden -->
            <input
            type="file"
            style="display: none"
            ref="fileInput"
            accept="*/*"
            @change="onFilePicked">
            

            <v-divider class="mt-4"></v-divider>
            <v-divider class="mt-1 mb-4"></v-divider>

            <v-layout row wrap>
                <v-flex xs6 pr-3>
                    <v-select
                        :items="col_headers"
                        v-model="col_date"
                        label="Kolom Tanggal"
                        item-value="cell"
                        item-text="val"
                        return-object
                        :disabled="col_headers.length<1"
                    ></v-select>

                    <v-select
                        :items="col_headers"
                        v-model="col_to"
                        label="Kolom Tujuan Pembayaran"
                        item-value="cell"
                        item-text="val"
                        return-object
                        :disabled="col_headers.length<1"
                    ></v-select>

                    <v-select
                        :items="col_headers"
                        v-model="col_acc_credit"
                        label="Kolom Akun Kredit"
                        item-value="cell"
                        item-text="val"
                        return-object
                        :disabled="col_headers.length<1"
                    ></v-select>

                    <v-select
                        :items="col_headers"
                        v-model="col_acc_debit"
                        label="Kolom Akun Debit"
                        item-value="cell"
                        item-text="val"
                        return-object
                        :disabled="col_headers.length<1"
                    ></v-select>

                    

                </v-flex>
                <v-flex xs6 pl-3>
                    <v-select
                        :items="col_headers"
                        v-model="col_desc"
                        label="Kolom Deskripsi"
                        item-value="cell"
                        item-text="val"
                        return-object
                        :disabled="col_headers.length<1"
                    ></v-select>

                    <v-select
                        :items="col_headers"
                        v-model="col_memo"
                        label="Kolom Memo"
                        item-value="cell"
                        item-text="val"
                        return-object
                        :disabled="col_headers.length<1"
                    ></v-select>

                    <v-select
                        :items="col_headers"
                        v-model="col_tag"
                        label="Kolom Tag"
                        item-value="cell"
                        item-text="val"
                        return-object
                        :disabled="col_headers.length<1"
                    ></v-select>

                    <v-select
                        :items="col_headers"
                        v-model="col_nominal"
                        label="Kolom Nominal"
                        item-value="cell"
                        item-text="val"
                        return-object
                        :disabled="col_headers.length<1"
                    ></v-select>
                </v-flex>
            </v-layout>
        </v-card-text>

        <v-card-actions>
            <v-flex xs12 class="text-xs-right">
                <v-btn color="primary" @click="cancel" outline>Batal</v-btn>
                <v-btn color="primary" @click="save" :disabled="!btnSaveEnable">Simpan</v-btn>
            </v-flex>
        </v-card-actions>

        <finance-cash-upload-resume></finance-cash-upload-resume>
    </v-card>
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
        'finance-cash-upload-resume' : httpVueLoader('./finance-cash-upload-resume.vue?t='+rnd)
    },

    data () {
        return {
            imageName: '',
            imageUrl: '',
            imageFile: '',
            loading: false
        }
    },

    computed : {
        fileName : {
            get () { return this.$store.state.cashNew.fileName },
            set (v) { this.$store.commit('cashNew/set_object', ['fileName', v]) }
        },

        fileObject : {
            get () { return this.$store.state.cashNew.fileObject },
            set (v) { this.$store.commit('cashNew/set_object', ['fileObject', v]) }
        },

        url : {
            get () { return this.$store.state.cashNew.url },
            set (v) { this.$store.commit('cashNew/set_object', ['url', v]) }
        },

        col_headers () {
            return this.$store.state.cashNew.col_headers
        },

        col_date : {
            get () { return this.$store.state.cashNew.col_date },
            set (v) { this.$store.commit('cashNew/set_object', ['col_date', v]) }
        },

        col_to : {
            get () { return this.$store.state.cashNew.col_to },
            set (v) { this.$store.commit('cashNew/set_object', ['col_to', v]) }
        },

        col_acc_credit : {
            get () { return this.$store.state.cashNew.col_acc_credit },
            set (v) { this.$store.commit('cashNew/set_object', ['col_acc_credit', v]) }
        },

        col_acc_debit : {
            get () { return this.$store.state.cashNew.col_acc_debit },
            set (v) { this.$store.commit('cashNew/set_object', ['col_acc_debit', v]) }
        },

        col_desc : {
            get () { return this.$store.state.cashNew.col_desc },
            set (v) { this.$store.commit('cashNew/set_object', ['col_desc', v]) }
        },

        col_memo : {
            get () { return this.$store.state.cashNew.col_memo },
            set (v) { this.$store.commit('cashNew/set_object', ['col_memo', v]) }
        },

        col_tag : {
            get () { return this.$store.state.cashNew.col_tag },
            set (v) { this.$store.commit('cashNew/set_object', ['col_tag', v]) }
        },

        col_nominal : {
            get () { return this.$store.state.cashNew.col_nominal },
            set (v) { this.$store.commit('cashNew/set_object', ['col_nominal', v]) }
        },

        btnSaveEnable () {
            if (!this.col_nominal || !this.col_tag || !this.col_memo ||
                !this.col_desc || !this.col_acc_debit || !this.col_acc_credit ||
                !this.col_to || !this.col_date)
            return false
            return true
        }
    },

    methods : {
        save () {
            let datas = this.$store.state.cashNew.up_datas
            let newDatas = []

            for (let data of datas) {
                newDatas.push({
                    cash_date: data[this.col_date.col],
                    cash_from: data[this.col_to.col],
                    cash_from_account: 0,
                    cash_from_account_code: data[this.col_acc_credit.col],
                    cash_to_account: 0,
                    cash_to_account_code: data[this.col_acc_debit.col],
                    cash_amount: data[this.col_nominal.col],
                    cash_disc: 0,
                    cash_discrp: 0,
                    cash_tax: 0,
                    cash_tax_amount: 0,
                    cash_note: '',
                    cash_memo: data[this.col_memo.col],
                    cash_tags: JSON.stringify([data[this.col_tag.col]]),
                    cash_type_code: 'CASH.PAY'
                })
            }

            this.$store.commit('cashNew/set_object', ['batch_datas', newDatas])
            this.$store.dispatch('cashNew/saveBatch')

            return null
            // cash_date: context.state.cash_date,
            //         cash_from: context.state.cash_from,
            //         cash_from_account: context.state.selected_account ? context.state.selected_account.account_id : 0,
            //         cash_to_account: context.state.selected_account_to ? context.state.selected_account_to.account_id : 0,
            //         cash_amount: context.state.cash_amount,
            //         cash_disc: context.state.cash_disc,
            //         cash_discrp: context.state.cash_discrp,
            //         cash_tax: context.state.selected_tax ? context.state.selected_tax.tax_id : 0,
            //         cash_tax_amount: context.state.selected_tax ? context.state.selected_tax.tax_amount : 0,
            //         cash_note: context.state.cash_note,
            //         cash_memo: context.state.cash_memo,
            //         cash_tags: context.rootState.tag.selected_tagnames ? JSON.stringify(context.rootState.tag.selected_tagnames) : "[]",
            //         cash_type_code: context.state.cash_type_code
        },

        cancel () {
            this.$store.commit('cashNew/set_object', ['dialog_new', false])
            return null
        },

        // onFilePicked (e) {
		// 	const files = e.target.files
		// 	if(files[0] !== undefined) {
        //         this.imageName = files[0].name
                
		// 		if(this.imageName.lastIndexOf('.') <= 0) {
		// 			return
		// 		}
		// 		const fr = new FileReader ()
        //         fr.readAsDataURL(files[0])
		// 		fr.addEventListener('load', () => {
        //             this.imageUrl = fr.result
        //             this.imageFile = files[0] // this is an image file that can be sent to server...
        //             this.getBase64(files[0])
		// 		})
		// 	} else {
		// 		this.imageName = ''
		// 		this.imageFile = ''
		// 		this.imageUrl = ''
		// 	}
        // },

        onPickFile () {
        this.$refs.fileInput.click()
        },
        onFilePicked (event) {
        const files = event.target.files
        if (files[0] !== undefined) {
            this.fileName = files[0].name
            // Check validity of file
            if (this.fileName.lastIndexOf('.') <= 0) {
            return
            }
            // If valid, continue
            const fr = new FileReader()
            fr.readAsDataURL(files[0])
            fr.addEventListener('load', () => {
            this.url = fr.result
            this.fileObject = files[0] // this is an file that can be sent to server...
            
            })
        } else {
            this.fileName = ''
            this.fileObject = null
            this.url = ''
        }
        },
        onUploadSelectedFileClick () {
        this.loading = true
        
        console.log(this.fileObject)
        // A file is not chosen!
        if (!this.fileObject) {
            alert('No file!!')
            this.loading = false
            return
        }

        this.$store.dispatch("cashNew/upload")
        // DO YOUR JOB HERE with fileObjectToUpload
        // https://developer.mozilla.org/en-US/docs/Web/API/File/File
        this.name = this.fileObject.name
        this.size = this.fileObject.size
        this.type = this.fileObject.type
        this.lastModifiedDate = this.fileObject.lastModifiedDate
        // DO YOUR JOB HERE with fileObjectToUpload
        this.loading = false
        }
    }
}