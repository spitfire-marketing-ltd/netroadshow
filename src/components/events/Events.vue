<template>
  <v-container>
    <v-layout align-center justify-center pa-0 ma-0 pt-3>
      <v-flex sm6 tag="h1" color="primary" text-left
        >Welcome {{ curUser }}</v-flex
      >

      <v-flex sm6 class="text-right ">
        <v-layout
          align-center
          row
          justify-end
          mr-1
          v-if="inArray(userGroup, [3, 4])"
        >
          <span class="d-flex mr-3">
            <label>Filter my events only</label>
          </span>
          <span class="d-flex">
            <v-switch color="secondary" v-model="allevents"></v-switch>
          </span>
        </v-layout>
      </v-flex>
    </v-layout>

    <p>
      Here is your events dashboard. Each event will contain all of your
      conferences within that day. You can create as many conferences within a
      day event as you like, there is no minimum or maximum number.
    </p>

    <v-layout align-center justify-center pa-0 ma-0>
      <v-flex d-block sm12 align-center justify-center row fill-height>
        <v-list-item text-left class="pa-0 ma-0">
          <v-flex sm12>
            <v-data-table
              ref="dTable"
              v-model="selected"
              color="secondary"
              :headers="headers"
              :items="events"
              single-expand
              :expanded.sync="expanded"
              :search="search"
              item-key="eventID"
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
                        'text-weight-bold primary-text column ',
                        header.sortable ? 'sortable' : '',
                        header.sortDescending ? 'desc' : 'asc',
                        inArray(header.value, props.options.sortBy)
                          ? 'active'
                          : '',
                        inArray(userGroup, header.group) ? '' : 'd-none'
                      ]"
                      @click="changeSort(index, on)"
                    >
                      {{ header.text }}

                      <v-icon
                        v-if="header.sortable"
                        small
                        class="v-data-table-header__icon float-right"
                        color="secondary "
                        >arrow_upward</v-icon
                      >
                    </th>
                    <th>
                      <v-checkbox
                        v-model="selected"
                        color="secondary"
                        hide-details
                        @click.native="toggleAll()"
                      ></v-checkbox>
                    </th>
                  </tr>
                </thead>
              </template>

              <template
                v-slot:item="{
                  item,
                  isExpanded,
                  headers,
                  isSelected,
                  expand,
                  select
                }"
              >
                <tr>
                  <td
                    v-if="inArray(userGroup, headers[0].group)"
                    class="text-xs-right"
                  >
                    <div
                      class="datatable-cell-wrapper"
                      @click="expandrow(item, expand, isExpanded)"
                    >
                      {{ item.eventRef }}

                      <v-icon
                        class="float-right"
                        medium
                        color="secondary"
                        @change="expandrow(item, expand, isExpanded)"
                      >
                        mdi-chevron-down
                      </v-icon>
                      <v-chip v-if="(item.total>0)" color="accent" small class="float-right">{{ item.total }}</v-chip>
                      <v-chip v-if="(item.total==0)"class="float-right" color="accent" small>{{item.invited}}</v-chip>
                    </div>
                  </td>
                  <td
                    v-if="inArray(userGroup, headers[1].group)"
                    class="text-xs-right"
                  >
                    <div class="datatable-cell-wrapper">
                      {{ item.eventTitle }}
                    </div>
                  </td>
                  <td
                    v-if="inArray(userGroup, headers[2].group)"
                    class="text-xs-right"
                  >
                    <div class="datatable-cell-wrapper">
                      {{ item.eventDate | moment("DD/MM/YYYY")}}
                    </div>
                  </td>
                  <td
                    v-if="inArray(userGroup, headers[3].group)"
                    class="text-xs-right"
                  >
                    <div class="datatable-cell-wrapper">{{ item.userFullName }} [{{ item.userID }}]</div>
                  </td>
                  <td
                    v-if="inArray(userGroup, headers[4].group)"
                    class="text-xs-right"
                  >
                    <div class="datatable-cell-wrapper">
                      {{ item.companyName }}
                    </div>
                  </td>

                  <td
                    v-if="inArray(userGroup, headers[5].group)"
                    class="text-xs-right "
                  >
                    <div class="datatable-cell-wrapper">
                      <v-icon @click="gotoHost(item)" class="mdi-24px" color="secondary" left
                        >{{reportHostIcon(item)}} </v-icon
                      >

                    </v-btn>
                    </div>
                  </td>
                  <td
                    v-if="inArray(userGroup, headers[6].group)"
                    class="text-xs-right"
                  >
                    <div class="datatable-cell-wrapper">
                      <v-icon medium color="secondary" @click="editEvent(item)">
                        edit
                      </v-icon>
                    </div>
                  </td>
                  <td
                    v-if="inArray(userGroup, headers[7].group)"
                    class="text-xs-right"
                  >
                    <div class="datatable-cell-wrapper">{{ item.eventID }}</div>
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
                v-slot:item.data-table-expand="{ item, isExpanded, expand }"
              >
                <v-icon
                  small
                  ref="expand"
                  @click="expandrow(item, expand, isExpanded)"
                >
                  edit
                </v-icon>
              </template>

              <template v-slot:expanded-item="{ item, index, headers }">
                <td :colspan="headers.length + 1" class="pa-0 ma-0">
                  <v-card flat class="grey lighten-4">
                    <v-card-text>
                      <v-simple-table class="stripped">
                        <thead>
                          <tr class="v-data-table__header-row">
                            <th class="text-left primary-text">Conferences</th>
                            <th class="text-left primary-text">Invited </th>
                            <th class="text-left primary-text">Responded</th>
                            <th class="text-left primary-text">Attended</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr
                            v-for="conference in conferences"
                            :key="conference.timeslot"
                          >
                            <td>{{ conference.startTime }} - {{ conference.endTime }} <v-chip class="float-right" color="accent" small>{{conference.pending}}</v-chip></td>
                            <td>{{ conference.invited }}</td>
                            <td>{{ conference.responded }}</td>
                            <td>{{ conference.attended }}</td>
                          </tr>
                        </tbody>
                      </v-simple-table>
                    </v-card-text>

                    <v-card-actions>
                      <v-spacer></v-spacer>
                      <v-btn color="blue darken-1" text @click="close()"
                        >Cancel</v-btn
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
                    hide-details
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
                  ><v-icon class="mdi-18px" color="secondary" left
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
                <div class="test-header">
                  Showing
                  <span id="starting">{{ starting }}</span> to
                  <span id="ending">{{ ending }}</span> of
                  <span id="total">{{ total }}</span> entries
                </div>
              </v-flex>
              <v-flex sm6 text-sm-right>
                <span class="d-inline-block mr-3 mt-3" style="max-width: 300px">
                  <v-text-field
                    hide-details
                    id="qsearch"
                    label="Event Ref or Title"
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
          @click.stop="newEvent"
        >
          <v-icon class="mdi-24px" color="secondary" left
            >mdi-plus-circle</v-icon
          >
          New Event
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
            <v-list dense>
              <v-list-item py-2>
                <v-list-item-title @click.stop="newEvent"
                  >New event</v-list-item-title
                >
              </v-list-item>
              <v-list-item py-2>
                <v-list-item-title @click="exportSelectedEvents()"
                  >Export event selection</v-list-item-title
                >
              </v-list-item>
              <v-divider class="ma-0 pa-0"></v-divider>
              <v-list-item  @click="deleteSelectedEvents()">
                <v-list-item-title>Delete event</v-list-item-title>
              </v-list-item>
            </v-list>
          </span>
        </v-menu>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<style lang="scss">
  .theme-nrs .stripped .v-data-table__wrapper {
    border: none;
  }
  .theme--light.v-data-table.stripped {
    background-color: transparent;
    color: rgba(0, 0, 0, 0.87);
  }
  .theme--light.v-data-table.stripped tbody tr:not(:last-child) td:not(.v-data-table__mobile-row) {
    border-bottom: none;
  }

  .theme-nrs.v-data-table.stripped tbody tr td:not(:last-child), .theme-nrs.v-data-table thead tr th:not(:last-child) {
    border-right: none;
  }

  .theme--light.v-data-table.stripped tbody tr:nth-child(2n+2) {
    background-color: #fafafa;
}
</style>

<script>
export default {
  name: "App",
  data: () => ({
    all:false,
    allevents: true,
    readonly: true,
    loading: false,
    items: [],

    selected: [],
    pagesizes: [10, 25, 75, 100],
    itemsPerPage: 10,
    page: 1,
    pageCount: 0,
    newuser: false,
    search: "",
    expanded: [],
    dialog: false,

    headers: [
      {
        text: "Event reference",
        align: "left",
        sortable: true,
        value: "eventRef",
        sortDescending: "desc",
        group: [1, 2, 3, 4]
      },
      {
        text: "Title",
        value: "eventTitle",
        sortable: true,
        group: [1, 2, 3, 4]
      },
      {
        text: "Event date",
        value: "eventDate",
        sortable: true,
        group: [1, 2, 3, 4]
      },
      { text: "User", value: "userFullName", sortable: true, group: [1, 2, 3, 4] },
      { text: "Company", value: "companyID", sortable: false, group: [1, 2] },
      { text: "Host", value: "view", sortable: false, group: [1, 3] },
      { text: "View", value: "view", sortable: false, group: [1, 2, 3, 4] },
      { text: "EventID", value: "eventID", sortable: false, group: [1, 2] }
    ],
    events: [],
    conferences: [],

  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New Item" : "Edit Item";
    },
    starting: function() {
      return (this.page - 1) * this.itemsPerPage + 1;
    },
    ending: function() {
      return Math.min(this.page * this.itemsPerPage + 1, this.events.length);
    },
    total: function() {
      return this.events.length;
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

  },

  created() {
    this.initialize();
  },

  beforeMount() {

    let token = this.$store.state.token
    fetch("http://netroadshow.incommglobalevents.com/api/events.php", {
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
    initialize() {},

    reportHostIcon: function(item) {
      console.log(this.$moment(item.eventDate, "YYYY-MM-DD"))
      console.log(this.$moment())

      if (this.$moment(item.eventDate, "YYYY-MM-DD") > this.$moment()) return "mdi-open-in-new";
      else return "mdi-chart-bar";
    },

    exportSelectedEvents() {

      const forExport = this.selected.map(u => Object.keys(u).reduce((newObj, key) => (
             key != 'speakers' && key != 'conferenceID'
             && key != 'pending' && key != 'userGroup'

        ) ? { ...newObj, [key]: u[key]} : newObj, {}));

        console.log(forExport)
        this.csvExport(forExport)

    },

    deleteSelectedEvents() {

      console.log("DELETING SELECTION")

      const _this = this
      const _deleteList = []
      if (this.selected.length>0) {
        confirm("Are you sure you want to delete these items?") &&
        this.selected.forEach(function(entry) {
            const index = _this.events.indexOf(entry);
              console.log(entry)
            _deleteList.push({eventID:entry.eventID})
            _this.events.splice(index, 1);
          })

          if (_deleteList.length>0) {
            console.log("Committing chage to DB");
            console.log(_deleteList)

            axios({
              url: "http://netroadshow.incommglobalevents.com/api/delete_events.php",
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

    gotoHost(item){
      this.$store
      .dispatch("editEvent", item)
      .then(result => {
        console.log("Host Event");
        this.$router.push("/viewer");
      })
      .catch(err => console.log(err));},

    newEvent() {
      this.$store
        .dispatch("createEvent", false)
        .then(result => {
          console.log("New Event");
          this.$router.push("/create-event");
        })
        .catch(err => console.log(err));
    },

    editEvent(item) {
      this.eventData = Object.assign({}, item);
      console.log(this.eventData);
      let eventData = this.eventData
      this.$store
        .dispatch("getEvent", { eventData })
        .then(result => {
          console.log(result);

          console.log("Get Event");
          this.eventData= Object.assign({}, this.$store.state.editEventData);
          console.log(this.eventData)

          console.log("EventData Retrieved Successfully");

        })
        .then(result => {

          console.log("Getting Conferences");
          let eventData = this.eventData
          this.$store
            .dispatch("editEvent", eventData)
            .then(result => {
              console.log("Edit Event");

              this.$router.push("/create-event");
            })
            .catch(err => console.log(err));

        })
        .catch(err => console.log(err));


    },



    onBtNext() {
      this.page = Math.min(
        this.page + 1,
        Math.ceil(this.events.length / this.itemsPerPage)
      );
    },
    onBtPrevious() {
      this.page = Math.max(this.page - 1, 1);
    },

    editItem(item) {
      this.editedIndex = this.events.indexOf(item);
      this.editedItem = Object.assign({}, item);
    },

    deleteItem(item) {
      const index = this.events.indexOf(item);
      confirm("Are you sure you want to delete this item?") &&
        this.events.splice(index, 1);
    },

    close() {

      this.$set(this.$refs.dTable.expanded, 0, false);
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      }, 300);
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
        this.eventData = Object.assign({}, item)
        console.log(this.eventData);
        this.$store
          .dispatch("editEvent", this.eventData)
          .then(result => {
            console.log("Edit Event");
            this.conferences=[]
            Object.assign(this.conferences, this.$store.state.conferenceData);
            console.log(this.conferences)
          })
          .catch(err => console.log(err));
        this.editedIndex = this.events.indexOf(item);
        this.editedItem = Object.assign({}, item);

      }
      expand(1 - isExpanded);
    },

    save() {
      if (this.editedIndex > -1) {
        Object.assign(this.events[this.editedIndex], this.editedItem);
      } else {
        this.events.push(this.editedItem);
      }
      this.close();
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
