import "@mdi/font/css/materialdesignicons.css";
import Vue from "vue";
import Vuetify from "vuetify/lib";

Vue.use(Vuetify);

export default new Vuetify({
  icons: {
    iconfont: "mdi"
  },
  css: {
    loaderOptions: {
      sass: {
        data: `@import "~@/sass/main.scss"`,
      },
    },
  },
  data() {
    return {
      picker: new Date().toISOString().substr(0, 10),
      picker2: new Date().toISOString().substr(0, 10)
    };
  },
  theme: {
    themes: {
      light: {
        primary: "#335886",
        secondary: "#00aeef",
        accent: "#f45d4e",
        error: "#f45d4e",
        warning: '#e91e63',
        info: '#ff9800',
        success: '#8bc34a'
      }
    }
  }
});
