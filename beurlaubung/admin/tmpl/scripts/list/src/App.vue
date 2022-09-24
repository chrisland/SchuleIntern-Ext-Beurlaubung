<template>

  <div>
    <AjaxError v-bind:error="error"></AjaxError>
    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>

    <div v-if="page == 'list'">
      <table class="si-table" v-if="sortList && sortList.length >= 1">
        <thead>
        <tr>
          <th v-on:click="handlerSort('datumStart')" class="curser-sort">Datum</th>
          <th v-on:click="handlerSort('userID')" class="curser-sort">Schüler*in</th>
          <th v-on:click="handlerSort('stunden')" class="curser-sort">Stunden</th>
          <th v-on:click="handlerSort('info')" class="curser-sort">Begründung</th>
          <th v-on:click="handlerSort('done')" class="curser-sort">Genehmigung</th>
        </tr>
        </thead>
        <tbody>
        <tr v-bind:key="index" v-for="(item, index) in  sortList"
            class="">
          <td>{{ item.datumStart }}<span v-if="item.datumEnde && item.datumEnde != item.datumStart"> - {{ item.datumEnde }}</span></td>
          <td>{{item.user.name}} <span class="text-small" v-if="item.user.klasse">{{item.user.klasse}}</span></td>
          <td>{{ item.stunden }}</td>
          <td>{{ item.info }}</td>
          <td v-if="item.status == 1">
            <button class="si-btn si-btn-green margin-r-m" @click="handlerDone(item, 2)"><i class="fa fa-check"></i> Genehmigen</button>
            <button class="si-btn si-btn-red" @click="handlerDone(item, 3)"><i class="fa fa-ban"></i> Nicht Genehmigen</button>
          </td>
          <td v-if="item.status == 2">
            <button class="si-btn si-btn-border text-green"><i class="fa fa-check"></i> Genehmigt</button>
          </td>
          <td v-if="item.status == 3">
            <button class="si-btn si-btn-border text-red"><i class="fa fa-check"></i> Nicht Genehmigt</button>
          </td>
        </tr>
        </tbody>
      </table>
      <div v-else> - keine Inhalte vorhanden -</div>
    </div>

  </div>
</template>

<script>

import AjaxError from './mixins/AjaxError.vue'
import AjaxSpinner from './mixins/AjaxSpinner.vue'

import '@vuepic/vue-datepicker/dist/main.css'

const axios = require('axios').default;



export default {
  setup() {

    return {

    }
  },
  name: 'App',
  components: {
    AjaxError, AjaxSpinner
  },
  data() {
    return {
      apiURL: window.globals.apiURL,
      settings: window.globals.settings,
      error: false,
      loading: false,
      page: 'list',
      sort: {
        column: 'datumStart',
        order: true
      },
      searchColumns: ['status', 'info'],
      searchString: '',

      list: false

    };
  },
  computed: {
    sortList: function () {
      if (this.list) {
        let data = this.list;
        if (data.length > 0) {

          // SUCHE
          if (this.searchString != '') {
            let split = this.searchString.toLowerCase().split(' ');
            var search_temp = [];
            var search_result = [];
            this.searchColumns.forEach(function (col) {
              search_temp = data.filter((item) => {
                return split.every(v => item[col].toLowerCase().includes(v));
              });
              if (search_temp.length > 0) {
                search_result = Object.assign(search_result, search_temp);
              }
            });
            data = search_result;
          }

          // SORTIERUNG
          if (this.sort.column) {
            if (this.sort.order) {
              return data.sort((a, b) => a[this.sort.column].localeCompare(b[this.sort.column]))
            } else {
              return data.sort((a, b) => b[this.sort.column].localeCompare(a[this.sort.column]))
            }
          }

          return data;
        }
      }
      return [];
    }

  },
  created: function () {

    this.loadList();

  },
  methods: {
    handlerDone: function (item, status) {

      if (!item || !item.id) {
        return false;
      }
      if (!status) {
        return false;
      }
      const formData = new FormData();
      formData.append('id', item.id);
      formData.append('status', status);

      this.loading = true;
      var that = this;
      axios.post( this.apiURL+'/setAntragDoneAdmin', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(function(response){
        if ( response.data ) {
          if (response.data.error) {
            that.error = ''+response.data.msg;
          } else {
            if (response.data.success) {
              item.status = status;
              console.log('ok', item)
            }
          }
        } else {
          that.error = 'Fehler beim Laden. 01';
        }
      })
      .catch(function(){
        that.error = 'Fehler beim Laden. 02';
      })
      .finally(function () {
        // always executed
        that.loading = false;
      });


    },
    handlerSort: function (column) {
      if (column) {
        this.sort.column = column;
        if (this.sort.order) {
          this.sort.order = false;
        } else {
          this.sort.order = true;
        }
      }
    },
    loadList() {

      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getListAdmin/list')
          .then(function (response) {
            if (response.data) {
              if (response.data.error) {
                that.error = '' + response.data.msg;
              } else {
                that.list = response.data;
              }
            } else {
              that.error = 'Fehler beim Laden. 01';
            }
          })
          .catch(function () {
            that.error = 'Fehler beim Laden. 02';
          })
          .finally(function () {
            // always executed
            that.loading = false;
          });

    },
    handlerPage(page = 'list') {
      this.page = page;
    },
    handlerSaveForm(e) {

      e.preventDefault();

      if (this.form.stunden.length < 1 && this.form.schueler && this.form.date) {
        return false;
      }

      const formData = new FormData();
      formData.append('stunden', this.form.stunden);
      formData.append('schueler', this.form.schueler);
      formData.append('date', this.form.date);
      formData.append('info', this.form.info);

      this.loading = true;
      var that = this;
      axios.post(this.apiURL + '/setAntrag', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
          .then(function (response) {
            if (response.data) {
console.log(response.data);
              if (response.data.error) {
                that.error = '' + response.data.msg;
              } else {
                this.loadList();
                this.handlerPage();
                //data.item.favorite = response.data.favorite;
              }
            } else {
              that.error = 'Fehler beim Laden. 01';
            }
          })
          .catch(function () {
            that.error = 'Fehler beim Laden. 02';
          })
          .finally(function () {
            // always executed
            that.loading = false;
          });


    },
    handlerSetStunden(e, arr) {
      e.preventDefault();

      arr.forEach((stunde) => {
        if (this.form.stunden.includes(stunde)) {
          this.form.stunden.splice(this.form.stunden.indexOf(stunde), 1);
        } else {
          this.form.stunden.push(stunde);
        }
      })
      this.form.stunden.sort(function (a, b) {
        return a - b
      });
    },
    inStunden(stunde) {
      if (this.form.stunden.includes(stunde)) {
        return true;
      }
      return false;
    },
    handleDate(newDate) {
      this.form.date = newDate;
    }
  }
}
</script>

<style>
.dp__theme_light {
  --dp-background-color: #ffffff;
  --dp-text-color: #212121;
  --dp-hover-color: #f3f3f3;
  --dp-hover-text-color: #212121;
  --dp-hover-icon-color: #959595;
  --dp-primary-color: #3c8dbc;
  --dp-primary-text-color: #f8f5f5;
  --dp-secondary-color: #c0c4cc;
  --dp-border-color: #ddd;
  --dp-menu-border-color: #ddd;
  --dp-border-color-hover: #aaaeb7;
  --dp-disabled-color: #f6f6f6;
  --dp-scroll-bar-background: #f3f3f3;
  --dp-scroll-bar-color: #959595;
  --dp-success-color: #018d4e;
  --dp-success-color-disabled: #a3d9b1;
  --dp-icon-color: #959595;
  --dp-danger-color: #dd4b39;
}

.dp__action.dp__cancel,
.dp__action.dp__select {
  box-shadow: rgba(0, 0, 0, 0.1) 0px 10px 15px -3px, rgba(0, 0, 0, 0.05) 0px 4px 6px -2px;
  border-radius: 3rem;

  display: inline-block;

  padding: 1rem 1.6rem;
  margin-bottom: 0.3rem;
  margin-top: 0.3rem;

  font-size: 11pt;
  font-weight: 300;
  line-height: 100%;
  letter-spacing: 0.75pt;
  text-align: center;


  vertical-align: middle;
  -ms-touch-action: manipulation;
  touch-action: manipulation;
  cursor: pointer;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  background-image: none;

  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  background-color: #3c8dbc;
  border: 1px solid #3c8dbc;
  color: #fff;
}

.dp__action.dp__cancel {
  background-color: #b7c7ce;
  border-color: #b7c7ce;
  color: #fff;
  margin-right: 1rem;
}

.dp__action_row {
  flex-direction: column;
}

.dp__selection_preview,
.dp__action_buttons {
  flex: 1;
  width: 100%;
}

.dp__menu {
  font-size: inherit;
}

.dp__selection_preview {
  font-size: inherit;
  display: flex;
  justify-content: space-around;
}

.dp__input_icons {
  width: 1.5rem;
  height: 1.5rem;
  margin-left: 1rem;
  margin-right: 0.3rem;
}

.dp__input {
  padding-left: 5rem !important;
}
</style>
