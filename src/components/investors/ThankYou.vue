<template>
  <v-container py-0 :style="cssProps">
    <v-row>
      <v-col cols="7" class="py-0 pr-6">
        <v-row>
          <v-col cols="12">
            <h1 class="investorPC--text">Thank you {{ attendeeData.firstName }}</h1>
          </v-col>
        </v-row>
        <v-row>
          <v-col cols="12">
            <p>
              We have now received your 3 time preferences and will confirm the
              selected conference time by email for you with full access
              details.
            </p>
            <p>We look forward to speaking with you then.</p>
          </v-col>
        </v-row>
        <v-row>
          <v-col cols="12">
            <strong>Your selected time options.</strong> (All times are GMT)
          </v-col>
          <v-chip-group
            multiple
            v-model="timeSelection"
            max="3"
            column
            color="white"
            class="timeslot"
            active-class="white--text, grey darken-1"
          >
            <v-row class="ma-0">
              <v-col
                cols="4"
                v-for="(timeslot, index) in conferences"
                :key="timeslot.conferenceID"
                v-if="inArray(timeslot.conferenceID,timeSelection)"
              >
                <v-chip
                  :value="timeslot.conferenceID"
                  label
                  filter
                  pill
                  disabled
                  ripple
                  filter-icon="mdi-clock-check-outline"
                  tag="div"
                  class="white--text, grey darken-1"
                  style="width:100%"

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
          <v-row class="mt-12 mx-0">
            <v-col cols="12">
              <p class="small">
                <v-icon class="mr-1">mdi-clock-outline</v-icon>Need to
                <a href="#" class="font-weight-bold" style="text-decoration: underline">change</a> your conference
                time options? We will contact you by phone to confirm a new time
                allocation for you. Alternatively, you can
                <a href="#" class="font-weight-bold" style="text-decoration: underline">cancel</a> your conference
                attendance.
              </p>
            </v-col>
          </v-row>
        </v-row>
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
  name: "ThankYou",
  props: {
    e: {
      type: String,
      required: true
    }

  },
  data: () => ({
    drawerRight: false,

    timeSelection: [],

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
          if (this.attendeeData.firstChoice>0) this.timeSelection.push(this.attendeeData.firstChoice)
          if (this.attendeeData.secondChoice>0) this.timeSelection.push(this.attendeeData.secondChoice)
          if (this.attendeeData.thirdChoice>0) this.timeSelection.push(this.attendeeData.thirdChoice)
          console.log(resp);
        })
        .catch(err => {
          console.log("Attendee retrieval error" + err);
        });

  },
  methods: {
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

<style>
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

.timeslot {
  width: 100% !important;
}
.timeslot .v-chip__content {
  width: 100% !important;
}
.timeslot .v-chip--disabled {
  opacity: 1;
}
</style>
