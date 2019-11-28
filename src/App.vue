<template>
  <v-app id="app">
    <v-navigation-drawer v-model="drawerRight" app right  height="100%">
      <v-list ma-0 pa-0>
        <v-list-item @click.stop="drawerRight = !drawerRight">
          <v-list-item-content>
            <v-list-item-title>
              <v-icon class="mdi-24px" color="secondary" left
                >mdi-exit-to-app</v-icon
              >
              Close Guide
            </v-list-item-title>
          </v-list-item-content>
        </v-list-item>
        <v-divider></v-divider>

        <v-list-item>
          <v-list-item-title>Admin functions</v-list-item-title>
        </v-list-item>

        <v-list-item to="/events">
          <v-list-item-title>Home</v-list-item-title>
        </v-list-item>
        <v-select
          :items="[
            { groupValue: '1', groupName: 'Incomm Manager' },
            { groupValue: '2', groupName: 'Incomm Agent' },
            { groupValue: '3', groupName: 'Banker' },
            { groupValue: '4', groupName: 'Investor' }
          ]"
          item-text="groupName"
          item-value="groupValue"
          v-model="uGroup"
          v-on:change="updateUserGroup()"
          filled
          dense
          color="secondary"
          hint="Change current User Admin Level"
          persistent-hint
          label="Admin level"
        >
        </v-select>

        <v-divider></v-divider>
        <v-list-item>
          <v-list-item-title>Emulate...</v-list-item-title>
        </v-list-item>
        <v-list-item @click.stop="suggestinvitees()">
          <v-list-item-title>Suggest invitees</v-list-item-title>
        </v-list-item>
        <v-list-item @click.stop="timeselect()">
          <v-list-item-title>Investor Invitation</v-list-item-title>
        </v-list-item>
        <v-list-item @click.stop="thankyou()">
          <v-list-item-title>Investor Thank you</v-list-item-title>
        </v-list-item>
        <v-list-item @click.stop="viewer()">
          <v-list-item-title>Conference Viewer</v-list-item-title>
        </v-list-item>
      </v-list>
    </v-navigation-drawer>

    <v-app-bar
      app
      fixed
      clipped-right
      color="white"
      :height="toolbarheight"
      elevation="0"
      id="mainmasthead"

    >
      <v-layout column class="pa-0 ma-0">
        <div id="masthead" :class="[!isLoggedIn ? 'loggedout': 'loggedin']">
          <v-container pa-0 my-0>
            <v-layout align-center pa-0 ma-0>
              <v-flex pa-6 xs6 text-sm-left>
                <a @click="goHome()">
                  <img v-if="this.eventData.brandLogo && !isLoggedIn" :src="this.eventData.brandLogo" class="incomm_logo"/>
                    <img v-else src="./assets/netroadshow-conferencing.png" class="incomm_logo"/>
                </a>

              </v-flex>
              <v-flex px-6 py-0 xs6 text-right>
                <img
                  v-if="!isLoggedIn"
                  src="./assets/powered-by-incomm.png"
                  class="nrs_logo"
                />

                <v-menu
                  id="companyFilter"
                  v-if="isLoggedIn"
                  :offset-y="true"
                  :disabled="userGroup > 2 ? true : false"
                >
                  <template v-slot:activator="{ on }">
                    <v-layout align-center justify-end  class="float-right" >
                      <img
                        v-on="on"
                        :src="companies[currentCompanyIndex].logo"
                        class="nrs_logo"
                        height="55"
                      />
                      <v-icon
                        v-if="userGroup < 3"
                        small
                        color="secondary"
                        class="ml-4 float-right mdi-rotate-180"
                      >
                        mdi-triangle
                      </v-icon>
                    </v-layout>
                  </template>

                  <v-list class="companyFilter">
                    <v-list-item
                      class="py-3"
                      v-model="currentCompany"
                      v-for="(item, index) in companies"
                      :key="item + '_' + index"
                      @click="setCurrentCompany(item.companyID)"
                    >
                      <img
                        :src="item.logo"
                        :alt="item.companyName"
                        height="55"
                      />
                    </v-list-item>
                  </v-list>
                </v-menu>
              </v-flex>
            </v-layout>
          </v-container>
        </div>

        <div
          class="py-0"
          id="navbar"
          v-if="isLoggedIn && inArray(userGroup, [1, 2, 3])"
        >
          <v-container pa-0 my-0>
            <v-layout pl-6 pr-6 v-if="isLoggedIn" align-center>
              <v-flex xs6 text-sm-left color="white">
                <span class="navbut">
                  <v-btn
                    text
                    dark
                    medium
                    rounded
                    outlined
                    min-height="40"
                    color="#fff"
                    to="/events"
                    >Events</v-btn
                  >
                </span>
                <span class="navbut">
                  <v-btn
                    text
                    dark
                    medium
                    rounded
                    outlined
                    min-height="40"
                    color="#fff"
                    to="/attendees"
                    >Attendees</v-btn
                  >
                </span>
                <span class="navbut" v-if="inArray(userGroup, [1, 2])">
                  <v-btn
                    text
                    dark
                    medium
                    rounded
                    outlined
                    min-height="40"
                    color="#fff"
                    to="/users"
                    >Users</v-btn
                  >
                </span>
                <span class="navbut" v-if="inArray(userGroup, [1, 2])">
                  <v-btn
                    text
                    dark
                    medium
                    rounded
                    outlined
                    min-height="40"
                    color="#fff"
                    to="/companies"
                    >Companies</v-btn
                  >
                </span>
              </v-flex>
              <v-flex xs6 text-sm-right>
                <span class="current-user text-white"
                  ><strong
                    >{{ curUser }} ({{ userGroup }}/{{currentCompany}})</strong
                  >
                  | {{ userCompany }}
                  <v-menu offset-y>
                    <template v-slot:activator="{ on }">
                      <v-btn icon v-on="on" dark>
                        <v-icon>mdi-menu</v-icon>
                      </v-btn>
                    </template>
                    <span>
                      <v-list v-if="isLoggedIn">
                        <v-list-item>
                          <v-list-item-title @click="password"
                            >Reset Password</v-list-item-title
                          >
                        </v-list-item>

                        <v-list-item>
                          <v-list-item-title @click="logout"
                            >Logout</v-list-item-title
                          >
                        </v-list-item>
                      </v-list>
                    </span>
                  </v-menu>
                  <v-icon color="white" @click.stop="drawerRight = !drawerRight"
                    >mdi-help-circle</v-icon
                  >
                </span>
              </v-flex>
            </v-layout>
          </v-container>
        </div>
      </v-layout>
    </v-app-bar>

    <v-content>
      <v-container fluid pa-0 ma-0>
        <router-view :users="users"/>
      </v-container>
    </v-content>

    <v-footer app absolute height="215" clipped-right>
      <v-container pa-0 ma-auto>
        <v-flex text-center xs12 v-if="!isLoggedIn">
          <div class="caption font-weight-light">
            Copyright ©{{ new Date().getFullYear() }} In Communication
            Limited.All rights reserved.
          </div>
        </v-flex>

        <v-layout v-if="isLoggedIn">
          <v-flex text-left sm6>
            <div class="caption font-weight-light">
              Copyright ©{{ new Date().getFullYear() }} In Communication
              Limited.All rights reserved.
            </div>
          </v-flex>
          <v-flex text-right sm6>
            <img src="./assets/powered-by-incomm-ft.png" class="footer_logo" />
          </v-flex>
        </v-layout>
      </v-container>
    </v-footer>
  </v-app>
</template>

<style lang="scss">
@import url("https://fonts.googleapis.com/css?family=Questrial");

$typoOptions: display-4 display-3 display-2 display-1 headline title subtitle-1 subtitle-2 body-1 body-2 caption overline; // all md typography options provided by vuetify

%font-choice {
font-family: 'Questrial', sans-serif !important;
}

@mixin md-typography {
@each $typoOption in $typoOptions {
  .#{$typoOption} {
    @extend %font-choice;
  }
}
}

.v-application { // This is where we'll add all the md-typography classes
  @extend %font-choice;
  @include md-typography;
}

.incomm_logo { height: 66px; width: auto;}
.nrs_logo { height: 66px; width: auto;}
.footer_logo { height: 47px; width: auto;}

#mainmasthead .v-toolbar__content,
#mainmasthead .v-toolbar__extension {
  padding: 0;
  margin: 0;
}
#masthead { position: relative;}
#masthead.loggedout::after { content: '';width: 100%; height: 5px; background: none;  position: absolute; left: 0; bottom: 2px;z-index: 10000;

    background: #eeeeee;
    background: -webkit-gradient(linear, 0 0, 100% 0, from(white), to(white), color-stop(50%, #eeeeee));
  }

.companyFilter  .theme--light.v-list-item--active:hover::before, .theme--light.v-list-item--active::before {
    opacity: 0!important;
}

.theme--light.v-data-table .v-data-table__header-row:hover:not(.v-data-table__expand-row) {
  background: #ffffff;
}
.theme--light.v-data-table tbody tr:hover:not(.v-data-table__expand-row):not(.v-data-table__header-row) {
  background: #1ab5ef26;
}

.theme--light.v-data-table.inactive {
    background-color: #FFFFFF;
    color: rgba(0, 0, 0, 0.33);
}



</style>

<script>
var jwt = require('jsonwebtoken');

export default {

  computed: {
    isLoggedIn: function() {
      if (this.$store.getters.isLoggedIn) {
        this.toolbarheight= "140";
      } else {
        this.toolbarheight= "101";
      }
      return this.$store.getters.isLoggedIn;
    },
    curUser: function() {
      return this.$store.state.userName
    },
    userID: function() {
      return this.$store.state.userID
    },
    currentCompany:  {
      get: function() {
        return this.$store.state.clientID
      },
      set: function(newValue) {
        this.$store.commit("updateClientID",newValue);
      }
    },
    eventData: function() {
      return this.$store.state.editEventData
    },
    currentCompanyIndex: function() {
      return this.getIndexValue(
        this.companies,
        "companyID",
        this.currentCompany)
    },
    userCompany: function() {
      return this.$store.state.companyName
    },
    authStatus: function() {
      return this.$store.getters.authStatus;
    },

    userGroup: {
      get: function() {
        return this.$store.state.userGroup
      },
      set: function(newValue) {
        return this.$store.state.userGroup=newValue
      }
    },
    copy_url: function() {

      let eventID = 16
      let attendeeID = 8
      var token = jwt.sign({ eventID, attendeeID }, 'nrs-1234');
      console.log(eventID)
      console.log(token)
      return "/timeselect/?e="+token
    }
  },
  props: {
    source: String
  },

  data: () => ({

    toolbarheight:101,
    companies: [
      {
        companyID: "",
        companyName: "",
        emailAddress: "",
        language: "",
        dataRetention: "",
        passwordFrequency: "",
        accountStatus: "",
        logo: "",
        primaryColor: "",
        secondaryColor: "",
        LBcustomerID: ""
      }
    ],

    users: null,

    drawerRight: false,
    left: false,

    logo: "",
    toolbarhieght:101,
    errors: false,
    errorMessage: null,
      uGroup:1,
  }),



  beforeMount() {
    this.setCurrentCompany(this.currentCompany);

    fetch("http://netroadshow.incommglobalevents.com/api/companies.php")
      .then(result => result.json())
      .then(companies => (this.companies = companies));
  },

  methods: {
    password: function() {
      this.$store.dispatch("resetrequest").then(() => {
        this.$router.push("/password");
      });
    },

    logout: function() {
      this.$store.dispatch("logout").then(() => {
        this.$router.push("/login");
      });
    },

    suggestinvitees: function() {
      this.$store.dispatch("logout").then(() => {
        this.$router.push("/suggestinvitees");
      });
    },

    timeselect: function() {

      let event = 16
      var token = jwt.sign({ event }, 'nrs-1234');
      console.log(event);
      console.log(token);
      let payload = jwt.verify(token, "nrs-1234");
      console.log(payload);
    //  ?e=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJldmVudCI6MTYsImlhdCI6MTU2ODY1MDEwNH0.Z3Y0VV8X0HRRJQoXmnnCOdOdPx_0oTY-BNQ1TIfvtw8

      this.$store.dispatch("logout").then(() => {
        window.open(this.copy_url, '_blank');
      });
    },

    thankyou: function() {
      this.$store.dispatch("logout").then(() => {
        this.$router.push("/thankyou");
      });
    },

    viewer: function() {
      this.$router.push("/viewer");
    },

    goHome: function() {
      if (this.isLoggedIn)
        this.$router.push("/events");
      else
        this.$router.push("/login");
    },

    setCurrentCompany: function(newVal) {
      console.log(newVal)
      this.currentCompany = newVal;
      let clientID = this.currentCompany
      this.$store
        .dispatch("getUsers", { clientID })
        .then(result => {
          console.log(result);
          console.log( result.data);
          this.users = result.data
          this.$store.commit("updateClientID", newVal);
        })
        .catch(err => {
          console.log(err)
          this.onError = true
          this.errorMessage = err.data.status
        });

    },

    inArray: function(needle, haystack) {
      var length = haystack.length;
      for (var i = 0; i < length; i++) {
        if (haystack[i] == needle) return true;
      }
      return false;
    },
    updateUserGroup() {
      console.log(this.uGroup)
      this.$store.commit('updateUserGroup',this.uGroup)

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
