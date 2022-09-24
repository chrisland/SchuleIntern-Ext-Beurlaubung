<template>

  <div>
    <AjaxError v-bind:error="error"></AjaxError>
    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>

    <div v-if="page == 'list'">
      <button class="si-btn" @click="handlerPage('form')"><i class="fa fa-plus"></i> Neuen Antrag stellen</button>
      <table class="si-table" v-if="sortList && sortList.length >= 1">
        <thead>
        <tr>
          <th v-on:click="handlerSort('status')" class="curser-sort">Status</th>
          <th v-on:click="handlerSort('datumStart')" class="curser-sort">Datum</th>
          <th v-on:click="handlerSort('userID')" class="curser-sort">Schüler*in</th>
          <th v-on:click="handlerSort('stunden')" class="curser-sort">Stunden</th>
          <th v-on:click="handlerSort('info')" class="curser-sort">Begründung</th>
          <th v-on:click="handlerSort('doneKL')" class="curser-sort">Genehmigung Klassenleitung</th>
          <th v-on:click="handlerSort('doneSL')" class="curser-sort">Genehmigung Schulleitung</th>
        </tr>
        </thead>
        <tbody>
        <tr v-bind:key="index" v-for="(item, index) in  sortList"
            class="">
          <td>{{ item.status }}</td>
          <td>{{ item.datumStart }}</td>
          <td>{{item.user.name}} <span class="text-small" v-if="item.user.klasse">{{item.user.klasse}}</span></td>
          <td>{{ item.stunden }}</td>
          <td>{{ item.info }}</td>
          <td>{{ item.doneKL }}</td>
          <td>{{ item.doneSL }}</td>
        </tr>
        </tbody>
      </table>
      <div v-else> - keine Inhalte vorhanden -</div>
    </div>

    {{ form }}
    <div v-if="page == 'form'">
      <button class="si-btn si-btn-light" @click="handlerPage()"><i class="fa fa fa-angle-left"></i> Zurück</button>

      <form class="si-form" @submit="handlerSaveForm($event)">
        <ul>
          <li>
            <label>Schüler*in</label>
            <select required v-model="form.schueler">
              <option v-bind:key="index" v-for="(item, index) in  mySchueler" :value="item.id">{{ item.name }} -
                {{ item.klasse }}
              </option>
            </select>
          </li>
          <li>
            <label>Datum</label>
            <Datepicker required :previewFormat="format" :format="format" v-model="form.date" modelType="yyyy-MM-dd" range
                        :enableTimePicker="false" locale="de" cancelText="Abbrechen"
                        selectText="Ok"
                        :monthChangeOnScroll="false"></Datepicker>
          </li>
          <li>
            <label>Stunden</label>
            <div>
              <div class="si-btn-multiple ">
                <button class="si-btn si-btn-light width-12rem margin-r-m"
                        @click="handlerSetStunden($event, [1,2,3,4,5,6])">Vormittag
                </button>
                <button class="si-btn si-btn-border margin-r-s" :class="{'si-btn-active': inStunden(item)}"
                        v-bind:key="index"
                        v-for="(item, index) in  stundenVormittag" @click="handlerSetStunden($event, [item])">
                  {{ item }}
                </button>
              </div>

              <div class="si-btn-multiple ">
                <button class="si-btn si-btn-light width-12rem margin-r-m"
                        @click="handlerSetStunden($event, stundenNachmittagArray)">Nachmittag
                </button>
                <button class="si-btn si-btn-border margin-r-s"
                        :class="{'si-btn-active': inStunden(stundenVormittag+item)}"
                        v-bind:key="index"
                        v-for="(item, index) in  stundenNachmittag"
                        @click="handlerSetStunden($event, [stundenVormittag + item])">
                  {{ stundenVormittag + item }}
                </button>
              </div>
            </div>


          </li>
          <li>
            <label>Begründung <span v-if="settings['extBeurlaubung-form-info-required']" class="text-small">* Pflichtfeld</span></label>
            <textarea :required="settings['extBeurlaubung-form-info-required'] == 1 ? true : false"
                      v-model="form.info"></textarea>
          </li>
          <li>
            <button class="si-btn"><i class="fa fa-plus"></i> Beurlaubungsantrag stellen</button>
          </li>
        </ul>

      </form>

    </div>

  </div>
</template>

<script>

import AjaxError from './mixins/AjaxError.vue'
import AjaxSpinner from './mixins/AjaxSpinner.vue'

import Datepicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'

const axios = require('axios').default;

import {ref, onMounted} from 'vue';


export default {
  setup() {
    const date = ref();

    // For demo purposes assign range from the current date
    onMounted(() => {
      const startDate = new Date();
      const endDate = new Date(new Date().setDate(startDate.getDate() + 1));
      date.value = [startDate, endDate];
    })

    const format = (val) => {
      let startDate = val[0];
      let endDate = val[1];
      if (endDate) {
        return `${startDate.getDate()}.${startDate.getMonth() + 1}.${startDate.getFullYear()} - ${endDate.getDate()}.${endDate.getMonth() + 1}.${endDate.getFullYear()}`
      } else {
        return `${startDate.getDate()}.${startDate.getMonth() + 1}.${startDate.getFullYear()}`

      }
    }

    return {
      date,
      format
    }
  },
  name: 'App',
  components: {
    AjaxError, AjaxSpinner, Datepicker
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

      list: false,

      maxStunden: window.globals.maxStunden,
      stundenVormittag: window.globals.stundenVormittag,
      stundenNachmittag: window.globals.stundenNachmittag,
      stundenNachmittagArray: [],

      mySchueler: window.globals.mySchueler,

      form: {
        schueler: false,
        date: false,
        stunden: [],
        info: ''
      }


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

    for (let i = 1; i <= this.stundenNachmittag; i++) {
      this.stundenNachmittagArray.push(this.stundenVormittag + i);
    }
    if (this.mySchueler && this.mySchueler[0] && this.mySchueler[0].id) {
      this.form.schueler = this.mySchueler[0].id;
    }

  },
  methods: {
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
      axios.get(this.apiURL + '/getMyList')
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
