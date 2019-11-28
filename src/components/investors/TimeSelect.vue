<template>
  <v-container py-0 :style="cssProps">
    <v-row>
      <v-col cols="7" class="py-0 pr-6">
        <v-row>
          <v-col cols="12">
            <h1 class="investorPC--text">Welcome {{ attendeeData.firstName }}</h1>
          </v-col>
        </v-row>
        <v-row>
          <v-col cols="12">
            <p>{{ eventData.organiser }} invites you to...</p>
            <h2>{{ eventData.eventTitle }}</h2>
          </v-col>
        </v-row>
        <v-row>
          <v-col cols="12">
            <h5 class="investorPC--text">{{ eventData.eventDate | moment("dddd, MMMM Do YYYY")}}</h5>
            <p>
              <strong>Speaker:</strong>
              <span v-for="(speaker, index) in eventData.speakers" :key="index">
                <template>
                  {{ speaker
                  }}<span v-if="index != eventData.speakers.length - 1">,</span>
                </template>
              </span>
            </p>
          </v-col>
        </v-row>
        <v-form ref="timeSelectForm" v-model="valid" @submit.prevent>
        <v-row>
          <v-col cols="12">
            <strong>Please select your 3 preferred time options.</strong> (All
            times are GMT)
          </v-col>
          <v-chip-group
            multiple
            v-model="timeSelection"
            max="3"
            column
            color="white"
            class="timeslot"
            active-class="white--text, green darken-1"
          >
            <v-row class="ma-0 ">
              <v-col
                cols="4"
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
                  style="width:100%; height:40px"
                >
                  {{ timeslot.startTime }} -
                  {{ timeslot.endTime }}
                  <v-spacer></v-spacer>
                  <span v-if="firstChoice === timeslot.conferenceID"
                    ><v-icon class="white--text">mdi-bookmark-check</v-icon
                    >1st</span
                  >
                  <span v-if="secondChoice === timeslot.conferenceID"
                    ><v-icon class="white--text">mdi-bookmark-check</v-icon
                    >2nd</span
                  >
                  <span v-if="thirdChoice === timeslot.conferenceID"
                    ><v-icon class="white--text">mdi-bookmark-check</v-icon
                    >3rd</span
                  >
                  <!--<v-icon class="mdi-18px mdi-flip-v  float-right" color="white" right>mdi-triangle</v-icon>-->
                </v-chip>
              </v-col>
            </v-row>
          </v-chip-group>
          <v-col cols="12" class="text-center">
            <v-btn
              dark
              rounded
              class="investorPC--BG"
              minHeight="40"
              minWidth="150"
              type="submit"
              @click="save('timeSelectForm')"
              >Submit Options
            </v-btn>
          </v-col>
          <v-row class="mt-8 mx-0">
            <v-col cols="12">
              <p>
                Alternatively, if your are unable to attend, please kindly click
                to <strong><a href="#" style="text-decoration: underline">decline invitation</a></strong>
              </p>
            </v-col>
          </v-row>
        </v-row>
      </v-form>
      </v-col>
      <v-col cols="5" class="py-0">
        <v-img
          :src="eventData.eventImage"
          aspect-ratio="0.75"
          max-height="700"
          class="grey lighten-2"
        ></v-img>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
var jwt = require('jsonwebtoken');
import axios from "axios";

export default {
  name: "TimeSelect",
  props: {
    e: {
      type: String,
      required: true
    }

  },
  data: () => ({
    drawerRight: true,
    valid:false,

    timeSelection: [],

    investorFirstname: "John",

    attendeeData: {
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
    search: null,
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
    }
  },
  beforeMount() {
    console.log(this.e);
    let payload = jwt.verify(this.e, "nrs-1234");
    this.selectedEvent = payload.eventID;
    this.selectedAttendee = payload.attendeeID;
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

      let attendeeData = { attendeeID: this.selectedAttendee };
      axios({
        url: "http://netroadshow.incommglobalevents.com/api/getAttendee.php",
        data: { attendeeData },
        method: "POST"
      })
        .then(resp => {
          console.log("Got Attendee details");
          this.attendeeData = Object.assign({}, resp.data);
          console.log(resp);
        })
        .catch(err => {
          console.log("Attendee retrieval error" + err);
        });

  },
  created() {
    this.initialize();
  },

  methods: {
    initialize() {
      console.log(this.e);
    },
    save(form) {
      console.log("SAVING ATTENDEE PREFS")
      let result = this.$refs[form].validate();

      console.log(result);

      if (result) {
        let attendeeData = this.attendeeData;
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

        this.$store
          .dispatch("saveAttendee", { attendeeData })
          .then(result => {
            console.log(result);
            this.thankyou()
          })
          .catch(err => console.log(err));
      }


    },

    thankyou: function() {

      let eventID = this.eventData.eventID
      let attendeeID = this.attendeeData.attendeeID
      var token = jwt.sign({ eventID, attendeeID }, 'nrs-1234');
      console.log(eventID);
      console.log(token);
      let payload = jwt.verify(token, "nrs-1234");
      console.log(payload);

      let thankyou_url = "/thankyou/?e="+token
      window.open(thankyou_url,"_top");

    },

  }
};
</script>

<style>
.investorPC--text {
  color: var(--investor-primary-color) !important;
}

.investorSC--text {
  color: var(--investor-secondary-color) !important;
}

.investorPC--BG {
  background-color: var(%) !important;
}

.investorSC--BG {
  background-color: var(--investor-secondary-color) !important;
}

.timeslot .v-chip__content {
  width: 100% !important;
}
</style>
