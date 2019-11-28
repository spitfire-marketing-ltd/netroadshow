<template>
  <div class="col-sm-6 offset-sm-3">
    <h4>Register</h4>
    <form @submit.prevent="register">
      <div class="form-group">
        <label for="name">Name</label>
        <div>
          <input id="name" type="text" v-model="name" required autofocus />
        </div>
      </div>
      <div class="form-group">
        <label for="email">E-Mail Address</label>
        <div>
          <input id="email" type="email" v-model="email" required />
        </div>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <div>
          <input id="password" type="password" v-model="password" required />
        </div>
      </div>
      <div class="form-group">
        <label for="password-confirm">Confirm Password</label>
        <div>
          <input
            id="password-confirm"
            type="password"
            v-model="password_confirmation"
            required
          />
        </div>
      </div>
      <div class="form-group">
        <button type="submit" class="btn btn-primary">
          <span>Register</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script>
export default {
  data() {
    return {
      name: "",
      email: "",
      password: "",
      password_confirmation: ""
    };
  },
  methods: {
    register: function() {
      let data = {
        name: this.name,
        email: this.email,
        password: this.password
      };
      this.$store
        .dispatch("register", data)
        .then(() => this.$router.push("/"))
        .catch(err => console.log(err));
    }
  }
};
</script>
