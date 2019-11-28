import Vue from "vue";
import Vuex from "vuex";
import VuexPersistence from 'vuex-persist'
import axios from "axios";

var jwt = require('jsonwebtoken');

Vue.use(Vuex);

const vuexPersist = new VuexPersistence({
  strictMode: true, // This **MUST** be set to true
  storage: window.sessionStorage,
  //reducer: (state) => ({ dog: state.dog }),
  //filter: (mutation) => mutation.type === 'dogBark'
});

const store = new Vuex.Store({
  strict: true, // This makes the Vuex store strict
  state: {
    status: "",
    reset: localStorage.getItem("reset") || "",
    token: "",
    userGroup: localStorage.getItem("userGroup") || "4",
    userName: localStorage.getItem("username") || "",
    userID: localStorage.getItem("userID") || "",
    companyName: localStorage.getItem("company") || "",
    companyID: localStorage.getItem("companyID") || "",
    clientID: localStorage.getItem("clientID") || "0",
    eventCreated: localStorage.getItem("eventCreated") || false,
    editEventData: localStorage.getItem("editEventData") || [],
    conferenceData: localStorage.getItem("conferenceData") || [],
    currentEvent : localStorage.getItem("currentEvent") || ''
  },
  mutations: {
    updateUserGroup(state, userGroup) {
      state.userGroup = userGroup;
    },
    updateClientID(state, clientID) {
      state.clientID = clientID;
    },
    updateCurrentEvent(state, currentEvent) {
      state.currentEvent = currentEvent;
    },
    reset_request(state) {
      state.reset = true;
    },
    auth_request(state) {
      state.status = "loading";
    },
    auth_success(
      state,
      data
    ) {
      state.status = "success";
      state.reset = false;
      state.token = data.token;
      state.userName = data.firstName+" "+data.lastName;
      state.userGroup = data.userGroup;
      state.companyName = data.companyName;
      state.companyID = data.clientID;
      state.clientID = data.clientID;
      state.userID = data.userID;
      state.eventCreated = false;
      console.log("SAVED STATE");
      console.log(  state.userGroup);
    },
    auth_error(state) {
      state.status = "error";
    },
    alert(state, val) {
      state.alert = val;
    },
    createEvent(state, val) {
      state.eventCreated = val;
      state.editEventData = [];
      state.conferenceData = [];
    },
    hostEvent(state, val) {
      state.editEventData = val;
      console.log("STATE");
      console.log(state.editEventData);
    },
    editEvent(state, val) {
      state.editEventData = val;
      console.log("STATE");
      console.log(state.editEventData);
    },
    stashConferences(state, val) {
      state.conferenceData = val;
      console.log("STATE");
      console.log(state.conferenceData);
    },
    logout(state) {
      state.status = "";
      state.token = "";
      state.userName = "";
      state.companyName = "";
      state.userGroup = "";
      state.reset = "";
      state.currentEvent="";
      state.editEventData = [];
      state.conferenceData = [];
    },
    RESTORE_MUTATION: vuexPersist.RESTORE_MUTATION // this mutation **MUST** be named "RESTORE_MUTATION"
  },
  actions: {
    login({ commit }, user) {
      return new Promise((resolve, reject) => {
        commit("auth_request");
        var token = jwt.sign({ user }, 'nrs-1234');
        axios({
          url: "http://netroadshow.incommglobalevents.com/api/auth.php",
          data: {token: token},
          method: "POST"
        })
          .then(resp => {
            console.log("AUTH:");
            console.log(resp)

            if (resp.data.token =="ACCOUNT SUSPENDED") {
              reject(resp);
            } else if (resp.data.token =="NO USER") {
              reject(resp);
            }  else {
              // Add the following line:
              axios.defaults.headers.common["Authorization"] = token;
              commit(
                "auth_success",
                resp.data
              );
              resolve(resp);
            }

          })
          .catch(err => {
            commit("auth_error");

            reject(err);
          });
      });
    },
    setpassword({ commit }, user) {
      return new Promise((resolve, reject) => {
        //commit("auth_request");
          var token = jwt.sign({ user }, 'nrs-1234');
        axios({
          url: "http://netroadshow.incommglobalevents.com/api/auth.php",
          data: {token: token},
          method: "POST"
        })
          .then(resp => {

            resolve(resp);
          })
          .catch(err => {
            commit("auth_error", err);

            reject(err);
          });
      });
    },
    recover({ commit }, user) {
      return new Promise((resolve, reject) => {
        //commit("auth_request");
          var token = jwt.sign({ user }, 'nrs-1234');
        axios({
          url: "http://netroadshow.incommglobalevents.com/api/recover.php",
          data: {token: token},
          method: "POST"
        })
          .then(resp => {

            resolve(resp);
          })
          .catch(err => {
            commit("auth_error", err);

            reject(err);
          });
      });
    },
    logout({ commit }) {
      return new Promise((resolve, reject) => {
        commit("logout");

        delete axios.defaults.headers.common["Authorization"];
        resolve();
      });
    },
    resetrequest({ commit }) {
      return new Promise((resolve, reject) => {
        commit("reset_request");
        resolve();
      });
    },
    createEvent({ commit }, val) {
      return new Promise((resolve, reject) => {
        commit("createEvent", val);
        resolve();
      })
        .catch(err => {
        commit("auth_error", err);
        console.log("Error creating Event");
        reject(err);
      });;
    },
    getEvent({ commit }, eventData) {
      return new Promise((resolve, reject) => {
        axios({
          url:
            "http://netroadshow.incommglobalevents.com/api/getEvent.php",
          data:  eventData ,
          method: "POST"
        })
          .then(resp => {
            console.log("Got Event");
            let eventData = resp.data
            commit("editEvent", eventData);
            resolve(resp);
          })
          .catch(err => {
            commit("auth_error", err);
            console.log("Event retrieval error");
            reject(err);
          });

      });
    },
    getUsers({ commit }, clientID) {
      return new Promise((resolve, reject) => {
        axios({
          url:
            "http://netroadshow.incommglobalevents.com/api/users.php",
          data:  clientID ,
          method: "POST"
        })
          .then(resp => {
            console.log("Got UserList");            
            //commit("editEvent", eventData);
            resolve(resp);
          })
          .catch(err => {
            commit("auth_error", err);
            console.log("Users retrieval error");
            reject(err);
          });

      });
    },
    editEvent({ commit }, eventData) {
      return new Promise((resolve, reject) => {
        commit("editEvent", eventData);
        axios({
          url:
            "http://netroadshow.incommglobalevents.com/api/getConferences.php",
          data: { eventData: eventData },
          method: "POST"
        })
          .then(resp => {
            console.log("Got Conferences");
            commit("stashConferences", resp.data);
            console.log(resp);
            resolve(resp);
          })
          .catch(err => {
            commit("auth_error", err);
            console.log("Conference retrieval error");
            reject(err);
          });
      });
    },
    saveCompany({ commit }, companyData) {
      return new Promise((resolve, reject) => {
        axios({
          url: "http://netroadshow.incommglobalevents.com/api/saveCompany.php",
          data: companyData,
          method: "POST"
        })
          .then(resp => {
            console.log("Save Succeeded");
            console.log(resp);
            resolve(resp);
          })
          .catch(err => {
            commit("auth_error", err);
            console.log("Save Failed");
            reject(err);
          });
      });
    },
    saveUser({ commit }, userData) {
      return new Promise((resolve, reject) => {
        axios({
          url: "http://netroadshow.incommglobalevents.com/api/saveUser.php",
          data: userData,
          method: "POST"
        })
          .then(resp => {
            if (resp.data.token =="DUPE") {
              reject(resp);
            } else {
              console.log("Save Succeeded");
              resolve(resp);
            }
          })
          .catch(err => {
            commit("auth_error", err);
            console.log("Save Failed");
            reject(err);
          });
      });
    },
    saveEvent({ commit }, eventData) {
      return new Promise((resolve, reject) => {
        axios({
          url: "http://netroadshow.incommglobalevents.com/api/saveEvent.php",
          data: eventData,
          method: "POST"
        })
          .then(resp => {
            console.log("Save Succeeded");
            resolve(resp);
          })
          .catch(err => {
            commit("auth_error", err);
            console.log("Save Failed");
            reject(err);
          });
      });
    },
    bookConferences({ commit }, eventSchedule, eventData) {
      return new Promise((resolve, reject) => {
        axios({
          url:
            "http://netroadshow.incommglobalevents.com/api/saveConferences.php",
          data: { eventSchedule: eventSchedule, eventData: eventData },
          method: "POST"
        })
          .then(resp => {
            console.log("Save Succeeded");
            console.log(resp);
            if (Array.isArray(resp.data)) {
              commit('stashConferences',resp.data)
              resolve(resp);
            } else {
                commit("auth_error", resp);
                console.log("Save Failed");
                reject(resp);
            }

          })
          .catch(err => {
            commit("auth_error", err);
            console.log("Save Failed");
            reject(err);
          });
      });
    },
    saveAttendee({ commit }, attendeeData) {
      return new Promise((resolve, reject) => {
        axios({
          url: "http://netroadshow.incommglobalevents.com/api/saveAttendee.php",
          data: attendeeData,
          method: "POST"
        })
          .then(resp => {
            console.log("Save Succeeded");
            resolve(resp);
          })
          .catch(err => {
            commit("auth_error", err);
            console.log("Save Failed");
            reject(err);
          });
      });
    },
    importCSVData({ commit }, attendeeData, eventData) {
      return new Promise((resolve, reject) => {
        axios({
          url: "http://netroadshow.incommglobalevents.com/api/importCSV.php",
          data: {eventData, attendeeData},
          method: "POST"
        })
          .then(resp => {
            console.log("Import Succeeded");
            resolve(resp);
          })
          .catch(err => {
            commit("auth_error", err);
            console.log("Import Failed");
            reject(err);
          });
      });
    },
    saveInvitee({ commit }, inviteeData, attendeeData) {
      return new Promise((resolve, reject) => {
        axios({
          url: "http://netroadshow.incommglobalevents.com/api/saveInvitee.php",
          data: {inviteeData, attendeeData},
          method: "POST"
        })
          .then(resp => {
            console.log("Save Succeeded");
            resolve(resp);
          })
          .catch(err => {
            commit("auth_error", err);
            console.log("Save Failed");
            reject(err);
          });
      });
    }
  },
  getters: {
    isLoggedIn: state => !!state.token,
    authStatus: state => state.status,
    curUser: state => state.userName,
    userCompany: state => state.companyName,
    userGroup: state => state.userGroup,
    isResetRequest: state => state.reset,
    isEventCreated: state => state.eventCreated,
    editEventData: state => state.editEventData,
    currentEvent: state => state.currentEvent,
  },
  plugins: [vuexPersist.plugin]
});

export default store
