import Vue from "vue";
import Router from "vue-router";
import store from "./store.js";
import Login from "./components/auth/Login.vue";
import Password from "./components/auth/Password.vue";
import Forgotten from "./components/auth/Forgotten.vue";

import Invite from "./components/investors/Invite.vue";
import TimeSelect from "./components/investors/TimeSelect.vue";
import ThankYou from "./components/investors/ThankYou.vue";

import Events from "./components/events/Events.vue";
import NewEvent from "./components/events/CreateEvent.vue";
import Viewer from "./components/events/Viewer.vue";

import Attendees from "./components/attendees/Attendees.vue";
import ImportInvestors from "./components/attendees/Import.vue";
import importCSV from "./components/attendees/ImportAssign.vue";
import Companies from "./components/companies/Companies.vue";
import Users from "./components/users/Users.vue";

Vue.use(Router);

let router = new Router({
  mode: "history",
  routes: [
    {
      path: "/",
      name: "home",
      component: Login
    },

    {
      path: "/password",
      name: "password",
      component: Password
    },

    {
      path: "/forgotten",
      name: "forgotten",
      component: Forgotten,
      beforeRouteLeave(to, from, next) {
        // called when the route that renders this component is about to
        // be navigated away from.
        // has access to `this` component instance.
        console.log("Going" + to + " from " + from + ": " + next);
      }
    },
    {
      path: "/login",
      name: "login",
      component: Login
    },
    {
      path: "/suggestinvitees/",
      name: "suggestinvitees",
      component: Invite,
      props: (route) => ({
        e: route.query.e,
      })
    },
    {
      path: "/timeselect",
      name: "timeselect",
      component: TimeSelect,
      props: (route) => ({
        e: route.query.e,
      })
    },
    {
      path: "/thankyou",
      name: "thankyou",
      component: ThankYou,
      props: (route) => ({
        e: route.query.e,
      })
    },

    {
      path: "/users",
      name: "userlist",
      component: Users,
      meta: {
        requiresAuth: true
      }
    },
    {
      path: "/attendees",
      name: "attendees",
      component: Attendees,
      meta: {
        requiresAuth: true
      }
    },
    {
      path: "/importinvestors",
      name: "importinvestors",
      component: ImportInvestors,
      meta: {
        requiresAuth: true
      }
    },
    {
      path: "/importCSV",
      name: "importCSV",
      component: importCSV,
      meta: {
        requiresAuth: true
      }
    },
    {
      path: "/events",
      name: "events",
      component: Events,
      meta: {
        requiresAuth: true
      }
    },
    {
      path: "/create-event",
      name: "creat-eevent",
      component: NewEvent,
      meta: {
        requiresAuth: true
      }
    },

    {
      path: "/viewer",
      name: "viewer",
      component: Viewer,
      meta: {
        requiresAuth: true
      }
    },
    {
      path: "/companies",
      name: "companies",
      component: Companies,
      meta: {
        requiresAuth: true
      }
    }
  ]
});

router.beforeEach((to, from, next) => {
  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (store.getters.isLoggedIn) {
      next();
      return;
    }
    next("/login");
  } else {
    next();
  }
});

export default router;
