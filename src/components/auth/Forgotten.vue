<template>
  <v-container py-0>
    <v-layout align-center justify-end pa-0 ma-0>
      <v-flex sm6 align-center justify-center row fill-height>
        <v-card flat max-width="600" color="transparent" text-left>
          <v-list-item text-left>
            <v-layout align-left justify-end pa-0 ma-0 column text-left>
              <v-flex tag="h1" py-6 color="primary">Recover password</v-flex>
              <p>Enter your email address and a new password will be sent.</p>
            </v-layout>
          </v-list-item>

          <v-list-item text-left>
            <v-flex sm12>
              <v-layout max-width="400">
                <v-flex sm12>
                  <v-form
                    class="login"
                    max-width="600"
                    @submit.prevent="forgotten"
                  >
                    <v-list-item pa-0>
                      <v-text-field
                        height="67"
                        background-color="#f6f6f6"
                        v-model="email"
                        label="Enter your email address"
                        name="email"
                        type="text"
                        :rules="[v => !!v || 'Email address is required']"
                      ></v-text-field>
                    </v-list-item>

                    <v-list-item>
                      <v-spacer></v-spacer>
                      <v-tooltip bottom color="success">
                        <template v-slot:activator="{ on }">
                          <v-btn
                            rounded
                            color="secondary"
                            minHeight="40"
                            minWidth="150"
                            v-on="on"
                            type="submit"
                            >Reset</v-btn
                          >
                        </template>
                        <span color="success" class="white--text"
                          >Click here to request a new your password</span
                        >
                      </v-tooltip>
                    </v-list-item>
                  </v-form>
                </v-flex>
              </v-layout>
            </v-flex>
          </v-list-item>
        </v-card>
      </v-flex>

      <v-flex sm6>
        <v-img
          :src="require('../../assets/login_splash.jpg')"
          aspect-ratio="1"
          max-height="700"
          class="grey lighten-2"
        ></v-img>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
import Vue from "vue";
import VeeValidate from "vee-validate";

export default {
  data() {
    return {
      email: ""
    };
  },
  computed: {
    authStatus: function() {
      return this.$store.getters.authStatus;
    }
  },
  methods: {
    forgotten: function() {
      console.log("RECOVER LOGIN");
      console.log(this.email);

      let email = this.email;

      this.$store

        .dispatch("recover", { email })
        .then(resp => {
            console.log(resp)
            this.$router.push("/login")
        })
        .catch(err => console.log(err));
    }
  }
};
</script>
