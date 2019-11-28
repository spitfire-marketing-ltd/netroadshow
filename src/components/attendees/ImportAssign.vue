<template>
  <v-container>
    <v-layout align-center justify-center pa-0 ma-0 pt-3>
      <v-flex sm12 tag="h1" color="primary" text-left
        >Assign your imported data</v-flex
      >
    </v-layout>

    <v-row no-gutters>
      <v-col cols="sm">
        <v-flex align-center pa-0 ma-0 pt-3 column full-width>
          <template v-for="(item, key, index) in CSVimportData">
            <v-row mb-3>
              <v-col cols="12" md="12">
                <v-simple-table width="100%" class="theme-nrs">
                  <thead>
                    <tr>
                      <th class="text-left" width="50%">
                        Column A - {{ key }}
                      </th>
                      <th class="text-left" width="50%">Assign to...</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>{{ item }}</td>
                      <td>
                        <v-autocomplete
                          :items="importHeadings"
                          item-text="name"
                          item-value="name"
                        >
                          <template v-slot:selection="data">
                            <v-list-item>
                              {{ data.item.name }}
                            </v-list-item>
                          </template>

                          <template v-slot:item="data">
                            <template v-if="typeof data.item !== 'object'">
                              <v-list-item-content
                                v-text="data.item"
                              ></v-list-item-content>
                            </template>
                            <template v-else>
                              <v-list-item-content>
                                <v-list-item-title
                                  v-html="data.item.name"
                                ></v-list-item-title>
                              </v-list-item-content>
                            </template>
                          </template>

                          <template v-slot:append-item>
                            <v-list-item>
                              <v-icon class="mdi-24px" color="secondary" left
                                >mdi-plus</v-icon
                              >
                              Create new label
                            </v-list-item>
                          </template>
                        </v-autocomplete>
                      </td>
                    </tr>
                  </tbody>
                </v-simple-table>
              </v-col>
            </v-row>
          </template>
          <v-row>
            <v-col class="text-center">
              <v-btn color="secondary" rounded dark class="mb-2" to="/attendees"
                >Import</v-btn
              >
            </v-col>
          </v-row>
        </v-flex>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
export default {
  name: "ImportAssign",

  data: () => ({
    CSVimportData: {
      firstname: "John",
      lastname: "Smith",
      email: "jsmith@aol.com",
      area: "+44",
      phone: "02380442640",
      company: "Merril",
      Department: "Consumer Lending"
    },

    importHeadings: [
      { name: "First name", fieldname: "firstname" },
      { name: "Last name", fieldname: "lastname" },
      { name: "Email address", fieldname: "email" },
      { name: "Area code", fieldname: "areacode" },
      { name: "Phone", fieldname: "phone" },
      { name: "Company", fieldname: "company" },
      { name: "Department", fieldname: "department" },
      { divider: true },
      { name: "Nothing (ignore)", fieldname: "firstname" }
    ]
  })
};
</script>

<style scoped></style>
