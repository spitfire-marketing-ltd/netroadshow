<template>
  <v-container>
    <v-row>
      <v-col cols="12">
        <h2>{{ eventData.eventTitle }}</h2>
        <p>
          <strong>Speakers:</strong>
          <span v-for="(speaker, index) in eventData.speakers" :key="index">
            <template>
              {{ speaker
              }}<span v-if="index != eventData.speakers.length - 1">,</span>
            </template>
          </span>
        </p>
      </v-col>
    </v-row>

    <v-row>
      <v-col col="4">
        <v-card color="accent" dark height="100%">
          <v-card-title>
            Don't forget!
          </v-card-title>
          <v-card-text>
            <h4>Have you uploaded your presentation slides?</h4>
            <v-btn
              text
              small
              class="ma-0 py-0 text-none font-weight-bold"
              @click="newuser = true"
            >
              <v-icon class="mdi-24px" color="white" left
                >mdi-open-in-new</v-icon
              >
              Upload now
            </v-btn>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col col="4">
        <v-card color="primary" dark height="100%">
          <v-card-title>
            Request invite suggestions
          </v-card-title>
          <v-card-text>
            <p>
              Should you require support during an event, please contact our
              <strong>Customer Service Team</strong> on UK
              <strong>0800 138 2636</strong>, overseas
              <strong>+44 2380 201 275</strong> or email
              <a href="mailto:service@incommglobal.com"
                >service@incommglobal.com</a
              >.
            </p>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col col="4">
        <v-card color="white" height="100%">
          <v-card-title>
            Conference Access
          </v-card-title>
          <v-card-text>
            <v-simple-table dense>
              <tbody>
                <tr>
                  <th>UK Local</th>
                  <td>0209 362 999</td>
                </tr>
                <tr>
                  <th>USA</th>
                  <td>0209 362 999</td>
                </tr>
                <tr>
                  <th>Germany</th>
                  <td>0209 362 999</td>
                </tr>
              </tbody>
            </v-simple-table>

            <v-btn
              text
              small
              class="ma-0 py-0 text-none font-weight-bold"
              @click="newuser = true"
            >
              <v-icon class="mdi-24px" color="secondary" left
                >mdi-open-in-new</v-icon
              >
              View full list of access numbers
            </v-btn>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <v-layout my-6 v-show="conferencesMade" column>
      <v-row class="mx-2">
        <h2>Host Conferences</h2>
      </v-row>
      <template v-for="(conference, index) in conferences">
        <v-viewer-table :conferenceData="conference" :conferences="conferences" :iteration="index"></v-viewer-table>
      </template>
    </v-layout>
    <v-layout align-center pa-0 ma-0>
      <v-flex pb-6 xs12 text-sm-right>
        <v-btn color="secondary" rounded dark class="mb-2"
          >Export Event Report</v-btn
        >
      </v-flex>
    </v-layout>

  </v-container>
</template>

<style lang="scss">
.circle {
  border-radius: 50%;
}

.speakers
  i.v-icon.notranslate.v-chip__close.v-icon--link.v-icon--right.mdi.mdi-close-circle.theme--light {
  position: absolute;
  right: 10px;
}

.v-select.v-text-field input {
  position: absolute;
}
</style>

<script>
var jwt = require('jsonwebtoken');

import ViewerTable  from "./ViewerTable.vue";
export default {
  name: "Viewer",
  components: {
    'v-viewer-table' : ViewerTable
  },
  data: () => ({

    showhelp: false,
    readonly: true,
    loading: false,
    drawerRight: false,
    companyBranding: true,

    items: [],

    selected: [
      {
        eventID: ""
      }
    ],

    eventScheduler: false,
    bookConferences: false,

    eventDatepick: false,

    eventDate: new Date().toISOString().substr(0, 10),

    conferencesMade: true,

    eventSchedule: [],




    headers: [
      {
        text: "Name",
        align: "left",
        sortable: true,
        value: "name",
        sortDescending: "desc"
      },
      { text: "Company", value: "company", sortable: true },
      { text: "Attendee Status", value: "attendeeStatus", sortable: true },
      { text: "PINs", value: "PIN", sortable: true },
      { text: "Audio", value: "audio", sortable: true },
      { text: "Options", value: "options", sortable: false },
      { text: "attendeeID", value: "attendeeID", sortable: false }
    ],
    attendees: [],

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
        expanded:0
      }
    ],
    events: [],

    editedIndex: -1,
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
    defaultItem: {
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

    clientID: function() {
      return this.$store.state.clientID
    },

    backgroundMainColor: function() {
      return "background:" + this.eventData.primaryColor;
    },

    backgroundButtonColor: function() {
      return "background:" + this.eventData.secondaryColor;
    },



    minutes: function() {
      var i;
      var arr = [];
      for (i = 0; i < 60; i += 15) {
        arr.push(i.toString().padStart(2, "0"));
      }
      return arr;
    },

    hours: function() {
      var i;
      var arr = [];
      for (i = 0; i < 24; i++) {
        arr.push(i.toString().padStart(2, "0"));
      }
      return arr;
    },


  },

  created() {
    this.initialize();
  },

  beforeMount() {

  },

  methods: {
    initialize() {
      console.log("Initislaise with");

      let tempEvent = [];
      Object.assign(tempEvent, this.$store.state.editEventData);
      let tempConferences = [];

      console.log("Event Data");
      console.log(tempEvent);

      if (tempEvent.hasOwnProperty("eventID")) {
        Object.assign(this.eventData, tempEvent);

        Object.assign(tempConferences, this.$store.state.conferenceData);
        Object.assign(this.eventSchedule, tempConferences);
        Object.assign(this.conferences, tempConferences);
        //this.setAutoConferenceTimes();

        this.conferences[0].active=true

        console.log(this.eventData);
        console.log("EventData Retrieved Successfully");
      } else {
        Object.assign(this.eventData, this.defaultItem);
        console.log(this.eventData);
        console.log("EventData Initialised Successfully");
      }
    },

    inArray: function(needle, haystack) {
      var length = haystack.length;
      for (var i = 0; i < length; i++) {
        if (haystack[i] == needle) return true;
      }
      return false;
    },


  }
};
</script>
