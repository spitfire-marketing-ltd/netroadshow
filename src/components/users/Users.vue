<template>
  <v-container>
    <v-layout align-center justify-center pa-0 ma-0>
      <v-flex sm12 align-center justify-center row fill-height>
        <v-flex d-block tag="h1" py-6 color="primary" text-left>Users</v-flex>
        <v-list-item text-left class="pa-0 ma-0">
          <v-flex sm12>
            <v-data-table
              ref="dTable"
              color="secondary"
              v-model="selected"
              :headers="headers"
              :items="userData"
              single-expand
              :expanded.sync="expanded"
              :search="search"
              item-key="userID"
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
                v-slot:item="{headers, item, isExpanded, isSelected, expand, select }"
              >
                <tr>
                  <td class="text-xs-right">
                    <div
                      class="datatable-cell-wrapper"
                      @click="expandrow(item, expand, isExpanded)"
                    >
                      {{ item.firstName }} {{ item.lastName }}
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
                      {{ item.companyName }}
                    </div>
                  </td>
                  <td class="text-xs-right">
                    <div class="datatable-cell-wrapper">
                      {{ item.emailAddress }}
                    </div>
                  </td>
                  <td class="text-xs-right">
                    <div class="datatable-cell-wrapper">
                      {{ item.areaCode }} {{ item.contactNumber }}
                    </div>
                  </td>
                  <td class="text-xs-right">
                    <div class="datatable-cell-wrapper">
                      {{ userType(item) }}
                    </div>
                  </td>
                  <td class="text-xs-right">
                    <div class="datatable-cell-wrapper">
                      <v-icon
                        medium
                        color="secondary"
                        @click="expandrow(item, expand, isExpanded)"
                      >
                        edit
                      </v-icon>
                    </div>
                  </td>
                  <td class="text-xs-right">
                    <div class="datatable-cell-wrapper">
                      {{ item.clientID }}
                    </div>
                  </td>
                  <td class="text-xs-right">
                    <div class="datatable-cell-wrapper">{{ item.userID }}</div>
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
                v-slot:expanded-item="{ item, headers, isSelected, expand }"
              >
                <td :colspan="headers.length + 1" class="pa-0 ma-0">
                  <v-card elevation-0 class="mb-6">
                    <v-form valid="valid" ref="entryForm">
                      <v-card-text>
                        <v-container>
                          <v-layout wrap>
                            <v-flex xs12 sm6 md4 px-3>
                              <v-layout column>
                                <v-select
                                  :items="userGroups"
                                  v-model="editedItem.userGroup"
                                  item-text="groupName"
                                  item-value="groupValue"
                                  filled
                                  label="UserType"
                                  :rules="[v => !!v || 'Item is required']"
                                  required
                                >
                                </v-select>

                                <v-text-field
                                  filled
                                  v-model="editedItem.firstName"
                                  label="Firstname"
                                  :rules="nameRules"
                                  required
                                ></v-text-field>
                                <v-text-field
                                  filled
                                  v-model="editedItem.lastName"
                                  label="Last name"
                                  :rules="nameRules"
                                  required
                                ></v-text-field>
                                <v-text-field
                                  filled
                                  v-model="editedItem.emailAddress"
                                  label="Email"
                                  :rules="emailRules"
                                  required
                                ></v-text-field>

                                <v-layout row>
                                  <v-flex xs8 sm7 ma-0 px-3>
                                    <v-text-field
                                      filled
                                      v-model="editedItem.contactNumber"
                                      label="Phone"
                                      :rules="[v => !!v || 'Item is required']"
                                      required
                                    ></v-text-field>
                                  </v-flex>

                                  <v-flex xs4 sm5 ma-0 px-3>
                                    <v-select
                                      v-model="editedItem.areaCode"
                                      :items="dialingCodes"
                                      item-text="code"
                                      item-value="code"
                                      hint="Country code"
                                      persistent-hint
                                      filled
                                      full-width
                                      label="Area"
                                      :rules="[v => !!v || 'Item is required']"
                                      required
                                    >
                                    </v-select>
                                  </v-flex>
                                </v-layout>
                              </v-layout>
                            </v-flex>

                            <v-flex xs12 sm6 md4 px-3>
                              <v-layout column>
                                <v-select
                                  :items="companies"
                                  item-value="companyID"
                                  item-text="companyName"
                                  filled
                                  label="Company"
                                  :rules="[v => !!v || 'Item is required']"
                                  required
                                  v-model="editedItem.clientID"
                                >
                                </v-select>
                                <v-text-field
                                  filled
                                  v-model="editedItem.address1"
                                  label="Street"
                                  :rules="[v => !!v || 'Item is required']"
                                  required
                                ></v-text-field>
                                <v-text-field
                                  filled
                                  v-model="editedItem.address2"
                                  label="Town"
                                  :rules="[v => !!v || 'Item is required']"
                                  required
                                ></v-text-field>

                                <v-select
                                    v-model="editedItem.country"
                                    :items="countries"
                                    :loading="isLoadingCountries"
                                    :search-input.sync="countries"
                                    required
                                    :rules="[v => !!v || 'Item is required']"
                                    class="fixed"
                                    hide-no-data
                                    :menu-props="{ 'position-y': 52, 'offset-y': true, 'nudge-bottom': 15 }"
                                    filled
                                    item-text="country_name"
                                    item-value="country_code"
                                    label="Country"
                                    @change="resetTimeZones()"
                                  ></v-select>
                                <v-text-field
                                  filled
                                  v-model="editedItem.postcode"
                                  label="Postcode/Zip code"
                                  :rules="[v => !!v || 'Item is required']"
                                  required
                                ></v-text-field>
                              </v-layout>
                            </v-flex>

                            <v-flex xs12 sm6 md4 px-3>
                              <v-layout column>
                                <v-select
                                  :items="['English', 'French']"
                                  filled
                                  label="Language"
                                  :rules="[v => !!v || 'Item is required']"
                                  required
                                  v-model="editedItem.language"
                                >
                                </v-select>
                                <v-select
                                    v-model="editedItem.timezone"
                                    :items="timezones"
                                    :loading="isLoadingTimezones"
                                    :search-input.sync="LUTimezones"
                                    required
                                    :rules="[v => !!v || 'Item is required']"
                                    class="fixed"
                                    hide-no-data
                                    filled
                                    item-text="timeZone"
                                    item-value="tz"
                                    label="Time zone"
                                    placeholder="Fill out country first"

                                  ></v-select>
                                <v-select
                                  :items="['Live', 'Suspended']"
                                  filled
                                  label="Account status"
                                  :rules="[v => !!v || 'Item is required']"
                                  required
                                  v-model="editedItem.accountStatus"
                                >
                                </v-select>
                                <v-text-field
                                  filled
                                  :type="showPassword2 ? 'text' : 'password'"
                                  hint="(User will be sent email to invite them to set a password)"
                                  persistent-hint
                                  v-model="editedItem.userPass"
                                  label="Password"
                                  :rules="passwordRules"

                                >
                                <v-icon
                                  slot="append"
                                  color="secondary"

                                  @click.stop="showPassword2 = !showPassword2"
                                  >{{ showpasswordIcon2 }}</v-icon
                                ></v-text-field>

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
                    </v-form>
                  </v-card>
                </td>
              </template>
              <template v-slot:no-data>
                <v-btn color="primary" @click="initialize">Reset</v-btn>
              </template>
            </v-data-table>

            <template v-slot:item.action="{ item, isExpanded, expand }">
              <v-icon small @click="deleteItem(item)">
                delete
              </v-icon>
            </template>

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
                    label="Name, company or email"
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
          v-if="inArray(userGroup, [1])"
          text
          small
          class="ma-0 pa-0 text-none font-weight-bold"
          @click="newitem()"
        >
          <v-icon class="mdi-24px" color="secondary" left
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
              <v-list-item py-2>
                <v-list-item-title @click="exportSelectedUsers()"
                  >Export user selection</v-list-item-title
                >
              </v-list-item>

              <v-list-item  v-if="inArray(userGroup, [1])" py-2>
                <v-list-item-title @click="deleteSelectedUsers()"
                  >Delete user</v-list-item-title
                >
              </v-list-item>
              <v-divider v-if="inArray(userGroup, [1])"></v-divider>
              <v-list-item v-if="inArray(userGroup, [1])"py-2>
                <v-list-item-title @click="newitem()"
                  >New user</v-list-item-title
                >
              </v-list-item>
            </v-list>
          </span>
        </v-menu>
      </v-flex>
    </v-layout>

    <v-card v-show="showNewItem" elevation-0 class="mb-6">
      <v-card-title class="px-10 py-6">
        <span class="headline primary-text">Add a new user</span>
      </v-card-title>
      <v-form v-model="valid" class="entryForm" ref="newEntryForm">
        <v-card-text>
          <v-container>
            <v-layout wrap>
              <v-flex xs12 sm6 md4 px-3>
                <v-layout column>
                  <v-select
                    :items="userGroups"
                    v-model="editedItem.userGroup"
                    item-text="groupName"
                    item-value="groupValue"
                    filled
                    required
                    :rules="[v => !!v || 'Item is required']"
                    label="UserType"
                  >
                  </v-select>

                  <v-text-field
                    filled
                    v-model="editedItem.firstName"
                    label="Firstname"
                    required
                    :rules="nameRules"
                  ></v-text-field>
                  <v-text-field
                    filled
                    v-model="editedItem.lastName"
                    label="Last name"
                    required
                    :rules="nameRules"
                  ></v-text-field>
                  <v-text-field
                    filled
                    v-model="editedItem.emailAddress"
                    label="Email"
                    :rules="emailRules"
                    required
                  ></v-text-field>

                  <v-layout row>
                    <v-flex xs8 sm7 ma-0 px-3>
                      <v-text-field
                        filled
                        v-model="editedItem.contactNumber"
                        required
                        :rules="[v => !!v || 'Item is required']"
                        label="Phone"
                      ></v-text-field>
                    </v-flex>

                    <v-flex xs4 sm5 ma-0 px-3>
                      <v-select
                        v-model="editedItem.areaCode"
                        :items="dialingCodes"
                        item-text="code"
                        item-value="code"
                        hint="Country code"
                        persistent-hint
                        filled
                        full-width
                        label="Area"
                        required
                        :rules="[v => !!v || 'Item is required']"
                      >
                      </v-select>
                    </v-flex>
                  </v-layout>
                </v-layout>
              </v-flex>

              <v-flex xs12 sm6 md4 px-3>
                <v-layout column>
                  <v-select
                    :items="companies"
                    item-value="companyID"
                    item-text="companyName"
                    filled
                    label="Company"
                    required
                    v-model="editedItem.clientID"
                    :rules="[v => !!v || 'Item is required']"
                  >
                  </v-select>
                  <v-text-field
                    filled
                    v-model="editedItem.address1"
                    label="Street"
                    :rules="[v => !!v || 'Item is required']"
                    required
                  ></v-text-field>
                  <v-text-field
                    filled
                    v-model="editedItem.address2"
                    label="Town"
                    :rules="[v => !!v || 'Item is required']"
                    required
                  ></v-text-field>

                  <v-select
                      v-model="editedItem.country"
                      :items="countries"
                      :loading="isLoadingCountries"
                      :search-input.sync="countries"
                      required
                      :rules="[v => !!v || 'Item is required']"
                      class="fixed"
                      hide-no-data
                      :menu-props="{ 'position-y': 52, 'offset-y': true, 'nudge-bottom': 15 }"
                      filled
                      item-text="country_name"
                      item-value="country_code"
                      label="Country"
                      placeholder="Start typing to search"
                      @change="resetTimeZones()"

                    ></v-select>
                  <v-text-field
                    filled
                    v-model="editedItem.postcode"
                    label="Postcode/Zip code"
                    required
                    :rules="[v => !!v || 'Item is required']"
                  ></v-text-field>
                </v-layout>
              </v-flex>

              <v-flex xs12 sm6 md4 px-3>
                <v-layout column>
                  <v-select
                    :items="['English', 'French', 'Spanish', 'Chinese', 'Binary']"
                    filled
                    label="Language"
                    v-model="editedItem.language"
                    required
                    :rules="[v => !!v || 'Item is required']"
                  >
                  </v-select>
                  <v-select
                      v-model="editedItem.timezone"
                      :items="timezones"
                      :loading="isLoadingTimezones"
                      :search-input.sync="LUTimezones"
                      required
                      :rules="[v => !!v || 'Item is required']"
                      class="fixed"
                      hide-no-data
                      filled
                      item-text="timeZone"
                      item-value="tz"
                      label="Time zone"
                      placeholder="Fill out country first"

                    ></v-select>
                  <v-select
                    :items="['Live', 'Suspended']"
                    filled
                    label="Account status"
                    v-model="editedItem.accountStatus"
                    required
                    :rules="[v => !!v || 'Item is required']"
                  >
                  </v-select>
                  <v-text-field
                    filled
                    :type="showPassword1 ? 'text' : 'password'"
                    hint="(User will be sent email to invite them to set a password)"
                    persistent-hint
                    v-model="editedItem.userPass"
                    label="Password"
                    :rules="passwordRules"
                    required
                  >
                  <v-icon
                    slot="append"
                    color="secondary"

                    @click.stop="showPassword1 = !showPassword1"
                    >{{ showpasswordIcon1 }}</v-icon
                  ></v-text-field>


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
            >Invite New User</v-btn
          >
        </v-card-actions>
      </v-form>
    </v-card>
    <v-alert  v-model="onError" dismissible type="error">{{errorMessage}}</v-alert>
  </v-container>
</template>

<style lang="scss">
.fixed.v-text-field--filled .v-select__slot > input {
    margin-top: 0!important;
}
</style>

<script>
import zxcvbn from 'zxcvbn'



export default {
  name: "App",

  props: {
    users: {
      type: Array,
        required: true,
    },
  },
  data: () => ({
    readonly: true,
    loading: false,
    items: [],
    drawerRight: false,
    valid: false,
    isLoadingCountries:false,
    isLoadingTimezones:false,
    all: false,
    errorMessage:"",
    onError: false,
    showPassword1: false,
    showPassword2: false,

    pagesizes: [10, 25, 75, 100],
    itemsPerPage: 10,
    page: 1,
    pageCount: 0,
    showNewItem: false,
    search: "",
    expanded: [],
    selected:[],
    dialog: false,

    userGroups:[
      { groupValue: '1', groupName: 'Incomm Manager' },
      { groupValue: '2', groupName: 'Incomm Agent' },
      { groupValue: '3', groupName: 'Banker' },
      { groupValue: '4', groupName: 'Investor' }
    ],

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
    passwordRules: [

      v => (v && v.length >= 6) || 'Password must be min 8 characters long',
      v => (v &&  zxcvbn(v).score>2) || 'Password not strong enough',
      v => /(?=.*\d)(?=.*[a-z])(?=.*[~\`!@#$%^&*()\-_+={}\[\];;:"<>\\|,\.\/\?])(?=.*[A-Z]).{6,}/.test(v) ||
        "Password must contaiin at least one number, one lowercase and one uppercase letter and one special character"
//        ~`!@#$%^&*()-_+={}[]|\;:"<>,./?
    ],

    headers: [
      {
        text: "Name",
        align: "left",
        sortable: true,
        value: "lastName",
        sortDescending: "desc"
      },
      { text: "Company", value: "companyName", sortable: true },
      { text: "Email", value: "emailAddress", sortable: true },
      { text: "Phone", value: "contactNumber", sortable: true },
      { text: "User Type", value: "userGroup", sortable: true },
      { text: "Action", value: "action", sortable: false },
      { text: "ClientID", value: "clientID", sortable: false },
      { text: "UserID", value: "userID", sortable: false }
    ],

    companies: [],
    countries: [],
    timezones: [],


    editedIndex: -1,
    editedItem: {
      userID: "",
      clientID: "",
      sessionID: "",
      userName: "",
      userPass: "",
      userGroup: "",
      isAdmin: "",
      pwLastChanged: "",
      firstName: "",
      lastName: "",
      emailAddress: "",
      contactNumber: "",
      areaCode: "",
      companyName: "",
      address1: "",
      address2: "",
      address3: "",
      address4: "",
      country: "GB",
      postcode: "",
      language: "",
      timezone: "",
      accountStatus: "",
      lastLog: ""
    },
    defaultItem: {
      userID: "",
      clientID: "",
      sessionID: "",
      userName: "",
      userPass: "",
      userGroup: "",
      isAdmin: "",
      pwLastChanged: "",
      firstName: "",
      lastName: "",
      emailAddress: "",
      contactNumber: "",
      areaCode: "",
      companyName: "",
      address1: "",
      address2: "",
      address3: "",
      address4: "",
      country: "GB",
      postcode: "",
      language: "",
      timezone: "",
      accountStatus: "",
      lastLog: ""
    },

    dialingCodes: [
      {
        "code": "+7 840",
        "name": "Abkhazia"
      },
      {
        "code": "+93",
        "name": "Afghanistan"
      },
      {
        "code": "+355",
        "name": "Albania"
      },
      {
        "code": "+213",
        "name": "Algeria"
      },
      {
        "code": "+1 684",
        "name": "American Samoa"
      },
      {
        "code": "+376",
        "name": "Andorra"
      },
      {
        "code": "+244",
        "name": "Angola"
      },
      {
        "code": "+1 264",
        "name": "Anguilla"
      },
      {
        "code": "+1 268",
        "name": "Antigua and Barbuda"
      },
      {
        "code": "+54",
        "name": "Argentina"
      },
      {
        "code": "+374",
        "name": "Armenia"
      },
      {
        "code": "+297",
        "name": "Aruba"
      },
      {
        "code": "+247",
        "name": "Ascension"
      },
      {
        "code": "+61",
        "name": "Australia"
      },
      {
        "code": "+672",
        "name": "Australian External Territories"
      },
      {
        "code": "+43",
        "name": "Austria"
      },
      {
        "code": "+994",
        "name": "Azerbaijan"
      },
      {
        "code": "+1 242",
        "name": "Bahamas"
      },
      {
        "code": "+973",
        "name": "Bahrain"
      },
      {
        "code": "+880",
        "name": "Bangladesh"
      },
      {
        "code": "+1 246",
        "name": "Barbados"
      },
      {
        "code": "+1 268",
        "name": "Barbuda"
      },
      {
        "code": "+375",
        "name": "Belarus"
      },
      {
        "code": "+32",
        "name": "Belgium"
      },
      {
        "code": "+501",
        "name": "Belize"
      },
      {
        "code": "+229",
        "name": "Benin"
      },
      {
        "code": "+1 441",
        "name": "Bermuda"
      },
      {
        "code": "+975",
        "name": "Bhutan"
      },
      {
        "code": "+591",
        "name": "Bolivia"
      },
      {
        "code": "+387",
        "name": "Bosnia and Herzegovina"
      },
      {
        "code": "+267",
        "name": "Botswana"
      },
      {
        "code": "+55",
        "name": "Brazil"
      },
      {
        "code": "+246",
        "name": "British Indian Ocean Territory"
      },
      {
        "code": "+1 284",
        "name": "British Virgin Islands"
      },
      {
        "code": "+673",
        "name": "Brunei"
      },
      {
        "code": "+359",
        "name": "Bulgaria"
      },
      {
        "code": "+226",
        "name": "Burkina Faso"
      },
      {
        "code": "+257",
        "name": "Burundi"
      },
      {
        "code": "+855",
        "name": "Cambodia"
      },
      {
        "code": "+237",
        "name": "Cameroon"
      },
      {
        "code": "+1",
        "name": "Canada"
      },
      {
        "code": "+238",
        "name": "Cape Verde"
      },
      {
        "code": "+ 345",
        "name": "Cayman Islands"
      },
      {
        "code": "+236",
        "name": "Central African Republic"
      },
      {
        "code": "+235",
        "name": "Chad"
      },
      {
        "code": "+56",
        "name": "Chile"
      },
      {
        "code": "+86",
        "name": "China"
      },
      {
        "code": "+61",
        "name": "Christmas Island"
      },
      {
        "code": "+61",
        "name": "Cocos-Keeling Islands"
      },
      {
        "code": "+57",
        "name": "Colombia"
      },
      {
        "code": "+269",
        "name": "Comoros"
      },
      {
        "code": "+242",
        "name": "Congo"
      },
      {
        "code": "+243",
        "name": "Congo, Dem. Rep. of (Zaire)"
      },
      {
        "code": "+682",
        "name": "Cook Islands"
      },
      {
        "code": "+506",
        "name": "Costa Rica"
      },
      {
        "code": "+385",
        "name": "Croatia"
      },
      {
        "code": "+53",
        "name": "Cuba"
      },
      {
        "code": "+599",
        "name": "Curacao"
      },
      {
        "code": "+537",
        "name": "Cyprus"
      },
      {
        "code": "+420",
        "name": "Czech Republic"
      },
      {
        "code": "+45",
        "name": "Denmark"
      },
      {
        "code": "+246",
        "name": "Diego Garcia"
      },
      {
        "code": "+253",
        "name": "Djibouti"
      },
      {
        "code": "+1 767",
        "name": "Dominica"
      },
      {
        "code": "+1 809",
        "name": "Dominican Republic"
      },
      {
        "code": "+670",
        "name": "East Timor"
      },
      {
        "code": "+56",
        "name": "Easter Island"
      },
      {
        "code": "+593",
        "name": "Ecuador"
      },
      {
        "code": "+20",
        "name": "Egypt"
      },
      {
        "code": "+503",
        "name": "El Salvador"
      },
      {
        "code": "+240",
        "name": "Equatorial Guinea"
      },
      {
        "code": "+291",
        "name": "Eritrea"
      },
      {
        "code": "+372",
        "name": "Estonia"
      },
      {
        "code": "+251",
        "name": "Ethiopia"
      },
      {
        "code": "+500",
        "name": "Falkland Islands"
      },
      {
        "code": "+298",
        "name": "Faroe Islands"
      },
      {
        "code": "+679",
        "name": "Fiji"
      },
      {
        "code": "+358",
        "name": "Finland"
      },
      {
        "code": "+33",
        "name": "France"
      },
      {
        "code": "+596",
        "name": "French Antilles"
      },
      {
        "code": "+594",
        "name": "French Guiana"
      },
      {
        "code": "+689",
        "name": "French Polynesia"
      },
      {
        "code": "+241",
        "name": "Gabon"
      },
      {
        "code": "+220",
        "name": "Gambia"
      },
      {
        "code": "+995",
        "name": "Georgia"
      },
      {
        "code": "+49",
        "name": "Germany"
      },
      {
        "code": "+233",
        "name": "Ghana"
      },
      {
        "code": "+350",
        "name": "Gibraltar"
      },
      {
        "code": "+30",
        "name": "Greece"
      },
      {
        "code": "+299",
        "name": "Greenland"
      },
      {
        "code": "+1 473",
        "name": "Grenada"
      },
      {
        "code": "+590",
        "name": "Guadeloupe"
      },
      {
        "code": "+1 671",
        "name": "Guam"
      },
      {
        "code": "+502",
        "name": "Guatemala"
      },
      {
        "code": "+224",
        "name": "Guinea"
      },
      {
        "code": "+245",
        "name": "Guinea-Bissau"
      },
      {
        "code": "+595",
        "name": "Guyana"
      },
      {
        "code": "+509",
        "name": "Haiti"
      },
      {
        "code": "+504",
        "name": "Honduras"
      },
      {
        "code": "+852",
        "name": "Hong Kong SAR China"
      },
      {
        "code": "+36",
        "name": "Hungary"
      },
      {
        "code": "+354",
        "name": "Iceland"
      },
      {
        "code": "+91",
        "name": "India"
      },
      {
        "code": "+62",
        "name": "Indonesia"
      },
      {
        "code": "+98",
        "name": "Iran"
      },
      {
        "code": "+964",
        "name": "Iraq"
      },
      {
        "code": "+353",
        "name": "Ireland"
      },
      {
        "code": "+972",
        "name": "Israel"
      },
      {
        "code": "+39",
        "name": "Italy"
      },
      {
        "code": "+225",
        "name": "Ivory Coast"
      },
      {
        "code": "+1 876",
        "name": "Jamaica"
      },
      {
        "code": "+81",
        "name": "Japan"
      },
      {
        "code": "+962",
        "name": "Jordan"
      },
      {
        "code": "+7 7",
        "name": "Kazakhstan"
      },
      {
        "code": "+254",
        "name": "Kenya"
      },
      {
        "code": "+686",
        "name": "Kiribati"
      },
      {
        "code": "+965",
        "name": "Kuwait"
      },
      {
        "code": "+996",
        "name": "Kyrgyzstan"
      },
      {
        "code": "+856",
        "name": "Laos"
      },
      {
        "code": "+371",
        "name": "Latvia"
      },
      {
        "code": "+961",
        "name": "Lebanon"
      },
      {
        "code": "+266",
        "name": "Lesotho"
      },
      {
        "code": "+231",
        "name": "Liberia"
      },
      {
        "code": "+218",
        "name": "Libya"
      },
      {
        "code": "+423",
        "name": "Liechtenstein"
      },
      {
        "code": "+370",
        "name": "Lithuania"
      },
      {
        "code": "+352",
        "name": "Luxembourg"
      },
      {
        "code": "+853",
        "name": "Macau SAR China"
      },
      {
        "code": "+389",
        "name": "Macedonia"
      },
      {
        "code": "+261",
        "name": "Madagascar"
      },
      {
        "code": "+265",
        "name": "Malawi"
      },
      {
        "code": "+60",
        "name": "Malaysia"
      },
      {
        "code": "+960",
        "name": "Maldives"
      },
      {
        "code": "+223",
        "name": "Mali"
      },
      {
        "code": "+356",
        "name": "Malta"
      },
      {
        "code": "+692",
        "name": "Marshall Islands"
      },
      {
        "code": "+596",
        "name": "Martinique"
      },
      {
        "code": "+222",
        "name": "Mauritania"
      },
      {
        "code": "+230",
        "name": "Mauritius"
      },
      {
        "code": "+262",
        "name": "Mayotte"
      },
      {
        "code": "+52",
        "name": "Mexico"
      },
      {
        "code": "+691",
        "name": "Micronesia"
      },
      {
        "code": "+1 808",
        "name": "Midway Island"
      },
      {
        "code": "+373",
        "name": "Moldova"
      },
      {
        "code": "+377",
        "name": "Monaco"
      },
      {
        "code": "+976",
        "name": "Mongolia"
      },
      {
        "code": "+382",
        "name": "Montenegro"
      },
      {
        "code": "+1664",
        "name": "Montserrat"
      },
      {
        "code": "+212",
        "name": "Morocco"
      },
      {
        "code": "+95",
        "name": "Myanmar"
      },
      {
        "code": "+264",
        "name": "Namibia"
      },
      {
        "code": "+674",
        "name": "Nauru"
      },
      {
        "code": "+977",
        "name": "Nepal"
      },
      {
        "code": "+31",
        "name": "Netherlands"
      },
      {
        "code": "+599",
        "name": "Netherlands Antilles"
      },
      {
        "code": "+1 869",
        "name": "Nevis"
      },
      {
        "code": "+687",
        "name": "New Caledonia"
      },
      {
        "code": "+64",
        "name": "New Zealand"
      },
      {
        "code": "+505",
        "name": "Nicaragua"
      },
      {
        "code": "+227",
        "name": "Niger"
      },
      {
        "code": "+234",
        "name": "Nigeria"
      },
      {
        "code": "+683",
        "name": "Niue"
      },
      {
        "code": "+672",
        "name": "Norfolk Island"
      },
      {
        "code": "+850",
        "name": "North Korea"
      },
      {
        "code": "+1 670",
        "name": "Northern Mariana Islands"
      },
      {
        "code": "+47",
        "name": "Norway"
      },
      {
        "code": "+968",
        "name": "Oman"
      },
      {
        "code": "+92",
        "name": "Pakistan"
      },
      {
        "code": "+680",
        "name": "Palau"
      },
      {
        "code": "+970",
        "name": "Palestinian Territory"
      },
      {
        "code": "+507",
        "name": "Panama"
      },
      {
        "code": "+675",
        "name": "Papua New Guinea"
      },
      {
        "code": "+595",
        "name": "Paraguay"
      },
      {
        "code": "+51",
        "name": "Peru"
      },
      {
        "code": "+63",
        "name": "Philippines"
      },
      {
        "code": "+48",
        "name": "Poland"
      },
      {
        "code": "+351",
        "name": "Portugal"
      },
      {
        "code": "+1 787",
        "name": "Puerto Rico"
      },
      {
        "code": "+974",
        "name": "Qatar"
      },
      {
        "code": "+262",
        "name": "Reunion"
      },
      {
        "code": "+40",
        "name": "Romania"
      },
      {
        "code": "+7",
        "name": "Russia"
      },
      {
        "code": "+250",
        "name": "Rwanda"
      },
      {
        "code": "+685",
        "name": "Samoa"
      },
      {
        "code": "+378",
        "name": "San Marino"
      },
      {
        "code": "+966",
        "name": "Saudi Arabia"
      },
      {
        "code": "+221",
        "name": "Senegal"
      },
      {
        "code": "+381",
        "name": "Serbia"
      },
      {
        "code": "+248",
        "name": "Seychelles"
      },
      {
        "code": "+232",
        "name": "Sierra Leone"
      },
      {
        "code": "+65",
        "name": "Singapore"
      },
      {
        "code": "+421",
        "name": "Slovakia"
      },
      {
        "code": "+386",
        "name": "Slovenia"
      },
      {
        "code": "+677",
        "name": "Solomon Islands"
      },
      {
        "code": "+27",
        "name": "South Africa"
      },
      {
        "code": "+500",
        "name": "South Georgia and the South Sandwich Islands"
      },
      {
        "code": "+82",
        "name": "South Korea"
      },
      {
        "code": "+34",
        "name": "Spain"
      },
      {
        "code": "+94",
        "name": "Sri Lanka"
      },
      {
        "code": "+249",
        "name": "Sudan"
      },
      {
        "code": "+597",
        "name": "Suriname"
      },
      {
        "code": "+268",
        "name": "Swaziland"
      },
      {
        "code": "+46",
        "name": "Sweden"
      },
      {
        "code": "+41",
        "name": "Switzerland"
      },
      {
        "code": "+963",
        "name": "Syria"
      },
      {
        "code": "+886",
        "name": "Taiwan"
      },
      {
        "code": "+992",
        "name": "Tajikistan"
      },
      {
        "code": "+255",
        "name": "Tanzania"
      },
      {
        "code": "+66",
        "name": "Thailand"
      },
      {
        "code": "+670",
        "name": "Timor Leste"
      },
      {
        "code": "+228",
        "name": "Togo"
      },
      {
        "code": "+690",
        "name": "Tokelau"
      },
      {
        "code": "+676",
        "name": "Tonga"
      },
      {
        "code": "+1 868",
        "name": "Trinidad and Tobago"
      },
      {
        "code": "+216",
        "name": "Tunisia"
      },
      {
        "code": "+90",
        "name": "Turkey"
      },
      {
        "code": "+993",
        "name": "Turkmenistan"
      },
      {
        "code": "+1 649",
        "name": "Turks and Caicos Islands"
      },
      {
        "code": "+688",
        "name": "Tuvalu"
      },
      {
        "code": "+1 340",
        "name": "U.S. Virgin Islands"
      },
      {
        "code": "+256",
        "name": "Uganda"
      },
      {
        "code": "+380",
        "name": "Ukraine"
      },
      {
        "code": "+971",
        "name": "United Arab Emirates"
      },
      {
        "code": "+44",
        "name": "United Kingdom"
      },
      {
        "code": "+1",
        "name": "United States"
      },
      {
        "code": "+598",
        "name": "Uruguay"
      },
      {
        "code": "+998",
        "name": "Uzbekistan"
      },
      {
        "code": "+678",
        "name": "Vanuatu"
      },
      {
        "code": "+58",
        "name": "Venezuela"
      },
      {
        "code": "+84",
        "name": "Vietnam"
      },
      {
        "code": "+1 808",
        "name": "Wake Island"
      },
      {
        "code": "+681",
        "name": "Wallis and Futuna"
      },
      {
        "code": "+967",
        "name": "Yemen"
      },
      {
        "code": "+260",
        "name": "Zambia"
      },
      {
        "code": "+255",
        "name": "Zanzibar"
      },
      {
        "code": "+263",
        "name": "Zimbabwe"
      }
    ]

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
    formTitle() {
      return this.editedIndex === -1 ? "Add a new user" : "Edit user";
    },
    userData: function () {
      return this.users;
    },
    starting: function() {
      return (this.page - 1) * this.itemsPerPage + 1;
    },
    ending: function() {
      return Math.min(this.page * this.itemsPerPage + 1, this.userData.length);
    },
    total: function() {
      return this.userData.length;
    },

    showpasswordIcon1() {
      if (this.showPassword1) return "mdi-eye";
      else return "mdi-eye-off";
    },
    showpasswordIcon2() {
      if (this.showPassword2) return "mdi-eye";
      else return "mdi-eye-off";
    },

    LUTimezones: function () {
      // Items have already been loaded
      if (this.timezones.length > 0) return

      // Items have already been requested
      if (this.isLoadingTimezones) return

      // Nothing to look for
      if (this.editedItem.country=="") return

      this.isLoadingTimezones = true

      // Lazily load input items
      fetch('http://netroadshow.incommglobalevents.com/api/timezones.php',{
          method: 'POST',

          // THIS IS IMPORTANT
          headers: new Headers({
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization':true,
          }),

          body: JSON.stringify({
            countryCode : this.editedItem.country
          })
        })
        .then(res => res.json())
        .then(timezones => (this.timezones = timezones))
        .catch(err => {
          console.log(err)
        })
        .finally(() => (this.isLoadingTimezones = false))
      }
  },

  created() {
    this.initialize();
  },

  beforeMount() {
    let clientID = this.$store.state.clientID
/*
    fetch("http://netroadshow.incommglobalevents.com/api/users.php",{
        method: 'POST',

        // THIS IS IMPORTANT
        headers: new Headers({
          'Content-Type': 'applications/json',
          'Accept': 'application/json',
          'Authorization':true
        }),

        body: JSON.stringify({
          clientID : clientID
        })
      })
      .then(result => result.json())
      .then(users => (this.users = users))
      .catch(err => {
        console.log(err)
      });
    */
    fetch("http://netroadshow.incommglobalevents.com/api/companies.php")
      .then(result => result.json())
      .then(companies => (this.companies = companies))
      .catch(err => {
        console.log(err)
      });
      fetch('http://netroadshow.incommglobalevents.com/api/countries.php')
        .then(res => res.json())
        .then(countries => (this.countries = countries))
        .catch(err => {
          console.log(err)
        })
  },




  methods: {
    initialize() {},

    exportSelectedUsers() {

      const forExport = this.selected.map(u => Object.keys(u).reduce((newObj, key) => (
             key != 'sessionID' && key != 'isAdmin'
             && key != 'userPass' && key != 'userGroup'

        ) ? { ...newObj, [key]: u[key]} : newObj, {}));

        console.log(forExport)
        this.csvExport(forExport)

    },

    deleteSelectedUsers() {

      console.log("DELETING SELECTION")

      const _this = this
      const _deleteList = []
      if (this.selected.length>0) {
        confirm("Are you sure you want to delete these items?") &&
        this.selected.forEach(function(entry) {
            const index = _this.userData.indexOf(entry);
              console.log(entry)
            _deleteList.push({userID:entry.userID})
            _this.userData.splice(index, 1);
          })

          if (_deleteList.length>0) {
            console.log("Committing chage to DB");
            console.log(_deleteList)

            axios({
              url: "http://netroadshow.incommglobalevents.com/api/delete_users.php",
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

    resetTimeZones(){
      this.timezones=[]
    },

    onBtNext() {
      this.page = Math.min(
        this.page + 1,
        Math.ceil(this.userData.length / this.itemsPerPage)
      );
    },
    onBtPrevious() {
      this.page = Math.max(this.page - 1, 1);
    },

    editItem(item) {
      this.timezones = []
      this.editedIndex = this.userData.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },

    deleteItem(item) {
      const index = this.userData.indexOf(item);
      confirm("Are you sure you want to delete this item?") &&
        this.userData.splice(index, 1);
    },

    newitem() {
      this.timezones = []
      this.$refs.newEntryForm.reset();
      this.$refs.newEntryForm.resetValidation();
      this.editedItem = Object.assign({}, this.defaultItem);
      this.editedIndex = -1;
      this.showNewItem = true;
      this.valid = false;
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
        this.editedItem.userPass = "";
      } else {
        this.resetTimeZones()
        this.editedIndex = this.userData.indexOf(item);
        this.editedItem = Object.assign({}, item);
        this.editedItem.userPass = "";
      }
      expand(1 - isExpanded);
    },

    userType(item) {
        let ut = this.getByValue(this.userGroups, 'groupValue', item.userGroup)
        return ut.groupName
    },

    getByValue(arr, property, value) {
      for (var i = 0, iLen = arr.length; i < iLen; i++) {
        if (arr[i][property] == value) return arr[i];
      }
    },

    save(form) {
      let result = this.$refs[form].validate();

      console.log(result);

      if (result) {
        this.editedItem.userName = this.editedItem.emailAddress;

        var company = this.getByValue(
          this.companies,
          "companyID",
          this.editedItem.clientID
        );

        console.log(company)

        this.editedItem.companyName = company.companyName;

        let userData = this.editedItem;

        this.$store
          .dispatch("saveUser", { userData })
          .then(result => {
            console.log(result);
            console.log( result.data);
            this.onError = false;
            this.editedItem.userID = result.data.userID;
            if (this.editedIndex > -1) {
              Object.assign(this.userData[this.editedIndex], this.editedItem);
            } else {
              this.userData.push(this.editedItem);
            }
            this.showNewItem = false;
            this.$refs[form].resetValidation();
            this.closeRow()
          })
          .catch(err => {
            console.log(err)
            this.onError = true
            this.errorMessage = err.data.status
          });
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
