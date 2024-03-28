<template>
    <div class="container">
      <h1>Hapus Data !!!</h1>
      <p>silahkan pilih data apa saja yang akan dihapus</p>
      <div class="custom-checkbox">
        <div class="checkbox-item">
          <input type="checkbox" v-model="selectedData.customer" id="customer">
          <label for="customer">Customer</label>
        </div>
  
        <div class="checkbox-item">
          <input type="checkbox" v-model="selectedData.item" id="item">
          <label for="item">Item</label>
        </div>
  
        <div class="checkbox-item">
          <input type="checkbox" v-model="selectedData.vendor" id="vendor">
          <label for="vendor">Vendor</label>
        </div>
  
        <div class="checkbox-item">
          <input type="checkbox" v-model="selectedData.penjualan" id="penjualan">
          <label for="penjualan">Penjualan</label>
        </div>
  
        <div class="checkbox-item">
          <input type="checkbox" v-model="selectedData.pembelian" id="pembelian">
          <label for="pembelian">Pembelian</label>
        </div>
  
        <div class="checkbox-item">
          <input type="checkbox" v-model="selectedData.pembayaran" id="pembayaran">
          <label for="pembayaran">Pembayaran</label>
        </div>
      </div>
      <v-checkbox
      v-model="checkbox"
      :label="`Checkbox 1: ${checkbox.toString()}`"
    ></v-checkbox>
      <p class="left-align">Silahkan ketik "OK" untuk melanjutkan penjualan</p>
      <div class="input-group">
        <input type="text" v-model="confirmation" placeholder="placeholder">
        <button @click="proceed">Go !</button>
      </div>
    </div>
  </template>
  
  <script>
  module.exports = {
    data() {
      return {
        selectedData: {
          customer: false,
          item: false,
          vendor: false,
          penjualan: false,
          pembelian: false,
          pembayaran: false,
        },
        confirmation: ''
      };
    },
    methods: {
      proceed() {
        if (this.confirmation === 'OK') {
          console.log('Data yang dipilih:', this.selectedData);
        } else {
          alert('Konfirmasi tidak valid!');
        }
      }
    }
  }
  </script>
  
  <style scoped>
  .container {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: left;
    height: 70vh;
  }
  
  .custom-checkbox {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
  }
  
  .checkbox-item {
    margin-bottom: 10px;
    align-items: center;
  }
  
  .custom-checkbox input[type="checkbox"] {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    width: 20px;
    height: 20px;
    border: 1px solid #129ac7;
    border-radius: 4px;
    outline: none;
    cursor: pointer;
    vertical-align: middle;
    position: relative;
    margin-right: 10px;
  }
  
  .custom-checkbox input[type="checkbox"]:checked {
    background-color: #129ac7;
  }
  
  .custom-checkbox label {
    vertical-align: middle;
  }
  
  .left-align {
    text-align: left;
    margin-bottom: 0px;
  }
  
  .input-group {
    display: flex;
    align-items: center;
  }
  
  input[type="text"] {
    padding: 5px;
    border: 1px solid black;
  }
  
  button {
    margin-left: 5px;
    padding: 5px;
    color: white;
    background-color: #129ac7;
  }
  
  </style>
  