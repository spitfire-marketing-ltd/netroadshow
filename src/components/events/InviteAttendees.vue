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

    <v-select
      label="Select event"
      color="secondary"
      filled
      :items="[
        'Example Event name 1',
        'Example Event name 1',
        'Example Event name 1'
      ]"
    ></v-select>

    <v-layout align-center justify-center pa-0 ma-0>
      <v-flex d-block sm12 align-center justify-center row fill-height>
        <v-list-item text-left class="pa-0 ma-0">
          <v-flex sm12>
            <v-data-table
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
                        @click.native="toggleAll"
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
                  selected
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
                      {{ item.reference }}
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
                      {{ item.eventDate }}
                    </div>
                  </td>
                  <td
                    v-if="inArray(userGroup, headers[3].group)"
                    class="text-xs-right"
                  >
                    <div class="datatable-cell-wrapper">{{ item.user }}</div>
                  </td>
                  <td
                    v-if="inArray(userGroup, headers[4].group)"
                    class="text-xs-right"
                  >
                    <div class="datatable-cell-wrapper">{{ item.company }}</div>
                  </td>

                  <td
                    v-if="inArray(userGroup, headers[5].group)"
                    class="text-xs-right "
                  >
                    <div class="datatable-cell-wrapper">
                      <v-icon
                        medium
                        color="secondary"
                        @click="expandrow(item, expand, isExpanded)"
                      >
                        delete
                      </v-icon>
                    </div>
                  </td>
                  <td
                    v-if="inArray(userGroup, headers[6].group)"
                    class="text-xs-right"
                  >
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
                  <td
                    v-if="inArray(userGroup, headers[7].group)"
                    class="text-xs-right"
                  >
                    <div class="datatable-cell-wrapper">{{ item.eventID }}</div>
                  </td>
                  <td>
                    <v-checkbox
                      color="secondary"
                      hide-details
                      :input-value="selected"
                      @change="showSelected(item)"
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

              <template v-slot:expanded-item="{ item, headers }">
                <td :colspan="headers.length + 1" class="pa-0 ma-0">
                  <v-card flat class="grey lighten-4">
                    <v-card-text>
                      <v-simple-table>
                        <thead>
                          <tr>
                            <th class="text-left">Conferences</th>
                            <th class="text-left">Invited</th>
                            <th class="text-left">Responded</th>
                            <th class="text-left">Attended</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr
                            v-for="conference in item.conferences"
                            :key="conference.timeslot"
                          >
                            <td>{{ conference.timeslot }}</td>
                            <td>{{ conference.invited }}</td>
                            <td>{{ conference.responded }}</td>
                            <td>{{ conference.attended }}</td>
                          </tr>
                        </tbody>
                      </v-simple-table>
                    </v-card-text>

                    <v-card-actions>
                      <v-spacer></v-spacer>
                      <v-btn color="blue darken-1" text @click="close"
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
              </v-flex>
              <v-flex sm6 text-sm-right>
                <span class="d-inline-block mr-3 mt-3" style="max-width: 300px">
                  <v-text-field
                    hide-details
                    id="qsearch"
                    label="Event Reference or Title"
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
                <span id="total">{{ total }}</span> entries
              </div>
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
          @click="newuser = true"
        >
          <v-icon class="mdi-36px" color="secondary" left
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
                <v-list-item-title
                  ><router-link to="/create-event"
                    >New event</router-link
                  ></v-list-item-title
                >
              </v-list-item>
              <v-divider></v-divider>
              <v-list-item py-2>
                <v-list-item-title @click="exportSelectedevents"
                  >Export event selection</v-list-item-title
                >
              </v-list-item>
            </v-list>
          </span>
        </v-menu>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<style lang="scss"></style>

<script>
export default {
  name: "App",
  data: () => ({
    allevents: true,
    readonly: true,
    loading: false,
    items: [],

    selected: [
      {
        eventID: ""
      }
    ],
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
        value: "reference",
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
      { text: "User", value: "user", sortable: true, group: [1, 2, 3, 4] },
      { text: "Company", value: "company", sortable: false, group: [1, 2] },
      { text: "Host", value: "view", sortable: false, group: [1, 3] },
      { text: "View", value: "view", sortable: false, group: [1, 2, 3, 4] },
      { text: "EventID", value: "eventID", sortable: false, group: [1, 2] }
    ],
    events: [],
    editedIndex: -1,
    editedItem: {
      name: "",
      company: "",
      email: "",
      phone: "",
      usertype: ""
    },
    defaultItem: {
      name: "",
      company: "",
      email: "",
      phone: "",
      usertype: ""
    }
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
      return localStorage.getItem("username");
    },
    userCompany: function() {
      return localStorage.getItem("company");
    },
    userGroup: function() {
      return localStorage.getItem("usergroup");
    }
  },

  created() {
    this.initialize();
  },

  beforeMount() {
    fetch("http://netroadshow.incommglobalevents.com/api/events.php")
      .then(result => result.json())
      .then(events => (this.events = events));
  },

  watch: {
    searchStates(val) {
      val && val !== this.selectedStates && this.queryStates(val);
    }
  },

  methods: {
    initialize() {},

    exportSelectedevents() {},

    deleteselectedevents() {},
    queryStates(v) {
      this.loading = true;
      // Simulated ajax query
      setTimeout(() => {
        this.items = this.states.filter(e => {
          return (e || "").toLowerCase().indexOf((v || "").toLowerCase()) > -1;
        });
        this.loading = false;
      }, 500);
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

    toggleAll() {
      if (this.selected.length) this.selected = [];
      else this.selected = this.events.slice();
      console.log(this.selected);
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
