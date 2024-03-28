<template>
  <v-card>
    <v-container grid-list-md>
      <v-card-title primary-title class="red white--text">
        <h1>Hapus Data !!!</h1>
        <v-spacer></v-spacer>
      </v-card-title>
      <hr class="mt-2">
      <p>Silahkan pilih data apa saja yang akan dihapus</p>
      <v-checkbox
        v-model="selectedData"
        label="Customer"
        class="mt-0 black-label"
        hide-details
        value="customer"
      ></v-checkbox>
      <v-checkbox
        v-model="selectedData"
        label="Item"
        hide-details
        class="mt-0 black-label"
        value="item"
      ></v-checkbox>
      <v-checkbox
        v-model="selectedData"
        label="Vendor"
        hide-details
        class="mt-0 black-label"
        value="vendor"
      ></v-checkbox>
      <v-checkbox
        v-model="selectedData"
        label="Penjualan"
        hide-details
        class="mt-0 black-label"
        value="penjualan"
      ></v-checkbox>
      <v-checkbox
        v-model="selectedData"
        label="Pembelian"
        hide-details
        class="mt-0 black-label"
        value="pembelian"
      ></v-checkbox>
      <v-checkbox
        v-model="selectedData"
        label="Pembayaran"
        hide-details
        class="mt-0 black-label"
        value="pembayaran"
      ></v-checkbox>
      <p class="mt-2 no-margin"> ketik "OK" untuk melanjutkan</p>
      <v-layout>
        <v-flex xs2 sm2 md2>
          <v-text-field
            v-model="confirmation"
            outline
            label="Placeholder"
            class="h-1"
            name="confirmation"
            large
            hide-details
          ></v-text-field>
        </v-flex>
        <v-flex xs4>
          <v-btn color="info" large @click="proceed">Go !!!!</v-btn>
        </v-flex>
      </v-layout>
      <v-card class="px-4 py-4 mt-2">
        <p>Data yang akan dihapus : </p>
        <pre>{{ respon.toString() }}</pre>
      </v-card>
    </v-container>
    <v-snackbar
            v-model="snackbar"
            multi-line
            :timeout="6000"
            top
            vertical
            color="primary"
            class="pt-3"
            >
            {{ snackbar_text }}
            <v-btn
                color="white"
                flat
                @click="snackbar = false"
            >
                Tutup
            </v-btn>
        </v-snackbar>
        <v-footer class="pa-3">
    <v-spacer></v-spacer>
    <div>&copy; {{ new Date().getFullYear() }}</div>
  </v-footer>
  </v-card>
</template>
<script>
module.exports = {
  computed: {
    selectedData : {
      get () { return this.$store.state.qris.selectedData },
      set (v) { this.$store.commit('qris/update_selected_data', v) }
    },
    confirmation : {
      get () { return this.$store.state.qris.confirmation },
      set (v) {this.$store.commit("qris/set_confirmation", v)}
    },
    respon () {
      return this.$store.state.qris.respon;
    },
        snackbar : {
            get () { return this.$store.state.qris.snackbar },
            set (v) { this.$store.commit('qris/set_common', ['snackbar', v]) }
        },

        snackbar_text () {
            return this.$store.state.qris.snackbar_text
        },
},
methods: {
    proceed() {
      if (this.confirmation === 'OK' || 'ok' ) {
          this.$store.dispatch("qris/save_selected");
          this.$store.dispatch('qris/selected');

      } else {
        alert('Konfirmasi tidak valid!');
      }
    }
},
mounted () {
      this.$store.dispatch('qris/selected').then(() => {
          console.log("Search dispatched!");
          
      }).catch(error => {
          console.error("Error dispatching search:", error);
      });
  }


}
</script>

<style scoped>
.black-label label {
  color: rgba(0, 0, 0) !important;
}
p {
  font-size: 15px;
}
.no-margin{
  margin-bottom: -5px;
} 
</style>
