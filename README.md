# Directus 8 Extensions - Philleep Florence
> The Philleep Florence Extended Version of [Directus 8](https://github.com/directus/v8-archive) is an amagamation of Directus App and API Extensions, UI and UX Additions.
> 

## Guides
*Instructions and Utility Information*

#### What is the Context for Value, Utility, and Purpose
Directus 8 Suite is used by Philleep Florence for *Data Management*, *Automation*, *Efficiency*, *Pattern Recognition*, and *Third Party API Management Tools*.   
When one or more requests are made for an update or feature addition to maintained instances of Directus, the *non-conflicting*, *NDA abiding* version is added to the repository.  
These extensions may not be suitable when Directus 8 is used primarily for Content Management.  
These repository also automates the upgrade of multiple instances of Directus 8 across servers or platforms. 

#### What is the Learning Objective Context
**Guide** 
A concise document that offers information or instruction; Template or Reference Guide 
- How to use something
- What is the utility information

**Concept**.
A description, interpretation or generalization of technical components or utilities 
(A description of supported operations on a type, including their syntax and semantics.); 
Analogy or Illustration Concept 
- What is the context
- What does something do

**Reference**
A collection of terms, definitions, or references (identities, definitions, and sources), 
which is not intended to be read from beginning to end, and is compiled for ease of reference.
- What is the identity
- What is the definition 
- What is the knowledge reference or source

### Getting Started Guide
The following directories will need to be synced to your Directus installation to apply the Global UI and UX Styles and Scripts:
```
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
If you move the snippet to another file, be sure to change the `__DIR__` variable!

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
For now, use this repository as a guide or reference for creating your own extensions.  
See References and Tools for the link to a live instance of the Directus App and API Extensions, UI and UX Additions.

*End of Guides*
***

## Concepts
*Context and Descriptive Information*

### Terminology


### PhilleepEdit
A Project Identification for the collection of Extensions added to Directus Codebase.  
Previously a custom Data Management Application, before the discovery of Directus.

### Nomenclature
A set of rules used for forming the names or terms in a particular field of arts or sciences.

#### How to use Nomenclature for Extensions, Endpoints, Directories, and Files
Custom Endpoints follow the URL Structure and Format - `/app/custom/{controller}/{method}`. 
The directory containing an Extension is named after the `controller`, and the Controller Files correspond the the `method`.
Helper Files and Classes are named after the `controller`, with matching methods that correspond to the `method`; 

#### How to use Nomenclature for Collections
- `app_` - Provides a nature context and value for automation or pattern rules for collections that hold templates, configuration, or utility for Applications; reserved for Administrative Roles. 
	- `app_pages` - Holds data, content templates. options, or configuration for the "pages" of a web app. 
	- `app_navigation` - Holds data, templates, or configuration for the "navigation" for a web app.
- `contents_` - Provides a nature context and automation rules for collections that hold content displayed in Applications; available to Content Roles. 
	- `contents_posts` - Holds data, content, and configuration for *Blog*, *Post*, or *Article* Page Templates in `app_pages`.
	- `contents_components` - Holds data, content, and configuration for collections that have dynamic Content Blocks, Forms or Components. 
- `data_` - Provides a nature context and automation rules for collections that hold data used by other collections or tools, but not displayed in Applications; reserved for Webmaster and Technical Roles. 
	- `data_app_configuration` - Configuration, Options, or Settings for web apps. 
- `meta_` - Provides a nature context and automation rules for collections that hold dynamic structured data for *App*, *Contents*, and *Directus* collections via *O2M* or *M20* Interfaces; obeys the rules for the Primary Collection.
- `joins_` - Provides a nature context and automation rules for collections that hold dynamic structured data for *App*, *Contents*, *Data*, or *Meta* collections, but not displayed in Applications; reserved for Webmaster Roles.

#### Core Directory
Core Directus Files that have been edited - may also be included in a Pull Request in the Directus 8 GitHub Repository.

#### Endpoints Directory
Name Space Nomenclature - `namespace Directus\Custom\Endpoints\{class};`
Custom endpoints are easy to create files that return an array with the endpoint path, method, and handler.

#### Helpers Directory
Name Space Nomenclature - `namespace Directus\Custom\Helpers`.
Custom helper classes are to allow for use within the Directus custom internal ecosystem as well as the custom endpoints.  
Helper filenames correspond to the endpoint directory names.

#### Hooks Directory
Directus provides event hooks for all actions performed within the App or API. You can use these hooks to run any arbitrary code when a certain thing in the system happens. You can use this for automated build triggers, webhooks, or other automated systems.

#### Interfaces Directory
Interfaces allow for different ways of viewing and interacting with field data. These interfaces are primarily used on the edit form of the Item Detail page, but also render readonly data on the Item Browse page.
> An interface is made up out of three required core files. You can create a layout from scratch or use the [extension toolkit](https://github.com/directus/v8-archive/tree/master/extension-toolkit) to generate boilerplate code.
When syncing Interfaces, be sure to only sync the `dist` directories!

#### Layouts Directory
Layouts are different ways to view or even interact with data on the Item Browse page. Directus includes List and Card layouts out-of-the-box, but others can easily be created to fit specific needs.

#### Mail Directory
Custom Mail Twig templates.

#### Modules Directory
Modules are a way to add full-featured modules to Directus. You can build page modules for custom dashboards, reporting, point-of-sale systems, or anything else. 
Each page is protected within the auth gateway and can easily access project data and global variables.
When syncing Modules, be sure to only sync the `dist` directories!

#### Vendor Directory
Vendor dependents of the custom endpoints or helpers.
Avoids changing the core Directus Composer Settings.
For use mainly in the API, for Modules and Interfaces, use the package.json to manage dependents.

*End of Concepts*
***

# References
*Resources and Directory References*

### Dependent and Requirement References
[PHP - API or Server Extensions](https://www.php.net)  
[Vue - Client or Component Extensions](https://vuejs.org). 

### Reference Resources
[Directus 8 - GitHub Repository](https://github.com/directus/v8-archive)  
[PhilleepEdit - Directus 8 Demo](https://api.philleepedit.com). 

### Folder Structure and Extensions Reference

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
