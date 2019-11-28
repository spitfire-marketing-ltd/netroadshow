<template>
  <v-container py-0 :style="cssProps">
    <v-row>
      <v-col cols="12" class="py-0 pt-6">
        <v-row>
          <v-col cols="12">
            <h1>Dear colleage</h1>

          </v-col>
        </v-row>

          <v-row>
            <v-col cols="12"><p>
              {{ eventData.organiser }} invited you to suggest participants for the
              following event.
            </p>
              <h2>{{ eventData.eventTitle }}</h2>
              <h5 class="investorPC--text">{{ eventData.eventDate |  moment("dddd, MMMM Do YYYY") }}</h5>
              <p>
                <strong>Speaker:</strong>
                <span v-for="(speaker, index) in eventData.speakers" :key="index">
                  <template>
                    {{ speaker}}
                    <span v-if="index != eventData.speakers.length - 1">,</span>
                  </template>
                </span>
              </p>
            </v-col>
          </v-row>
        <v-card outlined class="pa-6">
        <v-form v-model="valid" class="entryForm" ref="inviteeForm">
          <v-row>
            <v-col cols="12">
              <p class="font-weight-bold">
                Please enter your details so we can contact you if required.
              </p>
            </v-col>
          </v-row>
          <v-row id="inviteRevist">
            <v-col cols="4">
              <v-layout column>
                <v-text-field
                  filled
                  v-model="invitee.firstName"
                  label="Firstname"
                  :rules="nameRules"
                  required
                ></v-text-field>
                <v-text-field
                  filled
                  v-model="invitee.lastName"
                  label="Last name"
                  :rules="nameRules"
                  required
                ></v-text-field>
              </v-layout>
            </v-col>
            <v-col cols="4">
              <v-layout column>
                <v-text-field
                  filled
                  v-model="invitee.companyName"
                  label="Company"
                  :rules="nameRules"
                  required
                ></v-text-field>
                <v-text-field
                  filled
                  v-model="invitee.emailAddress"
                  label="Email"
                  :rules="emailRules"
                  required
                ></v-text-field>
              </v-layout>
            </v-col>
            <v-col cols="4">
              <v-layout row>
                <v-flex xs4 sm5 ma-0 px-3>
                  <v-select
                    v-model="invitee.areaCode"
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
                    v-model="invitee.contactNumber"
                    label="Phone"
                    :rules="[v => !!v || 'Item is required']"
                    required
                  ></v-text-field>
                </v-flex>
              </v-layout>
            </v-col>
          </v-row>
        </v-form>
        </v-card>
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="12">
        <h2 class="mb-6">Suggest invitees</h2>
        <!--
        Invitee table
        -->
        <v-layout align-center justify-center pa-0 ma-0>
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
                  @page-count="pageCount = $event"
                >
                  <template v-slot:header="{ props, on, selected }">
                    <thead class="v-data-table-header">
                      <tr>
                        <th
                          v-bind:key="index"
                          v-for="(header, index) in props.headers"
                          :class="[
                            'text-weight-bold investorPC--text column sortable',
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
                            class="v-data-table-header__icon float-right investorSC--text"
                            >arrow_upward
                          </v-icon>
                        </th>
                        <th>
                          <v-checkbox
                            v-model="selected"
                            class="ma-auto pa-0 investorSC--text"
                            hide-details
                            @click.native="toggleAll()"
                          ></v-checkbox>
                        </th>
                      </tr>
                    </thead>
                  </template>

                  <template
                    v-slot:item="{
                      headers,
                      item,
                      isExpanded,
                      isSelected,
                      expand,
                      select
                    }"
                  >
                    <tr>
                      <td class="text-xs-right">
                        <div
                          class="datatable-cell-wrapper"
                          @click="expandrow(item, expand, isExpanded)"
                        >
                          {{ item.firstName }} {{ item.lastName }}
                          <v-icon
                            class="float-right investorSC--text"
                            medium
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
                          <v-chip v-if="item.firstChoiceTIME" dark pill small class="caption investorSC--BG mx-1">{{
                            item.firstChoiceTIME
                          }}</v-chip>
                          <v-chip v-if="item.secondChoiceTIME" dark pill small  class="caption investorSC--BG mx-1" >{{
                            item.secondChoiceTIME
                          }}</v-chip>
                          <v-chip v-if="item.thirdChoiceTIME" dark pill small  class="caption investorSC--BG mx-1">{{
                            item.thirdChoiceTIME
                          }}</v-chip>
                        </div>
                      </td>
                      <td class="text-xs-right">
                        <div class="datatable-cell-wrapper">
                          {{ item.attendeeID }}
                        </div>
                      </td>
                      <td>
                        <v-checkbox
                          class="investorSC--text ma-auto pa-0"
                          hide-details
                          :input-value="isSelected"
                          @change="select(1-isSelected)"
                        ></v-checkbox>
                      </td>
                    </tr>
                  </template>

                  <template
                    v-slot:expanded-item="{
                      item,
                      headers,
                      isSelected,
                      expand
                    }"
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
                                      :rules="nameRules"
                                      required
                                      label="Firstname"
                                    ></v-text-field>
                                    <v-text-field
                                      filled
                                      v-model="editedItem.lastName"
                                      :rules="nameRules"
                                      required
                                      label="Last name"
                                    ></v-text-field>
                                  </v-layout>
                                </v-flex>

                                <v-flex xs12 sm6 md4 px-3>
                                  <v-layout column>
                                    <v-text-field
                                      filled
                                      v-model="editedItem.companyName"
                                      :rules="nameRules"
                                      required
                                      label="Company"
                                    ></v-text-field>
                                    <v-text-field
                                      filled
                                      v-model="editedItem.emailAddress"
                                      :rules="emailRules"
                                      required
                                      label="Email"
                                    ></v-text-field>
                                  </v-layout>
                                </v-flex>

                                <v-flex xs12 sm6 md4 px-3>
                                  <v-layout column>
                                    <v-layout row>
                                      <v-flex xs4 sm5 ma-0 px-3>
                                        <v-select
                                          v-model="editedItem.areaCode"
                                          :rules="[
                                            v => !!v || 'Item is required'
                                          ]"
                                          required
                                          :items="['+44', '+33']"
                                          hint="Country code"
                                          persistent-hint
                                          filled
                                          full-width
                                          label="Area"
                                        >
                                        </v-select>
                                      </v-flex>

                                      <v-flex xs8 sm7 ma-0 px-3>
                                        <v-text-field
                                          filled
                                          v-model="editedItem.contactNumber"
                                          :rules="[
                                            v => !!v || 'Item is required'
                                          ]"
                                          required
                                          label="Phone"
                                        ></v-text-field>
                                      </v-flex>
                                    </v-layout>
                                  </v-layout>
                                </v-flex>
                              </v-layout>

                              <v-row>
                                <v-col cols="12">
                                  <strong
                                    >Please select their 3 preferred time
                                    options.</strong
                                  >
                                  (All times are GMT)
                                </v-col>
                                <v-row class="ma-0 timeslot">
                                  <v-chip-group
                                    multiple
                                    v-model="timeSelection"
                                    max="3"
                                    column
                                    color="white"
                                    class="timeslot"
                                    active-class="white--text, investorSC--BG"
                                  >
                                    <v-col
                                      cols="3"
                                      v-for="timeslot in conferences"
                                      :key="timeslot.conferenceID"
                                    >
                                      <v-chip
                                        :value="timeslot.conferenceID"
                                        label
                                        filter
                                        pill
                                        ripple
                                        filter-icon="mdi-clock-check-outline"
                                        tag="div"
                                        class="ma-3 "
                                        style="width:100%"
                                      >
                                        {{ timeslot.startTime }} -
                                        {{ timeslot.endTime }}
                                        <v-spacer></v-spacer>
                                        <span v-if="firstChoice === timeslot.conferenceID"
                                          ><v-icon class="white--text"
                                            >mdi-bookmark-check</v-icon
                                          >1st</span
                                        >
                                        <span v-if="secondChoice === timeslot.conferenceID"
                                          ><v-icon class="white--text"
                                            >mdi-bookmark-check</v-icon
                                          >2nd</span
                                        >
                                        <span v-if="thirdChoice === timeslot.conferenceID"
                                          ><v-icon class="white--text"
                                            >mdi-bookmark-check</v-icon
                                          >3rd</span
                                        >
                                        <!--<v-icon class="mdi-18px mdi-flip-v  float-right" color="white" right>mdi-triangle</v-icon>-->
                                      </v-chip>
                                    </v-col>
                                  </v-chip-group>
                                </v-row>
                              </v-row>
                            </v-container>
                          </v-card-text>
                          <v-card-actions class="px-10">
                            <v-spacer></v-spacer>
                            <v-btn
                              class="investorSC--text px-10"
                              text
                              @click="closeRow()"
                            >
                              Cancel
                            </v-btn>
                            <v-btn
                              class=" investorSC--text font-weight-bold"
                              text
                              @click="save('entryForm')"
                            >
                              Save
                            </v-btn>
                          </v-card-actions>
                        </v-form>
                      </v-card>
                    </td>
                  </template>
                  <template v-slot:no-data>
                    <v-btn class="investorSC--BG" @click="initialize"
                      >Reset
                    </v-btn>
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
                      <v-icon class="mdi-18px investorSC--text" left
                        >mdi-less-than
                      </v-icon>
                      Previous
                    </v-btn>

                    <v-icon class="mdi-18px investorSC--text"
                      >mdi-power-on
                    </v-icon>

                    <v-btn
                      text
                      small
                      class="ma-0 pa-0 text-none font-weight-bold"
                      v-on:click="onBtNext()"
                      >Next
                      <v-icon class="mdi-18px investorSC--text" right
                        >mdi-greater-than
                      </v-icon>
                    </v-btn>
                  </v-flex>
                  <v-flex sm6 text-sm-right>
                    <span
                      class="d-inline-block mr-3 mt-3"
                      style="max-width: 300px"
                    >
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
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="12">
        <!-- OPTIONS -->
        <v-layout align-center pa-0 ma-0>
          <v-flex pa-6 xs6 text-sm-left>
            <v-btn
              text
              small
              class="investorSC--text ma-0 pa-0 text-none font-weight-bold"
              @click="newitem()"
            >
              <v-icon class="investorSC--text mdi-36px mr-4" left
                >mdi-plus-circle
              </v-icon>
              <span class="investorSC--text">New invitee</span>
            </v-btn>
          </v-flex>
          <v-flex pa-6 xs6 text-sm-right>
            <v-menu offset-y>
              <template v-slot:activator="{ on }">
                <v-btn rounded dark class="mb-2 investorSC--BG" v-on="on"
                  >Options
                </v-btn>
              </template>
              <span>
                <v-list>
                  <v-list-item py-2 @click="importInvestors">
                    <v-list-item-title>Import invitees</v-list-item-title>
                  </v-list-item>

                  <v-list-item py-2 @click="deleteselectedattendees">
                    <v-list-item-title>Delete invitees</v-list-item-title>
                  </v-list-item>
                  <v-divider></v-divider>
                  <v-list-item py-2 @click="newitem()">
                    <v-list-item-title>New invitee</v-list-item-title>
                  </v-list-item>
                </v-list>
              </span>
            </v-menu>
          </v-flex>
        </v-layout>
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="12">
        <!-- NEW INVITEE FORM -->

        <v-card v-show="showNewItem" elevation-0 class="mb-6">
          <v-card-title class="px-10 py-6">
            <span class="headline primary-text">New attendee details</span>
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
                        :rules="nameRules"
                        required
                        label="Firstname"
                      ></v-text-field>
                      <v-text-field
                        filled
                        v-model="editedItem.lastName"
                        :rules="nameRules"
                        required
                        label="Last name"
                      ></v-text-field>
                    </v-layout>
                  </v-flex>

                  <v-flex xs12 sm6 md4 px-3>
                    <v-layout column>
                      <v-text-field
                        filled
                        v-model="editedItem.companyName"
                        :rules="nameRules"
                        required
                        label="Company"
                      ></v-text-field>
                      <v-text-field
                        filled
                        v-model="editedItem.emailAddress"
                        :rules="emailRules"
                        required
                        label="Email"
                      ></v-text-field>
                    </v-layout>
                  </v-flex>

                  <v-flex xs12 sm6 md4 px-3>
                    <v-layout column>
                      <v-layout row>
                        <v-flex xs4 sm5 ma-0 px-3>
                          <v-select
                            v-model="editedItem.areaCode"
                            :rules="[v => !!v || 'Item is required']"
                            required
                            :items="['+44', '+33']"
                            hint="Country code"
                            persistent-hint
                            filled
                            full-width
                            label="Area"
                          >
                          </v-select>
                        </v-flex>

                        <v-flex xs8 sm7 ma-0 px-3>
                          <v-text-field
                            filled
                            v-model="editedItem.contactNumber"
                            :rules="[v => !!v || 'Item is required']"
                            required
                            label="Phone"
                          ></v-text-field>
                        </v-flex>
                      </v-layout>
                    </v-layout>
                  </v-flex>
                </v-layout>

                <v-row>
                  <v-col cols="12">
                    <strong
                      >Please select their 3 preferred time options.</strong
                    >
                    (All times are GMT)
                  </v-col>
                </v-row>
                <v-row class="ma-0 timeslot">
                  <v-chip-group
                    multiple
                    v-model="timeSelection"
                    max="3"
                    column
                    color="white"
                    class="timeslot"
                    active-class="white--text, investorSC--BG"
                  >
                    <v-col
                      cols="3"
                      v-for="timeslot in conferences"
                      :key="timeslot.conferenceID"
                    >
                      <v-chip
                        :value="timeslot.conferenceID"
                        label
                        filter
                        pill
                        ripple
                        filter-icon="mdi-clock-check-outline"
                        tag="div"
                        class="ma-3 "
                        style="width:100%"
                      >
                        {{ timeslot.startTime }} - {{ timeslot.endTime }}
                        <v-spacer></v-spacer>
                        <span v-if="firstChoice === timeslot.conferenceID"
                          ><v-icon class="white--text"
                            >mdi-bookmark-check</v-icon
                          >1st</span
                        >
                        <span v-if="secondChoice === timeslot.conferenceID"
                          ><v-icon class="white--text"
                            >mdi-bookmark-check</v-icon
                          >2nd</span
                        >
                        <span v-if="thirdChoice === timeslot.conferenceID"
                          ><v-icon class="white--text"
                            >mdi-bookmark-check</v-icon
                          >3rd</span
                        >
                        <!--<v-icon class="mdi-18px mdi-flip-v  float-right" color="white" right>mdi-triangle</v-icon>-->
                      </v-chip>
                    </v-col>
                  </v-chip-group>
                </v-row>
              </v-container>
            </v-card-text>
            <v-card-actions class="px-10">
              <v-spacer></v-spacer>
              <v-btn class=" investorSC--text px-10" text @click="close"
                >Cancel
              </v-btn>
              <v-btn
                class="investorSC--text font-weight-bold"
                text
                @click="save('newEntryForm')"
                >Save
              </v-btn>
            </v-card-actions>
          </v-form>
        </v-card>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12" class="text-center mb-10">
        <v-btn
          rounded
          dark
          class=" mx-auto investorSC--BG"
          @click="saveInvitee('inviteeForm')"
          >Submit Suggestions
        </v-btn>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
var jwt = require("jsonwebtoken");
import axios from "axios";

export default {
  name: "InviteRequest",
  props: {
    e: {
      type: String,
      required: true
    }
  },
  data: () => ({
    drawerRight: false,
    all:false,
    valid: false,
    readonly: true,
    loading: false,
    items: [],


    nameRules: [
      v => !!v || "This is required",
      v => (v && v.length <= 50) || "Must be less than 50 characters"
    ],
    emailRules: [
      v => !!v || "E-mail is required",
      v =>
        /^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/.test(v) ||
        "E-mail must be valid"
    ],

    currentEvent: null,
    selectedEvent: "",

    selected: [],
    pagesizes: [10, 25, 75, 100],
    itemsPerPage: 10,
    page: 1,
    pageCount: 0,
    showNewItem: false,
    search: "",
    expanded: [],
    dialog: false,

    headers: [
      {
        text: "Name",
        align: "left",
        sortable: true,
        value: "lastName",
        sortDescending: "desc"
      },
      { text: "Company", value: "company", sortable: true },
      { text: "Email", value: "email", sortable: true },
      { text: "Phone", value: "phone", sortable: true },
      { text: "Time options", sortable: false },
      { text: "attendedID", value: "attendeeID", sortable: false }
    ],

    attendees: [],
    editedIndex: -1,
    invitee: {
      inviteeID: "",
      firstName: "",
      lastName: "",
      companyName: "",
      emailAddress: "",
      areaCode: "",
      contactNumber: ""
    },
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

    timeSelection: [],
    bankerFirstname: "John",

    eventData: {
      eventRef: "",
      eventID: "",
      eventTitle: "",
      eventDate: "",
      eventDateUK: "",
      eventImage: "",
      userID: "",
      companyID: "",
      speakers: [],
      logoOverride: "",
      primaryColor: "",
      secondaryColor: ""
    },


    conferences: [
      {
        conferenceID: "",
        eventID: "",
        eventTimeUK: "",
        startTime: "",
        endTime: "",
        duration: "",
        timeZone: "",
        conferenceLock: "",
        PAC: "",
        HAC: "",
        LBConfID: "",
        maxParticipants: "",
        recordAudio: "",
        expiryDate: "",
        expanded: 0
      }
    ],
    preferences: ["1st Preference", "2nd Preference", "3rd Preference"],

    userPreference: []
  }),

  computed: {
    cssProps() {
      return {
        "--investor-primary-color": this.eventData.preferredPC,
        "--investor-secondary-color": this.eventData.preferredSC
      };
    },
    firstChoice: function() {
      return this.timeSelection[0];
    },
    secondChoice: function() {
      return this.timeSelection[1];
    },
    thirdChoice: function() {
      return this.timeSelection[2];
    },

    starting: function() {
      return (this.page - 1) * this.itemsPerPage + 1;
    },
    ending: function() {
      return Math.min(this.page * this.itemsPerPage + 1, this.attendees.length);
    },
    total: function() {
      return this.attendees.length;
    }
  },

  beforeMount() {
    console.log(this.e);
    let payload = jwt.verify(this.e, "nrs-1234");
    this.selectedEvent = payload.event;
    console.log(payload);
    console.log(this.selectedEvent);
    let eventData = { eventID: this.selectedEvent };

    this.$store
      .dispatch("getEvent", { eventData })
      .then(result => {
        console.log(result);

        console.log("Get Event");
        this.eventData= Object.assign({}, result.data);
        console.log(this.eventData)

        console.log("EventData Retrieved Successfully");

      })

    axios({
      url: "http://netroadshow.incommglobalevents.com/api/getConferences.php",
      data: { eventData },
      method: "POST"
    })
      .then(resp => {
        console.log("Got Conferences");
        this.conferences = Object.assign({}, resp.data);
        console.log(resp);
      })
      .catch(err => {
        console.log("Conference retrieval error" + err);
      });

  //  this.fetchAttendees()
  },

  fetchAttendees() {
    console.log(this.selectedEvent)
    this.attendees = []
    let eventID = this.selectedEvent
    let conferenceID = null
    axios({
      url: "http://netroadshow.incommglobalevents.com/api/attendees.php",
      data: { eventID, conferenceID },
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

  created() {
    this.initialize();
  },

  methods: {
    initialize() {
      console.log(this.e);
    },
    importInvestors() {
      this.$router.push("/importinvestors");
    },

    fetchAttendees(val) {
      fetch(
        "http://netroadshow.incommglobalevents.com/api/attendees.php?event=" +
          val
      )
        .then(result => result.json())
        .then(attendees => (this.attendees = attendees));
    },

    exportSelectedattendees() {},

    deleteselectedattendees() {},


    onBtNext() {
      this.page = Math.min(
        this.page + 1,
        Math.ceil(this.attendees.length / this.itemsPerPage)
      );
    },
    onBtPrevious() {
      this.page = Math.max(this.page - 1, 1);
    },

    editItem(item) {
      this.editedIndex = this.attendees.indexOf(item);
      this.editedItem = Object.assign({}, item);
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
      this.timeSelection = []
      if (isExpanded) {
        this.editedIndex = -1;
        this.editedItem = Object.assign({}, null);
      } else {
        this.editedIndex = this.attendees.indexOf(item);
        this.editedItem = Object.assign({}, item);
        if (this.editedItem.firstChoice>0) this.timeSelection.push(this.editedItem.firstChoice)
        if (this.editedItem.secondChoice>0) this.timeSelection.push(this.editedItem.secondChoice)
        if (this.editedItem.thirdChoice>0) this.timeSelection.push(this.editedItem.thirdChoice)
      }
      expand(1 - isExpanded);
    },

    newitem() {
      this.$refs.newEntryForm.reset();
      this.$refs.newEntryForm.resetValidation();
      this.timeSelection = [];
      this.editedItem = Object.assign({}, this.defaultItem);
      this.editedIndex = -1;
      this.showNewItem = true;
    },

    save(form) {
      let result = this.$refs[form].validate();

      console.log(result);

      if (result) {
        let attendeeData = this.editedItem;
        attendeeData.eventID = this.selectedEvent;
        attendeeData.conferenceID = this.firstChoice;
        attendeeData.firstChoice = this.firstChoice;
        attendeeData.secondChoice = this.secondChoice;
        attendeeData.thirdChoice = this.thirdChoice;
        console.log(this.conferences)
        console.log(attendeeData.firstChoice)
        for (const item in this.conferences) {
          if (this.conferences[item].conferenceID === attendeeData.firstChoice) {
            attendeeData.firstChoiceTIME = this.conferences[item].startTime + " - " + this.conferences[item].endTime
          }
          if (this.conferences[item].conferenceID === attendeeData.secondChoice) {
            attendeeData.secondChoiceTIME =  this.conferences[item].startTime + " - " +  this.conferences[item].endTime
          }
          if (this.conferences[item].conferenceID === attendeeData.thirdChoice) {
            attendeeData.thirdChoiceTIME =  this.conferences[item].startTime + " - " + this.conferences[item].endTime
          }
        }
        console.log(attendeeData)

        if (this.editedIndex > -1) {
          Object.assign(this.attendees[this.editedIndex], this.editedItem);
        } else {
          this.attendees.push(this.editedItem);
        }
        this.showNewItem = false;
        this.$refs.entryForm.resetValidation();
        this.closeRow();

        /*
        this.$store
          .dispatch("saveAttendee", { attendeeData })
          .then(result => {
            console.log(result);

            this.editedItem.userID = result.data;
            if (this.editedIndex > -1) {
              Object.assign(this.attendees[this.editedIndex], this.editedItem);
            } else {
              this.attendees.push(this.editedItem);
            }
            this.showNewItem = false;
            this.$refs.entryForm.resetValidation();
            this.closeRow();
          })
          .catch(err => console.log(err));
          */
      }
    },
    saveInvitee(form) {
      let result = this.$refs[form].validate();

      console.log(result);

      if (result) {
        let inviteeData = this.invitee;
        let attendeeData = this.attendees;

        this.$store
          .dispatch("saveInvitee", { inviteeData,attendeeData })
          .then(result => {
            console.log(result);
            this.showNewItem = false;
          })
          .catch(err => console.log(err));
      } else {
        this.$vuetify.goTo("#inviteRevist", {
          duration: 400,
          offset: 0,
          easing: "easeInCubic"
        });
      }
    },

    inArray: function(needle, haystack) {
      var length = haystack.length;
      for (var i = 0; i < length; i++) {
        if (haystack[i] == needle) return true;
      }
      return false;
    },
    getJsonItem (lookup, obj = this.conferences) {
      for (const item in obj) {
        if (item.conferenceID === lookup) {
          console.log(obj[item])
        } else if (obj[item] instanceof Object) {
          this.getJsonItem(lookup, obj[item])
        }
      }
    },
    getObjectByValue (obj,property, value) {
      return obj.filter(function (entry) {  return entry.conferenceID === 29});
    },
    getByValue(arr, property, value) {
      for (var i = 0, iLen = arr.length; i < iLen; i++) {
        if (arr[i][property] == value) return arr[i];
      }
    },
    getIndexValue(arr, property, value) {
      for (var i = 0, iLen = arr.length; i < iLen; i++) {
        if (arr[i][property] == value) return i;
      }
    }
  }
};
</script>

<style lang="scss">
.investorPC--text {
  color: var(--investor-primary-color) !important;
}

.investorSC--text {
  color: var(--investor-secondary-color) !important;
}

.investorPC--BG {
  background-color: var(--investor-primary-color) !important;
}

.investorSC--BG {
  background-color: var(--investor-secondary-color) !important;
}

.timeslot .v-chip__content {
  width: 100% !important;
}
</style>
