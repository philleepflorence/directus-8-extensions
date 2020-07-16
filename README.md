## Directus Extensions - Philleep Florence
> The Philleep Florence extended version of Directus 8+ is still under development, more information to come in the near future.

### Getting Started
The following directories will need to be synced to your Directus installation:
```
├── admin
├── scripts
│   └── script.js
├── styles
│   └── style.css
└── src
```
Remember to include the `admin/lib` in your `gitignore` if you pulled from Directus Suite!

To use the extensions, you will first need to include the file that auto-loads the helpers.
```
helpers/functions.php
```
In the function.php file you should see the following snippet that should auto load all helper files!
If you move the snippet to another file, be sire to change the `__DIR__` variable!
```
/*
	AutoLoad - Helper Files - Should Run at application.boot!
*/

$helpers = scandir(__DIR__);
	
foreach( $helpers as $file ) 
{
    if ('.' === $file || '..' === $file || __FILE__ === $file) continue;
    
    include_once($file);
}
```

This extension of Directus has a some pre-set schema definitions you might notice in the helpers.
The SQL Schema Dumps should be coming soon.
For now, use this repository as a guide on creating your own extensions.

### Folder Structure and Extensions

```
.
├── admin
│   └── lib
│       └── styles
│           ├── animations.css
│           ├── app.css
│           ├── extensions.css
│           ├── private.css
│           ├── public.css
│           ├── tour.css
│           └── wysiwyg.css
├── components
│   ├── charts
│   │   ├── bar.vue
│   │   ├── doughnut.vue
│   │   ├── pie.vue
│   │   └── radar.vue
│   ├── files
│   │   └── tree.vue
│   └── tables
│       └── table.vue
├── endpoints
│   ├── analytics
│   │   ├── controllers
│   │   │   ├── Analytics.php
│   │   │   ├── Application.php
│   │   │   ├── Dashboard.php
│   │   │   ├── Modules.php
│   │   │   ├── Project.php
│   │   │   └── Set.php
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
│   ├── curl
│   │   ├── controllers
│   │   │   └── Content.php
│   │   └── endpoints.php
│   ├── database
│   │   ├── controllers
│   │   │   ├── Archive.php
│   │   │   ├── Backup.php
│   │   │   ├── Collection.php
│   │   │   ├── Field.php
│   │   │   ├── Migrate.php
│   │   │   └── Schema.php
│   │   └── endpoints.php
│   ├── directus
│   │   ├── controllers
│   │   │   ├── App.php
│   │   │   ├── Mail.php
│   │   │   └── Metadata.php
│   │   └── endpoints.php
│   ├── files
│   │   ├── controllers
│   │   │   ├── Migrate.php
│   │   │   └── Unlink.php
│   │   └── endpoints.php
│   ├── form
│   │   ├── controllers
│   │   │   ├── Mail.php
│   │   │   └── Submit.php
│   │   └── endpoints.php
│   ├── mail
│   │   ├── controllers
│   │   │   ├── App.php
│   │   │   ├── Browser.php
│   │   │   ├── Mail.php
│   │   │   └── Metadata.php
│   │   └── endpoints.php
│   ├── metadata
│   │   ├── controllers
│   │   │   └── Import.php
│   │   └── endpoints.php
│   ├── notifications
│   │   ├── controllers
│   │   │   └── Push.php
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
│   ├── cache.php
│   ├── cdn.php
│   ├── collections.php
│   ├── csv.php
│   ├── curl.php
│   ├── database.php
│   ├── debugger.php
│   ├── directus.php
│   ├── filesystem.php
│   ├── form.php
│   ├── format.php
│   ├── functions.php
│   ├── mail.php
│   ├── notifications.php
│   ├── request.php
│   ├── response.php
│   ├── search.php
│   ├── server.php
│   ├── styles.php
│   ├── user.php
│   └── utils.php
├── hooks
│   ├── collections
│   │   └── hooks.php
│   ├── request
│   │   └── hooks.php
│   └── response
│       └── hooks.php
├── interfaces
│   ├── dropdown-extended
│   │   ├── dist
│   │   │   ├── display.css
│   │   │   ├── display.js
│   │   │   ├── display.js.map
│   │   │   ├── input.css
│   │   │   ├── input.js
│   │   │   ├── input.js.map
│   │   │   └── meta.json
│   │   ├── package-lock.json
│   │   ├── package.json
│   │   ├── readme.md
│   │   └── src
│   │       ├── display.vue
│   │       ├── input.vue
│   │       └── meta.json
│   ├── many-to-one-extended
│   │   ├── dist
│   │   │   ├── display.css
│   │   │   ├── display.js
│   │   │   ├── display.js.map
│   │   │   ├── input.css
│   │   │   ├── input.js
│   │   │   ├── input.js.map
│   │   │   └── meta.json
│   │   ├── package-lock.json
│   │   ├── package.json
│   │   ├── readme.md
│   │   └── src
│   │       ├── display.vue
│   │       ├── input.vue
│   │       └── meta.json
│   ├── map-extended
│   │   ├── dist
│   │   │   ├── display.css
│   │   │   ├── display.js
│   │   │   ├── display.js.map
│   │   │   ├── input.css
│   │   │   ├── input.css.map
│   │   │   ├── input.js
│   │   │   ├── input.js.map
│   │   │   └── meta.json
│   │   ├── package-lock.json
│   │   ├── package.json
│   │   ├── readme.md
│   │   └── src
│   │       ├── display.vue
│   │       ├── input.vue
│   │       ├── leaflet.css
│   │       └── meta.json
│   ├── text-input-html
│   │   ├── dist
│   │   │   ├── display.css
│   │   │   ├── display.js
│   │   │   ├── display.js.map
│   │   │   ├── input.css
│   │   │   ├── input.js
│   │   │   ├── input.js.map
│   │   │   └── meta.json
│   │   ├── package-lock.json
│   │   ├── package.json
│   │   ├── readme.md
│   │   └── src
│   │       ├── display.vue
│   │       ├── input.vue
│   │       └── meta.json
│   ├── url
│   │   ├── dist
│   │   │   ├── display.css
│   │   │   ├── display.js
│   │   │   ├── display.js.map
│   │   │   ├── input.css
│   │   │   ├── input.js
│   │   │   ├── input.js.map
│   │   │   └── meta.json
│   │   ├── package-lock.json
│   │   ├── package.json
│   │   ├── readme.md
│   │   └── src
│   │       ├── display.vue
│   │       ├── input.vue
│   │       └── meta.json
│   └── wysiwyg-extended
│       ├── dist
│       │   ├── display.css
│       │   ├── display.js
│       │   ├── display.js.map
│       │   ├── icons
│       │   │   └── default
│       │   │       ├── icons.js
│       │   │       ├── icons.min.js
│       │   │       └── index.js
│       │   ├── input.css
│       │   ├── input.css.map
│       │   ├── input.js
│       │   ├── input.js.map
│       │   └── meta.json
│       ├── package-lock.json
│       ├── package.json
│       ├── readme.md
│       └── src
│           ├── display.vue
│           ├── input.vue
│           ├── meta.json
│           ├── skin.css
│           └── wysiwyg.css
├── layouts
├── mail
│   ├── auth-fail.twig
│   ├── auth-success.twig
│   ├── base.twig
│   ├── footer.twig
│   ├── new-install.twig
│   ├── plain.twig
│   ├── reset-password.twig
│   ├── user-invitation.twig
│   └── user.twig
├── modules
│   ├── analytics
│   │   ├── dist
│   │   │   ├── meta.json
│   │   │   ├── module.css
│   │   │   ├── module.js
│   │   │   └── module.js.map
│   │   ├── package-lock.json
│   │   ├── package.json
│   │   ├── readme.md
│   │   └── src
│   │       ├── components
│   │       │   └── charts
│   │       │       ├── bar.vue
│   │       │       ├── doughnut.vue
│   │       │       └── pie.vue
│   │       ├── meta.json
│   │       └── module.vue
│   ├── application
│   │   ├── dist
│   │   │   ├── meta.json
│   │   │   ├── module.css
│   │   │   ├── module.js
│   │   │   └── module.js.map
│   │   ├── package-lock.json
│   │   ├── package.json
│   │   ├── readme.md
│   │   └── src
│   │       ├── meta.json
│   │       └── module.vue
│   ├── cdn
│   │   ├── dist
│   │   │   ├── meta.json
│   │   │   ├── module.css
│   │   │   ├── module.js
│   │   │   └── module.js.map
│   │   ├── package-lock.json
│   │   ├── package.json
│   │   ├── readme.md
│   │   └── src
│   │       ├── components
│   │       │   └── files
│   │       │       └── tree.vue
│   │       ├── meta.json
│   │       └── module.vue
│   ├── collections
│   │   ├── dist
│   │   │   ├── meta.json
│   │   │   ├── module.css
│   │   │   ├── module.js
│   │   │   └── module.js.map
│   │   ├── package-lock.json
│   │   ├── package.json
│   │   ├── readme.md
│   │   └── src
│   │       ├── meta.json
│   │       └── module.vue
│   ├── dashboard
│   │   ├── dist
│   │   │   ├── meta.json
│   │   │   ├── module.css
│   │   │   ├── module.js
│   │   │   └── module.js.map
│   │   ├── package-lock.json
│   │   ├── package.json
│   │   ├── readme.md
│   │   └── src
│   │       ├── meta.json
│   │       └── module.vue
│   ├── guides
│   │   ├── dist
│   │   │   ├── meta.json
│   │   │   ├── module.css
│   │   │   ├── module.js
│   │   │   └── module.js.map
│   │   ├── package-lock.json
│   │   ├── package.json
│   │   ├── readme.md
│   │   └── src
│   │       ├── meta.json
│   │       └── module.vue
│   ├── help
│   │   ├── dist
│   │   │   ├── meta.json
│   │   │   ├── module.css
│   │   │   ├── module.js
│   │   │   └── module.js.map
│   │   ├── package-lock.json
│   │   ├── package.json
│   │   ├── readme.md
│   │   └── src
│   │       ├── meta.json
│   │       └── module.vue
│   ├── icons
│   │   ├── dist
│   │   │   ├── meta.json
│   │   │   ├── module.css
│   │   │   ├── module.js
│   │   │   └── module.js.map
│   │   ├── package-lock.json
│   │   ├── package.json
│   │   ├── readme.md
│   │   └── src
│   │       ├── meta.json
│   │       └── module.vue
│   ├── modules
│   │   ├── dist
│   │   │   ├── meta.json
│   │   │   ├── module.css
│   │   │   ├── module.js
│   │   │   └── module.js.map
│   │   ├── package-lock.json
│   │   ├── package.json
│   │   ├── readme.md
│   │   └── src
│   │       ├── meta.json
│   │       └── module.vue
│   ├── notifications
│   │   ├── dist
│   │   │   ├── meta.json
│   │   │   ├── module.css
│   │   │   ├── module.js
│   │   │   └── module.js.map
│   │   ├── package-lock.json
│   │   ├── package.json
│   │   ├── readme.md
│   │   └── src
│   │       ├── meta.json
│   │       └── module.vue
│   ├── reports
│   │   ├── dist
│   │   │   ├── meta.json
│   │   │   ├── module.css
│   │   │   ├── module.js
│   │   │   └── module.js.map
│   │   ├── package-lock.json
│   │   ├── package.json
│   │   ├── readme.md
│   │   └── src
│   │       ├── components
│   │       │   ├── charts
│   │       │   │   ├── bar.vue
│   │       │   │   ├── doughnut.vue
│   │       │   │   ├── pie.vue
│   │       │   │   └── radar.vue
│   │       │   └── tables
│   │       │       └── table.vue
│   │       ├── meta.json
│   │       └── module.vue
│   └── search
│       ├── dist
│       │   ├── meta.json
│       │   ├── module.css
│       │   ├── module.js
│       │   └── module.js.map
│       ├── package-lock.json
│       ├── package.json
│       ├── readme.md
│       └── src
│           ├── components
│           │   └── tables
│           │       └── table.vue
│           ├── meta.json
│           └── module.vue
├── scripts
│   └── script.js
├── src
│   ├── core
│   │   └── Directus
│   │       ├── Authentication
│   │       │   └── Provider.php
│   │       ├── Database
│   │       │   └── TableGateway
│   │       │       └── RelationalTableGateway.php
│   │       └── Mail
│   │           └── Mailer.php
│   └── helpers
│       └── mail.php
├── styles
│   └── style.css
└── vendor
```

#### Core
Core Directus Files that have been edited - may also be included in a Pull Request

#### Endpoints
Custom endpoints are easy to create files that return an array with the endpoint path, method, and handler.

#### Helpers
Custom helper classes are to allow for use within Directus custom internal ecosystem as well as the custom endpoints.

#### Hooks
Directus provides event hooks for all actions performed within the App or API. You can use these hooks to run any arbitrary code when a certain thing in the system happens. You can use this for automated build triggers, webhooks, or other automated systems.

#### Interfaces
Interfaces allow for different ways of viewing and interacting with field data. These interfaces are primarily used on the edit form of the Item Detail page, but also render readonly data on the Item Browse page.
> An interface is made up out of three required core files. You can create a layout from scratch or use the [extension toolkit](https://github.com/directus/extension-toolkit) to generate boilerplate code.
When syncing Interfaces, be sure to only sync the `dist` directories!

#### Layouts
Layouts are different ways to view or even interact with data on the Item Browse page. Directus includes List and Card layouts out-of-the-box, but others can easily be created to fit specific needs.

#### Mail
Custom Mail Twig templates.

#### Modules
Modules are a way to add full-featured modules to Directus. You can build page modules for custom dashboards, reporting, point-of-sale systems, or anything else. 
Each page is protected within the auth gateway and can easily access project data and global variables.
When syncing Modules, be sure to only sync the `dist` directories!

#### Vendor
Vendor dependents of the custom endpoints or helpers.
Avoids changing the core Directus Composer Settings.
For use mainly in the API, for Modules and Interfaces, use the package.json to manage dependents.
