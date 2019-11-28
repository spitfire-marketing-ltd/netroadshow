<template>
  <div>
  <v-card
    width="100%"
    elevation="1"
    height="100%"

    class="my-3 pa-2"
  >

    <v-card-text>
      <v-layout align-center justify-space-between v-if="(conferenceData.conferenceID>0)">
        <v-flex sm4 px-3 >
          <v-btn class="title font-weight-bold" text @click.stop="toggleConference()">
          <v-icon class="mdi-24px" color="secondary" left v-if="open"
            >mdi-minus</v-icon
          >
          <v-icon class="mdi-24px" color="secondary" left v-if="!open"
          >mdi-plus</v-icon
          >
            <span class="primary-text text-capitalize" >{{ conferenceData.conferenceTitle}}</span>
          </v-btn>

        </v-flex>
        <v-flex sm4 px-3>
          <v-layout row align-center my-1 pa-0 class="justify-center">
            <v-row justify="space-around" align="center" >
              <v-col cols="4" class="px-3 py-0 ma-0">
                <v-text-field
                  v-model="conference.startTime"
                  label="Start Time"
                  @click="editStartTime()"
                  color="secondary"
                  readonly
                  hide-details
                  required
                >
                  <v-icon slot="prepend" color="green"
                  >access_time</v-icon
                  >
                </v-text-field>
              </v-col>
              <v-col cols="4" class="pa-0 ma-0">
                <v-text-field
                  v-model="conference.endTime"
                  label="End Time"
                  color="secondary"
                  hide-details
                  readonly
                  required
                  @click="editEndTime()"
                >
                  <v-icon slot="prepend" color="red">access_time</v-icon>
                </v-text-field>
              </v-col>
              <v-col cols="2" class="pa-0 ma-0">
                <span>{{conference.timeZone}}</span>
              </v-col>
              <v-col cols="2" class="pa-0 ma-0">

              </v-col>
            </v-row>
          </v-layout>
        </v-flex>
        <v-flex sm4 px-3 class="text-right">
          <span class="primary-text font-weight-bold"
          >{{((lock==1) ? 'unlock' : 'Lock')}} conference</span>
          <span

            :class="[
              'rounded d-span  pa-1 ml-2 text-cente',
              (lock==1) ? 'green' : 'secondary'
            ]"
            style="border-radius:20px"
            @click.stop="lock = 1 - lock"
          >
            <v-icon
              v-if="lock"
              class="mdi-18px ma-auto"
              color="white"
              right
              >mdi-lock</v-icon
            >
            <v-icon
              v-if="!lock"
              class="mdi-18px ma-auto"
              color="white"
              right
              >mdi-lock-open-outline</v-icon
            >
          </span>
        </v-flex>
      </v-layout>

      <div v-if="showConference">
      <v-layout align-top justify-space-between v-if="(conferenceData.conferenceID>0)">
        <v-flex sm4 px-3>
          <v-list-item class="text-left px-0"
            ><span class="font-weight-bold pr-2"
              >Participant Access Code:
            </span>
            {{ conference.PAC }}
          </v-list-item>
          <v-select
            :items="[10, 20, 30, 40, 50]"
            filled
            color="secondary"
            v-model="conference.maxParticipants"
            label="Maximum particpants in conference"
          ></v-select>
        </v-flex>
        <v-flex sm4 px-3>
          <v-list-item class="text-left px-0"
            ><span class="font-weight-bold pr-2">Host Access Code: </span>
            {{ conference.HAC }}
          </v-list-item>
          <v-select
            :items="['Yes', 'No']"
            filled
            color="secondary"
            label="Record audio?"
          ></v-select>
        </v-flex>
        <v-flex sm4 px-3>
          <v-list-item class="text-left px-0"
            ><span class="font-weight-bold pr-2">Conference ID: </span>
            {{ conference.LBConfID.replace("conf:","") }}
          </v-list-item>
        </v-flex>
      </v-layout>
      <v-layout align-center justify-center pa-0 ma-0>
        <v-flex sm12 align-center justify-center row fill-height>
          <v-list-item text-left class="pa-0 ma-0">
            <v-flex sm12>

              <v-data-table
                ref="dTable"
                v-model="selected"
                color="secondary"
                :headers="headers"
                :items="attendees"
                single-expand
                :expanded.sync="expanded"
                :search="search"
                item-key="attendeeID"
                hide-default-footer
                hide-default-header
                loading=false
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
                          class="ma-auto pa-0"
                          color="secondary"
                          hide-details
                          @click.native="toggleAll()"
                        ></v-checkbox>
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
                        {{ item.fullName }}
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
                    <td class="text-xs-right"   v-if="inArray(userGroup, headers[1].access)">
                      <div class="datatable-cell-wrapper">
                        {{ item.companyName }}
                      </div>
                    </td>
                    <td class="text-xs-right px-0"   v-if="inArray(userGroup, headers[2].access)">
                      <div class="datatable-cell-wrapper">

                        <v-menu offset-y>
                          <template v-slot:activator="{ on }">
                            <v-list-item v-on="on">
                              <v-list-item-title v-if="(item.allocationStatus==null || item.allocationStatus=='Unconfirmed')">
                                <v-btn  small color="green" rounded dark  @click.stop="changeStatus(item,'Confirmed')">Approve</v-btn>
                              </v-list-item-title>
                              <v-list-item-title v-if="(item.allocationStatus=='Confirmed')">
                                <v-icon class="mdi-24px" left color="green">mdi-check-circle-outline</v-icon> <span class="body-2 green--text">Confirmed</span>
                              </v-list-item-title>
                              <v-list-item-title v-if="(item.allocationStatus=='Confirmation sent')">
                                <v-icon class="mdi-24px" left color="green">mdi-check-circle-outline</v-icon> <span class="body-2 green--text">Confirmation sent</span>
                              </v-list-item-title>
                              <v-list-item-title v-if="(item.allocationStatus=='Requested change')">
                                <v-icon class="mdi-24px" left color="accent">mdi-alert-circle-outline</v-icon> <span class="body-2 accent-text">Requested Change</span>
                              </v-list-item-title>
                              <v-list-item-title v-if="(item.allocationStatus=='Awaiting change')">
                                <v-icon class="mdi-24px" left color="accent">mdi-alert-circle-outline</v-icon> <span class="body-2 accent-text">Awaiting Change</span>
                              </v-list-item-title>
                              <v-list-item-title v-if="(item.allocationStatus=='Unconfirm change')">
                                <v-icon class="mdi-24px" left color="red">mdi-alert-circle-outline</v-icon> <span class="body-2 accent-text">Unconfirm Change</span>
                              </v-list-item-title>
                              <v-list-item-title v-if="(item.allocationStatus=='Cancelled')">
                                <span class="body-2 ">Cancelled</span>
                              </v-list-item-title>
                              <v-icon class="mdi-24px float-right" color="secondary">mdi-chevron-down</v-icon>
                          </v-list-item>
                          </template>
                          <span>
                            <v-list dense>
                              <v-list-item  @click="changeStatus(item,'Confirmed')">
                                <v-list-item-title><v-icon class="mdi-24px" left color="green">mdi-check-circle-outline</v-icon> <span class="green--text">Confirmed</span></v-list-item-title>
                              </v-list-item>
                              <v-list-item  @click="changeStatus(item,'Confirmation sent')">
                                <v-list-item-title><v-icon class="mdi-24px" left color="green">mdi-check-circle-outline</v-icon> <span class="green--text">Confirmation sent</span></v-list-item-title>
                              </v-list-item>
                              <v-list-item  @click="changeStatus(item,'Requested change')">
                                <v-list-item-title><v-icon class="mdi-24px" left color="accent">mdi-alert-circle-outline</v-icon> <span class="accent-text">Requested Change</span></v-list-item-title>
                              </v-list-item>
                              <v-list-item  @click="changeStatus(item,'Awaiting change')">
                                <v-list-item-title><v-icon class="mdi-24px" left color="accent">mdi-alert-circle-outline</v-icon> <span class="accent-text">Awaiting Change</span></v-list-item-title>
                              </v-list-item>
                              <v-list-item  @click="changeStatus(item,'Unconfirmed')">
                                <v-list-item-title><v-icon class="mdi-24px" left color="red">mdi-alert-circle-outline</v-icon> <span class="accent-text">Unconfirm</span></v-list-item-title>
                              </v-list-item>
                              <v-list-item  @click="changeStatus(item,'Cancelled')">
                                <v-list-item-title><span >Cancelled</span></v-list-item-title>
                              </v-list-item>

                            </v-list>
                          </span>
                        </v-menu>
                      </div>
                    </td>

                    <td class="text-xs-right"   v-if="inArray(userGroup, headers[3].access)">
                      <div class="datatable-cell-wrapper">
                        <v-menu offset-y v-if="!lock">
                          <template v-slot:activator="{ on }">
                            <v-btn color="grey light-4" small rounded dark  v-on="on"
                            >Move
                            <v-icon class="mdi-24px" color="white">mdi-chevron-down</v-icon>
                            </v-btn
                            >
                          </template>
                          <span>
                            <v-list dense>
                              <template v-for="(destination, confnumber) in conferences">
                              <v-list-item  @click="moveToConference(item,destination.conferenceID)">
                                <v-list-item-title>Conference {{confnumber + 1}}
                                  <span class="px-3" v-if="(item.firstChoice==destination.conferenceID)">(1st)</span>
                                  <span class="px-3" v-if="(item.secondChoice==destination.conferenceID)">(2nd)</span>
                                  <span class="px-3" v-if="(item.thirdChoice==destination.conferenceID)">(3rd)</span>
                                </v-list-item-title>
                              </v-list-item>
                            </template>
                            <v-divider  v-if="conferenceData.conferenceID>0" class="ma-0 pa-0"></v-divider>
                            <v-list-item v-if="conferenceData.conferenceID>0" class="" @click="moveToConference(item,-1)">

                              <v-list-item-title>Holding list
                              </v-list-item-title>
                            </v-list-item>
                            </v-list>
                          </span>
                        </v-menu>
                      </div>
                    </td>
                    <td class="text-xs-right"   v-if="inArray(userGroup, headers[1].access) ">
                      <div class="datatable-cell-wrapper">
                        {{ item.PIN }}
                      </div>
                    </td>
                    <td class="text-xs-right"   v-if="inArray(userGroup, headers[4].access)">
                      <div class="datatable-cell-wrapper">
                        <v-chip v-if="item.firstChoiceTIME" :color="((item.firstChoice == item.conferenceID) ? 'accent' : 'secondary')" dark pill small class="caption  mx-1">{{
                          item.firstChoiceTIME.split(' ')[0]
                        }}</v-chip>
                        <v-chip v-if="item.secondChoiceTIME" :color="((item.secondChoice == item.conferenceID) ? 'accent' : 'secondary')"  dark pill small  class="caption  mx-1" >{{
                          item.secondChoiceTIME.split(' ')[0]
                        }}</v-chip>
                        <v-chip v-if="item.thirdChoiceTIME" :color="((item.thirdChoice == item.conferenceID) ? 'accent' : 'secondary')"  dark pill small  class="caption  mx-1">{{
                          item.thirdChoiceTIME.split(' ')[0]
                        }}</v-chip>
                      </div>
                    </td>
                    <td class="text-xs-right"  v-if="inArray(userGroup, headers[5].access)">
                      <div class="datatable-cell-wrapper">
                        {{ item.invitedBy.fullName }}
                        <v-icon v-if="item.invitedBy.fullName"
                          class="mdi-24px float-right"
                          color="secondary"
                          left
                          @click.stop="getInvitedBy(item.invitedBy)"
                          >mdi-open-in-new
                        </v-icon>
                      </div>
                    </td>
                    <td class="text-xs-right"  v-if="inArray(userGroup, headers[6].access)">
                      <div class="datatable-cell-wrapper">
                        {{ item.priority }}
                      </div>
                    </td>

                    <td class="text-xs-right"  v-if="inArray(userGroup, headers[7].access)">
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
                  <v-btn color="primary" >Load Attendees</v-btn>
                </template>

              </v-data-table>




              <v-layout py-0 px-3 pt-0 ma-0 align-center>
                <v-flex sm6 text-sm-left>
                  <span
                    class="select d-inline-block mr-6"
                    style="white-space: nowrap;"
                  >
                    <v-select
                      color="success"
                      v-model="itemsPerPage"
                      id="page-size"
                      :items="pagesizes"
                      height="24"
                      hide-details=""
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
                      >mdi-less-than</v-icon
                    >
                    Previous
                  </v-btn>

                  <v-icon class="mdi-18px" color="secondary"
                    >mdi-power-on</v-icon
                  >

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
                  <v-layout pa-0 ma-0>
                    <div class="test-header body-1">
                      Showing
                      <span id="starting">{{ starting }}</span> to
                      <span id="ending">{{ ending }}</span> of
                      <span id="total">{{ total }}</span>
                      entries
                    </div>
                  </v-layout>
                </v-flex>
                <v-flex sm6 text-sm-right>
                  <span
                    class="d-inline-block mr-3 mt-3"
                    style="white-space: nowrap;"
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

              <v-layout py-0 px-3 pt-0 mx-0 my-3 align-center>
                <v-flex sm6 text-sm-left>
                  <v-btn
                    v-if="conferenceData.conferenceID>0"
                    text
                    class="ma-0 pa-0 text-none font-weight-bold"
                    v-on:click="deleteConference()"
                  >
                    <v-icon class="mdi-24px" color="accent" left
                      >mdi-close-circle</v-icon
                    >
                    <span class="accent-text">Delete conference</span>
                  </v-btn>


                </v-flex>
                <v-flex sm6 text-sm-right>
                  <v-menu offset-y>
                    <template v-slot:activator="{ on }">
                      <v-btn color="secondary" rounded dark  v-on="on"
                      >Options
                      <v-icon class="mdi-24px" color="white">mdi-chevron-down</v-icon>
                      </v-btn
                      >
                    </template>
                    <span>
                      <v-list dense>
                        <v-list-item  v-if="conferenceData.conferenceID>0 && !lock" @click="approveSelected()">
                          <v-list-item-title>Approve</v-list-item-title>
                        </v-list-item>
                        <v-list-item  v-if="conferenceData.conferenceID>0 && lock" @click="sendConfirmations()">
                          <v-list-item-title>Send confirmations</v-list-item-title>
                        </v-list-item>
                        <v-list-item  v-if="conferenceData.conferenceID>0 && lock" @click="sendUpdates()">
                          <v-list-item-title>Send updates only</v-list-item-title>
                        </v-list-item>
                        <v-list-item  v-if="conferenceData.conferenceID>0 && !lock" @click="unconfirmAllocation()">
                          <v-list-item-title>Unconfirm allocation</v-list-item-title>
                        </v-list-item>
                        <v-list-item  v-if="conferenceData.conferenceID>0 && !lock" @click="moveToHoldingList()">
                          <v-list-item-title>Move to holding list</v-list-item-title>
                        </v-list-item>
                        <v-divider v-if="conferenceData.conferenceID>0 && !lock" class="ma-0 pa-0"></v-divider>
                        <v-list-item v-if="!lock"  @click="deleteSelectedAttendees()">
                          <v-list-item-title>Delete participant</v-list-item-title>
                        </v-list-item>
                      </v-list>
                    </span>
                  </v-menu>
                </v-flex>
              </v-layout>
            </v-flex>
          </v-list-item>
        </v-flex>
      </v-layout>
      </div>

    </v-card-text>
    <v-dialog
      v-model="editmodalStart"
      :return-value.sync="editTime.startTime"
      persistent

      width="290px"
    >
      <v-time-picker
        v-if="editmodalStart"
        v-model="editTime.startTime"

        format="24hr"
        :max="editTime.endTime"
      >
        <div class="flex-grow-1"></div>
        <v-btn text color="primary" @click="editmodalStart = false"
          >Cancel</v-btn
        >
        <v-btn
          text
          color="primary"
          @click="saveStartTime(editTime.startTime)"
          >OK</v-btn
        >
      </v-time-picker>
    </v-dialog>

    <v-dialog
      v-model="editmodalEnd"
      :return-value.sync="editTime.endTime"
      persistent

      width="290px"
    >
      <v-time-picker
        v-if="editmodalEnd"
        v-model="editTime.endTime"

        :min="editTime.startTime"
        format="24hr"
      >
        <div class="flex-grow-1"></div>
        <v-btn text color="primary" @click="editmodalEnd = false"
          >Cancel</v-btn
        >
        <v-btn
          text
          color="primary"
          @click="saveEndTime(editTime.endTime)"
          >OK</v-btn
        >
      </v-time-picker>
    </v-dialog>

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

    </v-card>
    <v-alert  v-model="onError" dismissible type="error">{{errorMessage}}</v-alert>
  </div>
</template>

<script>
export default {
  name: "ConferenceTable",

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
    readonly:false, // included to prevent autofill

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
    editSchedule:
      {
        startTime: "",
        endTime: "",
        duration: ""
      }
    ,

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
    expanded: [],
    selected: [],
    editTime:{
      startTime: "",
      endTime: "",
      duration: ""
    },

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
      { text: "Allocation Status", value: "allocationStatus", sortable: true,
      access:[1,2,3,4] },
      { text: "Move",  sortable: false,
      access:[1,2,3,4] },
      { text: "PIN",  value: "PIN", sortable: false,
      access:[1,2,3,4] },
      { text: "Time Slots", sortable: false,
      access:[1,2,3,4] },
      { text: "Invited by", value: "invitedBy", sortable: true,
      access:[1,2,3,4] },
      { text: "Priority", value: "priority", sortable: true,
      access:[1,2,3,4] },
      { text: "attendeeID", value: "attendeeID", sortable: true,
      access:[1,2] }
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

  }),

  computed: {
    onError: function () {
      if (this.conference.onError===true)
        return true
      else {
        return false
      }
    },
    errorMessage: function () {
      return this.conference.errorMessage
    },
    showConference: function (){
      if (this.conferenceData.conferenceID<0) return true
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
      return Object.assign({},this.conferenceData)
    },

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
    eventData: function() {
      return this.$store.state.editEventData;
    }
  },

  beforeMount() {

    let eventID =  this.conference.eventID
    let conferenceID = this.conference.conferenceID
    console.log("Getting Attendees for "+conferenceID);
    const cd = Object.assign({},this.conference)
    this.editSchedule = cd

    axios({
      url: "http://netroadshow.incommglobalevents.com/api/attendees.php",
      data: { eventID, conferenceID  },
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
            console.log("ALERT CONFERENCE UPDATESTARTTIME "+data.CID)
            console.log(data);
            this.conference= data.conference
            // commit cahneg to DB
        }

    });
  },

  methods: {
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

    sendConfirmations(){
      console.log("SENDING INVITIONS SELECTION")
      let token = this.$store.state.token
      const _this = this
      const _selectionList = []
      if (this.selected.length>0) {
        confirm("Are you sure you want to send confirmations for these items?") &&
        this.selected.forEach(function(entry) {
            const index = _this.attendees.indexOf(entry);
              console.log(entry)
            _selectionList.push({attendeeID:entry.attendeeID})
            _this.attendees[index].invited='Yes'
              _this.changeStatus(entry,'Confirmation sent')
          })

          if (_selectionList.length>0) {
            console.log("Committing change to DB");
            console.log(_selectionList)

            axios({
              url: "http://netroadshow.incommglobalevents.com/api/send-confirmations.php",
              data: { inviteList: _selectionList, token  },
              method: "POST"
            })
              .then(resp => {
                console.log("Confirmation Successful");
                this.selected=[]
                console.log(resp);
              })
              .catch(err => {
                console.log("Confirmation error" + err);
              });

          }

        } else {
          alert("Please make a selection first!")
        }
    },

    sendUpdates() {
      console.log("update SELECTION")
      let token = this.$store.state.token
      const _this = this
      const _selectionList = []
      if (this.selected.length>0) {
        confirm("Are you sure you want to send updates for these items?") &&
        this.selected.forEach(function(entry) {
            const index = _this.attendees.indexOf(entry);
              console.log(entry)
            _selectionList.push({attendeeID:entry.attendeeID})
            _this.attendees[index].invited='Yes'
              _this.changeStatus(entry,'Confirmation sent')
          })

          if (_selectionList.length>0) {
            console.log("Committing change to DB");
            console.log(_selectionList)

            axios({
              url: "http://netroadshow.incommglobalevents.com/api/send-updates.php",
              data: { inviteList: _selectionList, token  },
              method: "POST"
            })
              .then(resp => {
                console.log("Confirmation Successful");
                this.selected=[]
                console.log(resp);
              })
              .catch(err => {
                console.log("Confirmation error" + err);
              });

          }

        } else {
          alert("Please make a selection first!")
        }
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

    deleteConference(entry){
      console.log("DELETING CONFERENCE")
      this.$emit('delete-conference', this.iteration)
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


    updateConference() {
        let eventSchedule = [this.conference];
        let eventData = this.eventData

        this.$store
          .dispatch("bookConferences", { eventSchedule, eventData })
          .then(result => {
            console.log(result);
            this.editSchedule = result.data;

          })
          .catch(err => console.log(err));

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
      this.editTime = Object.assign({},this.conference)
      this.editmodalStart = true;
    },

    saveStartTime(newval) {

        this.conference.startTime = newval;
        var x = this.$moment(
          "12-25-1995 " + this.conference.startTime,
          "MM-DD-YYYY HH:mm"
        );
        var y = this.$moment(
          "12-25-1995 " + this.conference.endTime,
          "MM-DD-YYYY HH:mm"
        );
        var duration = this.$moment.duration(y.diff(x));
        this.conference.duration = duration;

        //this.conference.startTime = this.conference.startTime

        const cd = Object.assign({},this.conference)
        cd.startTime = newval
        cd.duration = duration

        this.$emit('update-conference', this.iteration, cd)
        //this.$root.$emit('updateConfernce',{action: 'UPDATE', conference:cd,CID:this.conferenceData.conferenceID })

      //  this.updateConference()

      this.editmodalStart = false;

    },

    editEndTime() {
      this.editTime = Object.assign({},this.conference)
      this.editmodalEnd = true;
    },

    saveEndTime(newval) {

        console.log(newval)

        this.conference.endTime = newval;
        var x = this.$moment(
          "12-25-1995 " + this.conference.startTime,
          "MM-DD-YYYY HH:mm"
        );
        var y = this.$moment(
          "12-25-1995 " + this.conference.endTime,
          "MM-DD-YYYY HH:mm"
        );
        var duration = this.$moment.duration(y.diff(x));
        this.conference.duration = duration;

        const cd = Object.assign({},this.conference)
        cd.endTime = newval
        cd.duration = duration

        this.$emit('update-conference', this.iteration, cd)
      //  this.$root.$emit('updateConfernce',{action: 'UPDATE', conference:cd,CID:this.conferenceData.conferenceID })

      //  this.updateConference()


      this.editmodalEnd = false;

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
        attendee.allocationStatus = "Unconfirmed"
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
