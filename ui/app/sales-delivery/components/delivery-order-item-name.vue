<template>
    <v-dialog
        v-model="dialog"
        scrollable
        :overlay="false"
        max-width="400px"
        transition="dialog-transition"
        content-class="dialog-itemname"
        
    >
        <v-card>
            <v-card-title primary-title class="cyan white--text pt-3">
                <h3>
                    <span>UBAH NAMA ITEM</span>
                </h3>
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                        <v-text-field
                            label="Nama Item"
                            v-model="custom_name"
                            @change="change_name($event)"
                            v-if="dialog"
                        ></v-text-field>
                    </v-flex>
                </v-layout>
            </v-card-text>
            <v-card-actions>
                <v-flex xs6 pr-1>
                    <v-btn color="primary" flat @click="dialog=!dialog" block>Batal</v-btn>
                </v-flex>
                <v-flex xs6 pl-1>
                    <v-btn color="primary" @click="save" block>Simpan</v-btn>
                </v-flex>
                
                <!-- <v-btn color="primary" @click="add_detail">Add</v-btn>                 -->
                <!-- <v-btn color="green" @click="save_confirm" :disabled="!btn_save_enabled" :dark="btn_save_enabled" v-show="!view">Simpan & Konfirmasi</v-btn> -->
                
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<style>
.input-dense .v-input__control {
    min-height: 36px !important;
}

.dialog-itemname .v-input__prepend-outer {
    margin: 0px !important;
}

.dialog-itemname .v-text-field.v-text-field--solo .v-input__control {
    min-height: 36px;
    padding: 0;
}
</style>
<script>
module.exports = {
    data () {
        return {
            // custom_name: ''
        }
    },

    computed : {
        dialog : {
            get () { return this.$store.state.delivery_new.dialog_itemname },
            set (v) { this.$store.commit('delivery_new/set_common', ['dialog_itemname', v]) }
        },

        item () {
            return this.$store.state.delivery_new.selected_item.item
        },

        item_name () {
            if (!this.item)
                return ''

            return this.item.item_name
        },

        custom_name : {
            get () { return this.$store.state.delivery_new.custom_item_name },
            set (v) { this.$store.commit('delivery_new/set_common', ['custom_item_name', v]) }
        }
    },

    methods : {
        save () {
            if (this.$store.state.delivery_new.edit) {
                this.$store.dispatch('delivery_new/save_item_name').then(res => {
                    this.save_form()

                    this.$store.commit('delivery/set_common', ['snackbar', true])
                    this.$store.commit('delivery/set_common', ['snackbar_text', 'Nama Item berhasil diubah'])
                })
            } else {
                this.save_form()
            }
        },

        save_form () {
            let items = JSON.parse(JSON.stringify(this.$store.state.delivery_new.details))
            for (let i in items) {
                if (items[i].item.item_id == this.item.item_id) {
                    items[i].item.item_name = this.custom_name
                    items[i].item.custom_name = true
                }                    
            }

            this.$store.commit('delivery_new/set_details', items)
            this.dialog = false
        },

        change_name (x) {
            return
            this.custom_name = x
        }
    }
}