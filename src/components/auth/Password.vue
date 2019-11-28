<template>
  <v-container py-0>
    <v-layout align-center justify-end ma-0 pa-0>
      <v-flex align-center fill-height justify-center row sm6>
        <v-card color="transparent" flat max-width="600" text-left>
          <v-list-item text-left>
            <v-layout align-left column justify-end ma-0 pa-0 text-left>
              <v-flex
                color="primary"
                d-block
                py-6
                tag="h1"
                text-left
                v-if="isResetRequest"
                >Reset password
              </v-flex>
              <v-flex color="primary" d-block py-6 tag="h1" text-left v-else
                >Create a password
              </v-flex>

              <p>
                Please create a password unique to you (please do not share your
                account access).
              </p>
              <p class="font-weight-bold">Your new password must contain:</p>
              <ul>
                <li>A minimum of 1 lower case letter [a-z] and</li>
                <li>A minimum of 1 upper case letter [A-Z] and</li>
                <li>A minimum of 1 numeric character [0-9] and</li>
                <li>
                  A minimum of 1 special character:
                  ~`!@#$%^&*()-_+={}[]|\;:"<>,./?
                </li>
              </ul>
            </v-layout>
          </v-list-item>

          <v-list-item text-left>
            <v-flex sm12>
              <v-layout max-width="400">
                <v-flex sm11>
                  <v-form
                    @submit.prevent="setpassword"
                    class="login"
                    max-width="600"
                    ref="resetPassword"
                  >
                    <v-list-item pa-0>
                      <v-text-field
                        height="67"
                        background-color="#f6f6f6"
                        :type="showPassword1 ? 'text' : 'password'"
                        v-model="new_password"
                        :rules="passwordRules"
                        required
                        id="password"
                        label="Password"
                        name="password"
                      >
                        <div slot="prepend-inner" class="ma-2"></div>
                        <v-icon
                          slot="append"
                          color="secondary"
                          class="ma-4"
                          @click.stop="showPassword1 = !showPassword1"
                          >{{ showpasswordIcon1 }}</v-icon
                        >
                      </v-text-field>
                    </v-list-item>

                    <v-list-item>
                      <v-text-field

                        background-color="#f6f6f6"
                        height="67"
                        id="confirm_password"
                        label="Re-type password"
                        name="confirm-password"
                        :rules="passwordMatchRules"
                        required
                        :type="showPassword2 ? 'text' : 'password'"
                        v-model="confirm_password"
                      ><div slot="prepend-inner" class="ma-2"></div>
                      <v-icon
                        slot="append"
                        color="secondary"
                        class="ma-4"
                        @click.stop="showPassword2 = !showPassword2"
                        >{{ showpasswordIcon2 }}</v-icon
                      ></v-text-field>
                    </v-list-item>

                    <!--
                  <v-list-item >
                    <v-menu v-model="menu2"  :close-on-content-click="false" :nudge-right="40" transition="scale-transition" offset-y full-width min-width="290px">
                      <template v-slot:activator="{ on }">
                        <v-text-field v-model="date" label="Picker without buttons" prepend-icon="event" readonly v-on="on"></v-text-field>
                      </template>
                      <v-date-picker v-model="date" @input="menu2 = false"></v-date-picker>
                    </v-menu>
                  </v-list-item>
                  -->
                    <v-list-item>
                      <v-spacer></v-spacer>
                      <v-btn
                        color="secondary"
                        minHeight="40"
                        minWidth="150"
                        rounded
                        type="submit"
                        >Set Password
                      </v-btn>
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
          aspect-ratio="0.75"
          class="grey lighten-2"
          max-height="700"
        ></v-img>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
import Vue from "vue";
import VeeValidate from "vee-validate";
import zxcvbn from 'zxcvbn'

export default {
  name: "Password",
  props: {
    source: String
  },
  data() {
    return {
      new_password: "",
      confirm_password: "",
      showPassword1: false,
      showPassword2: false,
      errorMessage:"",
      onError: false,
      nameRules: [
        v => !!v || "This is required",
        v => (v && v.length <= 50) || "Must be less than 50 characters"
      ],
      passwordMatchRules: [
        v => !!v || "This is required",
        v => (v &&  this.new_password === this.confirm_password) || 'Password must match'
      ],
      passwordRules: [
        v => !!v || "This is required",
        v => (v && v.length >= 6) || 'Password must be min 8 characters long',
        v => (v &&  zxcvbn(this.new_password).score>2) || 'Password not strong enough',
        v => /(?=.*\d)(?=.*[a-z])(?=.*[~\`!@#$%^&*()\-_+={}\[\];;:"<>\\|,\.\/\?])(?=.*[A-Z]).{6,}/.test(v) ||
          "Password must contaiin at least one number, one lowercase and one uppercase letter and one special character"
//        ~`!@#$%^&*()-_+={}[]|\;:"<>,./?
      ],
    };
  },
  computed: {
    isResetRequest: function() {
      return this.$store.getters.isResetRequest;
    },
    backgroundMainColor() {
      return "background:" + this.editedItem.primaryColor;
    },
    showpasswordIcon1() {
      if (this.showPassword1) return "mdi-eye";
      else return "mdi-eye-off";
    },
    showpasswordIcon2() {
      if (this.showPassword2) return "mdi-eye";
      else return "mdi-eye-off";
    }
  },

  methods: {
    setpassword: function() {
      let result = this.$refs['resetPassword'].validate();
      console.log(zxcvbn(this.new_password))
      console.log(result);

      if (result) {
        console.log("SET Password IN");
        console.log(this.new_password);
        let new_password = this.new_password;
        let confirm_password = this.confirm_password;
        let userid = this.$store.getters.userID;
        this.$store
          .dispatch("setpassword", { userid, new_password, confirm_password })
          .then(() => this.$router.push("/events"))
          .catch(err => console.log(err));
      }
    }
  }
};
</script>
