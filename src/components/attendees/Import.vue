<template>
  <v-container>
    <div class="container">
      <v-layout align-center justify-center pa-0 ma-0 pt-3>
        <v-flex sm12 tag="h1" color="primary" text-left
          >Import and invite list</v-flex
        >
      </v-layout>
        <section class="py-5">

            <div class="row mt-5">
                <div class="col-8 offset-2">
                    <vue-csv-import
                          v-model="csv"

                          :map-fields="{firstName: 'First Name', lastName: 'Last Name', emailAddress: 'Email Address', areaCode: 'Area code', contactNumber:'Phone', companyName:'Company', priority:'Priority' }"

                        >

                          <template slot="next" slot-scope="{load}">

                              <v-btn
                                color="secondary"
                                rounded
                                dark
                                class="mb-2"
                                @click.prevent="load"
                                >Load File</v-btn
                              >
                          </template>

                          <template slot="submit" slot-scope="{submit}">

                              <v-btn
                                color="secondary"
                                rounded
                                dark
                                class="mb-2"
                                @click.prevent="submit"
                                >Evaluate</v-btn
                              >
                          </template>

                      </vue-csv-import>

                    <div class="mt-2" v-if="csv">

                      <h2 > Sample</h2>

                      <v-simple-table class="mt-6" >
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>First name</th>
                            <th>Last name</th>
                            <th>Email address</th>
                            <th>Phone</th>
                            <th>Company</th>
                            <th>Priority</th>
                          </tr>
                        </thead>
                          <tbody>
                            <tr v-for="(item) in Math.min(5, csv.length)" v-bind:key="'cvs_' + item">
                                <td>{{item}}</td>
                              <td>{{csv[item-1].firstName}}</td>
                                <td>{{csv[item-1].lastName}}</td>
                                <td>{{csv[item-1].emailAddress}}</td>
                                <td>{{csv[item-1].areaCode}} {{csv[item-1].contactNumber}}</td>
                                <td>{{csv[item-1].companyName}}</td>
                                <td>{{csv[item-1].priority}}</td>
                            </tr>
                          </tbody>
                      </v-simple-table>
                      <div class="text-center mt-6">
                        <v-btn
                          color="secondary"
                          rounded
                          dark
                          class="mb-2"
                          @click.prevent="importCSV()"
                          >Import</v-btn>
                      </div>
                    </div>
                </div>
            </div>
        </section>
    </div>



  </v-container>
</template>

<script>
export default {
  name: "Import",
  data() {
      return {
          csv: null,
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
            invitationAccepted: "",
            allocationStatus:"Unconfirmed"
          },
      };
  },
  methods: {
    importCSV(){
      let attendeeData = this.csv
      let eventData = {eventID:this.$store.getters.currentEvent}

      console.log(eventData)

      this.$store
        .dispatch("importCSVData", {attendeeData, eventData})
        .then(result => {
          console.log(result);
            this.$router.push("/attendees");
        })
        .catch(err => console.log(err));
    }
  }
};
</script>

<style scoped></style>
