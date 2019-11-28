<template>
    <div class="vue-csv-uploader">
        <div class="form">
            <div class="vue-csv-uploader-part-one">

              <v-row class=" ma-0 mb-3 pa-0" v-if="!csv">
                  <v-col cols="12" class="form-group csv-import-file text-center ma-0 pa-0">


                    <input ref="csv" type="file" @change.prevent="validFileMimeType" class="hidden" name="csv" v-on-change="csv">
                    <v-btn
                      text
                      small
                      class="ma-0 pa-0 text-none font-weight-bold"
                      @click.stop="activeFileSelect()"
                    >
                      <v-icon class="mdi-24px" color="secondary" left
                        >mdi-plus-circle</v-icon
                      >
                      <span v-if="!filename" class="secondary-text">Select File to import</span>
                      <span v-if="filename" class="secondary-text">  {{filename}}</span>
                    </v-btn>

                    </v-col>
                </v-row>
                <v-row class=" ma-0 mb-3 pa-0" v-if="showErrorMessage">
                    <v-col cols="12" class="form-group csv-import-file text-center ma-0 pa-0">

                      <slot name="error" >
                          <div class="invalid-feedback red--text d-block">
                              File type is invalid
                          </div>
                      </slot>
                      </v-col>
                  </v-row>
                <v-row class=" ma-0 mb-3 pa-0" v-if="headers === null && filename.length>0 && !showErrorMessage">
                    <v-col cols="12" align-center justify-center class="form-check form-group csv-import-checkbox text-center ma-0 pa-0">


                            <!--  <input :class="checkboxClass" type="checkbox" :id="makeId('hasHeaders')" :value="hasHeaders" @change="toggleHasHeaders">-->
                            <v-checkbox color="secondary" class="ma-auto d-inline-block"  v-model="hasHeaders"  hide-details>
                              <template v-slot:label class="mb-0">
                                <span class="mt-2 body-2 secondary-text">
                                  File Has Headers?
                                </span>
                              </template>
                            </v-checkbox>


                    </v-col>
                </v-row>
                <v-row  class=" ma-0 mt-6 pa-0" v-if="!csv && filename.length>0 && !showErrorMessage" >
                    <v-col cols="12"  class=" ma-0 pa-0">
                      <div class="form-group text-center" >
                          <slot name="next" :load="load">
                              <button type="submit" :disabled="disabledNextButton" :class="buttonClass" @click.prevent="load">
                                  {{ loadBtnText }}
                              </button>
                          </slot>
                      </div>
                    </v-col>
                </v-row>
            </div>
            <div class="vue-csv-uploader-part-two">
                <div class="vue-csv-mapping" v-if="sample">

                  <template v-for="(field, key) in fieldsToMap" >
                    <v-row mb-3 >
                      <v-col cols="12" md="12">
                        <v-simple-table width="100%" class="theme-nrs">
                          <thead>
                            <tr>
                              <th class="text-left" width="50%">
                                Column A - {{ field.key }}
                              </th>
                              <th class="text-left" width="50%">Assign to...</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                              <td>{{ field.label }}</td>
                              <td>
                                <v-select
                                  eager
                                  :name="`csv_uploader_map_${key}`"
                                  :items="firstRow"
                                  v-model="map[field.key]"
                                  v-on:change="map[field.key]"
                                  hide-details
                                  autocomplete="off"
                                >
                                  <template v-slot:selection="data">
                                    <v-list-item>
                                      {{ data.item.text }}
                                    </v-list-item>
                                  </template>

                                  <template v-slot:item="data">
                                    <template v-if="typeof data.item !== 'object'">
                                      <v-list-item-content
                                        v-text="data.item"
                                      ></v-list-item-content>
                                    </template>
                                    <template v-else>
                                      <v-list-item-content>
                                        <v-list-item-title
                                          v-html="data.item.text"
                                        ></v-list-item-title>
                                      </v-list-item-content>
                                    </template>
                                  </template>


                                </v-select>
                              </td>
                            </tr>
                          </tbody>
                        </v-simple-table>
                      </v-col>
                    </v-row>
                  </template>



                    <div class="form-group text-center" >
                        <slot name="submit" :submit="submit">
                            <input type="submit" :class="buttonClass" @click.prevent="submit" :value="submitBtnText">
                        </slot>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.hidden { display: none;}
</style>

<script>
    import _ from 'lodash';
    import axios from 'axios';
    import Papa from 'papaparse';


    export default {

        props: {
            value: Array,
            url: {
                type: String
            },
            mapFields: {
                required: true
            },
            callback: {
                type: Function,
                default: () => ({})
            },
            catch: {
                type: Function,
                default: () => ({})
            },
            finally: {
                type: Function,
                default: () => ({})
            },
            parseConfig: {
                type: Object,
                default() {
                    return {};
                }
            },
            headers: {
                default: null
            },
            loadBtnText: {
                type: String,
                default: "Next"
            },
            submitBtnText: {
                type: String,
                default: "Submit"
            },
            tableClass: {
                type: String,
                default: "table"
            },
            checkboxClass: {
                type: String,
                default: "form-check-input"
            },
            buttonClass: {
                type: String,
                default: "btn btn-primary"
            },
            inputClass: {
                type: String,
                default: "form-control-file"
            },
            validation: {
                type: Boolean,
                default: true,
            },
            fileMimeTypes: {
                type: Array,
                default: () => {
                    return ["text/csv", "text/x-csv", "application/vnd.ms-excel", "text/plain"];
                }
            }
        },

        data: () => ({
            form: {
                csv: null,
            },
            file: null,
            fieldsToMap: [],
            map: {
              'firstName':0,
              'lastName':1,
              'emailAddress':2,
              'areaCode':3,
              'contactNumber':4,
              'companyName':5,
              'priority':6
            },
            hasHeaders: true,
            csv: null,
            sample: null,
            isValidFileMimeType: false,
            fileSelected: false,
            filename:''
        }),

        created() {


            if (_.isArray(this.mapFields)) {
                this.fieldsToMap = _.map(this.mapFields, (item) => {
                    return {
                        key: item,
                        label: item
                    };
                });
                /*
                this.map = Objectify(this.mapFields, (item,key) => {
                    return {
                        key:index

                    };
                });
                */
            } else {
                this.fieldsToMap = _.map(this.mapFields, (label, key) => {
                    return {
                        key: key,
                        label: label
                    };
                });

            }

        },



        methods: {
            submit() {
                const _this = this;
                this.form.csv = this.buildMappedCsv();
                this.$emit('input', this.form.csv);

                if (this.url) {
                    axios.post(this.url, this.form).then(response => {
                        _this.callback(response);
                    }).catch(response => {
                        _this.catch(response);
                    }).finally(response => {
                        _this.finally(response);
                    });
                } else {
                    _this.callback(this.form.csv);
                }
            },
            buildMappedCsv() {
                const _this = this;

                let csv = this.hasHeaders ? _.drop(this.csv) : this.csv;

                console.log(csv)

                return _.map(csv, (row) => {
                    let newRow = {};

                    _.forEach(_this.map, (column, field) => {
                        console.log(row)
                        console.log(column)
                        console.log(field)
                        console.log("Getting")
                        console.log( _.get(row, column))
                        _.set(newRow, field, _.get(row, column));
                    });

                    return newRow;
                });
            },
            validFileMimeType() {
                let file = this.$refs.csv.files[0];

                console.log(file)

                if (file) {
                    this.fileSelected = true;
                    this.isValidFileMimeType = this.validation ? this.validateMimeType(file.type) : true;
                    this.filename = file.name
                } else {
                    this.isValidFileMimeType = !this.validation;
                    this.fileSelected = false;
                    this.filename=''
                }
            },
            validateMimeType(type) {
                return this.fileMimeTypes.indexOf(type) > -1;
            },
            load() {
                const _this = this;

                this.readFile((output) => {
                    _this.sample = _.get(Papa.parse(output, { preview: 2, skipEmptyLines: true }), "data");
                    _this.csv = _.get(Papa.parse(output, { skipEmptyLines: true }), "data");
                });

            //  this.autoMap()

            },
            readFile(callback) {
                let file = this.$refs.csv.files[0];

                if (file) {
                    let reader = new FileReader();
                    reader.readAsText(file, "UTF-8");
                    reader.onload = function (evt) {
                        callback(evt.target.result);
                    };
                    reader.onerror = function () {
                    };
                }
            },
            toggleHasHeaders() {
                this.hasHeaders = !this.hasHeaders;
            },
            makeId(id) {
                return `${id}${this._uid}`;
            },
            toObject(arr) {
              var rv = [];
              for (var i = 0; i < arr.length; ++i)
                rv.push( {  value:i, text: arr[i] } );
                //rv.push( {  'label': arr[i] } );
              return rv;
            },
            activeFileSelect() {
              this.$refs.csv.click()
            },

            autoMap () {

                const _this = this;

                if (!this.url) {
                    let hasAllKeys = Array.isArray(this.mapFields) ? _.every(this.mapFields, function (item) {
                        return _this.map.hasOwnProperty(item);
                    }) : _.every(this.mapFields, function (item, key) {
                        return _this.map.hasOwnProperty(key);
                    });

                    console.log(hasAllKeys)

                    if (hasAllKeys) {
                        this.submit();
                    }
                }
            }
        },
        watch: {
            map: {
                deep: true,
                handler: function (newVal) {
                    if (!this.url) {
                        let hasAllKeys = Array.isArray(this.mapFields) ? _.every(this.mapFields, function (item) {
                            return newVal.hasOwnProperty(item);
                        }) : _.every(this.mapFields, function (item, key) {
                            return newVal.hasOwnProperty(key);
                        });

                        if (hasAllKeys) {
                            this.submit();
                        }
                    }
                }
            }
        },
        computed: {
            firstRow() {
                //let row1 = _.get(this, "sample.0");
                //const row1select = row1.map(arr => { return { value: arr[0], text: arr[1] } })
                return  this.toObject(_.get(this, "sample.0"));
            },
            showErrorMessage() {
                return this.fileSelected && !this.isValidFileMimeType;
            },
            disabledNextButton() {
                return !this.isValidFileMimeType;
            }
        },
    };
</script>
