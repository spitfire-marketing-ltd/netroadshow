<template>
  <v-container py-0>
    <v-layout align-center justify-end pa-0 ma-0>
      <v-flex sm6 align-center justify-center row fill-height>
        <v-card flat max-width="600" color="transparent" text-left>
          <v-list-item text-left>
            <v-flex tag="h1" py-6 color="primary"
              >Your secure, open table conference facility</v-flex
            >
          </v-list-item>

          <v-list-item text-left>
            <v-flex sm12>
              <v-layout max-width="400">
                <v-flex sm11>
                  <v-form class="login" @submit.prevent="login">
                    <v-list-item pa-0>
                      <v-text-field
                        height="67"
                        background-color="#f6f6f6"
                        v-model="email"
                        label="Username"
                        name="email"
                        type="text"
                        :rules="[v => !!v || 'Username is required']"
                      >
                        <div slot="prepend-inner" class="ma-2"></div>
                      </v-text-field>
                    </v-list-item>

                    <v-list-item>
                      <v-text-field
                        height="67"
                        background-color="#f6f6f6"
                        :type="showPassword ? 'text' : 'password'"
                        v-model="password"
                        id="password"
                        label="Password"
                        name="password"
                      >
                        <div slot="prepend-inner" class="ma-2"></div>
                        <v-icon
                          slot="append"
                          color="secondary"
                          class="ma-4"
                          @click.stop="showPassword = !showPassword"
                          >{{ showpasswordIcon }}</v-icon
                        >
                      </v-text-field>
                    </v-list-item>

                    <v-list-item>
                      <v-spacer></v-spacer>
                      <v-btn
                        rounded
                        color="secondary"
                        minHeight="40"
                        minWidth="150"
                        type="submit"
                        >Login</v-btn
                      >
                    </v-list-item>
                  </v-form>
                </v-flex>
              </v-layout>
            </v-flex>
          </v-list-item>
          <v-list-item text-left>
            <v-flex tag="p" py-6 color="grey"
              ><router-link to="/forgotten"
                >Forgot your password?</router-link
              ></v-flex
            >
          </v-list-item>
          <v-list-item >
            <v-flex sm11>
              <v-alert  v-model="onError" dismissible type="error">{{errorMessage}}</v-alert>
            </v-flex>
          </v-list-item>
        </v-card>
      </v-flex>
      <v-flex sm6>
        <v-img
          :src="require('../../assets/login_splash.jpg')"
          aspect-ratio=".75"
          max-height="700"
          class="grey lighten-2"
        ></v-img>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
export default {
  data() {
    return {
      email: "",
      password: "",
      showPassword: false,
      errorMessage:"",
      onError: false
    };
  },
  computed: {
    backgroundMainColor() {
      return "background:" + this.editedItem.primaryColor;
    },
    showpasswordIcon() {
      if (this.showPassword) return "mdi-eye";
      else return "mdi-eye-off";
    }
  },
  methods: {
    login: function() {
      console.log("LOGGING IN");
      console.log(this.email);
      let email = this.email;
      let password = this.password;
      this.$store
        .dispatch("login", { email, password })
        .then(result => {
          console.log("Login OK");


          console.log(result.data.first_time);
          if (result.data.first_time) {
            console.log("Routing to password");
            this.$router.push("/password");
          } else {
            console.log(result.data.reset_required);

            if (result.data.reset_required) {
              console.log("Routing to reset password");
              this.$store.dispatch("resetrequest").then(() => {
                this.$router.push("/password");
              });
            } else {
              console.log("No Change to PW");
              this.$router.push("/events");
            }
          }
        })
        .catch(err => {
          console.log(err)
          this.onError = true
          this.errorMessage = err.data.status
        });
    }
  }
};
</script>
