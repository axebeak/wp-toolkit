Vue.component('mainmenu',{
  data: function() {
      return {
          mainData: {
              home: '',
              siteurl: '',
              title: '',
              desc: ''
          }
      }
  },
  props: ['home', 'siteurl', 'title', 'desc'],
  template: `
      <div>
        <div class="row">
            <div class="col">
                <button class="btn btn-block" @click="selfDestruct()">Delete WordPress Toolkit</button>
            </div>
            <div class="col">
                <button class="btn btn-block" @click="flush()">Flush Temporary Files</button>
            </div>
        </div>
        <div class="row mt-5">
            <div class="col justify-content-center text-center">
                <div>Home:</div>
                <input  class="main-input" type="text" v-model="mainData['home']">
            </div>
            <div class="col justify-content-center text-center">
                <div>Title:</div>
                <input  class="main-input" type="text" v-model="mainData['title']">
            </div>
        </div>
        <div class="row mt-3">
            <div class="col justify-content-center text-center">
                <div>Site URL:</div>
                <input class="main-input" type="text" v-model="mainData['siteurl']">
            </div>
            <div class="col justify-content-center text-center">
                <div>Description:</div>
                <input  class="main-input" type="text" v-model="mainData['desc']">
            </div>
        </div>
        <div class="row justify-content-center text-center mt-5">
            <button class="btn theme-button" @click="updateMainData()">Submit</button>
        </div>
      </div>
  `,
  created: function(){
      this.defineMainData()
  },
  methods: {
      selfDestruct: function(){
          app.selfDestruct()
      },
      flush: function(){
          app.flush()
      },
      defineMainData: function(){
          this.mainData['home'] = this.home
          this.mainData['siteurl'] = this.siteurl
          this.mainData['title'] = this.title
          this.mainData['desc'] = this.desc
      },
      updateMainData: function(){
          app.updateMainData(this.mainData)
      }
  },
  watch: {
      home: function(){
        this.defineMainData()
      },
      siteurl: function(){
        this.defineMainData()
      },
      title: function(){
        this.defineMainData()
      },
      desc: function(){
        this.defineMainData()
      }
  }
})

Vue.component('plugins',{
  data: function() {
      return {
        isActive: false,
        plugins: [],
        allChecked: false
      }
  },
  props: ['list', 'status'],
  template: `
      <div>
        <div class="box">
            <ul>
                <li v-for="(plugin, index) in list">
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input ch-b" type="checkbox" :id="plugin" :name="plugin" :value="plugin" v-model="plugins">
                        <label class="custom-control-label" :class="isSelected(plugin) ? 'selected' : ''" :for="plugin">{{plugin}}</label>
                        <i v-if="!checkFolder(plugin)" class="fas fa-exclamation" :class="isSelected(plugin) ? 'selected' : ''" title="Plugin is enabled, but no folder for it exists!"></i>
                    </div>
                </li>
            </ul>
        </div>
        <div class="row d-flex text-center navbar">
            <button class="btn" v-if="!allChecked" @click="checkAll()">Check All</button>
            <button class="btn" v-else @click="uncheckAll()">Uncheck All</button>
            <button v-if="status === 'inactive'" class="btn" @click="changeStatus()">Enable</button>
            <button v-else class="btn" @click="changeStatus()">Disable</button>
            <button class="btn" @click="removePlugins()">Remove</button>
        </div>
      </div>
  `,
  created: function () {
     if (this.status === 'active'){
         return this.isActive = true
     }
     if (this.status === 'inactive'){
         return this.isActive = false
     }
  },
  methods: {
      checkAll: function() {
          for (value in this.list){
              if (this.plugins.indexOf(this.list[value]) !== -1){
                  continue
              }
              this.plugins.push(this.list[value])
          }
          return this.allChecked = true
      },
      uncheckAll: function() {
          if (this.allChecked){
                this.plugins.length = 0
                this.allChecked = false
                return true
          }
          return false
      },
      areAllChecked: function() {
        for (value in this.list){
            if (this.plugins.indexOf(this.list[value]) == -1){
                return false
            }
        }
        return true
      },
      isSelected: function(plugin) {
        if (this.plugins.indexOf(plugin) == -1){
            return false
        }

        return true
      },
      changeStatus: function() {
          if (this.status === 'inactive'){
              app.enablePlugins()
              return true
          }
          app.disablePlugins()
          return true
      },
      removePlugins: function() {
          app.removePlugins(this.status)
      },
      checkFolder: function(plugin) {
          if (typeof app.data.nofolder == "undefined"){
              return true   
          }
          if (app.data.nofolder.indexOf(plugin) == -1){
              return true
          }
          return false
      }
  },
  watch: {
      plugins: function(val){
          this.allChecked = this.areAllChecked()
          if (this.isActive){
              return app.selectedPlugins.active = this.plugins
          }
          
          return app.selectedPlugins.inactive = this.plugins 
      },
      list: function(){
        for (var i = 0; i < this.plugins.length; i++){
            if (typeof this.list[this.plugins[i]] == "undefined"){
                this.$delete(this.plugins, i)
            }
        }
      }
  }
});

Vue.component('themes',{
    data: function(){
        return {
            selectedTheme: ''
        }
    },
    props: ['themes', 'stylesheet', 'template'],
    template: `
        <div>
            <div class="d-flex justify-content-center flex-column ml-59">
                <h5>Template: {{template}}</h5>
                <h5>Stylesheet: {{stylesheet}}</h5>
            </div>
            <h3 class="text-center">Theme List:</h3>
            <div class="d-flex justify-content-center">
                <div class="box">
                    <ul>
                        <li v-for="(theme, index) in themes">
                            <div class="custom-control custom-radio">
                                <input class="custom-control-input" type="radio" :value="theme" :name="theme" :id="theme" v-model="selectedTheme">
                                <label class="custom-control-label" :class="isSelected(theme) ? 'selected' : ''" :for="theme">{{theme}}</label>
                            </div>
                        </li>
                        <li>
                            <div class="custom-control custom-radio">
                                <input class="custom-control-input" type="radio" id="defaultTheme" value="defaultTheme" name="theme" v-model="selectedTheme">
                                <label class="custom-control-label" :class="isSelected('defaultTheme') ? 'selected' : ''" for="defaultTheme">Default Theme</label>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    `,
    methods: {
        isSelected: function(theme){
            if (this.selectedTheme === theme){
                return true
            }
            
            return false
        }
    },
    watch: {
        selectedTheme: function(){
            if (this.selectedTheme === 'defaultTheme'){
                app.defaultThemeSelected = true
            } else {
                app.defaultThemeSelected = false
            }
            return app.selectedTheme = this.selectedTheme 
        },
    }
})

Vue.component('reupload',{
    data: function(){
        return {
            items: {},
            selected: [],
            isLatest: {},
        }
    },
    props: ['list', 'status'],
    template: `
        <div :id="status + 'box'" class="box box-padding">
            <ul>
                <li v-for="(item, id) in list">
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input ch-b" type="checkbox" :value="id" :id="id" v-model="selected">
                        <label class="custom-control-label text-wrap" :class="isSelected(id) ? 'selected' : ''" :for="id" :title="id.length > 17 ? id : ''">{{ checkName(id)}}</label>
                        <input class="ver-input" :class="versionChosen(id) ? 'selected' : ''" type="text" :name="status"  v-model="items[id]"> 
                        <span class="custom-control custom-checkbox latest-checkbox">
                            <input class="custom-control-input ch-b" type="checkbox" :id="id + '-LATEST'" value="LATEST" :name="status" v-model="isLatest[id]">
                            <label class="custom-control-label" :for="id + '-LATEST'" :class="latestChosen(id) ? 'selected' : ''">Latest</label>
                        </span>
                    </div>
                </li>
            </ul>
        </div>
    `,
    methods: {
        isSelected: function(id){
            if (this.selected.indexOf(id) == -1){
                return false
            }
            return true
        },
        checkName: function(name){
            if (name.length > 17){
                let result = name.length - 17
                name = name.substring(0, name.length - result);
                name = name + "..."
            }
            return name
        },
        latestChosen: function(name){
            if (this.selected.indexOf(name) == -1){
                return false
            }
            if (typeof this.isLatest[name] == "undefined" || !this.isLatest[name]){
                return false
            }
            return true
        },
        versionChosen: function(name){
            if (this.selected.indexOf(name) == -1){
                return false
            }
            if (typeof this.isLatest[name] == "undefined" || !this.isLatest[name]){
                return true
            }
            return false
        },
    },
    created: function(){
        this.items = this.list
    },
    watch: {
        selected: function() {
            for (var i = 0; i < this.selected.length; i++){
                if (typeof this.items[this.selected[i]] == "undefined"){
                    this.$delete(this.selected, i)
                }
            }
            return app.selectedReupload[this.status] = this.selected
        },
        list: function() {
           this.items = this.list
        },
        isLatest: function() {
            app.latestVersions[this.status] = this.isLatest
        },
    }
})

Vue.component('users',{
    props: ['users'],
    template: `
        <div class="user-list">
            <ul v-for="(user, id) in users">
                <li>
                    <div>
                        <a class="user-item" href="javascript:void(0)" :class="isSelected(id) ? 'selected' : ''" :id="id" @click="loadUser(id)">{{user}}</a>
                    </div>
                </li>
            </ul>
        </div>
    `,
    methods: {
        loadUser: function(id){
            app.loadUser(id)
        },
        isSelected: function(id){
            if (Object.keys(app.userinfo).length === 0) {
                return false
            }
            if (!app.userinfoOpened){
                return false
            }
            if (app.userinfo['id'] === id){
                return true
            }
            return false
        }
    }
})

Vue.component('userinfo',{
    data: function(){
        return {
            showCapabilities: true,
            areDifferent: false,
            areEmpty: false,
            areNotSet: false,
            userLevel: '',
            newPass: ''
        }
    },
    props: ['info', 'levels'],
    template: `
        <div class="userinfo">
            <div class="userinfo-row mt-2"><h3>{{info.username}}</h3></div>
            <div class="userinfo-row">ID: {{info.id}}</div>
            <div class="userinfo-row">Email: {{info.email}}</div>
            <div class="userinfo-row">Nice Name: {{info.nice_name}}</div>
            <div class="userinfo-row" v-if="areDifferent">The values of the user_level and capababilites entries in the database are different!</div>
            <div class="userinfo-row" v-if="areEmpty">The values of the user_level and capababilites entries in the database are empty!</div>
            <div class="userinfo-row" v-if="areNotSet">The values of the user_level and capababilites entries in the database are not set!</div>
            <div class="userinfo-row">User level: {{info.level}}</div>
            <div class="userinfo-row" v-if="showCapabilities">Capabilities: {{info.capabilities}}</div>
            <div class="userinfo-row">Set user level:</div>
            <div class="userinfo-row d-flex justify-content-center">
                <div class="selector input-userinfo">
                    <select class="text-center w-100" v-model="userLevel">
                        <option v-for="(level, id) in levels">
                            <span>{{ level }}</span>
                        </option>
                    </select>
                </div>
            </div>
            <div class="userinfo-row">
                <button class="btn" @click="setLevel()">Change level</button>
            </div>
            <div class="userinfo-row">
                <input type="password" class="input-userinfo" placeholder="New password here" v-model="newPass">
            </div>
            <div class="userinfo-row">
                <button class="btn" @click="changePass()">Reset</button>
            </div>
            <div class="userinfo-row mb-4">
                <button class="btn" @click="deleteUser()">Delete User</button>
            </div>
        </div>
    `,
    methods: {
        checkLevel: function(){
            if (this.info.capabilities !== this.info.level){
                this.showCapabilities = true
                this.areDifferent = true
                this.areNotSet = false
                this.areEmpty = false
            }
            if (this.info.capabilities == this.info.level){
                this.showCapabilities = false
                this.areDifferent = false
                this.areNotSet = false
                this.areEmpty = false
            }
            if (this.info.capabilities == 'Not Set' && this.info.capabilities === this.info.level){
                this.areNotSet = true
                this.showCapabilities = false
                this.areDifferent = false
                this.areEmpty = false
            }
            if (this.info.capabilities == 'Empty' && this.info.capabilities === this.info.level){
                this.areEmpty = true
                this.areNotSet = false
                this.showCapabilities = false
                this.areDifferent = false
            }
        },
        setLevel: function(){
            let index = ''
            for (var level in this.levels){
                if (this.userLevel === this.levels[level]){
                    index = level
                    break
                }
            }
            if (index === ''){
                app.success = false
                app.message = "Select a proper user level value."
                return false
            }
            if (app.setUserLevel(this.info.id, index)){
                this.info = app.userinfo
            }
        },
        changePass: function() {
            if (this.newPass === ''){
                app.success = false
                app.message = "Password cannot be empty."
                return false                
            }
            app.setPassword(this.info.id, this.newPass)
        },
        deleteUser: function() {
            app.deleteUser(this.info.id)
        }
    },
    created: function(){
        this.checkLevel()
        for (var level in this.levels){
            this.userLevel = this.levels[level]
            break
        }
    },
    watch: {
        info: function(){
            this.checkLevel()
        }
    }
})

Vue.component('newuser', {
    data: function(){
        return {
            username: '',
            pass: '',
            nicename: '',
            email: '',
            userLevel: '',
        }
    },
    props: ['levels'],
    template: `
        <div>
            <h3 class="text-center mb-3">Create New User</h3>
            <div class="row mb-3 justify-content-center">
                <input type="text" class="w-100 text-center" placeholder="Username*" required v-model="username">
            </div>
            <div class="row mb-3 justify-content-center">
                <input type="password" class="w-100 text-center" placeholder="Password*" required v-model="pass">
            </div>
            <div class="row mb-3 justify-content-center">
                <input type="text" class="w-100 text-center" placeholder="Nice Name" v-model="nicename">
            </div>
            <div class="row mb-3 justify-content-center">
                <input type="email" class="w-100 text-center" placeholder="Email Address" v-model="email">
            </div>
            <div class="row mb-3 justify-content-center">User level:</div>
            <div class="row mb-3 justify-content-center">
                <div class="selector w-50">
                    <select class="text-center w-100" v-model="userLevel">
                        <option v-for="(level, id) in levels">
                            <span>{{ level }}</span>
                        </option>
                    </select>
                </div>
            </div>
            <div class="row mb-3 justify-content-center">
                <button class="btn w-75" @click="createUser()">Create</button>
            </div>
        </div>
    `,
    methods: {
        createUser: function(){
            let index = ''
            for (var level in this.levels){
                if (this.userLevel === this.levels[level]){
                    index = level
                    break
                }
            }
            if (index === ''){
                app.success = false
                app.message = "Select a proper user level value."
                return false
            }
            let userinfo = {
                username: this.username,
                pass: this.pass,
                nicename: this.nicename,
                email: this.email,
                userlevel: level
            }
            app.createUser(userinfo)
        }
    },
    created: function() {
        for (var level in this.levels){
            this.userLevel = this.levels[level]
            break
        }
    }
})

Vue.component('roles', {
    data: function() {
        return {
            openedRoles: {},
            rolesColumns: {}
        }
    },
    props: ['roles'],
    template: `
        <div>
            <div v-for="(perms, name) in roles" class="mb-4">
                <div>
                    <span class="level-name">{{ name }}</span> 
                    <i @click="toggleRole(name)" class="fas" :class="openedRoles[name] ? 'fa-minus' : 'fa-plus'"></i>
                </div>
                <div v-if="openedRoles[name]"  class="row">
                    <div v-for="(roleColumn, id) in rolesColumns[name]"  class="col">
                        <ul>
                            <li v-for="(roleName, id) in roleColumn">
                                {{ roleName }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    `,
    methods: {
        toggleRole: function(name){
            return this.openedRoles[name] = !this.openedRoles[name]
        },
    },
    created: function() {
        for (var role in this.roles){
            Vue.set(this.openedRoles, role, false)
            var roleArray = []
            for (var item in this.roles[role]){
                if (typeof i === 'undefined'){
                    var i = 0
                }
                if (typeof roleArray[i] === 'undefined'){
                    roleArray[i] = []
                }
                if (roleArray[i].length === 10){
                    i++
                    roleArray[i] = []
                }
                roleArray[i].push(item)
            }
            Vue.set(this.rolesColumns, role, roleArray)
            var i = 0
        }
    }
})

Vue.component('dberror',{
    data: function() {
        return {
            details: {}
        }
    },
    props: ['error'],
    template: `
        <div>
            <div class="alert alert-danger mt-2">An error occured during database connection: {{ readErrorOutput() }}</div>
            <h3 class="text-center mt-2">Enter Database Details Manually:</h3>
            <div class="text-center mt-2">
                <div>Username:</div>
                <input class="w-50 text-center" type="text" v-model="details.user">
            </div>
            <div class="text-center mt-2">
                <div>Password:</div>
                <input class="w-50 text-center" type="text" v-model="details.pass">
            </div>
            <div class="text-center mt-2">
                <div>Database Name:</div>
                <input class="w-50 text-center" type="text" v-model="details.db">
            </div>
            <div class="text-center mt-2">
                <div>Host:</div>
                <input class="w-50 text-center" type="text" v-model="details.host">
            </div>
            <div class="text-center mt-2">
                <div>Prefix:</div>
                <input class="w-50 text-center" type="text" v-model="details.prefix">
            </div>
            <div class="text-center mt-3">
                <button class="btn theme-button" @click="submitDetails()">Submit</button>
            </div>
        </div>
    `,
    methods: {
      readErrorOutput: function() {
          if (this.error.error === 'connection'){
              return this.error.output
          }
          if (this.error.error === 'empty'){
              return 'The following fields are empty: ' + this.error.output
          }
          if (this.error.error === 'keys'){
              return 'The following fields are absent: ' + this.error.output
          }
      },
      defineDetails: function(){
          this.details['user'] = this.error.details.user
          this.details['pass'] = this.error.details.pass
          this.details['db'] = this.error.details.db
          this.details['host'] = this.error.details.host
          this.details['prefix'] = this.error.details.prefix
      },
      submitDetails: function(){
          app.submitDbDetails(this.details)
      }
    },
    created: function() {
        this.defineDetails()
    },
    watch: {
        error: function(){
            this.defineDetails()
        }
    }
})

var app = new Vue({
  el: '#app',
  data: {
    loggedIn: false,
    toolkitPassword: '',
    loginMessage: '',
    startup: false,
    toolkitVer: true,
    fopen: true,
    multisite: false,
    success: false,
    message: false,
    warn: false,
    loading: true,
    loadingResponse: false,
    data: false,
    database: false,
    databaseError: {},
    ajaxData: {},
    menu: {"isActive": false},
    userinfo: {},
    users: {
        userList: false,
        newUser: false,
        roles: false
    },
    userSearch: '',
    userMatches: {},
    userinfoOpened: false,
    levels: {},
    roles: {},
    selectedTheme: '',
    defaultThemeSelected: false,
    selectedPlugins: {
        active: [], 
        inactive: []
    },
    selectedReupload: {
        plugins: [],
        themes: []
    },
    latestVersions: {
        plugins: [],
        themes: []
    },
  },
  methods: {
      check(){
        var self = this
          $.ajax({
            url: "api.php",
            method: "POST",
            data: {
                check: true
            },
            success: function (response) {
                self.toolkitVer = response['toolkit-ver']
                self.multisite = response.multisite
                self.fopen = response.fopen
                if (response.database){
                    self.database = true
                } else {
                    self.databaseError.error = response.error
                    self.databaseError.output = response[response.error]
                    self.databaseError.details = response.details
                }
                self.startup = true
            },
            error: function () {
                $(".col-lg-6").append("<div class='alert alert-danger mt-5'>An unexpected error occured during API call. Make sure that the PHP version is 5.6 or higher.</div>");
            }
        })
      },
      login(){
          var self = this
          $.ajax({
            url: "api.php",
            method: "POST",
            data: {
                'log-in': true,
                'toolkit-password': self.toolkitPassword
            },
            success: function (response) {
                    self.loggedIn = response.loggedIn
                    self.loginMessage = response.loginMessage
                    if (response.removed){
                        $("body").empty();
                        $("body").append("<div class='alert alert-danger mt-5 ml-5 mr-5'>You have reached the limit on login tries. WordPress Toolkit has been removed.</div>");
                    }
            },
            error: function () {
                $("body").append("<div class='alert alert-danger mt-5'>An unexpected error occured during API call. Please check the logs.</div>");
            }
          })
      },
      loginMessageColor(){
          if (this.loginMessage === '' && !this.loggedIn){
              return true
          }
          return false
      },
      selfDestruct(){
          var self = this
          $.ajax({
            url: "api.php",
            method: "POST",
            data: {
                'self-destruct': true
            },
            success: function (response) {
                if (response.removed){
                    self.success = true
                    self.message = "WordPress Toolkit has been succesfully removed."
                    self.menu = {"isActive": true}
                    if (!self.startup){
                        $("body").empty();
                        $("body").append("<div class='alert alert-success mt-5 ml-5 mr-5'>WordPress Toolkit has been succesfully removed.</div>");
                    }
                }
            },
            error: function () {
                $("body").append("<div class='alert alert-danger mt-5'>An unexpected error occured during API call. Please check the logs.</div>");
            }
        }) 
      },
      flush(){
        this.makeAjax({
            flush: true
        })  
      },
      submitDbDetails(details){
          var self = this
          $.ajax({
            url: "api.php",
            method: "POST",
            data: {
                'db-details': details
            },
            success: function (response) {
                if (response.database){
                    self.database = true
                } else {
                    self.databaseError.error = response.error
                    self.databaseError.output = response[response.error]
                    self.databaseError.details = response.details
                }
            },
            error: function () {
                $(".col-lg-6").append("<div class='alert alert-danger mt-5'>An unexpected error occured during API call. Please check the logs.</div>");
            }
        })
      },
      moveMenuBar(element, time){
        var left = $(element).position().left;
        var width = $(element).width();
        $(".menu-item").removeClass("active-menu-item")
        $(element).addClass("active-menu-item")
        $(".menu-bar").animate({
            left: left - 14,
            width: width
        }, time);
      },
      alertColor(){
        if (this.warn){
            return 'alert-warning'
        }
        if (this.success && !this.message){
            return 'alert-info'
        }
        if (!this.success){
            return 'alert-danger'
        }
        return 'alert-success'
      },
      userOpen(option){
        for (var menu in this.users){
          this.users[menu] = false
        }
        if (typeof this.users[option] !== "undefined"){
          return this.users[option] = true
        }
        return false
      },
      usersResult(){
          if (Object.keys(this.userMatches).length === 0 && this.userSearch === ''){
              return this.data.users
          }
          return this.userMatches
      },
      searchUser(string){
          let matches = {}
          string = string.toLowerCase()
          for (var id in this.data.users){
              let user = this.data.users[id]
              let userLowerCase = user.toLowerCase()
              if (userLowerCase.startsWith(string)){
                  matches[id] = user
              }
          }
          return matches
      },
      makeAjax(parameters){
          var self = this
          self.loadingResponse = true
          $('button').prop('disabled', true)
          $.ajax({
            url: "api.php",
            method: "POST",
            data: parameters,
            success: function (response) {
                $('button').prop('disabled', false)
                self.loadingResponse = false
                self.ajaxData = response
            },
            error: function () {
                $('button').prop('disabled', false)
                self.loadingResponse = false
                self.success = false
                self.message = "The API call has failed. Please check the error logs."
            }
        })
      },
      fetch(type){
        var self = this
        self.menu.isActive = false
        self.loading = true
        this.moveMenuBar('#' + type, 500)
        $.ajax({
            url: "api.php",
            method: "POST",
            data: {
                fetch: type
            },
            success: function (response) {
                self.loading = false
                self.ajaxData = response
                self.menu = {}
                self.menu[type] = true
                self.menu.isActive = true
                if (type === 'users'){
                    self.userOpen('userList')
                    self.userinfoOpened = false
                }
                if (type === 'themes'){
                    self.defaultThemeSelected = false
                }
            },
            error: function () {
                self.loading = false
                self.success = false
                self.message = "The API call has failed. Please check the error logs."
            }
        })
      },
      loadUser(id){
        var self = this
        $.ajax({
            url: "api.php",
            method: "POST",
            data: {
              userinfo: id
            },
            success: function (response) {
                self.userinfo = response.data
                self.userinfoOpened = true
            },
            error: function () {
                self.success = false
                self.message = "The API call has failed. Please check the error logs."
            }
        })
      },
      setUserLevel(id, level){
        var self = this
        $.ajax({
            url: "api.php",
            method: "POST",
            data: {
                user: id,
                action: 'user-level',
                value: level
            },
            success: function (response) {
                self.success = response.success
                self.message = response.message
                self.userinfo = response.data
            },
            error: function () {
                self.success = false
                self.message = "The API call has failed. Please check the error logs."
            }
        })
      },
      deleteUser(id){
        var self = this
        $.ajax({
            url: "api.php",
            method: "POST",
            data: {
                user: id,
                action: 'delete'
            },
            success: function (response) {
                self.ajaxData = response
                self.userinfoOpened = false
            },
            error: function () {
                self.success = false
                self.message = "The API call has failed. Please check the error logs."
            }
        })
      },
      createUser(userinfo){
        var self = this
        $.ajax({
            url: "api.php",
            method: "POST",
            data: {
                'new-user': true,
                username: userinfo.username,
                password: userinfo.pass,
                email: userinfo.email,
                nicename: userinfo.nicename,
                capabilities: userinfo.userlevel
            },
            success: function (response) {
                self.userOpen('userList')
                self.ajaxData = response
            },
            error: function () {
                self.success = false
                self.message = "The API call has failed. Please check the error logs."
            }
        })
      },
      updateMainData(mainData){
          this.makeAjax({
              'main-data': mainData
          })          
      },
      enablePlugins(){
          this.makeAjax({
             plugins: this.selectedPlugins.inactive,
             action: 'enable'
        })
      },
      disablePlugins(){
        this.makeAjax({
            plugins: this.selectedPlugins.active,
            action: 'disable'
        })
      },
      setStylesheet(){
        this.makeAjax({
            theme: 'stylesheet',
            name: this.selectedTheme
        })
      },
      setTemplate(){
        this.makeAjax({
            theme: 'template',
            name: this.selectedTheme
        })
      },
      setTheme(){
        this.makeAjax({
            theme: 'both',
            name: this.selectedTheme
        })
      },
      reupload(type){
        let reupload = {}
        for (value in this.selectedReupload[type]){
            reupload[this.selectedReupload[type][value]] = this.data[type][this.selectedReupload[type][value]]
            if (this.latestVersions[type][this.selectedReupload[type][value]]){
                reupload[this.selectedReupload[type][value]] = "LATEST"
            }
        }
        this.makeAjax({
            reupload: type,
            list: reupload
        })
      },
      reuploadDefault(){
        this.makeAjax({
            'reupload-default': true,
        })
      },
      removePlugins(directive){
        let plugins
        let source
        if (directive === 'active'){
            plugins = this.selectedPlugins.active
            this.selectedPlugins.active = []
            source = 'plugins'
        } else if (directive === 'inactive') {
            plugins = this.selectedPlugins.inactive
            this.selectedPlugins.inactive = []
            source = 'plugins'
        } else if (directive === 'reupload') {
            plugins = this.selectedReupload.plugins
            this.selectedReupload.plugins = []
            source = 'reupload'
        } else {
              return false
        }
        this.makeAjax({
             remove: 'plugins',
             list: plugins,
             source: source
        })
      },
      removeThemes(directive){
        let themes
        let source
        if (directive === 'themes'){
            themes = this.selectedTheme
            this.selectedTheme = ''
            source = 'themes'
        } else if (directive === 'reupload') {
            themes = this.selectedReupload.themes
            this.selectedReupload.themes = []
            source = 'reupload'
        } else {
              return false
        }
        this.makeAjax({
             remove: 'themes',
             list: themes,
             source: source
        })          
      },
      setPassword(id, pass){
        var self = this
        $.ajax({
            url: "api.php",
            method: "POST",
            data: {
                user: id,
                action: 'password',
                value: pass
            },
            success: function (response) {
                self.success = response.success
                self.message = response.message
                self.userinfo = response.data
            },
            error: function () {
                self.success = false
                self.message = "The API call has failed. Please check the error logs."
            }
        })
      },
      restoreDefaultRoles(){
        this.makeAjax({
            'user-roles': true
        })
      },
      reuploadDefaultTheme(set){
        this.makeAjax({
             'default-theme': set
        })
      },
      revert(directive){
        this.makeAjax({
             revert: directive
        })
      },
      undo(directive){
        this.makeAjax({
             undo: directive
        })
      },
      removeOldFiles(){
          this.makeAjax({
             'old-files': true
          })
      },
      cacheFolder(action){
          this.makeAjax({
             'cache-folder': action   
          })
      },
      totalCacheFolder(action){
          this.makeAjax({
              'tc-config': action
          })
      },
  },
  watch: {
      loggedIn: function(val){
          if (val){
            this.check()
          }
      },
      database: function(){
        if (this.database){
            setTimeout(() => {
                this.fetch('main')
            }, 10)
        }
      },
      ajaxData: function (val) {
        this.success = val.success
        this.message = val.message
        if (typeof val.data !== "undefined"){
            this.data = val.data
        }
        if (typeof val.warn !== "undefined"){
            this.warn = val.warn
        } else {
            this.warn = false
        }
      },
      menus: function (val) {
        this.userMenu = false
        this.pluginMenu = false
        this.themeMenu = false
      },
      userSearch: function (val){
        this.userMatches = this.searchUser(val)
      },
  },
});