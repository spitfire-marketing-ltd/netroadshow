<template>
  <v-container>
    <v-layout align-center justify-center pa-0 ma-0 pt-3>
      <v-flex sm12 tag="h1" color="primary" text-left>Invite Attendees</v-flex>
    </v-layout>
    <p>
      You can invite attendees individually or by importing them. We have
      provided a CSV template for you to download below - simply complete and
      upload.
    </p>
    <v-flex md4>
      <v-select

        @change="fetchAttendees()"
        max-width="300"
        v-model="selectedEvent"
        label="Select event"
        color="secondary"
        filled
        item-key="eventID"
        item-text="eventTitle"
        item-value="eventID"
        :items="events"
      ></v-select>
    </v-flex>
    <v-layout align-center justify-center pa-0 ma-0 v-if="selectedEvent>0" >
      <v-flex sm12 align-center justify-center row fill-height>
        <v-list-item text-left class="pa-0 ma-0">
          <v-flex sm12>
            <v-data-table
              ref="dTable"
              color="secondary"
              :headers="headers"
              :items="attendees"
              single-expand
              :expanded.sync="expanded"
              :search="search"
              item-key="attendeeID"
              hide-default-footer
              hide-default-header
              loading="false"
              :page.sync="page"
              :items-per-page="itemsPerPage"
              multi-sort
              class="theme-nrs"
              v-model="selected"
              @page-count="pageCount = $event"
            >
              <template v-slot:header="{ props, on, selected }">
                <thead class="v-data-table-header">
                <tr>
                  <th
                    v-bind:key="index"
                    v-for="(header, index) in props.headers"
                    v-if="inArray(userGroup, header.access)"
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

              <template  v-slot:item="{ headers, item, isExpanded, isSelected, expand, select }"
              >
                <tr>
                  <td class="text-xs-right" v-if="inArray(userGroup, headers[0].access)">
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
                  <td class="text-xs-right" v-if="inArray(userGroup, headers[1].access)">
                    <div class="datatable-cell-wrapper">
                      {{ item.companyName }}
                    </div>
                  </td>
                  <td class="text-xs-right" v-if="inArray(userGroup, headers[2].access)">
                    <div class="datatable-cell-wrapper">
                      {{ item.emailAddress }}
                    </div>
                  </td>
                  <td class="text-xs-right" v-if="inArray(userGroup, headers[3].access)">
                    <div class="datatable-cell-wrapper">
                      {{ item.contactNumber }}
                    </div>
                  </td>
                  <td class="text-xs-right" v-if="inArray(userGroup, headers[4].access)">
                    <div class="datatable-cell-wrapper">
                      {{ item.invited }}
                    </div>
                  </td>
                  <td class="text-xs-right"  v-if="inArray(userGroup, headers[5].access)">
                    <div class="datatable-cell-wrapper">
                      <span v-if="item.invitedBy.fullName">{{ item.invitedBy.fullName }}</span>

                      <v-icon v-if="item.invitedBy.fullName"
                        class="mdi-24px float-right"
                        color="secondary"
                        left
                        @click.stop="getInvitedBy(item.invitedBy)"
                        >mdi-open-in-new
                      </v-icon>
                    </div>
                  </td>
                  <td class="text-xs-right" v-if="inArray(userGroup, headers[6].access)">
                    <div class="datatable-cell-wrapper">
                      {{ item.priority }}
                    </div>
                  </td>

                  <td class="text-xs-right" v-if="inArray(userGroup, headers[7].access)">
                    <div class="datatable-cell-wrapper">
                      {{ item.attendeeID }}
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
                v-slot:expanded-item="{ item, headers, isSelected, expand }"
              >
                <td :colspan="headers.length + 1" class="pa-0 ma-0">
                  <v-card elevation-0 class="mb-6">
                    <v-form
                      v-model="valid"
                      class="entryForm"
                      ref="entryForm"
                    >
                      <v-card-text>
                        <v-container>
                          <v-layout wrap>
                            <v-flex xs12 sm6 md4 px-3>
                              <v-layout column>
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
                                  v-model="editedItem.companyName"
                                  label="Company"
                                  :rules="nameRules"
                                  required
                                ></v-text-field>
                              </v-layout>
                            </v-flex>

                            <v-flex xs12 sm6 md4 px-3>
                              <v-layout column>
                                <v-text-field
                                  filled
                                  v-model="editedItem.emailAddress"
                                  label="Email"
                                  :rules="emailRules"
                                  required
                                ></v-text-field>
                                <v-layout row>
                                  <v-flex xs4 sm5 ma-0 px-3>
                                    <v-select
                                      v-model="editedItem.areaCode"
                                      :items="['+44', '+33']"
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

                                  <v-flex xs8 sm7 ma-0 px-3>
                                    <v-text-field
                                      filled
                                      v-model="editedItem.contactNumber"
                                      label="Phone"
                                      :rules="[v => !!v || 'Item is required']"
                                      required
                                    ></v-text-field>
                                  </v-flex>
                                </v-layout>
                              </v-layout>
                            </v-flex>

                            <v-flex xs12 sm6 md4 px-3>
                              <v-layout column>
                                <v-select
                                  :items="['High', 'Medium', 'Low']"
                                  filled
                                  v-model="editedItem.priority"
                                  label="Priority (optional)"
                                  :rules="[v => !!v || 'Item is required']"
                                  required
                                >
                                </v-select>
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
                        >Cancel
                        </v-btn
                        >
                        <v-btn
                          color="blue darken-1"
                          class="font-weight-bold"
                          text
                          @click="save('entryForm')"
                        >Save
                        </v-btn
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
                  >mdi-less-than
                  </v-icon
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
                  >mdi-greater-than
                  </v-icon
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

    <v-layout align-center pa-0 ma-0  v-if="selectedEvent>0">
      <v-flex pa-6 xs6 text-sm-left>
        <v-btn
          text
          small
          class="ma-0 pa-0 text-none font-weight-bold"
          @click="newitem()"
        >
          <v-icon class="mdi-36px" color="secondary" left
          >mdi-plus-circle
          </v-icon
          >
          <span class="secondary-text">New attendee</span>
        </v-btn>
      </v-flex>
      <v-flex pa-6 xs6 text-sm-right>
        <v-menu offset-y>
          <template v-slot:activator="{ on }">
            <v-btn color="secondary" rounded dark class="mb-2" v-on="on"
            >Options
            </v-btn
            >
          </template>
          <span>
            <v-list dense>
              <v-list-item py-2 @click="sendInvitations()">
                <v-list-item-title>Send Invitations</v-list-item-title>
              </v-list-item>
              <v-list-item py-2 @click="sendInvitations()">
                <v-list-item-title>Resend Invitations</v-list-item-title>
              </v-list-item>
              <v-divider class="ma-0 pa-0" ></v-divider>
              <v-list-item py-2 @click="importInvestors()">
                <v-list-item-title>Import invitees</v-list-item-title>
              </v-list-item>

              <v-list-item py-2 @click="exportSelectedAttendees()">
                <v-list-item-title>Export invitees</v-list-item-title>
              </v-list-item>
              <v-list-item py-2 @click="deleteSelectedAttendees()">
                <v-list-item-title>Delete invitees</v-list-item-title>
              </v-list-item>
              <v-divider class="ma-0 pa-0"></v-divider>
              <v-list-item py-2 @click="newitem()">
                <v-list-item-title>New invitee</v-list-item-title>
              </v-list-item>
            </v-list>
          </span>
        </v-menu>
      </v-flex>
    </v-layout>

    <v-card v-show="showNewItem" elevation-0 class="mb-6" >
      <v-card-title class="px-10 py-6">
        <span class="headline primary-text">{{ formTitle }}</span>
      </v-card-title>
      <v-form v-model="valid" class="entryForm" ref="newEntryForm">
        <v-card-text>
          <v-container>
            <v-layout wrap>
              <v-flex xs12 sm6 md4 px-3>
                <v-layout column>
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
                    v-model="editedItem.companyName"
                    label="Company"
                    :rules="nameRules"
                    required
                  ></v-text-field>
                </v-layout>
              </v-flex>

              <v-flex xs12 sm6 md4 px-3>
                <v-layout column>
                  <v-text-field
                    filled
                    v-model="editedItem.emailAddress"
                    label="Email"
                    :rules="emailRules"
                    required
                  ></v-text-field>
                  <v-layout row>
                    <v-flex xs4 sm5 ma-0 px-3>
                      <v-select
                        v-model="editedItem.areaCode"
                        :items="['+44', '+33']"
                        hint="Country code"
                        persistent-hint
                        filled
                        label="Area"
                        :rules="[v => !!v || 'Item is required']"
                        required
                      >
                      </v-select>
                    </v-flex>

                    <v-flex xs8 sm7 ma-0 px-3>
                      <v-text-field
                        filled
                        v-model="editedItem.contactNumber"
                        label="Phone"
                        :rules="[v => !!v || 'Item is required']"
                        required
                      ></v-text-field>
                    </v-flex>
                  </v-layout>
                </v-layout>
              </v-flex>

              <v-flex xs12 sm6 md4 px-3>
                <v-layout column>
                  <v-select
                    :items="['High', 'Medium', 'Low']"
                    filled
                    v-model="editedItem.priority"
                    label="Priority (optional)"
                    :rules="[v => !!v || 'Item is required']"
                    required
                  >
                  </v-select>
                </v-layout>
              </v-flex>
            </v-layout>
          </v-container>
        </v-card-text>

        <v-card-actions class="px-10">
          <v-spacer></v-spacer>
          <v-btn color="blue darken-1" class="px-10" text @click="close()"
          >Cancel
          </v-btn
          >
          <v-btn color="blue darken-1" class="font-weight-bold" text  @click="save('newEntryForm')"
          >Save
          </v-btn
          >
        </v-card-actions>
      </v-form>
    </v-card>

    <v-layout align-stretch justify-space-between my-4  v-if="selectedEvent>0">
      <v-flex sm8 mr-3>
        <v-card color="white" height="100%">
          <v-card-title>
            Request invite suggestions
          </v-card-title>
          <v-card-text>
            <v-btn text small class="mx-0 mb-2 px-2 text-none font-weight-bold" :to="copy_url" target="_blank">
              <v-icon class="mdi-24px" color="secondary" left
              >mdi-open-in-new
              </v-icon
              >
              <span class="secondary-text">Copy link</span></v-btn
            >
            <p>
              Send this page to colleagues to enable them to nominate guests to
              invite to the event. They will be will added to your 'Invite
              attendees' list for you to see before where you can then invite or
              remove them from your list.
            </p>
          </v-card-text>
        </v-card>
      </v-flex>
      <v-flex sm4 ml-3>
        <v-card color="primary" dark height="100%">
          <v-card-title>
            Import template
          </v-card-title>
          <v-card-text>
            <v-btn text small class="mx-0 mb-2 px-2 text-none font-weight-bold" to="/assets/files/csv-sample.csv" target="_blank">
              <v-icon class="mdi-24px" color="secondary" left
              >mdi-cloud-download
              </v-icon
              >
              <span class="secondary-text"
              >Download import template</span
              ></v-btn
            >

            <p class="white--text">
              To ensure you have the right data when importing invitees,
              download our template spreadsheet to make things simple.
            </p>
          </v-card-text>
        </v-card>
      </v-flex>
    </v-layout>
    <v-dialog v-model="showInvitedBy" width="800">
      <v-card color="white" class="text-center">
        <v-toolbar dark color="secondary" elevation="0">
          <v-toolbar-title class="text-center ma-auto"
            >Invited by ...</v-toolbar-title
          >
        </v-toolbar>

        <v-card-text>
          <v-simple-table class="theme-nrs mt-6">
            <thead>
              <tr>
                <th class="text-left">Full Name</th>
                <th class="text-left">Company</th>
                <th class="text-left">Phone</th>
                <th class="text-left">Email</th>
              </tr>
            </thead>
            <tbody>
              <tr >
                <td>{{ invitedBy.fullName }}</td>
                <td>{{ invitedBy.companyName }}</td>
                <td>{{ invitedBy.areaCode }} {{ invitedBy.contactNumber }}</td>
                <td>{{ invitedBy.emailAddress }}</td>
              </tr>
            </tbody>
          </v-simple-table>
        </v-card-text>
        <v-card-actions class="px-10 pb-6 text-center">
          <v-layout justity-center>
            <v-flex>
              <v-btn
                color="blue darken-1"
                class="px-10"
                text
                @click="showInvitedBy = false"
              >
                <v-icon class="mdi-24px" color="secondary" left
                  >mdi-close</v-icon
                >
                Close
              </v-btn>
            </v-flex>
          </v-layout>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<style lang="scss"></style>

<script>

  var jwt = require('jsonwebtoken');

  export default {
    name: "Attendees",
    data: () => ({
      all:false,
      readonly: true,
      loading: false,
      items: [],
      events: null,
      drawerRight: false,
      selectedEvent: null,

      showInvitedBy: false,

      selected: [],
      pagesizes: [10, 25, 75, 100],
      itemsPerPage: 10,
      page: 1,
      pageCount: 0,
      showNewItem: false,
      search: "",
      expanded: [],
      valid: false,

      invitedBy:
        {
          fullName: '',
          companyName: '',
          areaCode: '',
          contactNumber:'',
          emailAddress: ''
        }
      ,

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


      headers: [
        {
          text: "Name",
          align: "left",
          sortable: true,
          value: "fullName",
          sortDescending: "desc",
          access:[1,2,3,4]
        },
        { text: "Company", value: "companyName", sortable: true,
        access:[1,2,3,4] },

        { text: "Email",  value: "emailAddress", sortable: true,
        access:[1,2,3,4] },
        { text: "Phone", value: "contactNumber", sortable: true,
        access:[1,2,3,4] },
        { text: "Invited", value: "invited", sortable: true,
        access:[1,2,3,4] },
        { text: "Invited by", value: "invitedBy", sortable: true,
        access:[1,2,3,4] },
        { text: "Priority", value: "priority", sortable: true,
        access:[1,2,3,4] },
        { text: "attendeeID", value: "attendeeID", sortable: true,
        access:[1,2] }
      ],
      attendees: [],
      editedIndex: -1,
      editedItem: {
        attendeeID: "",
        eventID: "",
        conferenceID: "",
        inviteeID: "",
        firstName: "",
        lastName: "",
        emailAddress: "",
        contactNumber: "",
        areaCode: "",
        invitedBy:
          {
            fullName: '',
            companyName: '',
            areaCode: '',
            contactNumber:'',
            emailAddress: ''
          }
        ,
        companyName: "",
        priority: "",
        dateCreated: "",
        firstChoice: "",
        secondChoice: "",
        thirdChoice: "",
        invited: "",
        accepted: "",
        attended: "",
        allocationStatus:"Unconfirmed"
      },
      defaultItem: {
        attendeeID: "",
        eventID: "",
        conferenceID: "",
        inviteeID: "",
        firstName: "",
        lastName: "",
        emailAddress: "",
        contactNumber: "",
        areaCode: "",
        companyName: "",
        invitedBy:
          {
            fullName: '',
            companyName: '',
            areaCode: '',
            contactNumber:'',
            emailAddress: ''
          }
        ,
        priority: "",
        dateCreated: "",
        firstChoice: "",
        secondChoice: "",
        thirdChoice: "",
        invited: "",
        accepted: "",
        attended: "",
        allocationStatus:"Unconfirmed"
      },
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
        return this.editedIndex === -1
          ? "New attendees details"
          : "Edit attendees";
      },
      starting: function () {
        return (this.page - 1) * this.itemsPerPage + 1;
      },
      ending: function () {
        if (this.attendees!=null) {
          return Math.min(this.page * this.itemsPerPage + 1, this.attendees.length);
        } else {
          return 0
        }
      },
      total: function () {
        if (this.attendees!=null) {
          return this.attendees.length;
        }else {
          return 0
        }
      },
      copy_url: function() {

        let event = this.selectedEvent
        var token = jwt.sign({ event }, 'nrs-1234');
        return "/suggestinvitees/?e="+token
      },
      csvData() {
        return this.attendees.map(item => ({
        ...item,
        }));
      }
    },

    created() {
      this.initialize();
    },

    beforeMount() {
      let token = this.$store.state.token
      fetch("http://netroadshow.incommglobalevents.com/api/eventsList.php", {
          method: 'POST',

          // THIS IS IMPORTANT
          headers: new Headers({
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization':true
          }),

          body: JSON.stringify({
            token : token
          })
        })
        .then(result => result.json())
        .then(events => (this.events = events));
    },



    methods: {
      initialize() {
      },

      importInvestors() {
        this.$router.push("/importinvestors");
      },

      fetchAttendees() {
        console.log(this.selectedEvent)
        this.attendees = []
        let token = this.$store.state.token
        let eventID = this.selectedEvent
        let conferenceID = null
        this.$store.commit("updateCurrentEvent",eventID)
        axios({
          url: "http://netroadshow.incommglobalevents.com/api/attendees.php",
          data: { eventID, conferenceID, token  },
          method: "POST"
        })
          .then(resp => {
            console.log("Got Attendees for "+conferenceID);
            this.attendees =  resp.data;
            console.log(resp);
          })
          .catch(err => {
            console.log("Attendees retrieval error" + err);
          });
      },

      exportSelectedAttendees() {

        const forExport = this.selected.map(u => Object.keys(u).reduce((newObj, key) => (
               key != 'invitedBy' && key != 'allocationStatus'
            && key != 'firstChoice' && key != 'firstChoiceTIME'
            && key != 'secondChoice' && key != 'secondChoiceTIME'
            && key != 'thirdChoice' && key != 'thirdChoiceTIME'
            && key != 'invitationAccepted' && key != 'attended'  && key != 'fullName'

          ) ? { ...newObj, [key]: u[key]} : newObj, {}));

          console.log(forExport)
          this.csvExport(forExport)

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

      sendInvitations(){
        console.log("SENDING INVITIONS SELECTION")
        let token = this.$store.state.token
        const _this = this
        const _selectionList = []
        if (this.selected.length>0) {
          confirm("Are you sure you want to send invitations?") &&
          this.selected.forEach(function(entry) {
              const index = _this.attendees.indexOf(entry);
                console.log(entry)
              _selectionList.push({attendeeID:entry.attendeeID})
              _this.attendees[index].invited='Yes'
            })

            if (_selectionList.length>0) {
              console.log("Committing change to DB");
              console.log(_selectionList)

              axios({
                url: "http://netroadshow.incommglobalevents.com/api/send-invitations.php",
                data: { inviteList: _selectionList, token  },
                method: "POST"
              })
                .then(resp => {
                  console.log("Invitaion Successful");
                  this.selected=[]
                  console.log(resp);
                })
                .catch(err => {
                  console.log("Invitaion error" + err);
                });

            }

          } else {
            alert("Please make a selection first!")
          }
      },

      deleteSelectedAttendees() {

        console.log("DELETING SELECTION")

        const _this = this
        const _deleteList = []
        if (this.selected.length>0) {
          confirm("Are you sure you want to delete these items?") &&
          this.selected.forEach(function(entry) {
              const index = _this.attendees.indexOf(entry);
                console.log(entry)
              _deleteList.push({attendeeID:entry.attendeeID})
              _this.attendees.splice(index, 1);
            })

            if (_deleteList.length>0) {
              console.log("Committing chage to DB");
              console.log(_deleteList)

              axios({
                url: "http://netroadshow.incommglobalevents.com/api/delete_attendees.php",
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
          Math.ceil(this.attendees.length / this.itemsPerPage)
        );
      },
      onBtPrevious() {
        this.page = Math.max(this.page - 1, 1);
      },

      newitem() {
        this.$refs.newEntryForm.reset();
        this.$refs.newEntryForm.resetValidation();
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
        this.showNewItem = true;
      },

      editItem(item) {
        this.editedIndex = this.attendees.indexOf(item);
        this.editedItem = Object.assign({}, item);
        this.dialog = true;
      },

      deleteItem(item) {
        const index = this.attendees.indexOf(item);
        confirm("Are you sure you want to delete this item?") &&
        this.attendees.splice(index, 1);
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
        if (this.selected[item.attendeeID]) this.selected[item.attendeeID] = null;
        else this.selected[item.attendeeID] = item;
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
          this.editedIndex = this.attendees.indexOf(item);
          this.editedItem = Object.assign({}, item);
        }
        expand(1 - isExpanded);
      },

      save(form) {
        let result = this.$refs[form].validate();

        console.log(result);

        console.log("For Event:"+this.selectedEvent)

        if (result) {
          this.editedItem.fullName = this.editedItem.firstName+" "+this.editedItem.lastName
          let attendeeData = this.editedItem;
          if (attendeeData.eventID=='') attendeeData.eventID=this.selectedEvent

          this.$store
            .dispatch("saveAttendee", {attendeeData})
            .then(result => {
              console.log(result);

              this.editedItem.attendeeID = result.data;
              console.log(this.editedItem)
              if (this.editedIndex > -1) {
                Object.assign(this.attendees[this.editedIndex], this.editedItem);
              } else {
                this.attendees.push(this.editedItem);
              }
              this.showNewItem = false;
              this.$refs[form].resetValidation();
              this.closeRow();
            })
            .catch(err => console.log(err));
        }
      },

      getInvitedBy(item) {
        console.log(item.fullName)
        /*
        this.invitedBy.fullName = item.fullName
        this.invitedBy.companyName = item.companyName
        this.invitedBy.areaCode = item.areaCode
        this.invitedBy.contyactNumber = item.contyactNumber
        this.invitedBy.emailAddress = item.emailAddress
        */
        this.invitedBy = Object.assign({}, item);
        console.log(this.invitedBy)
        this.showInvitedBy=true
      },

      inArray: function (needle, haystack) {
        var length = haystack.length;
        for (var i = 0; i < length; i++) {
          if (haystack[i] == needle) return true;
        }
        return false;
      }
    }
  };
</script>
