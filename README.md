### Folder Structure and Extensions

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
├── pages
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