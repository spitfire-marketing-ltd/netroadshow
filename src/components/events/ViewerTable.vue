<template>
  <v-card
    width="100%"
    elevation="1"
    height="100%"
    rounded
    class="ma-3 pa-0"
  >
  <v-layout align-center justify-space-between>
    <v-flex sm4 pa-3>
      <v-simple-table dense>
        <tbody>
          <tr>
            <td>
              <v-btn class="title font-weight-bold" text @click.stop="toggleConference()">
              <v-icon class="mdi-24px" color="secondary" left v-if="open"
                >mdi-minus-box</v-icon
              >
              <v-icon class="mdi-24px" color="secondary" left v-if="!open"
              >mdi-plus-box</v-icon
              >
                <span class="primary-text text-capitalize" >Conference {{ iteration  + 1}}</span>
              </v-btn>
            </td>
            <td>
              <span class="float-right"
                ><v-btn
                  v-show="conferenceActive"
                  color="green lighten-1"
                  small
                  rounded
                  dark
                  class="mb-2"
                  >Active</v-btn
                ></span>
            </td>
          </tr>
        </tbody>
      </v-simple-table>
    </v-flex>
    <v-flex sm4 px-3>
      <span class="primary-text font-weight-bold body-1"
        >13:00 - 13:45
        <span class="caption font-weight-bold">(GMT)</span></span
      >
    </v-flex>
    <v-flex sm4 px-3 class="text-right">
      <span
        >Manage conference
        <v-menu offset-y offset-x>
          <template v-slot:activator="{ on }">
            <v-btn icon v-on="on">
              <v-icon>mdi-dots-vertical</v-icon>
            </v-btn>
          </template>
          <span>
            <v-list>
              <v-list-item v-if="conferenceActive" @click="moveToNextConference(conference)">
                <v-list-item-title
                  >Move to next conference</v-list-item-title
                >
              </v-list-item>

              <v-list-item>
                <v-list-item-title>Cancel</v-list-item-title>
              </v-list-item>
            </v-list>
          </span>
        </v-menu>
      </span>
    </v-flex>
  </v-layout>

  <v-card-text class="pa-0">
    <div v-if="showConference">
      <v-layout align-top justify-space-between class="grey lighten-4">
      <v-flex sm4 px-3>
        <v-list dense color="grey lighten-4">
          <v-list-item class="text-left px-0"
            ><span class="font-weight-bold pr-2">Conference ID: </span>
            {{ conference.confID }}
          </v-list-item>
        </v-list>
      </v-flex>
      <v-flex sm4 px-3>
        <v-list dense color="grey lighten-4">
          <v-list-item class="text-left px-0"
            ><span class="font-weight-bold pr-2"
              >Host Access Code:
            </span>
            {{ conference.HAC }}
          </v-list-item>
          <v-list-item class="text-left px-0"
            ><span class="font-weight-bold pr-2"
              >Host Conference Web Access
            </span>
            <v-btn
              text
              small
              class="ma-0 py-0 text-none font-weight-bold"
              @click="newuser = true"
            >
              <v-icon class="mdi-24px" color="secondary" left
                >mdi-open-in-new</v-icon
              >
              <span class="secondary-text">Open</span>
            </v-btn>
          </v-list-item>
        </v-list>
      </v-flex>
      <v-flex sm4 px-3>
        <v-list dense color="grey lighten-4">
          <v-list-item class="text-left px-0"
            ><span class="font-weight-bold pr-2"
              >Participant Access Code:
            </span>
            {{ conference.PAC }}
          </v-list-item>
          <v-list-item class="text-left px-0"
            ><span class="font-weight-bold pr-2"
              >Participant Web Access
            </span>
            <v-btn
              text
              small
              class="ma-0 py-0 text-none font-weight-bold"
              @click="newuser = true"
            >
              <v-icon class="mdi-24px" color="secondary" left
                >mdi-open-in-new</v-icon
              >
              <span class="secondary-text">Open</span>
            </v-btn>
          </v-list-item>
        </v-list>
      </v-flex>
      </v-layout>

      <v-layout align-center justify-center pa-0 ma-0>
        <v-flex sm12 align-center justify-center row fill-height>
          <v-list-item text-left class="pa-0 ma-0">
            <v-flex sm12>

              <v-data-table
                ref="dTable"
                color="secondary"
                :headers="headers"
                :items="participants"
                single-expand
                :expanded.sync="expanded"
                item-key="attendeeID"
                hide-default-footer
                hide-default-header
                loading=false
                multi-sort
                :class="[
                  conferenceActive ? 'active': 'inactive',
                  'theme-nrs'
                  ]"
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


                    </tr>
                  </thead>
                </template>

                <template  v-slot:item="{
                    headers,
                    item,
                    isExpanded,
                    isSelected,
                    expand,
                    select
                  }"
                >
                  <tr>
                    <td class="text-xs-right"   v-if="inArray(userGroup, headers[0].access)">
                      <div
                        class="datatable-cell-wrapper"
                        @click="expandrow(item, expand, isExpanded)"
                      >
                        {{ item.label }}

                      </div>
                    </td>
                    <td class="text-xs-right"   v-if="inArray(userGroup, headers[1].access)">
                      <div class="datatable-cell-wrapper">
                        {{ item.company }}
                      </div>
                    </td>
                    <td class="text-xs-right "   v-if="inArray(userGroup, headers[2].access)">
                      <div class="datatable-cell-wrapper">
                        {{item["online-state"]}}
                      </div>
                    </td>
                    <td class="text-xs-right"   v-if="inArray(userGroup, headers[3].access)">
                      <div class="datatable-cell-wrapper">
                        {{ item.pin }}
                      </div>
                    </td>
                    <td class="text-xs-right"   v-if="inArray(userGroup, headers[4].access)">
                      <div class="datatable-cell-wrapper">
                        <span v-if="userGroup<2">{{item["audio-state"]}}</span>

                        <v-icon v-if="item['audio-state']=='NONE'" color="green lighten-1">mdi-phone-off</v-icon>
                        <v-icon v-if="item['audio-state']=='DISCONNECTED'" color="accent">mdi-phone-alert</v-icon>
                        <v-icon v-if="item['audio-state']=='DISABLED'" color="">mdi-phone-cancel</v-icon>
                        <v-icon v-if="item['audio-state']=='DIALING'" color="accent">mdi-phone-incoming</v-icon>
                        <v-icon v-if="item['audio-state']=='BUSY'" color="accent">mdi-pause-circle-outline</v-icon>
                        <v-icon v-if="item['audio-state']=='SALUTATION'" color="accent">mdi-phone-message</v-icon>
                        <v-icon v-if="item['audio-state']=='CONFERENCING'" color="green lighten-1">mdi-voice</v-icon>
                        <v-icon v-if="item['audio-state']=='MUTED'" color="accent">mdi-voice-off</v-icon>
                        <v-icon v-if="item['audio-state']=='PARKED'" color="">mdi-phone-paused</v-icon>
                        <v-icon v-if="item['audio-state']=='HANGUP'" color="accent">mdi-phone-hangup</v-icon>
                        <v-icon v-if="item['audio-state']=='CONNECTION_LOST'" color="accent">mdi-phone-cancel</v-icon>
                        <v-icon v-if="item['audio-state']=='OPERATOR_WAIT'" color="accent">mdi-headphones-settings</v-icon>
                        <v-icon v-if="item['audio-state']=='OPERATOR_CALL'" color="green lighten-1">mdi-headset</v-icon>
                        <v-icon v-if="item['audio-state']=='LATER'" color="accent">mdi-phone-missed</v-icon>
                        <v-icon v-if="item['audio-state']=='FAILED'" color="">mdi-phone-alert</v-icon>
                        <v-icon v-if="item['audio-state']=='UNKNOWN'" color="accent">mdi-phone-alert</v-icon>

                      </div>
                    </td>
                    <td class="text-xs-right"  v-if="inArray(userGroup, headers[5].access)">
                      <div class="datatable-cell-wrapper">
                        <v-menu offset-y>
                          <template v-slot:activator="{ on }">
                            <v-btn icon v-on="on">
                              <v-icon>mdi-dots-vertical</v-icon>
                            </v-btn>
                          </template>
                          <span>
                            <v-list dense>

                              <v-list-item  @click="toggleAudio(item)">
                                <v-list-item-title>Mute/Unmute</v-list-item-title>
                              </v-list-item>
                              <v-list-item  @click="toggleHold(item)">
                                <v-list-item-title>Hold/Unhold</v-list-item-title>
                              </v-list-item>
                              <v-list-item  @click="removeParticipant(item)">
                                <v-list-item-title>Remove participant</v-list-item-title>
                              </v-list-item>
                              <v-divider class="ma-0 pa-0" ></v-divider>
                              <v-list-item >

                                <v-list-item-title>Cancel
                                </v-list-item-title>
                            </v-list-item>
                            </v-list>
                          </span>
                        </v-menu>
                      </div>
                    </td>


                    <td class="text-xs-right"  v-if="inArray(userGroup, headers[6].access)">
                      <div class="datatable-cell-wrapper">
                        {{ item.id }}/{{ item.role }}
                      </div>
                    </td>

                  </tr>
                </template>

              </v-data-table>
            </v-flex>
          </v-list-item>
        </v-flex>
      </v-layout>
      </div>

    </v-card-text>
    <v-card-actions>
      <v-btn @click="pollLB()">TEST</v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
export default {
  name: "ViewerTable",

  props: {
    conferenceData: {
      type: Object,
      required: true,
    },
    conferences: {
      type: Array,
      required: true,
    },

    iteration: {
      type: Number,
      required: true,
    }
  },

    data: () => ({

    showInvitedBy: false,
    readonly:false, // included to prevent autofil
    conferenceActive: false,
    interval: null,

    all: false,
    valid: false,

    open:false,
    lock:false,
    start: null,
    end: null,

    timeStart: null,
    menuStart: false,
    timeEnd: null,
    menuEnd: false,

    startTime: "",
    endTime: "",

    time: null,
    menu2: false,
    modalStart: false,
    modalEnd: false,

    editmodalEnd: false,
    editmodalStart: false,
    editScheduleIndex: -1,
    editSchedule: [
      {
        startTime: "",
        endTime: "",
        duration: ""
      }
    ],

    pagesizes: [10, 25, 75, 100],
    itemsPerPage: 10,
    page: 1,
    pageCount: 0,
    search: "",

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

    attendees:[],
    participants:[],
    expanded: [],
    selected: [],

    eventSchedule: {
      startTime: "",
      endTime: "",
      duration: ""
    },

    headers: [
      {
        text: "Name",
        align: "left",
        sortable: true,
        value: "name",
        sortDescending: "desc",
        access:[1,2,3,4]
      },
      { text: "Company", value: "companyName", sortable: true,  access:[1,2,3,4] },
      { text: "Attendee Status", value: "attendeeStatus", sortable: true,  access:[1,2,3,4] },
      { text: "PINs", value: "PIN", sortable: true,  access:[1,2,3,4] },
      { text: "Audio", value: "audio", sortable: true,  access:[1,2,3,4] },
      { text: "Options", value: "options", sortable: false,  access:[1,2,3,4] },
      { text: "attendeeID", value: "attendeeID", sortable: false,  access:[1,2] }
    ],


    invitedBy:
      {
        fullName: '',
        companyName: '',
        areaCode: '',
        contactNumber:'',
        emailAddress: ''
      }
    ,

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
      companyName: "",
      priority: "",
      dateCreated: "",
      "1stChoiceConfID": "",
      "2ndChoiceConfID": "",
      "3rdChoiceConfID": "",
      invitationAccepted: ""
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
      "1stChoiceConfID": "",
      "2ndChoiceConfID": "",
      "3rdChoiceConfID": "",
      invitationAccepted: ""
    },

  }),

  computed: {

    showConference: function (){
      if (this.conferenceActive) {this.open = true; return true }
      else return this.open
    },
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
    conference: function () {
      return this.conferenceData
    },
    /*
    conferenceActive: {
      get: function() {
        return this.conferenceData.active
      },
      set: function(newValue) {
        return newValue
      }
    },
    */
    starting: function() {
      return (this.page - 1) * this.itemsPerPage + 1;
    },
    ending: function() {
      return Math.min(this.page * this.itemsPerPage + 1, this.attendees.length);
    },
    total: function() {
      return this.attendees.length;
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

  beforeMount() {
    let index = this.conferences.indexOf(this.conference);
    if (index===0)
      this.conferenceActive = true
  },

  mounted() {
    this.$root.$on('updateAttendeeList', data => {

        // detect change - if destination is us then we add to our attendee List
        if (data.action=='MOVE' && data.CID==this.conferenceData.conferenceID ) {
            console.log("ALERT MOVE " + data.CID)
            console.log(data);
            this.attendees.push(data.attendee)
            // commit cahneg to DB
        }
        if (data.action=="UPDATE" && data.CID==this.conferenceData.conferenceID) {
            console.log("ALERT LIST UPDATE "+data.CID)
            console.log(data);
            this.attendees= data.attendeeList
            // commit cahneg to DB
        }


    });

    this.$root.$on('updateConference', data => {

        // detect change - if destination is us then we add to our attendee List

        if (data.action=="UPDATE" && data.CID==this.conferenceData.conferenceID) {
            console.log("ALERT CONFERENCE UPDATE "+data.CID)
            console.log(data);
            this.conference= data.conference
            // commit cahneg to DB
        }
        if (data.action=="ACTIVATE" && data.CID==this.conferenceData.conferenceID) {
            console.log("ALERT ACTIVATE "+data.CID)
            console.log(data);
            this.conferenceActive = true
            // commit cahneg to DB
        }

    });

    this.polling();

  },

  beforeDestroy () {

         clearInterval(this.interval)
         clearTimeout(this.interval)
      },

  methods: {

    polling: function(){
      this.interval = setTimeout( () => {

           if (this.conferenceActive && this.conferenceData.confID>0) {
              this.pollLB()
           } else {
              //clearInterval(this.interval)
           }
         }, 1000 )
      //this.interval = setInterval(this.pollLB(), 2000);
    },

  pollLB: function () {
    console.log("Polling");
    if (!this.conferenceActive) { return;}

    console.log("Polling permitted");
    let conferenceData = this.conferenceData;
    conferenceData.userID = this.userID;
    console.log(conferenceData)
    axios({
      url: "http://netroadshow.incommglobalevents.com/api/updateViewer.php",
      data: conferenceData,
      method: "POST"
    })
      .then(resp => {
        console.log("Poll Sucessful");
        this.participants=resp.data.participants
        console.log(this.participants);
        this.polling()
      })
      .catch(err => {
        console.log("Poll error" + err);
      });
   },

   toggleAudio: function (item) {
     console.log("Altering Audio State");
     if (!this.conferenceActive) { return;}


     let conferenceData = this.conferenceData;
     conferenceData.userID = this.userID;

     let participant = item;
     if ( participant["audio-state"]=="CONFERENCING") {participant["audio-state"] = "MUTED";}
     else {participant["audio-state"] ="CONFERENCING";}


     console.log(conferenceData)
     axios({
       url: "http://netroadshow.incommglobalevents.com/api/updateParticipant.php",
       data: {conferenceData, participant},
       method: "POST"
     })
       .then(resp => {
         console.log("Altering Audio State Sucessful");
         this.participants=resp.data.participants
         console.log(this.participants);

       })
       .catch(err => {
         console.log("Altering Audio State error" + err);
       });
    },

    toggleHold: function (item) {
      console.log("Altering Hold State");
      if (!this.conferenceActive) { return;}


      let conferenceData = this.conferenceData;
      conferenceData.userID = this.userID;

      let participant = item;
      if ( participant["audio-state"]!="PARKED") {participant["online-state"] = "PARKED";}
      else {participant["audio-state"] ="CONNECTED";}


      console.log(conferenceData)
      axios({
        url: "http://netroadshow.incommglobalevents.com/api/updateParticipant.php",
        data: {conferenceData, participant},
        method: "POST"
      })
        .then(resp => {
          console.log("Altering Audio State Sucessful");
          this.participants=resp.data.participants
          console.log(this.participants);

        })
        .catch(err => {
          console.log("Altering Hold State error" + err);
        });
     },

     removeParticipant: function (item) {
       console.log("Altering Hold State");
       if (!this.conferenceActive) { return;}


       let conferenceData = this.conferenceData;
       conferenceData.userID = this.userID;

       let participant = item;
       if ( participant["online-state"]=="CONNECTED") { participant["collaboration-state"] = "DISCONNECTED";}
       else {participant["online-state"] ="CONNECTED";}


       console.log(conferenceData)
       axios({
         url: "http://netroadshow.incommglobalevents.com/api/updateParticipant.php",
         data: {conferenceData, participant},
         method: "POST"
       })
         .then(resp => {
           console.log("Altering Audio State Sucessful");
           this.participants=resp.data.participants
           console.log(this.participants);

         })
         .catch(err => {
           console.log("Altering Hold State error" + err);
         });
      },

    moveToNextConference(item) {
      console.log(item)
      this.conferenceActive= false
      console.log(this.conferences)
      let index = this.conferences.indexOf(item);
        console.log(this.conferences.length)
      //  this.conferenceData.active=false
        if (index < this.conferences.length-1) {
          let activateID = this.conferences[index+1].conferenceID
          this.$root.$emit('updateConference',{action: 'ACTIVATE', CID:activateID })
        }
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

    approveSelected() {
      console.log("CONFIRM SELECTION")

      const _this = this
      const _processList = []
      if (this.selected.length>0) {
        confirm("Are you sure you want to send approve items?") &&
        this.selected.forEach(function(entry) {
            const index = _this.attendees.indexOf(entry);
              console.log(entry)
            _processList.push({attendeeID:entry.attendeeID})
            _this.changeStatus(entry,'Confirmed')
          })
        } else {
          alert("Please make a selection first!")
        }
        this.selected=[]
    },

    sendConfirmations() {
      console.log("CONFIRM SELECTION")

      const _this = this
      const _processList = []
      if (this.selected.length>0) {
        confirm("Are you sure you want to send confirmations for these items?") &&
        this.selected.forEach(function(entry) {
            const index = _this.attendees.indexOf(entry);
              console.log(entry)
            _processList.push({attendeeID:entry.attendeeID})
            _this.changeStatus(entry,'Confirmation sent')
          })
        } else {
          alert("Please make a selection first!")
        }
        this.selected=[]
    },

    sendUpdates() {
      console.log("update SELECTION")

      const _this = this
      const _processList = []
      if (this.selected.length>0) {
        confirm("Are you sure you want to send updates for these items?") &&
        this.selected.forEach(function(entry) {
            const index = _this.attendees.indexOf(entry);
              console.log(entry)
            _processList.push({attendeeID:entry.attendeeID})
            _this.changeStatus(entry,'Confirmation sent')
          })
        } else {
          alert("Please make a selection first!")
        }
        this.selected=[]
    },

    unconfirmAllocation() {
      console.log("UNCONFIRM SELECTION")

      const _this = this
      const _processList = []
      if (this.selected.length>0) {
        confirm("Are you sure you want to unconfirm these items?") &&
        this.selected.forEach(function(entry) {
            const index = _this.attendees.indexOf(entry);
              console.log(entry)
            _processList.push({attendeeID:entry.attendeeID})
            _this.changeStatus(entry,'Unconfirmed')
          })
        } else {
          alert("Please make a selection first!")
        }
        this.selected=[]
    },

    moveToHoldingList() {
      console.log("MOVE SELECTION")

      const _this = this
      const _processList = []
      if (this.selected.length>0) {
        confirm("Are you sure you want to move these items?") &&
        this.selected.forEach(function(entry) {
            const index = _this.attendees.indexOf(entry);
              console.log(entry)
            _processList.push({attendeeID:entry.attendeeID})
            _this.attendees.splice(index, 1);
            _this.moveToConference(entry,-1)
          })
        } else {
          alert("Please make a selection first!")
        }
        this.selected=[]
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
          this.selected=[]
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


    bookConference(form, attendees=true) {
      let result = this.$refs[form].validate();

      console.log(result);

      if (result) {
        let eventSchedule = this.eventSchedule;
        let eventData = this.eventData;

        this.$store
          .dispatch("bookConferences", { eventSchedule, eventData })
          .then(result => {
            console.log(result);
            this.eventSchedule = result.data;
            this.$refs[form].resetValidation();
            if (attendees)
              this.$router.push("/attendees");
          })
          .catch(err => console.log(err));
      }
    },

    closeRow() {
      this.$set(this.$refs.dTable.expanded, 0, false);
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

    editItem(item) {
      this.editedIndex = this.events.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },

    deleteItem(item) {
      const index = this.events.indexOf(item);
      confirm("Are you sure you want to delete this item?") &&
        this.events.splice(index, 1);
    },

    close() {
      this.dialog = false;
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      }, 300);
    },

    toggleConference(){
      console.log(this.open)
      this.open=1-this.open
    },


    toggleAll() {
      console.log(this.$refs.dTable)
      this.all = 1-this.all
      this.$refs.dTable.toggleSelectAll(this.all)
      console.log(this.$refs.dTable.$data.selection)

    },

    showSelected(select) {
      select
      console.log(this.selection)
    },

    changeSort(column, on) {
      this.headers[column].sortDescending = !this.headers[column]
        .sortDescending;

      on.sort(this.headers[column].value);
    },

    expandrow(item, expand, isExpanded) {
      console.log(isExpanded);
      console.log(this.$refs)
      if (isExpanded) {
        this.editedIndex = -1;
        this.editedItem = Object.assign({}, null);
      } else {
        this.editedIndex = this.attendees.indexOf(item);
        this.editedItem = Object.assign({}, item);
      }
      expand(1 - isExpanded);
    },

    editStartTime() {
      this.editSchedule = Object.assign({}, {startTime: this.conference.startTime, endTime:this.conference.endTime, duration: this.conference.duration});
      this.editmodalStart = true;
    },

    saveStartTime(newval) {

        this.eventSchedule.startTime = newval;
        var x = this.$moment(
          "12-25-1995 " + this.eventSchedule.startTime,
          "MM-DD-YYYY HH:mm"
        );
        var y = this.$moment(
          "12-25-1995 " + this.eventSchedule.endTime,
          "MM-DD-YYYY HH:mm"
        );
        var duration = this.$moment.duration(y.diff(x));
        this.eventSchedule.duration = duration;

        this.conference.startTime = this.eventSchedule.startTime

        this.$root.$emit('updateConfernce',{action: 'UPDATE', conference:this.conference,CID:this.conferenceData.conferenceID })

      this.editmodalStart = false;
      this.editSchedule.startTime = "";
    },

    editEndTime() {

      this.editSchedule = Object.assign({},{startTime: this.conference.startTime, endTime:this.conference.endTime, duration: this.conference.duration});
      this.editmodalEnd = true;
    },

    saveEndTime(newval) {

        this.eventSchedule.endTime = newval;
        var x = this.$moment(
          "12-25-1995 " + this.eventSchedule.startTime,
          "MM-DD-YYYY HH:mm"
        );
        var y = this.$moment(
          "12-25-1995 " + this.eventSchedule.endTime,
          "MM-DD-YYYY HH:mm"
        );
        var duration = this.$moment.duration(y.diff(x));
        this.eventSchedule.duration = duration;

        this.conference.endTime = this.eventSchedule.endTime

        this.$root.$emit('updateConfernce',{action: 'UPDATE', conference:this.conference,CID:this.conferenceData.conferenceID })


      this.editmodalEnd = false;
      this.editSchedule.endTime = "";
      this.editScheduleIndex = -1;
    },

    save(form) {
      let result = this.$refs[form].validate();

      console.log(result);

      console.log("For Event:"+this.conferenceData.eventID)

      if (result) {
        let attendeeData = this.editedItem;
        if (attendeeData.eventID=='') attendeeData.eventID=this.conferenceData.eventID

        attendeeData.fullName = attendeeData.firstName+" "+attendeeData.lastName

        this.$store
          .dispatch("saveAttendee", {attendeeData})
          .then(result => {
            console.log(result);

            this.editedItem.attendeeID = result.data;
            if (this.editedIndex > -1) {
              Object.assign(this.attendees[this.editedIndex], this.editedItem);

            } else {
              this.attendees.push(this.editedItem);
            }
            this.$root.$emit('updateAttendeeList',{action: 'UPDATE', attendeeList:this.attendees,CID:this.conferenceData.conferenceID })
            this.showNewItem = false;
            this.$refs[form].resetValidation();
            this.closeRow();
          })
          .catch(err => console.log(err));
      }
    },

    moveToConference(attendee,destinationID) {
      const index = this.attendees.indexOf(attendee);
      console.log(index);
      console.log(attendee);
      console.log(destinationID);
      if (destinationID!=this.conferenceData.conferenceID) {
        this.attendees.splice(index, 1); // remove from this list (as has moved)
        //update item recode
        attendee.conferenceID = destinationID
        //this.$emit('updateAttendeeList',index, attendeeID,destinationID)
        this.DBsaveAttendee(attendee)
        this.$root.$emit('updateAttendeeList',{action: 'MOVE', attendee:attendee, CID:destinationID})
      }
    },

    changeStatus(attendee,status) {
      const index = this.attendees.indexOf(attendee);
      console.log(index);
      console.log(attendee);
      //update item recode
        attendee.allocationStatus = status
        //this.$emit('updateAttendeeList',index, attendeeID,destinationID)
        this.DBsaveAttendee(attendee)
        this.$root.$emit('updateAttendeeList',{action: 'UPDATE', attendeeList:this.attendees,CID:this.conferenceData.conferenceID })

    },

    DBsaveAttendee(item){

        let attendeeData = item;

        this.$store
          .dispatch("saveAttendee", {attendeeData})
          .then(result => {
            console.log(result);
          })
          .catch(err => console.log(err));

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

    getIndexValue(arr, property, value) {
      for (var i = 0, iLen = arr.length; i < iLen; i++) {
        if (arr[i][property] == value) return i;
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
}
</script>

<style scoped>

</style>
