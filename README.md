## Directus Extensions - Philleep Florence
> The Philleep Florence extended version of Directus 8+ is still under development, more information to come in the near future.

### Folder Structure and Extensions

```
├── endpoints
│   ├── analytics
│   │   ├── controllers
│   │   │   └── Analytics.php
│   │   └── endpoints.php
│   ├── auth
│   │   ├── controllers
│   │   │   ├── Confirm.php
│   │   │   ├── Credentials.php
│   │   │   ├── Login.php
│   │   │   ├── Logout.php
│   │   │   ├── Register.php
│   │   │   └── Reset.php
│   │   └── endpoints.php
│   ├── cdn
│   │   ├── controllers
│   │   │   └── Files.php
│   │   └── endpoints.php
│   ├── collections
│   │   ├── controllers
│   │   │   ├── Compile.php
│   │   │   └── Migrate.php
│   │   └── endpoints.php
│   ├── csv
│   │   ├── controllers
│   │   │   ├── Export.php
│   │   │   └── Import.php
│   │   └── endpoints.php
│   ├── database
│   │   ├── controllers
│   │   │   ├── Backup.php
│   │   │   ├── Collection.php
│   │   │   ├── Field.php
│   │   │   ├── Migrate.php
│   │   │   └── Schema.php
│   │   └── endpoints.php
│   ├── form
│   │   ├── controllers
│   │   │   ├── Mail.php
│   │   │   └── Submit.php
│   │   └── endpoints.php
│   ├── metadata
│   │   ├── controllers
│   │   │   └── Import.php
│   │   └── endpoints.php
│   ├── search
│   │   ├── controllers
│   │   │   ├── Build.php
│   │   │   ├── Cache.php
│   │   │   └── Database.php
│   │   └── endpoints.php
│   ├── styles
│   │   ├── controllers
│   │   │   └── Variables.php
│   │   └── endpoints.php
│   ├── thumbnailer
│   │   ├── controllers
│   │   │   └── Sizes.php
│   │   └── endpoints.php
│   └── user
│       ├── controllers
│       │   ├── Metadata.php
│       │   ├── Notifications.php
│       │   └── Settings.php
│       └── endpoints.php
├── helpers
│   ├── analytics.php
│   ├── api.php
│   ├── auth.php
│   ├── cdn.php
│   ├── collections.php
│   ├── csv.php
│   ├── curl.php
│   ├── database.php
│   ├── debugger.php
│   ├── filesystem.php
│   ├── form.php
│   ├── format.php
│   ├── functions.php
│   ├── mail.php
│   ├── response.php
│   ├── search.php
│   ├── server.php
│   ├── styles.php
│   ├── user.php
│   └── utils.php
├── hooks
│   ├── request
│   │   └── hooks.php
│   └── response
│       └── hooks.php
├── interfaces
│   ├── many-to-one-extended
│   │   ├── package-lock.json
│   │   ├── package.json
│   │   ├── readme.md
│   │   └── src
│   │       ├── display.vue
│   │       ├── input.vue
│   │       └── meta.json
│   └── text-input-html
│       ├── package-lock.json
│       ├── package.json
│       ├── readme.md
│       └── src
│           ├── display.vue
│           ├── input.vue
│           └── meta.json
├── layouts
├── modules
│   ├── dashboard
│   │   ├── package-lock.json
│   │   ├── package.json
│   │   ├── readme.md
│   │   └── src
│   │       ├── meta.json
│   │       └── page.vue
│   ├── guide
│   │   ├── package-lock.json
│   │   ├── package.json
│   │   ├── readme.md
│   │   └── src
│   │       ├── meta.json
│   │       └── page.vue
│   ├── metadata
│   │   ├── package-lock.json
│   │   ├── package.json
│   │   ├── readme.md
│   │   └── src
│   │       ├── meta.json
│   │       └── page.vue
│   └── tools
│       ├── package-lock.json
│       ├── package.json
│       ├── readme.md
│       └── src
│           ├── meta.json
│           └── page.vue
├── scripts
└── styles
```

#### Endpoints
Custom endpoints are easy to create files that return an array with the endpoint path, method, and handler.

#### Helpers
Custom helper classes are to allow for use within Directus custom internal ecosystem as well as the custom endpoints.

#### Hooks
Directus provides event hooks for all actions performed within the App or API. You can use these hooks to run any arbitrary code when a certain thing in the system happens. You can use this for automated build triggers, webhooks, or other automated systems.

#### Interfaces
Interfaces allow for different ways of viewing and interacting with field data. These interfaces are primarily used on the edit form of the Item Detail page, but also render readonly data on the Item Browse page.
> An interface is made up out of three required core files. You can create a layout from scratch or use the [extension toolkit](https://github.com/directus/extension-toolkit) to generate boilerplate code.

#### Layouts
Layouts are different ways to view or even interact with data on the Item Browse page. Directus includes List and Card layouts out-of-the-box, but others can easily be created to fit specific needs.

#### Modules
Modules are a way to add full-featured modules to Directus. You can build page modules for custom dashboards, reporting, point-of-sale systems, or anything else. 
Each page is protected within the auth gateway and can easily access project data and global variables.