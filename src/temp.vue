<v-app-bar
  app
  clipped-right
  color="white"
  height="auto"
  tile
  hide-on-scroll
  id="mainmasthead"
>
  <template v-slot="extension" class="pa-0 ma-0">
    <v-layout column class="pa-0 ma-0">
      <div id="masthead">
        <v-container pa-0 my-0>
          <v-layout align-center pa-0 ma-0>
            <v-flex pa-6 xs6 text-sm-left>
              <img src="./assets/incomm_logo.png" class="incomm_logo"/>
              <span class="strapline">Open table conferencing</span>
            </v-flex>
            <v-flex pa-6 xs6 text-sm-right>
              <img src="./assets/netroadshow.png" class="nrs_logo"/>
            </v-flex>
          </v-layout>
        </v-container>
      </div>
      <div class="py-0" id="navbar" v-if="isLoggedIn">
        <v-container pa-0 my-0>
          <v-layout pl-6 pr-6 v-if="isLoggedIn" align-center>
            <v-flex xs6 text-sm-left color="white">
							<span class="navbut">
								<v-btn text dark medium rounded outlined min-height="40" color="#fff" to="/events">Events</v-btn>
							</span>
              <span class="navbut">
								<v-btn text dark medium rounded outlined min-height="40" color="#fff" to="/attendees">Attendees</v-btn>
							</span>
              <span class="navbut" v-if="inArray(userGroup,[1,2])">
								<v-btn text dark medium rounded outlined min-height="40" color="#fff" to="/users">Users</v-btn>
							</span>
              <span class="navbut" v-if="inArray(userGroup,[1,2])">
								<v-btn text dark medium rounded outlined min-height="40" color="#fff" to="/companies">Companies</v-btn>
							</span>
            </v-flex>
            <v-flex xs6 text-sm-right>
              <span class="current-user text-white""><strong>{{ curUser }} ({{userGroup}})</strong> | {{ userCompany }}
              <v-menu offset-y>
                <template v-slot:activator="{ on }">
                  <v-btn icon v-on="on" dark>
                    <v-icon>mdi-menu</v-icon>
                  </v-btn>
                </template>
                <span>
										<v-list v-if="isLoggedIn">
											<v-list-item>
												<v-list-item-title @click="password">Reset Password</v-list-item-title>
											</v-list-item>

											<v-list-item>
												<v-list-item-title @click="logout">Logout</v-list-item-title>
											</v-list-item>
										</v-list>
									</span>
              </v-menu>
              <v-app-bar-nav-icon @click.stop="drawer = !drawer"></v-app-bar-nav-icon>
              </span>
            </v-flex>
          </v-layout>
        </v-container>
      </div>
    </v-layout>
  </template>
</v-app-bar>

<v-content>
  <v-container fluid pa-0 ma-0>
    <router-view/>
  </v-container>
</v-content>

<v-footer app height="215">
  <v-flex text-center xs12>
    <div class="caption font-weight-light">
      Copyright �{{ new Date().getFullYear() }} In Communication Limited.All rights reserved.
    </div>
  </v-flex>
</v-footer>

<v-navigation-drawer
  v-model="drawer"
  app
  clipped
  right
>
  <v-list dense>
    <v-list-item @click="">
      <v-list-item-action>
        <v-icon>home</v-icon>
      </v-list-item-action>
      <v-list-item-content>
        <v-list-item-title>Home</v-list-item-title>
      </v-list-item-content>
    </v-list-item>
    <v-list-item @click="">
      <v-list-item-action>
        <v-icon>contact_mail</v-icon>
      </v-list-item-action>
      <v-list-item-content>
        <v-list-item-title>Contact</v-list-item-title>
      </v-list-item-content>
    </v-list-item>
  </v-list>
</v-navigation-drawer>
