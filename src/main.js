require("es6-promise/auto");

import '@babel/polyfill'
import Vue from "vue";
import "./plugins/axios";
import App from "./App.vue";
import router from "./router";
import store from "./store";
import Axios from "axios";
import vuetify from "./plugins/vuetify";
import VueCsvImport from "./components/attendees/VueCsvImport.vue";
import ConferenceTable from "./components/events/ConferenceTable.vue";
import ViewerTable from "./components/events/ViewerTable.vue";
import {
    AST,
    RegExpParser,
    RegExpValidator,
    RegExpVisitor,
    parseRegExpLiteral,
    validateRegExpLiteral,
    visitRegExpAST
} from "regexpp"

var VueRouter = require('vue-router')
var jwt = require('jsonwebtoken');

const VueCsvImportPlugin = {
    install(Vue, options = {}) {
        Vue.component(options.componentName || 'VueCsvImport', VueCsvImport);
    }
};

if (typeof window !== undefined && window.Vue && window.Vue === Vue) {
    VueCsvImportPlugin.install(window.Vue);
}

export { VueCsvImport, VueCsvImportPlugin };

Vue.use(VueCsvImportPlugin);
/*
const ConferenceTablePlugin = {
    install(Vue, options = {}) {
        Vue.component(options.componentName || 'ConferenceTable', ConferenceTable);
    }
};
if (typeof window !== undefined && window.Vue && window.Vue === Vue) {
    ConferenceTablePlugin.install(window.Vue);
}

export { ConferenceTable, ConferenceTablePlugin };

Vue.use(ConferenceTablePlugin);
*/

Vue.prototype.$http = Axios;
const token = localStorage.getItem("token");
if (token) {
  Vue.prototype.$http.defaults.headers.common["Authorization"] = token;
}
const HelloMixin = {
  methods: {
    hello() {
      console.log("hi");
    }
  }
};

Vue.config.productionTip = false;

Vue.use(require("vue-moment"));

new Vue({
  data: {
    // declare stuff with an empty value

    menu2: "",
    date: new Date().toISOString().substr(0, 10),

    errors: false,
    errorMessage: null
  },
  mixins: [HelloMixin],
  created() {
    this.hello();
  },
  methods: {},
  router,
  store,
  vuetify,
  render: h => h(App)
}).$mount("#app");
