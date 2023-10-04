<template>
  <v-app id="inspire">
    <v-content>
      <v-container
        class="fill-height"
        fluid
      >
        <v-layout
          align-center
          justify-center
          row
          wrap
        >
        <v-flex xs12 sm8 md4>
            <!-- <v-img
                        src="../assets/img/logo-mph.png"
                        aspect-ratio="1.4"
                        width="50%"
                        class=""
                    ></v-img> -->
        </v-flex>
          <v-flex
            xs12 sm8 md4
          >
            <v-card class="elevation-12">
              <v-toolbar
                class="zalfa-bg-purple"
                dark
                flat
              >
                <v-toolbar-title>Login form</v-toolbar-title>
                <v-spacer />
                
                <v-tooltip right>
                  <template v-slot:activator="{ on }">
                    <v-btn
                      icon
                      large
                      href="https://codepen.io/johnjleider/pen/pMvGQO"
                      target="_blank"
                      v-on="on"
                    >
                      <v-icon>mdi-codepen</v-icon>
                    </v-btn>
                  </template>
                  <span>Codepen</span>
                </v-tooltip>
              </v-toolbar>
              <v-card-text>
                <v-form>
                    
                    <v-alert
                        :value="error"
                        type="error"
                        class="mb-4"
                        transition="slide-x-transition"
                        >
                        {{ error_message }}
                    </v-alert>

                  <v-text-field
                    label="Username"
                    prepend-icon="person"
                    type="text"
                    v-model="username"
                    @keyup="do_login($event)"
                  ></v-text-field>

                  <v-text-field
                    label="Password"
                    prepend-icon="lock"
                    :type="show1 ? 'text' : 'password'"
                    v-model="password"
                    @keyup="do_login($event)"
                    :append-icon="show1 ? 'visibility' : 'visibility_off'"
                    @click:append="show1 = !show1"
                  ></v-text-field>
                </v-form>
              </v-card-text>
              <v-card-actions>
                <v-spacer />
                <v-btn color="primary" @click="login">Login</v-btn>
              </v-card-actions>
            </v-card>
          </v-flex>
        </v-layout>
      </v-container>
    </v-content>
  </v-app>
</template>

<script>
module.exports = {
    data () {
        return  {
            show1: false
        }
    },
    
    computed : {
        username : {
            get () { return this.$store.state.login.username },
            set (v) { this.$store.commit('login/set_username', v) }
        },

        password : {
            get () { return this.$store.state.login.password },
            set (v) { this.$store.commit('login/set_password', v) }
        },

        error : {
            get () { return this.$store.state.login.error },
            set (v) { this.$store.commit('login/set_error', v) }
        },

        error_message : {
            get () { return this.$store.state.login.error_message },
            set (v) { this.$store.commit('login/set_error_message', v) }
        }
    },

    methods : {
        login () {
            this.$store.dispatch('login/login')
        },

        do_login (e) {
            if (e.which == 13) {
                this.login()
            }
        }
    }
}
</script>