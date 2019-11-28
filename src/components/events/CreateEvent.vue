<template>
  <v-container>
    <v-layout align-center justify-center pa-0 ma-0 pt-3>
      <v-flex sm12 tag="h1" color="primary" text-left>Create your event</v-flex>
    </v-layout>

    <p>
      It takes just a few minutes to create your event. You can save and return
      to add additional conferences and attendees at any time you wish.
    </p>
    <v-form ref="eventForm">
      <v-layout align-stretch justify-space-between pa-0 ma-0>
        <v-flex sm4>
          <v-card flat elevation="1" height="100%">
            <v-card-title tag="h2" class="primary-text"
              >Event set-up</v-card-title
            >
            <v-card-text>
              <v-text-field
                filled
                v-model="eventData.eventTitle"
                label="Event title"
                :rules="titleRules"
                required
              ></v-text-field>

              <v-menu
                v-model="eventDatepick"
                :close-on-content-click="false"
                :nudge-right="40"
                transition="scale-transition"
                offset-y
                min-width="290px"
              >
                <template v-slot:activator="{ on }">
                  <v-text-field
                    v-model="eventData.eventDate"
                    filled
                    color="secondary"
                    label="Event date"
                    required
                    readonly
                    v-on="on"
                  >
                    <template v-slot:append>
                      <v-icon class="mdi-24px" color="secondary" left
                        >mdi-calendar-month</v-icon
                      >
                    </template>
                  </v-text-field>
                </template>
                <v-date-picker
                  v-model="eventData.eventDate"

                  @input="eventDatepick = false"
                ></v-date-picker>
              </v-menu>
            </v-card-text>
            <v-card-actions>
              <v-btn
                text
                small
                class="ma-0 py-0 text-none font-weight-bold"
                @click="newuser = true"
              >
                <v-icon class="mdi-24px" color="secondary" left
                  >mdi-calendar-plus</v-icon
                >
                Add to my calendar
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-flex>

        <v-flex sm4 mx-6>
          <v-card elevation="1" height="100%">
            <v-card-title tag="h2" class="primary-text"
              >Speaker(s)</v-card-title
            >
            <v-card-text>
              <template>
                <v-sheet
                  tile
                  color="grey lighten-4"
                  class="elevation-1 pa-3 speakers"
                >
                  <v-text-field
                    class="mb-3"
                    v-model="speaker"
                    label="Add Speaker(s)"
                    hint="Press enter to list each speaker seperately"
                    persistent-hint
                    v-on:blur = "addSpeaker()"
                    @keyup.native.enter="addSpeaker()"
                  ></v-text-field>
                  <template v-for="(item, index) in eventData.speakers">
                    <v-layout column v-bind:key="item" >
                      <v-chip
                        class="my-1"
                        close
                        @click:close="deleteSpeaker(index)"
                        width="100%"
                        label
                      >
                        <v-icon left color="secondary">mdi-account-tie</v-icon>
                        {{ item }}
                      </v-chip>
                    </v-layout>
                  </template>
                </v-sheet>
              </template>
            </v-card-text>
            <v-card-actions>
              <v-btn v-show="false"
                text
                small
                class="ma-0 py-0 text-none font-weight-bold"
                @click="addSpeaker"
              >
                <v-icon class="mdi-24px" color="secondary" left
                  >mdi-plus-circle</v-icon
                >
                Add Speaker
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-flex>

        <v-flex sm4>
          <v-card elevation="1" height="100%">
            <v-card-title tag="h2" class="primary-text"
              >Custom invitation image</v-card-title
            >
            <v-card-text>
              <p>
                You can select an image from our gallery, upload your own, or
                leave it blank.
              </p>
              <v-layout align-center justify-space-between>
                <v-flex sm4>
                  <v-img
                    :class="[
                      'circle',
                      isSystemImage ? 'primary' : 'grey lighten-1'
                    ]"
                    :src="systemImage"
                    width="95"
                    height="95"
                    @click.stop="chooser = true"
                  >
                    <v-layout align-center justify-center column>
                      <v-list-item dense class="text-center mt-1">
                        <v-icon class="mdi-24px" color="white"
                          >mdi-image</v-icon
                        >
                      </v-list-item>
                      <v-list-item dense class="text-center">
                        <div class="text-center white--text body-2">
                          Gallery Image
                        </div>
                      </v-list-item>
                    </v-layout>
                  </v-img>
                </v-flex>

                <v-flex sm4>
                  <v-img
                    :src="uploadedImage"
                    :class="[
                      'circle',
                      isUploadImage ? 'primary' : 'grey lighten-1'
                    ]"
                    width="95"
                    height="95"
                    @click.stop="upload = true"
                  >
                    <v-layout align-center justify-center column>
                      <v-list-item dense class="text-center mt-1">
                        <v-icon class="mdi-24px" color="white"
                          >mdi-image-plus</v-icon
                        >
                      </v-list-item>
                      <v-list-item dense class="text-center">
                        <div class="text-center white--text body-2">
                          Upload Image
                        </div>
                      </v-list-item>
                    </v-layout>
                  </v-img>
                </v-flex>

                <v-flex sm4>
                  <v-img
                    :class="[
                      'circle',
                      isNoImage ? 'primary' : 'grey lighten-1'
                    ]"
                    width="95"
                    height="95"
                    @click.stop="
                      (isNoImage = true),
                        (isUploadImage = false),
                        (isSystemImage = false),
                        (uploadedImage = ''),
                        (systemImage = ''),
                        (this.eventData.eventImage = '')
                    "
                  >
                    <v-layout align-center justify-center column>
                      <v-list-item dense class="text-center mt-1">
                        <v-icon class="mdi-24px" color="white"
                          >mdi-checkbox-blank</v-icon
                        >
                      </v-list-item>
                      <v-list-item dense class="text-center">
                        <div class="text-center white--text body-2">
                          No Image
                        </div>
                      </v-list-item>
                    </v-layout>
                  </v-img>
                </v-flex>
              </v-layout>
            </v-card-text>
            <v-card-actions>
              <v-btn
                text
                small
                class="ma-0 py-0 text-none font-weight-bold"
                @click.stop="previewInvitePage"
              >
                <v-icon class="mdi-24px" color="secondary" left
                  >mdi-open-in-new</v-icon
                >
                Preview invite page
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-flex>
      </v-layout>

      <v-layout my-6>
        <v-card width="100%" elevation="1" height="100%">
          <v-layout align-center justify-center py-0 px-3 ma-0 pt-3>
            <v-flex sm6 tag="h1" color="primary" text-left
              >Choose your branding</v-flex
            >
            <v-flex sm6 class="text-right ">
              <v-layout
                align-center
                row
                justify-end
                mr-1
                v-if="inArray(userGroup, [1, 2, 3, 4])"
              >
                <span class="d-flex mr-3">
                  <label>Use your company branding</label>
                </span>
                <span class="d-flex">
                  <v-switch
                    color="secondary"
                    v-model="eventData.useCompanyBranding"
                  ></v-switch>
                </span>
              </v-layout>
            </v-flex>
          </v-layout>

          <v-card-text>
            <p>
              You can use your existing company branding as default or
              personalise your attendee experience with a different brand.
            </p>
            <v-layout wrap v-show="!eventData.useCompanyBranding">
              <v-flex xs12 sm6 md4 px-3>
                <v-layout column justify-space-between>
                  <div class="mt-12">
                    Upload logo (Recommended size w600px)
                    <v-file-input
                      class="pt-0"
                      accept="image/*"
                      v-model="logo"
                      required
                      @change="onLogoChange"
                    ></v-file-input>
                    <img
                      :src="eventData.logoOverride"
                      height="75"
                      v-if="eventData.logoOverride"
                    />
                  </div>
                </v-layout>
              </v-flex>

              <v-flex xs12 sm6 md4 px-3>
                <v-layout column>
                  <div class="mt-12">
                    Select main colour scheme
                    <v-layout row class="px-3 mt-0">
                      <v-sheet
                        :style="backgroundMainColor"
                        width="80"
                        height="32"
                        class="pa-0 ma-0 mt-1"
                        d-inline-flex
                        @click="showMainPicker = 1 - showMainPicker"
                      ></v-sheet>
                      <v-text-field
                        hide-details
                        color="grey"
                        lighten-4
                        d-inline-flex
                        v-model="eventData.primaryColor"
                        clearable
                        class="pl-3 pt-0"
                        @click="showMainPicker = 1 - showMainPicker"
                      ></v-text-field>
                    </v-layout>
                    <v-card
                      flat
                      v-if="showMainPicker == true"
                      v-model="showMainPicker"
                    >
                      <v-card-text class="pa-0 ma-0">
                        <v-color-picker
                          canvas-height="75"
                          flat
                          width="500"
                          hide-inputs
                          class="pa-0 ma-0 mt-2"
                          mode="hexa"
                          v-model="eventData.primaryColor"
                        ></v-color-picker>
                      </v-card-text>
                      <v-card-actions>
                        <div
                          class="text-center"
                          style="width:100%;display: inline-flex"
                        >
                          <v-btn
                            class="mx-auto"
                            color="secondary"
                            text
                            @click="showMainPicker = 1 - showMainPicker"
                          >
                            Close
                          </v-btn>
                        </div>
                      </v-card-actions>
                    </v-card>
                  </div>
                </v-layout>
              </v-flex>

              <v-flex xs12 sm6 md4 px-3>
                <v-layout column>
                  <div class="mt-12">
                    Select button colour scheme
                    <v-layout row class="px-3 mt-0">
                      <v-sheet
                        :style="backgroundButtonColor"
                        width="80"
                        height="32"
                        class="pa-0 ma-0 mt-1"
                        d-inline-flex
                        @click="showButtonPicker = 1 - showButtonPicker"
                      ></v-sheet>
                      <v-text-field
                        hide-details
                        color="grey"
                        lighten-4
                        d-inline-flex
                        v-model="eventData.secondaryColor"
                        clearable
                        class="pl-3 pt-0"
                        @click="showButtonPicker = 1 - showButtonPicker"
                      ></v-text-field>
                    </v-layout>

                    <v-card
                      flat
                      v-if="showButtonPicker == true"
                      v-model="showButtonPicker"
                    >
                      <v-card-text class="pa-0 ma-0">
                        <v-color-picker
                          canvas-height="75"
                          hide-mode-switch
                          hide-inputs
                          flat
                          width="500"
                          class="pa-0 ma-0  mt-2"
                          mode="hexa"
                          v-model="eventData.secondaryColor"
                        ></v-color-picker>
                      </v-card-text>
                      <v-card-actions>
                        <div
                          class="text-center"
                          style="width:100%;display: inline-flex"
                        >
                          <v-btn
                            class="mx-auto"
                            color="secondary"
                            text
                            @click="showButtonPicker = 1 - showButtonPicker"
                          >
                            Close
                          </v-btn>
                        </div>
                      </v-card-actions>
                    </v-card>
                    <v-btn
                      text
                      small
                      class="my-6 pa-1 text-none font-weight-bold"
                      @click.stop="previewInvitePage"
                    >
                      <v-icon class="mdi-24px" color="secondary" left
                        >mdi-open-in-new</v-icon
                      >
                      <span class="secondary-text">Preview invite page</span>
                    </v-btn>
                  </div>
                </v-layout>
              </v-flex>
            </v-layout>
          </v-card-text>
        </v-card>
      </v-layout>

      <v-row v-if="!isEventCreated" class="text-center">
        <v-btn
          color="secondary"
          rounded
          class="m-auto"
          @click.stop="saveEvent('eventForm')"
          >Create Event</v-btn
        >
      </v-row>
      <v-row v-if="isEventCreated" class="text-center">
        <v-btn
          color="secondary"
          rounded
          class="m-auto"
          @click.stop="saveEvent('eventForm')"
          >Save Changes</v-btn
        >
      </v-row>
    </v-form>

    <v-form ref="bookConferences">
      <v-layout my-6 v-show="!isConferencesBooked && isEventCreated">
        <v-card width="100%" elevation="1" height="100%">
          <v-layout align-top justify-start pa-6 pb-0>
            <v-flex sm4 px-3 text-left>
              <h2>Now create your conference schedule</h2>
              <p>
                Simply select the time of your conferences to create your
                schedule. Once you have created your conferences for that day
                you can then start to invite guests to select their preferred
                time options.
              </p>
            </v-flex>
            <v-flex sm6 px-3>
              <!--{{ new Date() | moment("dddd, MMMM Do YYYY") }}-->
              <v-sheet
                color="white "
                class="elevation-0 pa-3 "
                v-if="this.eventSchedule.length > 0"
              >
                <template v-for="(item, index) in eventSchedule">
                  <v-row justify="space-around" align="center" :key="index">
                    <v-col cols="3" class="px-3 py-0 ma-0">
                      <v-text-field
                        v-model="item.startTime"
                        label="Start Time"
                        @click="editStartTime(item)"
                        color="secondary"
                        readonly
                        required
                      >
                        <v-icon slot="prepend" color="green"
                          >access_time</v-icon
                        >
                      </v-text-field>
                    </v-col>
                    <v-col cols="3" class="pa-0 ma-0">
                      <v-text-field
                        v-model="item.endTime"
                        label="End Time"
                        color="secondary"
                        readonly
                        required
                        @click="editEndTime(item)"
                      >
                        <v-icon slot="prepend" color="red">access_time</v-icon>
                      </v-text-field>
                    </v-col>
                    <v-col cols="2" class="pa-0 ma-0">
                      <span class="primary-text body-2 font-weight-bold">(GMT)</span>
                    </v-col>
                    <v-col cols="3" class="pa-0 ma-0">
                      <v-btn
                        text
                        small
                        class=" mx-0 my-3 py-0 text-none font-weight-bold"
                        @click.stop="deleteConference(index)"
                      >
                        <v-icon class="mdi-24px" color="red" left
                          >mdi-close-circle</v-icon
                        >

                      </v-btn>
                    </v-col>
                  </v-row>
                </template>
              </v-sheet>

              <v-dialog
                v-model="editmodalStart"
                :return-value.sync="editSchedule.startTime"
                persistent

                width="290px"
              >
                <v-time-picker
                  v-if="editmodalStart"
                  v-model="editSchedule.startTime"

                  format="24hr"
                  :max="editSchedule.endTime"
                >
                  <div class="flex-grow-1"></div>
                  <v-btn text color="primary" @click="editmodalStart = false"
                    >Cancel</v-btn
                  >
                  <v-btn
                    text
                    color="primary"
                    @click="saveStartTime(editSchedule.startTime)"
                    >OK</v-btn
                  >
                </v-time-picker>
              </v-dialog>

              <v-dialog
                v-model="editmodalEnd"
                :return-value.sync="editSchedule.endTime"
                persistent

                width="290px"
              >
                <v-time-picker
                  v-if="editmodalEnd"
                  v-model="editSchedule.endTime"

                  :min="editSchedule.startTime"
                  format="24hr"
                >
                  <div class="flex-grow-1"></div>
                  <v-btn text color="primary" @click="editmodalEnd = false"
                    >Cancel</v-btn
                  >
                  <v-btn
                    text
                    color="primary"
                    @click="saveEndTime(editSchedule.endTime)"
                    >OK</v-btn
                  >
                </v-time-picker>
              </v-dialog>
              <v-sheet
                color="white lighten-4"
                class="elevation-0 px-3 py-0 mb-3 "
              >
                <v-row justify="space-around" align="center" v-if="this.eventSchedule.length < 0">
                  <v-col cols="3" class="pa-3 ma-0">
                    <v-dialog
                      ref="dialogStart"
                      v-model="modalStart"
                      :return-value.sync="startTime"
                      persistent

                      width="290px"
                    >
                      <template v-slot:activator="{ on }">
                        <v-text-field
                          v-model="startTime"
                          label="Start Time"
                          prepend-icon="access_time"
                          placeholder=""
                          readonly
                          required
                          hide-details
                          v-on="on"
                        ></v-text-field>
                      </template>
                      <v-time-picker
                        v-if="modalStart"
                        v-model="startTime"

                        format="24hr"
                        :max="endTime"
                      >
                        <div class="flex-grow-1"></div>
                        <v-btn text color="primary" @click="modalStart = false"
                          >Cancel</v-btn
                        >
                        <v-btn
                          text
                          color="primary"
                          @click="$refs.dialogStart.save(startTime)"
                          >OK</v-btn
                        >
                      </v-time-picker>
                    </v-dialog>
                  </v-col>
                  <v-col cols="3" class="pa-0 ma-0">
                    <v-dialog
                      ref="dialogEnd"
                      v-model="modalEnd"
                      :return-value.sync="endTime"
                      persistent

                      width="290px"
                    >
                      <template v-slot:activator="{ on }">
                        <v-text-field
                          v-model="endTime"
                          label="End Time"
                          prepend-icon="access_time"
                          hide-details
                          placeholder=""
                          readonly
                          required
                          v-on="on"
                        ></v-text-field>
                      </template>
                      <v-time-picker
                        v-if="modalEnd"
                        v-model="endTime"

                        :min="startTime"
                        format="24hr"
                      >
                        <div class="flex-grow-1"></div>
                        <v-btn text color="primary" @click="modalEnd = false"
                          >Cancel</v-btn
                        >
                        <v-btn
                          text
                          color="primary"
                          @click="$refs.dialogEnd.save(endTime)"
                          >OK</v-btn
                        >
                      </v-time-picker>
                    </v-dialog>
                  </v-col>
                  <v-col cols="2" class="pa-0 ma-0">
                    <span class="pt-2">GMT</span>
                  </v-col>
                </v-row>
                <v-row>
                  <v-col cols="12" class="pa-0 ma-0 text-left">
                    <v-btn
                      text
                      small
                      class="ma-0 px-4 pt-0 text-none font-weight-bold"
                      @click="addConference(false)"
                    >
                      <v-icon class="mdi-24px" color="secondary" left
                        >mdi-plus-circle</v-icon
                      >
                      <span class="secondary-text">Add conference</span>
                    </v-btn>
                  </v-col>
                </v-row>
              </v-sheet>
            </v-flex>
          </v-layout>

          <v-card-text v-if="this.eventSchedule.length > 0">
            <v-row class="text-center my-2">
              <v-btn
                color="secondary"
                rounded
                class="m-auto"
                @click.stop="bookConference('bookConferences')"
                >Book Conferences</v-btn
              >
            </v-row>
          </v-card-text>
        </v-card>
      </v-layout>
    </v-form>

    <v-layout v-if="isConferencesBooked" my-6 column>
      <v-row class="mx-2">
        <h2>Conferences</h2>
        <p>
          Simply select the time of your conference and click ‘create’ - your
          conference will then be created. Once you have all your conferences
          created for that day you can then start to invite guests below who
          will pick their preferred conference time options.
        </p>
      </v-row>

      <template v-for="(conference, index) in conferences">
        <v-conference-table :conferenceData="conference" :conferences="conferences" :iteration="index" v-on:update-conference="updateConference" v-on:delete-conference="deleteBookedConference"></v-conference-table>
      </template>
    </v-layout>
    <v-row class="mx-2" >
      <v-col cols="12" >
        <v-btn
          text

          class="ma-0 pa-0 text-none font-weight-bold"
          @click="addConference(true)"
        >
          <v-icon class="mdi-24px" color="secondary" left
            >mdi-plus-circle</v-icon
          >
          <span class="secondary-text">Add conference</span>
        </v-btn>
      </v-col>
    </v-row>
    <v-layout v-if="isConferencesBooked" my-6 column>
      <v-row class="mx-2" no-gutters>
        <v-col cols="12">
        <h2>Holding list</h2>
        <p> You can add participants to this list temporarily whilst you organise which conference you would like participants allocated to.
        </p>
      </v-col>
      </v-row>
      <template v-for="(conference, index) in holdingList">
        <v-conference-table  :conferenceData="conference" :conferences="conferences" :iteration="index"></v-conference-table>
      </template>
      <v-row class="mx-2" >
        <v-col cols="12" >
          <v-btn
            text

            class="ma-0 pa-0 text-none font-weight-bold"
            @click.stop="inviteAttendees()"
          >
            <v-icon class="mdi-24px" color="secondary" left
              >mdi-plus-circle</v-icon
            >
            <span class="secondary-text">Add new attendees</span>
          </v-btn>
        </v-col>
      </v-row>
    </v-layout>

    <v-row>
      <v-col cols="12" class="text-center">
        <v-btn text @click="hostConference()">
          <v-icon class="mdi-36px" left color="green">mdi-open-in-new</v-icon>
          <span class="green--text display-1 font-weight-bold text-capitalize mx-3">Manage event</span>
        </v-btn>
        <div class="green--text py-3">Starts {{ eventData.eventDate | moment("DD/MM/YYYY")}} at {{conferences[0].startTime}}</div>
      </v-col>
    </v-row>

    <v-dialog v-model="upload" width="600">
      <v-card color="white" class="pa-12 text-center">
        <v-flex sm12 tag="h2" color="primary" text-center
          >Upload your own image</v-flex
        >
        <v-card-text>
          <p>
            This will show on your invitees time selection screen (Recommended
            minimium size w600 x h800 pixels)
          </p>
          <div class="my-6">
            <v-file-input
              placeholder="Select"
              hide-details
              outlined
              prepend-icon="mdi-camera"
              accept="image/*"
              v-model="file"
              required
              @change="onFileChange"
            ></v-file-input>
          </div>
          <img
            :src="uploadedImage"
            v-if="uploadedImage"
            height="300"
            width="500"
          />
        </v-card-text>
        <v-card-actions class="px-10">
          <v-layout justity-space-around>
            <v-flex>
              <v-btn
                color="blue darken-1"
                class="px-10"
                text
                @click="upload = false"
                >Cancel</v-btn
              >
            </v-flex>
            <v-flex>
              <v-btn
                color="blue darken-1"
                class="font-weight-bold"
                text
                @click.stop="
                  (upload = false),
                    (isSystemImage = false),
                    (isUploadImage = true),
                    (isNoImage = false),
                    (systemImage = '')
                "
              >
                Select this image
              </v-btn>
            </v-flex>
          </v-layout>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog v-model="chooser" width="600">
      <v-card color="white" class="pa-12 text-center">
        <v-flex sm12 tag="h3" color="primary" text-center
          >Choose an image from our gallery</v-flex
        >
        <v-card-text>
          <v-card flat tile>
            <v-window v-model="onboarding">
              <v-window-item v-for="n in length" :key="`card-${n}`">
                <v-row
                  class="fill-height ma-auto pa-0"
                  align="center"
                  justify="center"
                  tag="v-card-text"
                >
                  <v-img
                    src="../../assets/login_splash.jpg"
                    height="300"
                    width="550"
                  />
                </v-row>
              </v-window-item>
            </v-window>

            <v-card-actions class="justify-space-between">
              <v-btn text @click="prev">
                <v-icon color="secondary">mdi-chevron-left</v-icon>
              </v-btn>
              <v-item-group v-model="onboarding" class="text-center" mandatory>
                <v-item
                  v-for="n in length"
                  :key="`btn-${n}`"
                  v-slot:default="{ active, toggle }"
                >
                  <v-btn
                    :input-value="active"
                    icon
                    small
                    class="ma-auto pa-0"
                    color="secondary"
                    @click="toggle"
                  >
                    <v-icon color="secondary">mdi-record</v-icon>
                  </v-btn>
                </v-item>
              </v-item-group>
              <v-btn text @click="next">
                <v-icon>mdi-chevron-right</v-icon>
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-card-text>
        <v-card-actions class="px-10 text-center">
          <v-layout justity-space-around>
            <v-flex>
              <v-btn
                color="blue darken-1"
                class="px-10"
                text
                @click="chooser = false"
                >Cancel</v-btn
              >
            </v-flex>
            <v-flex>
              <v-btn
                color="blue darken-1"
                class="font-weight-bold"
                text
                @click.stop="
                  (chooser = false),
                    (isSystemImage = true),
                    (isUploadImage = false),
                    (isNoImage = false),
                    (uploadedImage = '')
                "
              >
                Select this image
              </v-btn>
            </v-flex>
          </v-layout>
        </v-card-actions>
      </v-card>
    </v-dialog>


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
//Vue.use(require('moment'));

var jwt = require('jsonwebtoken');

import ConferenceTable from "./ConferenceTable.vue";

export default {
  name: "App",
  components: {
    'v-conference-table' : ConferenceTable
  },
  data: () => ({
    event: [],

    showhelp: false,
    readonly: true,
    loading: false,
    drawerRight: false,
    companyBranding: true,
    upload: false,
    chooser: false,
    isNoImage: true,
    isSystemImage: false,
    isUploadImage: false,
    items: [],

    onError: false,
    errorMessage: "Error Message",
    logo: null,
    uploadedImage: "",

    systemImage:"",

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

    selected: [
      {
        eventID: ""
      }
    ],

    newuser: false,
    search: "",
    speaker: "",

    length: 8,
    onboarding: 0,

    file: null,
    valid: false,

    titleRules: [
      v => !!v || "This is required",
      v => (v && v.length <= 255) || "Must be less than 255 characters"
    ],
    emailRules: [
      v => !!v || "E-mail is required",
      v =>
        /^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/.test(v) ||
        "E-mail must be valid"
    ],

    showMainPicker: false,
    showButtonPicker: false,

    eventScheduler: false,
    bookConferences: false,

    eventDatepick: false,

    eventDate: new Date().toISOString().substr(0, 10),

    conferencesMade: false,

    eventSchedule: [],

    dialog: false,
    showInvitedBy: false,
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
      secondaryColor: "",
      useCompanyBranding: true
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
      secondaryColor: "",
      useCompanyBranding: true
    }
  }),

  computed: {

    holdingList() {
      let HL = [
      {
        conferenceID: "-1",
        eventID: this.eventData.eventID,
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
    ]
      return HL
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

    copy_url: function() {

      let eventID = this.eventData.eventID
      let attendeeID = 8
      var token = jwt.sign({ eventID, attendeeID }, 'nrs-1234');
      console.log(eventID)
      console.log(token)
      return "/timeselect/?e="+token
    },

    isEventCreated: function() {
      console.log("EID" + this.eventData.eventID);

      if (this.eventData.eventID != "") {
        return true;
      } else {
        return false;
      }
    },
    isConferencesBooked: function() {
      console.log("Conferences:" + this.conferences.length);

      if (
        this.conferences.length > 0 &&
        this.conferences[0].conferenceID != ""
      ) {
        return true;
      } else {
        return false;
      }
    }
  },

  created() {
    this.initialize();
  },

  beforeMount() {

  },

  watch: {

    isSystemImage(val) {
      if (val == false) this.systemImage = "";
      else this.systemImage="/assets/images/login_splash.jpg";
    },
    isUploadImage(val) {
      if (val == false) this.uploadImage = "";
    }
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
        if (this.eventData.eventImage) {
          this.uploadedImage = this.eventData.eventImage;
          this.isUploadImage = true;
          this.isSystemImage = false;
          this.isNoImage = false;
          this.systemImage = "";
        } else {
          this.systemImage = "";
          this.uploadImage = "";
          this.isNoImage = true;
        }

        Object.assign(tempConferences, this.$store.state.conferenceData);
        Object.assign(this.eventSchedule, tempConferences);
        Object.assign(this.conferences, tempConferences);
        this.setAutoConferenceTimes();

        console.log(this.eventData);
        console.log("EventData Retrieved Successfully");
      } else {
        Object.assign(this.eventData, this.defaultItem);
        console.log(this.eventData);
        console.log("EventData Initialised Successfully");
      }
    },



    inviteAttendees: function() {

        this.$router.push("/attendees");

    },

    previewInvitePage: function() {

      let event = 16
      var token = jwt.sign({ event }, 'nrs-1234');
      console.log(event);
      console.log(token);
      let payload = jwt.verify(token, "nrs-1234");
      console.log(payload);
    //  ?e=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJldmVudCI6MTYsImlhdCI6MTU2ODY1MDEwNH0.Z3Y0VV8X0HRRJQoXmnnCOdOdPx_0oTY-BNQ1TIfvtw8

      //this.$store.dispatch("logout").then(() => {
        window.open(this.copy_url, '_blank');
      //});
    },



    bookConference(form, attendees=true) {
      let result = this.$refs[form].validate();

      console.log("BOOKING")
      console.log(result);

      if (result) {
        let eventSchedule = this.eventSchedule;
        let eventData = this.eventData;

        this.$store
          .dispatch("bookConferences", { eventSchedule, eventData })
          .then(result => {
            console.log(result);
            this.eventSchedule = result.data;
            this.conferences = result.data;
            console.log(this.isConferencesBooked)
            this.setAutoConferenceTimes();

            this.$refs[form].resetValidation();
          //  if (attendees)
            //  this.$router.push("/attendees");
          })
          .catch(err => {
            console.log("ERROR")
            console.log(err)
            this.onError = true
            this.errorMessage = err.data.status
          });
      }
    },



    onBtNext() {
      this.page = Math.min(
        this.page + 1,
        Math.ceil(this.conferences.length / this.itemsPerPage)
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

    saveEvent(form) {
      // Make sure we have applied changes to speaker setup
      this.addSpeaker();

      let result = this.$refs[form].validate();

      this.eventData.userID = this.userID;
      this.eventData.companyID = this.clientID;

      console.log(result);

      if (result) {
        let eventData = this.eventData;



        // Save the event
        this.$store
          .dispatch("saveEvent", { eventData })
          .then(result => {
            console.log(result);

            this.eventData.eventID = result.data;

            this.eventScheduler = true;

            console.log(this.eventScheduler);
            this.$refs[form].resetValidation();
          })
          .catch(err => console.log(err));
      }
    },

    onFileChange() {
      let reader = new FileReader();
      reader.onload = () => {
        this.eventData.eventImage = reader.result;
        this.uploadedImage = this.eventData.eventImage;
      };
      if (this.file) {
        reader.readAsDataURL(this.file);
      }
    },

    onLogoChange() {
      let reader = new FileReader();
      reader.onload = () => {
        this.eventData.logoOverride = reader.result;
      };
      if (this.logo) {
        reader.readAsDataURL(this.logo);
      }
    },

    inArray: function(needle, haystack) {
      var length = haystack.length;
      for (var i = 0; i < length; i++) {
        if (haystack[i] == needle) return true;
      }
      return false;
    },

    next() {
      this.onboarding =
        this.onboarding + 1 === this.length ? 0 : this.onboarding + 1;
    },

    prev() {
      this.onboarding =
        this.onboarding - 1 < 0 ? this.length - 1 : this.onboarding - 1;
    },

    hostConference() {
        this.$router.push("/viewer");
    },

    addConference(saveToDB) {

      console.log(this.$moment().format("dddd, MMMM Do YYYY HH:mm:ss"));

      if (this.startTime == ''){
        console.log("Making up an Start Time")
        this.startTime = this.$moment().startOf('hour').format("HH:mm")
      }
      var x = this.$moment("12-25-1995 " + this.startTime, "MM-DD-YYYY HH:mm");

      if (this.endTime == ''){
        console.log("Making up an End Time")
        this.endTime = x.add(30, 'minutes').format("HH:mm");
      }
      var y = this.$moment("12-25-1995 " + this.endTime, "MM-DD-YYYY HH:mm");
      var duration = this.$moment.duration(y.diff(x));

      console.log(this.startTime);
      console.log(this.endTime);
      console.log(duration);

      this.eventSchedule.push({
        startTime: this.startTime,
        endTime: this.endTime,
        duration: duration,
        modalStart: false,
        modalEnd: false,
        conferenceID: "",
        eventID: "",
        eventTimeUK: "",
        timeZone: "",
        conferenceLock: "",
        conferenceTitle: 'Conference '+ (this.eventSchedule.length+1),
        PAC: "",
        HAC: "",
        LBConfID: "",
        maxParticipants: "",
        recordAudio: "",
        expiryDate: ""
      });

      if (saveToDB) {
        let eventSchedule = this.eventSchedule;
        let eventData = this.eventData;

        this.$store
          .dispatch("bookConferences", { eventSchedule, eventData })
          .then(result => {
            console.log(result);
            this.eventSchedule = result.data;
            this.conferences = result.data;
            console.log(this.isConferencesBooked)
          })
          .catch(err => console.log(err));
      }

      this.setAutoConferenceTimes()
    },

    deleteConference(index) {
      this.eventSchedule.splice(index, 1);
    },

    editStartTime(item) {
      this.editScheduleIndex = this.eventSchedule.indexOf(item);
      this.editSchedule = Object.assign({}, item);
      this.editmodalStart = true;
    },

    saveStartTime(newval) {
      if (this.editScheduleIndex > -1) {
        this.eventSchedule[this.editScheduleIndex].startTime = newval;
        var x = this.$moment(
          "12-25-1995 " + this.eventSchedule[this.editScheduleIndex].startTime,
          "MM-DD-YYYY HH:mm"
        );
        var y = this.$moment(
          "12-25-1995 " + this.eventSchedule[this.editScheduleIndex].endTime,
          "MM-DD-YYYY HH:mm"
        );
        var duration = this.$moment.duration(y.diff(x));
        this.eventSchedule[this.editScheduleIndex].duration = duration;

        var lasty = this.$moment(
          "12-25-1995 " +
            this.eventSchedule[this.eventSchedule.length - 1].endTime,
          "MM-DD-YYYY HH:mm"
        );
        this.startTime = this.eventSchedule[
          this.eventSchedule.length - 1
        ].endTime;
        this.endTime = lasty
          .add(this.eventSchedule[this.eventSchedule.length - 1].duration)
          .format("HH:mm");
      }
      this.editmodalStart = false;
      this.editSchedule.startTime = "";
      this.editScheduleIndex = -1;
    },

    editEndTime(item) {
      this.editScheduleIndex = this.eventSchedule.indexOf(item);
      this.editSchedule = Object.assign({}, item);
      this.editmodalEnd = true;
    },

    saveEndTime(newval) {
      if (this.editScheduleIndex > -1) {
        this.eventSchedule[this.editScheduleIndex].endTime = newval;
        var x = this.$moment(
          "12-25-1995 " + this.eventSchedule[this.editScheduleIndex].startTime,
          "MM-DD-YYYY HH:mm"
        );
        var y = this.$moment(
          "12-25-1995 " + this.eventSchedule[this.editScheduleIndex].endTime,
          "MM-DD-YYYY HH:mm"
        );
        var duration = this.$moment.duration(y.diff(x));
        this.eventSchedule[this.editScheduleIndex].duration = duration;

        this.setAutoConferenceTimes();
      }
      this.editmodalEnd = false;
      this.editSchedule.endTime = "";
      this.editScheduleIndex = -1;
    },

    setAutoConferenceTimes() {
      var lastx = this.$moment(
        "12-25-1995 " +
          this.eventSchedule[this.eventSchedule.length - 1].startTime,
        "MM-DD-YYYY HH:mm"
      );
      var lasty = this.$moment(
        "12-25-1995 " +
          this.eventSchedule[this.eventSchedule.length - 1].endTime,
        "MM-DD-YYYY HH:mm"
      );
      var duration = this.$moment.duration(lasty.diff(lastx));
      console.log("LastStart:" + lastx);
      console.log("LastEnd:" + lasty);
      console.log("LastDuration:" + duration);
      console.log(this.eventSchedule[this.eventSchedule.length - 1].duration);
      this.startTime = this.eventSchedule[
        this.eventSchedule.length - 1
      ].endTime;
      this.endTime = lasty.add(duration).format("HH:mm");
    },

    addSpeaker() {
      if (this.speaker!="") {
        this.eventData.speakers.push(this.speaker);
        this.$store.commit("editEvent",this.eventData);
      }
      this.speaker = "";
    },

    deleteSpeaker(index) {
      this.eventData.speakers.splice(index, 1);
    },

    deleteBookedConference: function (item) {
      const _this = this
      const _deleteList = []

        confirm("Are you sure you want to delete this item?") &&
            _deleteList.push({conferenceID:this.conferences[item].conferenceID})

          if (_deleteList.length>0) {
            console.log("Committing chage to DB");
            console.log(_deleteList)

            axios({
              url: "http://netroadshow.incommglobalevents.com/api/delete_conference.php",
              data: { forDeletion: _deleteList  },
              method: "POST"
            })
              .then(resp => {
                console.log("Deletion Successful");
                console.log(resp);

                _this.conferences.splice(item, 1);

                let eventSchedule = this.conferences;

                this.$store.commit('stashConferences',eventSchedule)

              })
              .catch(err => {
                console.log("Deletion error" + err);
              });

          }

    },

    updateConference: function (item, newConfData) {
      console.log("New Data")
      console.log(newConfData)
      this.conferences[item]=newConfData
      this.conferences[item].requestUpdate = true

      this.setAutoConferenceTimes()

      let eventSchedule = this.conferences;
      let eventData = this.eventData

      this.$store
        .dispatch("bookConferences", { eventSchedule, eventData })
        .then(result => {
          console.log("OK");
          console.log(result);
          this.conferences = result.data
        })
        .catch(err => {
          console.log("ERROR")
          console.log(err)
          this.onError = true
          this.errorMessage = err.data.status
        });
    }
  }
};
</script>
