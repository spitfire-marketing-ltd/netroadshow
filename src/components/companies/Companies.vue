<template>
  <v-container>
    <v-layout align-center justify-center pa-0 ma-0>
      <v-flex sm12 align-center justify-center row fill-height>
        <v-flex d-block tag="h1" py-6 color="primary" text-left
          >Companies</v-flex
        >
        <v-list-item text-left class="pa-0 ma-0">
          <v-flex sm12>
            <v-data-table
              ref="dTable"
              color="secondary"
              v-model="selected"
              :headers="headers"
              :items="companies"
              single-expand
              :expanded.sync="expanded"
              :search="search"
              item-key="companyID"
              hide-default-footer
              hide-default-header
              loading="false"
              :page.sync="page"
              :items-per-page="itemsPerPage"
              multi-sort
              class="theme-nrs"
              @page-count="pageCount = $event"
            >
              <template v-slot:header="{ props, on, selected }">
                <thead class="v-data-table-header">
                  <tr>
                    <th
                      v-for="(header, index) in props.headers"
                      :key="index"
                      :class="[
                        'text-weight-bold primary-text column sortable',
                        header.sortDescending ? 'desc' : 'asc',
                        inArray(header.value, props.options.sortBy)
                          ? 'active'
                          : ''
                      ]"
                      @click="changeSort(index, on)"
                    >
                      {{ header.text }}
                      <v-icon
                        v-if="header.sortable"
                        small
                        class="v-data-table-header__icon float-right"
                        color="secondary "
                        >arrow_upward
                      </v-icon>
                    </th>
                    <th>
                      <v-checkbox
                        v-model="selected"
                        color="secondary"
                        hide-details
                        @click.native="toggleAll"
                      ></v-checkbox>
                    </th>
                  </tr>
                </thead>
              </template>

              <template
                v-slot:item="{ headers,
                item,
                isExpanded,
                isSelected,
                expand,
                select }"
              >
                <tr>
                  <td class="text-xs-right">
                    <div
                      class="datatable-cell-wrapper"
                      @click="expandrow(item, expand, isExpanded)"
                    >
                      {{ item.companyName }}
                      <v-icon
                        class="float-right"
                        medium
                        color="secondary"
                        @change="expandrow(item, expand, isExpanded)"
                      >
                        mdi-chevron-down
                      </v-icon>
                    </div>
                  </td>

                  <td class="text-xs-right">
                    <div class="datatable-cell-wrapper">
                      {{ item.dataRetention }}
                    </div>
                  </td>
                  <td class="text-xs-right">
                    <div class="datatable-cell-wrapper">
                      {{ item.passwordFrequency }}
                    </div>
                  </td>

                  <td class="text-xs-right">
                    <div class="datatable-cell-wrapper">
                      {{ item.companyID }}
                    </div>
                  </td>
                  <td>
                    <v-checkbox
                      color="secondary"
                      class="ma-auto pa-0"
                      hide-details
                      :input-value="isSelected"
                      @change="select(1-isSelected)"
                    ></v-checkbox>
                  </td>
                </tr>
              </template>

              <template
                v-slot:expanded-item="{ item, headers, expand, isExpanded }"
              >
                <td :colspan="headers.length + 1" class="pa-0 ma-0">
                  <v-card flat color="grey lighten-4">
                    <v-form valid="valid" ref="entryForm">
                      <v-card-text>
                        <v-container>
                          <v-layout wrap>
                            <v-flex xs12 sm6 md4 px-3>
                              <v-layout column>
                                <v-text-field
                                  filled
                                  v-model="editedItem.companyName"
                                  label="Company name"
                                  required
                                  :rules="[v => !!v || 'Item is required']"
                                ></v-text-field>
                                <v-select
                                  :items="['Live', 'Suspended']"
                                  filled
                                  dense
                                  required
                                  :rules="[v => !!v || 'Item is required']"
                                  color="secondary"
                                  label="Account status"
                                  v-model="editedItem.accountStatus"
                                >
                                </v-select>
                                <div class="mt-12">
                                  Upload logo (Recommended size w600px)
                                  <v-file-input
                                    class="pt-0"
                                    accept="image/*"
                                    v-model="file"
                                    required
                                    @change="onFileChange"
                                  ></v-file-input>
                                  <img
                                    :src="editedItem.logo"
                                    height="75"
                                    v-if="editedItem.logo"
                                  />
                                </div>
                              </v-layout>
                            </v-flex>

                            <v-flex xs12 sm6 md4 px-3>
                              <v-layout column>
                                <v-text-field
                                  filled
                                  v-model="editedItem.emailAddress"
                                  label="Email (Username)"
                                  :rules="emailRules"
                                  required
                                ></v-text-field>
                                <v-select
                                  :items="['EN (English)', 'FR (French)']"
                                  filled
                                  dense
                                  color="secondary"
                                  label="Language"
                                  v-model="editedItem.language"
                                  required
                                  :rules="[v => !!v || 'Item is required']"
                                >
                                </v-select>
                                <div class="mt-12">
                                  Select main colour scheme
                                  <v-layout row class="px-3 py-0 mt-0">
                                    <v-sheet
                                      :style="backgroundMainColor"
                                      width="80"
                                      height="32"
                                      class="pa-0 ma-0 mt-1"
                                      d-inline-flex
                                      @click="
                                        showMainPicker = 1 - showMainPicker
                                      "
                                    ></v-sheet>
                                    <v-text-field
                                      hide-details
                                      color="grey"
                                      lighten-4
                                      outline
                                      d-inline-flex
                                      v-model="editedItem.primaryColor"
                                      clearable
                                      class="pl-3 pt-0"
                                      @click="
                                        showMainPicker = 1 - showMainPicker
                                      "
                                    ></v-text-field>
                                  </v-layout>

                                  <v-card
                                    flat
                                    v-if="showMainPicker == true"
                                    v-model="showMainPicker"
                                  >
                                    <v-card-text class="pa-0 ma-0">
                                      <v-color-picker
                                        canvas-height="75"
                                        flat
                                        width="500"
                                        hide-inputs
                                        class="pa-0 ma-0 mt-2"
                                        mode="hexa"
                                        v-model="editedItem.primaryColor"
                                      ></v-color-picker>
                                    </v-card-text>
                                    <v-card-actions>
                                      <div
                                        class="text-center"
                                        style="width:100%;display: inline-flex"
                                      >
                                        <v-btn
                                          class="mx-auto"
                                          color="secondary"
                                          text
                                          @click="
                                            showMainPicker = 1 - showMainPicker
                                          "
                                        >
                                          Close
                                        </v-btn>
                                      </div>
                                    </v-card-actions>
                                  </v-card>
                                </div>
                              </v-layout>
                            </v-flex>

                            <v-flex xs12 sm6 md4 px-3>
                              <v-layout column>
                                <v-select
                                  :items="[
                                    { text: '1 month', value: '1' },
                                    { text: '3 months', value: '3' },
                                    { text: '6 months', value: '6' },
                                    { text: '9 months', value: '9' },
                                    { text: '12 months', value: '12' },
                                    { text: '24 months', value: '24' }
                                  ]"
                                  item-text="text"
                                  item-value="value"
                                  filled
                                  dense
                                  required
                                  :rules="[v => !!v || 'Item is required']"
                                  color="secondary"
                                  hint="How long should event data/reports be stored for?"
                                  persistent-hint
                                  label="Event data/report retention period?"
                                  v-model="editedItem.dataRetention"
                                >
                                </v-select>
                                <v-select
                                  :items="[
                                    { text: '3 months', value: '3' },
                                    { text: '6 months', value: '6' },
                                    { text: '12 months', value: '12' },
                                    { text: '24 months', value: '24' }
                                  ]"
                                  item-text="text"
                                  item-value="value"
                                  filled
                                  required
                                  :rules="[v => !!v || 'Item is required']"
                                  dense
                                  color="secondary"
                                  hint="How often should user passwords be changed?"
                                  persistent-hint
                                  label="Password frequency change?"
                                  v-model="editedItem.passwordFrequency"
                                >
                                </v-select>

                                <div class="mt-12">
                                  Select button colour scheme
                                  <v-layout row class="px-3 py-0 mt-0">
                                    <v-sheet
                                      :style="backgroundButtonColor"
                                      width="80"
                                      height="32"
                                      class="pa-0 mt-1 ma-0"
                                      d-inline-flex
                                      @click="
                                        showButtonPicker = 1 - showButtonPicker
                                      "
                                    ></v-sheet>
                                    <v-text-field
                                      hide-details
                                      color="grey"
                                      lighten-4
                                      outline
                                      d-inline-flex
                                      v-model="editedItem.secondaryColor"
                                      clearable
                                      class="pl-3 pt-0"
                                      @click="
                                        showButtonPicker = 1 - showButtonPicker
                                      "
                                    ></v-text-field>
                                  </v-layout>

                                  <v-card
                                    flat
                                    v-if="showButtonPicker == true"
                                    v-model="showButtonPicker"
                                  >
                                    <v-card-text class="pa-0 ma-0">
                                      <v-color-picker
                                        canvas-height="75"
                                        hide-mode-switch
                                        hide-inputs
                                        flat
                                        width="500"
                                        class="pa-0 ma-0  mt-2"
                                        mode="hexa"
                                        v-model="editedItem.secondaryColor"
                                      ></v-color-picker>
                                    </v-card-text>
                                    <v-card-actions>
                                      <div
                                        class="text-center"
                                        style="width:100%;display: inline-flex"
                                      >
                                        <v-btn
                                          class="mx-auto"
                                          color="secondary"
                                          text
                                          @click="
                                            showButtonPicker =
                                              1 - showButtonPicker
                                          "
                                        >
                                          Close
                                        </v-btn>
                                      </div>
                                    </v-card-actions>
                                  </v-card>
                                </div>
                              </v-layout>
                            </v-flex>
                          </v-layout>
                        </v-container>
                      </v-card-text>
                    </v-form>
                    <v-card-actions class="px-10">
                      <v-spacer></v-spacer>
                      <v-btn
                        color="blue darken-1"
                        class="px-10"
                        text
                        @click="closeRow()"
                      >
                        Cancel
                      </v-btn>
                      <v-btn
                        color="blue darken-1"
                        class="font-weight-bold"
                        text
                        @click="save('entryForm')"
                        >Save</v-btn
                      >
                    </v-card-actions>
                  </v-card>
                </td>
              </template>
              <template v-slot:no-data>
                <v-btn color="primary" @click="initialize">Reset</v-btn>
              </template>
            </v-data-table>

            <v-layout py-0 px-3 pt-0 ma-0 align-center>
              <v-flex sm6 text-sm-left>
                <span
                  class="select d-inline-block mr-6"
                  style="max-width: 180px"
                >
                  <v-select
                    color="success"
                    v-model="itemsPerPage"
                    id="page-size"
                    :items="pagesizes"
                    height="24"
                    menu-props="auto"
                  >
                    <label slot="prepend">Show</label>
                    <label slot="append-outer">Entries</label>
                  </v-select>
                </span>

                <v-btn
                  text
                  small
                  class="ma-0 pa-0 text-none font-weight-bold"
                  v-on:click="onBtPrevious()"
                >
                  <v-icon class="mdi-18px" color="secondary" left
                    >mdi-less-than</v-icon
                  >
                  Previous
                </v-btn>

                <v-icon class="mdi-18px" color="secondary">mdi-power-on</v-icon>

                <v-btn
                  text
                  small
                  class="ma-0 pa-0 text-none font-weight-bold"
                  v-on:click="onBtNext()"
                  >Next
                  <v-icon class="mdi-18px" color="secondary" right
                    >mdi-greater-than</v-icon
                  >
                </v-btn>
              </v-flex>
              <v-flex sm6 text-sm-right>
                <span class="d-inline-block mr-3 mt-3" style="max-width: 300px">
                  <v-text-field
                    hide-details
                    id="qsearch"
                    label="Company"
                    v-model="search"
                    outlined
                    name="filter"
                    type="text"
                    class="pa-0"
                    height="45"
                    min-height="45"
                    :readonly="readonly"
                    @click="readonly = false"
                  >
                    <label slot="prepend">Search</label>
                  </v-text-field>
                </span>
              </v-flex>
            </v-layout>
            <v-layout py-0 px-3 ma-0>
              <div class="test-header">
                Showing
                <span id="starting">{{ starting }}</span> to
                <span id="ending">{{ ending }}</span> of
                <span id="total">{{ total }}</span>
                entries
              </div>
            </v-layout>
          </v-flex>
        </v-list-item>
      </v-flex>
    </v-layout>

    <v-layout align-center pa-0 ma-0>
      <v-flex pa-6 xs6 text-sm-left>
        <v-btn
          text
          small
          class="ma-0 pa-0 text-none font-weight-bold"
          @click="newitem()"
        >
          <v-icon class="mdi-36px" color="secondary" left
            >mdi-plus-circle</v-icon
          >
          New Item
        </v-btn>
      </v-flex>
      <v-flex pa-6 xs6 text-sm-right>
        <v-menu offset-y>
          <template v-slot:activator="{ on }">
            <v-btn color="secondary" rounded dark class="mb-2" v-on="on"
              >Options</v-btn
            >
          </template>
          <span>
            <v-list>
              <v-list-item py-2 @click="exportSelectedCompanies()">
                <v-list-item-title>Export company selection</v-list-item-title>
              </v-list-item>

              <v-list-item v-if="inArray(userGroup, [1,2])" py-2 @click="deleteSelectedCompanies()">
                <v-list-item-title>Delete company</v-list-item-title>
              </v-list-item>
              <v-divider ></v-divider>
              <v-list-item py-2 @click="newitem()">
                <v-list-item-title>New company</v-list-item-title>
              </v-list-item>
            </v-list>
          </span>
        </v-menu>
      </v-flex>
    </v-layout>

    <v-card v-show="showNewItem" elevation-0 class="mb-6">
      <v-card-title class="px-10 py-6">
        <span class="headline primary-text">Add a new company</span>
      </v-card-title>
      <v-form v-model="valid" class="entryForm" ref="newEntryForm">
        <v-card-text>
          <v-container>
            <v-layout wrap>
              <v-flex xs12 sm6 md4 px-3>
                <v-layout column>
                  <v-text-field
                    filled
                    v-model="editedItem.companyName"
                    :rules="nameRules"
                    required
                    label="Company name"
                  >
                  </v-text-field>
                  <v-select
                    :items="['Live', 'Suspended']"
                    filled
                    dense
                    :rules="[v => !!v || 'Item is required']"
                    required
                    color="secondary"
                    label="Account status"
                    v-model="editedItem.accountStatus"
                  >
                  </v-select>
                  <div class="mt-12">
                    Upload logo (Recommended size w600px)
                    <v-file-input
                      class="pt-0"
                      accept="image/*"
                      v-model="file"
                      :rules="[v => !!v || 'Logo is required']"
                      @change="onFileChange"
                    ></v-file-input>

                    <img
                      :src="editedItem.logo"
                      height="75"
                      v-if="editedItem.logo"
                    />
                  </div>
                </v-layout>
              </v-flex>

              <v-flex xs12 sm6 md4 px-3>
                <v-layout column>
                  <v-text-field
                    filled
                    v-model="editedItem.emailAddress"
                    label="Email (Username)"
                    :rules="emailRules"
                    required
                  ></v-text-field>
                  <v-select
                    :items="['EN (English)', 'FR (French)']"
                    filled
                    :rules="[v => !!v || 'Item is required']"
                    required
                    dense
                    color="secondary"
                    label="Language"
                    v-model="editedItem.language"
                  >
                  </v-select>
                  <div class="mt-12">
                    Select main colour scheme
                    <v-layout row class="px-3 mt-0">
                      <v-sheet
                        :style="backgroundMainColor"
                        width="80"
                        height="32"
                        class="pa-0 ma-0 mt-1"
                        d-inline-flex
                        @click="showMainPicker = 1 - showMainPicker"
                      ></v-sheet>
                      <v-text-field
                        hide-details
                        color="grey"
                        lighten-4
                        outline
                        d-inline-flex
                        v-model="editedItem.primaryColor"
                        clearable
                        class="pl-3 pt-0"
                        @click="showMainPicker = 1 - showMainPicker"
                      ></v-text-field>
                    </v-layout>

                    <v-card
                      flat
                      v-if="showMainPicker == true"
                      v-model="showMainPicker"
                    >
                      <v-card-text class="pa-0 ma-0">
                        <v-color-picker
                          canvas-height="75"
                          flat
                          width="500"
                          hide-inputs
                          class="pa-0 ma-0 mt-2"
                          mode="hexa"
                          v-model="editedItem.primaryColor"
                        ></v-color-picker>
                      </v-card-text>
                      <v-card-actions>
                        <div
                          class="text-center"
                          style="width:100%;display: inline-flex"
                        >
                          <v-btn
                            class="mx-auto"
                            color="secondary"
                            text
                            @click="showMainPicker = 1 - showMainPicker"
                          >
                            Close
                          </v-btn>
                        </div>
                      </v-card-actions>
                    </v-card>
                  </div>
                </v-layout>
              </v-flex>

              <v-flex xs12 sm6 md4 px-3>
                <v-layout column>
                  <v-select
                    :items="[
                      { text: '1 month', value: '1' },
                      { text: '3 months', value: '3' },
                      { text: '6 months', value: '6' },
                      { text: '9 months', value: '9' },
                      { text: '12 months', value: '12' },
                      { text: '24 months', value: '24' }
                    ]"
                    item-text="text"
                    item-value="value"
                    filled
                    dense
                    required
                    :rules="[v => !!v || 'Item is required']"
                    color="secondary"
                    hint="How long should event data/reports be stored for?"
                    persistent-hint
                    label="Event data/report retention period?"
                    v-model="editedItem.dataRetention"
                  >
                  </v-select>
                  <v-select
                    :items="[
                      { text: '3 months', value: '3' },
                      { text: '6 months', value: '6' },
                      { text: '12 months', value: '12' },
                      { text: '24 months', value: '24' }
                    ]"
                    item-text="text"
                    item-value="value"
                    filled
                    dense
                    required
                    :rules="[v => !!v || 'Item is required']"
                    color="secondary"
                    hint="How often should user passwords be changed?"
                    persistent-hint
                    label="Password frequency change?"
                    v-model="editedItem.passwordFrequency"
                  >
                  </v-select>

                  <div class="mt-12">
                    Select button colour scheme
                    <v-layout row class="px-3 mt-0">
                      <v-sheet
                        :style="backgroundButtonColor"
                        width="80"
                        height="32"
                        class="pa-0 ma-0 mt-1"
                        d-inline-flex
                        @click="showButtonPicker = 1 - showButtonPicker"
                      ></v-sheet>
                      <v-text-field
                        hide-details
                        color="grey"
                        lighten-4
                        outline
                        d-inline-flex
                        v-model="editedItem.secondaryColor"
                        clearable
                        class="pl-3 pt-0"
                        @click="showButtonPicker = 1 - showButtonPicker"
                      ></v-text-field>
                    </v-layout>

                    <v-card
                      flat
                      v-if="showButtonPicker == true"
                      v-model="showButtonPicker"
                    >
                      <v-card-text class="pa-0 ma-0">
                        <v-color-picker
                          canvas-height="75"
                          hide-mode-switch
                          hide-inputs
                          flat
                          width="500"
                          class="pa-0 ma-0  mt-2"
                          mode="hexa"
                          v-model="editedItem.secondaryColor"
                        ></v-color-picker>
                      </v-card-text>
                      <v-card-actions>
                        <div
                          class="text-center"
                          style="width:100%;display: inline-flex"
                        >
                          <v-btn
                            class="mx-auto"
                            color="secondary"
                            text
                            @click="showButtonPicker = 1 - showButtonPicker"
                          >
                            Close
                          </v-btn>
                        </div>
                      </v-card-actions>
                    </v-card>
                  </div>
                </v-layout>
              </v-flex>
            </v-layout>
          </v-container>
        </v-card-text>
        <v-card-actions class="px-10">
          <v-spacer></v-spacer>
          <v-btn
            color="blue darken-1"
            class="px-10"
            text
            @click="showNewItem = false"
            >Cancel</v-btn
          >
          <v-btn
            color="blue darken-1"
            class="font-weight-bold"
            text
            @click="save('newEntryForm')"
            >Save</v-btn
          >
        </v-card-actions>
      </v-form>
    </v-card>
  </v-container>
</template>

<style lang="scss"></style>

<script>
export default {
  name: "App",
  data: () => ({
    all: false,
    readonly: true,
    loading: false,
    items: [],
    selected: [],
    type: "hex",
    hex: "#FF00FF",
    showMainPicker: false,
    showButtonPicker: false,

    pagesizes: [10, 25, 75, 100],
    itemsPerPage: 10,
    page: 1,
    pageCount: 0,
    showNewItem: false,
    search: "",
    expanded: [],
    dialog: false,
    file: null,
    valid: false,

    nameRules: [
      v => !!v || "This is required",
      v => (v && v.length <= 50) || "Must be less than 50 characters"
    ],
    emailRules: [
      v => !!v || "E-mail is required",
      v =>
        /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(v) ||
        "E-mail must be valid"
    ],

    mainColor: "#990099",
    buttonColor: "#990000",

    headers: [
      { text: "Company", value: "companyName", sortable: true },
      { text: "Date Retention", value: "dataRetention", sortable: true },
      { text: "Password Reset", value: "passwordFrequency", sortable: true },
      { text: "Company ID", value: "companyID", sortable: false }
    ],

    companies: [],
    editedIndex: -1,
    editedItem: {
      companyID: "",
      companyName: "",
      emailAddress: "",
      language: "",
      dataRetention: "",
      passwordFrequency: "",
      accountStatus: "",
      logo: "",
      file: null,
      primaryColor: "#990000FF",
      secondaryColor: "#999900FF",
      LBcustomerID: ""
    },
    defaultItem: {
      companyID: "",
      companyName: "",
      emailAddress: "",
      language: "",
      dataRetention: "",
      passwordFrequency: "",
      accountStatus: "",
      logo: "",
      primaryColor: "#990000FF",
      secondaryColor: "#999900FF",
      LBcustomerID: ""
    }
  }),

  computed: {
    curUser: function() {
      return this.$store.state.userName
    },
    userID: function() {
      return this.$store.state.userID
    },
    userCompany: function() {
      return this.$store.state.companyName
    },
    userGroup: {
      get: function() {
        return this.$store.state.userGroup
      },
      set: function(newValue) {
        return this.$store.state.userGroup=newValue
      }
    },
    backgroundMainColor() {
      return "background:" + this.editedItem.primaryColor;
    },

    backgroundButtonColor() {
      return "background:" + this.editedItem.secondaryColor;
    },

    starting: function() {
      return (this.page - 1) * this.itemsPerPage + 1;
    },
    ending: function() {
      return Math.min(this.page * this.itemsPerPage + 1, this.companies.length);
    },
    total: function() {
      return this.companies.length;
    }
  },

  created() {
    this.initialize();
  },

  beforeMount() {
    fetch("http://netroadshow.incommglobalevents.com/api/companies.php")
      .then(result => result.json())
      .then(companies => (this.companies = companies));
  },

  methods: {
    initialize() {},

    exportSelectedCompanies() {

      const forExport = this.selected.map(u => Object.keys(u).reduce((newObj, key) => (
             key != 'logo' && key != 'LBcustomerID'
             && key != 'primaryColor' && key != 'secondaryColor'

        ) ? { ...newObj, [key]: u[key]} : newObj, {}));

        console.log(forExport)
        this.csvExport(forExport)

    },

    deleteSelectedCompanies() {

      console.log("DELETING SELECTION")

      const _this = this
      const _deleteList = []
      if (this.selected.length>0) {
        confirm("Are you sure you want to delete these items?") &&
        this.selected.forEach(function(entry) {
            const index = _this.companies.indexOf(entry);
              console.log(entry)
            _deleteList.push({companyID:entry.companyID})
            _this.companies.splice(index, 1);
          })

          if (_deleteList.length>0) {
            console.log("Committing chage to DB");
            console.log(_deleteList)

            axios({
              url: "http://netroadshow.incommglobalevents.com/api/delete_companies.php",
              data: { forDeletion: _deleteList  },
              method: "POST"
            })
              .then(resp => {
                console.log("Deletion Successful");
                this.selected=[]
                console.log(resp);
              })
              .catch(err => {
                console.log("Deletion error" + err);
              });

          }
        } else {
          alert("Please make a selection first!")
        }
    },

    onBtNext() {
      this.page = Math.min(
        this.page + 1,
        Math.ceil(this.companies.length / this.itemsPerPage)
      );
    },
    onBtPrevious() {
      this.page = Math.max(this.page - 1, 1);
    },

    editItem(item) {
      this.editedIndex = this.companies.indexOf(item);
      this.editedItem = Object.assign({}, item);
    },

    deleteItem(item) {
      const index = this.companies.indexOf(item);
      confirm("Are you sure you want to delete this item?") &&
        this.companies.splice(index, 1);
    },

    close() {
      this.showNewItem = false;
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      }, 300);
    },

    closeRow() {
      this.$set(this.$refs.dTable.expanded, 0, false);
    },

    toggleAll() {
      console.log(this.$refs.dTable)
      this.all = 1-this.all
      this.$refs.dTable.toggleSelectAll(this.all)
/*
      if (this.$refs.dTable.$data.selection.length>0) this.$refs.dTable.$data.selection = [];
      else this.$refs.dTable.$data.selection = this.$refs.dTable.items
*/
      console.log(this.$refs.dTable.$data.selection)

    },

    showSelected(item) {
      if (this.selected[item.userID]) this.selected[item.userID] = null;
      else this.selected[item.userID] = item;
      console.log(this.selected);
    },

    changeSort(column, on) {
      this.headers[column].sortDescending = !this.headers[column]
        .sortDescending;
      on.sort(this.headers[column].value);
    },

    expandrow(item, expand, isExpanded) {
      console.log(isExpanded);
      if (isExpanded) {
        this.editedIndex = -1;
        this.editedItem = Object.assign({}, null);
      } else {
        this.editedIndex = this.companies.indexOf(item);
        this.editedItem = Object.assign({}, item);
      }
      expand(1 - isExpanded);
    },

    newitem() {
      this.$refs.newEntryForm.reset();
      this.$refs.newEntryForm.resetValidation();
      this.editedItem = Object.assign({}, this.defaultItem);
      this.editedIndex = -1;
      this.showNewItem = true;
      this.valid = false;
    },

    save(form) {
      let result = this.$refs[form].validate();

      console.log(result);

      if (result) {
        let companyData = this.editedItem;

        this.$store
          .dispatch("saveCompany", { companyData })
          .then(result => {
            console.log(result);

            this.editedItem.companyID = result.data;
            if (this.editedIndex > -1) {
              Object.assign(this.companies[this.editedIndex], this.editedItem);
            } else {
              this.companies.push(this.editedItem);
            }
            this.showNewItem = false;
            this.$refs.entryForm.resetValidation();
            this.closeRow()
          })
          .catch(err => console.log(err));
      }
    },

    csvExport(arrData) {
      let csvContent = "data:text/csv;charset=utf-8,";
      csvContent += [
        Object.keys(arrData[0]).join(","),
        ...arrData.map(item => Object.values(item).join(","))
      ]
        .join("\n")
        .replace(/(^\[)|(\]$)/gm, "");

      const data = encodeURI(csvContent);
      const link = document.createElement("a");
      link.setAttribute("href", data);
      link.setAttribute("download", "export.csv");
      link.click();
    },

    onFileChange() {
      let reader = new FileReader();
      reader.onload = () => {
        this.editedItem.logo = reader.result;
      };
      if (this.file) {
        reader.readAsDataURL(this.file);
      }
    },

    inArray: function(needle, haystack) {
      var length = haystack.length;
      for (var i = 0; i < length; i++) {
        if (haystack[i] == needle) return true;
      }
      return false;
    }
  }
};
</script>
