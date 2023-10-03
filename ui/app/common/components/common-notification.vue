<template>
    <v-navigation-drawer
        v-model="drawer"
        fixed app right
        temporary
        :value="false"
        :dark="false"
        style='background: linear-gradient(rgba(200, 200, 200, 0.8), rgba(200, 200, 200, 0.8));' 
    >
        <v-list three-line class="pt-0">
            <v-subheader class="primary white--text"
            >
              <v-icon dark class="mr-2">notifications</v-icon> NOTIFIKASI
            </v-subheader>
          <template v-for="(item, index) in items">

            <v-divider
              :key="'d'+index"
              :inset="false"
            ></v-divider>

            <v-list-tile
              :key="item.notif_id"
              avatar
              dark
              :class="item.notif_read=='N'?'notif_unread purple lighten-4':''"
              @click="notif_act(item)"
            >
              <v-list-tile-avatar class="pt-1">
                  <v-divider vertical class="divider-message"></v-divider>
                <!-- <img :src="'https://cdn.vuetifyjs.com/images/lists/1.jpg'"> -->
              </v-list-tile-avatar>

              <v-list-tile-content>
                <!-- <v-list-tile-title v-html="item.title"></v-list-tile-title> -->
                <v-list-tile-sub-title v-html="item.notif_msg"></v-list-tile-sub-title>
                <v-list-tile-sub-title v-html="item.notif_time" class="caption"></v-list-tile-sub-title>
              </v-list-tile-content>
            </v-list-tile>
          </template>
        </v-list>
    </v-navigation-drawer>
</template>

<style scoped>
.notif_unread {
    color: red;
}

.theme--light.v-list .v-list__tile__sub-title {
    color: rgba(0,0,0,1);
}

.v-list__tile__avatar {
    min-width: auto;
}

.v-avatar {
    width: 20px !important;
}

.divider-message {
    border-width: 3px;
}
</style>
<script>
module.exports = {
    data () {
        return {
            
        }
    },

    computed : {
        drawer : {
            get () { return this.$store.state.system.drawer_notif },
            set (v) { this.$store.commit('system/set_drawer_notif', v) }
        },

        items () {
            return this.$store.state.system.notif_messages
        },

        unread () {
            return this.$store.state.system.notif_unread
        },

        md5 () {
            return this.$store.state.system.notif_md5
        },

        wscon : {
            get () { return this.$store.state.system.wscon },
            set (v) { this.$store.commit('system/set_wscon', v) }
        },

        sound () {
            return this.$store.state.system.sound
        },

        muted : {
            get () { return this.$store.state.system.notif_muted },
            set (v) { this.$store.commit('system/set_notif_muted', v) }
        }
    },

    methods : {
        send_message (x) {
            this.wscon.send(x)
        },

        notif_act (x) {
            if (x.notif_action == "GOTO")
                window.location.replace("../"+x.notif_action_prop)
            return
        }
    },

    watch : {
        drawer (v, o) {
            if (v && !o && this.unread > 0) {
                this.$store.dispatch('system/set_notif_read')
            }
            
            
        },

        unread (v, o) {
        }
    },

    created () {
        // console.log("Notificaton Created")
        console.log("OneSocket started")
        var store = this.$store
        let wscon = new WebSocket("wss://157.245.105.143:8080")

        wscon.onopen = function(event) {
            // console.log(event)
            console.log("OneSocket created")
        }

        wscon.onmessage = function(event) {
            // console.log(event.data)
            if (event.data == store.state.system.user.group_code)
                store.dispatch("system/get_notif_unread");
        }

        this.wscon = wscon

        // Audio
        let sound = this.$store.state.system.sound
        let audio = new Audio(sound);
        this.$store.commit('system/set_audio', audio)
    }
}
</script>