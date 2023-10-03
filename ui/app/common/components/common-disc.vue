<template>
    <v-layout row wrap>
        <v-flex xs12>
            <v-text-field
                :solo="modeSolo"
                :outline="modeOutline"
                reverse
                v-model="disc"
                v-show="disctype=='P'"
                dense
                :label="!!label?label:'Potongan'"
                depressed
                :hide-details="!!hide_details?hide_details:false"
                :class="{'super-dense':!!superdense?superdense:false}"
            >
                <template slot="prepend">
                    <v-btn small class="orange ma-0 btn-icon" @click="set_disc('R')" depressed dark v-show="modeSolo">%</v-btn>
                    <v-btn small class="orange ma-0 btn-icon btn-disc-regular" @click="set_disc('R')" depressed dark v-show="!modeSolo">%</v-btn>
                </template>
            </v-text-field>
            <v-text-field
                :solo="modeSolo"
                :outline="modeOutline"
                reverse
                v-model="discrp"
                v-show="disctype=='R'"
                dense
                :label="!!label?label:'Potongan'"
                :mask="one_mask_money(discrp)"
                depressed
                :hide-details="!!hide_details?hide_details:false"
                :class="{'super-dense':!!superdense?superdense:false}"
            >
                <template slot="prepend">
                    <v-btn small class="orange ma-0 btn-icon" @click="set_disc('P')" depressed dark v-show="modeSolo">Rp</v-btn>
                    <v-btn small class="orange ma-0 btn-icon btn-disc-regular" @click="set_disc('P')" depressed dark v-show="!modeSolo">Rp</v-btn>
                </template>
            </v-text-field>
        </v-flex>
    </v-layout>
</template>

<style scoped>
.super-dense .v-input__control {
    min-height: 36px !important;
}

.v-input__prepend-outer {
    margin: 0px !important;
}

.v-input__prepend-outer button {
    height: 48px;
}

.super-dense .v-input__prepend-outer button {
    height: 36px;
}

.v-text-field.v-text-field--solo.super-dense .v-input__control {
    min-height: 36px;
    padding: 0;
}

.v-input__append-outer {
    margin: 0px !important;
}

.v-input__append-outer button {
    min-height: 36px;
}

.btn-disc-regular {
    height: 34px !important;
}
</style>

<script>
module.exports = {
    props : ['mode', 'label', 'hide_details', 'superdense', 'default_disc'],
    components : {
    },

    data () {
        return { 
            modeSolo: this.mode == "solo" ? true : false,
            modeOutline: this.mode == "outline" ? true : false
        }
    },

    computed : {
        disc : {
            get () { return this.$store.state.disc.disc },
            set (v) { this.$store.commit('disc/set_common', ['disc', v]) }
        },

        discrp : {
            get () { return this.$store.state.disc.discrp },
            set (v) { this.$store.commit('disc/set_common', ['discrp', v]) }
        },

        disctype : {
            get () { return this.$store.state.disc.disctype },
            set (v) { this.$store.commit('disc/set_common', ['disctype', v]) }
        }
    },

    methods : {
        one_money (x) {
            return window.one_money(x)
        },

        one_mask_money (x) {
            return window.one_mask_money(x)
        },

        set_disc (type) {
            this.disctype = type
            if (type == 'P') this.discrp = 0
            if (type == 'R') this.disc = 0
            this.$emit('set_disc', {type:type})
        }
    },

    watch : {
       
    }
}
</script>