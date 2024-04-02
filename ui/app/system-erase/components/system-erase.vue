<template>
  <v-card>
    <v-container grid-list-md>
      <v-card-title primary-title class="red white--text">
        <h1>Hapus Data !!!</h1>
        <v-spacer></v-spacer>
      </v-card-title>
      <hr class="mt-2">
      <p>Silahkan pilih data apa saja yang akan dihapus</p>
      <v-checkbox v-for="(t, n) in tables" v-model="selectedData" :label="t.text" class="mt-0 black-label" hide-details
        :value="t.val"></v-checkbox>
      
      <p class="mt-2 no-margin"> ketik "OK" untuk melanjutkan</p>
      <v-layout>
        <v-flex xs2 sm2 md2>
          <v-text-field v-model="confirmation" outline label="Placeholder" class="h-1" name="confirmation" large
            hide-details></v-text-field>
        </v-flex>
        <v-flex xs4>
          <v-btn color="info" large @click="proceed">Go !!!!</v-btn>
        </v-flex>
      </v-layout>
      <!-- <v-card class="px-4 py-4 mt-2">
        <p>Data yang akan dihapus : </p>
        <pre>{{ respon.toString() }}</pre>
      </v-card> -->
    </v-container>
    <v-snackbar v-model="snackbar" multi-line :timeout="6000" top vertical color="primary" class="pt-3">
      {{ snackbar_text }}
      <v-btn color="white" flat @click="snackbar = false">
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
  data() {
    return {
      tables: [{text:"Penawaran",val:"offer"},
              {text:"Penjualan",val:"sales"},
              {text:"Retur",val:"retur"},
              {text:"Pembayaran",val:"pay"},
              {text:"Penyesuaian Stok",val:"adjust"},
              {text:"Customer",val:"customer"},
              {text:"Vendor / Supplier",val:"vendor"},
              {text:"Item",val:"item"},
              {text:"Stok",val:"stock"}]
    }
  },

  computed: {
    selectedData: {
      get() { return this.$store.state.erase.selectedData },
      set(v) { this.$store.commit('erase/update_selected_data', v) }
    },
    confirmation: {
      get() { return this.$store.state.erase.confirmation },
      set(v) { this.$store.commit("erase/set_confirmation", v) }
    },
    respon() {
      return this.$store.state.erase.respon;
    },
    snackbar: {
      get() { return this.$store.state.erase.snackbar },
      set(v) { this.$store.commit('erase/set_common', ['snackbar', v]) }
    },

    snackbar_text() {
      return this.$store.state.erase.snackbar_text
    },
  },
  methods: {
    proceed() {
      if (this.confirmation === 'OK' || this.confirmation === 'ok') {
        this.$store.dispatch("erase/save").then(d=>{
          if (d.status=="ERR") {
            alert('ERROR')
          } else {
            alert('Hapus Data Berhasil !')
            window.location.reload()
          }
        })

      } else {
        alert('Konfirmasi tidak valid!');
      }
    }
  },
  mounted() {
    this.$store.dispatch('erase/selected').then(() => {
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

.no-margin {
  margin-bottom: -5px;
}
</style>
