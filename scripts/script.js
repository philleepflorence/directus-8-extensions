/*
 * This is a custom script file that is loaded for each instance on each page.
 *
 * Use to it add whatever client scripts your project might need
 */
(function() {
    let timers = {
        loaded: 0,
        logo: 0,
        mutation: 0
    };
    
    if (window.location.hash === "#/") window.location.hash = "#/app/ext/dashboard";
    
    const get = function(url, done) {
        let request = new XMLHttpRequest();

        request.addEventListener("load", function(response) {
            return done(JSON.parse(this.response));
        });

        request.open("GET", url);
        request.send();
    };
    
    const projectName = String( getComputedStyle(document.documentElement).getPropertyValue('--project-name') ).replace(/"/g, '');
    const projectTagline = String( getComputedStyle(document.documentElement).getPropertyValue('--project-tagline') ).replace(/"/g, '');
    const isPublic = window.location.hash.indexOf('/login') > 0;
    
    
    if (isPublic) {
	    timers.logo = window.setInterval(function() {

	        const logo = document.querySelector('.container .logo');
	        
	        if (logo) {
		        clearInterval(timers.logo);
		        
		        document.title = `${ projectName } - ${ projectTagline }`;
		        
		        logo.href = window.location.href;
		        logo.target = "_self";
	        }
	
	    }, 1000);
    }
    
    /*
	   Force dark mode for all users...
   */

    document.body.classList.add('dark');

    /*
	   Load all Required Collections
   */

    this.load = function() {

        get("/app/collections", function(response) {
            let $collections = [];

            if (response.data) response.data.forEach(function(row) {
                if (row.managed) $collections.push({
                    collection: row.collection,
                    note: row.note,
                    icon: row.icon
                });
            });

            this.$collections = $collections;

            this.bookmarks();
        });
    };

    /*
	   Window Loaded Events
   */

    this.loaded = function() {
        clearInterval(timers.loaded);

        this.$hash = window.location.hash;
        this.$bookmarks = this.$menu.querySelectorAll('ul li.bookmark a');

        this.bookmarks();

        let observer = new MutationObserver(this.mutation);

        if (this.$page) observer.observe(this.$page, {
            childList: true,
            subtree: true
        });

        this.load();
        
        this.styles();
    };

    /*
	   Mutation Events - Navigation UI Updates
   */

    this.bookmarks = () => {
        if (!this.$bookmarks) return false;

        window._.forEach(this.$bookmarks, (bookmark) => {
            let collection = this.$hash.split('/')[3];
            let Collection = bookmark.getAttribute('href').split('/')[3];

            bookmark.classList.remove("router-link-active");

            /*
            	Set Bookmark Icon - Where applicable!
            */

            if (this.$collections) {
                let row = this.$collections.filter(function(currow) {
                    return currow.collection === Collection;
                });

                if (Array.isArray(row)) {
                    let icon = bookmark.querySelector('span.v-icon i');
                    row = row.shift();

                    if (icon) icon.innerHTML = row.icon;
                }
            }

            /*
            	Set Active Bookmark!
            */

            if (window.location.hash.indexOf('#/app/collections/') === 0) {

                if (collection === Collection) bookmark.classList.add("router-link-active");
            };
        });
    };
    
    this.header = () => {
	    let ID = this.$hash.split('?').shift().split('/').pop();
	    	    
	    if (this.$title.getAttribute('data-currpath') === this.$hash || !/\d/.test(ID)) return false;
	    
	    let input, fields = this.$page.querySelectorAll('.form [data-collection][data-field]');	    
	    let title, titles = ['title', 'name'];
	    
	    for (let field of fields) {
		    if (field && field.getAttribute && titles.includes(field.getAttribute('data-field'))) {
			    input = field.querySelector('.field input');
		    }
	    }
	    
	    if (input) title = input.value;
	    	    	    
	    if (!title) return false;	    
	    
	    let HTML = `${ this.$title.innerHTML } - ${ ID }. ${ title }`;	
	    
	    if (HTML && this.$title && !this.$title.getAttribute('data-currpath') !== this.$hash) {
		    this.$title.innerHTML = HTML;		    
		    this.$title.setAttribute('data-currpath', this.$hash);
	    }    
    };

    this.mutation = (mutations) => {
        let hash = window.location.hash.split('?').shift();

        if (this.$hash !== hash) setTimeout(this.bookmarks, 650);

        this.$hash = hash;
        this.$title = this.$page.querySelector('.page-root header.v-header .title .type-title');

        for (mutation of mutations) {
	        let input = mutation.target.querySelector('.field input');
	        
	        if (this.$title) {
		        clearTimeout(timers.mutation);		        
		        timers.mutation = setTimeout(this.header, 500);
	        }
	        
            if (mutation.type === "childList" && mutation.target && mutation.target.classList.contains('interface-wysiwyg')) {
                let iframe = mutation.target.querySelector('iframe');

                if (iframe) this.wysiwyg(iframe);
            }
        }
    };

    this.wysiwyg = (iframe) => {
        if (!iframe || iframe.getAttribute('data-rendered')) return false;

        iframe.setAttribute('data-rendered', Date.now());

        let StyleSheet = (window.location.origin + "/uploads/app/config/wysiwyg.css");
        let link = document.createElement("link");
	        link.href = StyleSheet;
	        link.rel = "stylesheet";
	        link.type = "text/css";

        iframe.contentDocument.head.appendChild(link);
    };
    
    this.styles = function () {
	    let cellWidth = Math.floor((this.$page.offsetWidth - 140) * 0.2);
	    let style = document.createElement('style');
	    	style.innerHTML = `.v-table .toolbar .cell, .v-table .body .cell { flex-basis: ${ cellWidth }px !important; }`;
	    
	    document.head.appendChild(style);
    };
        
    if (isPublic) return false;

    /*
	   Custom Window Events and Authenticated Events
   */

    let isScrolling;
    let scrollEnd = new Event('scrollend');

    window.addEventListener('scroll', function(event) {

        /*
        	Clear our timeout throughout the scroll
        */

        window.clearTimeout(isScrolling);

        /*
        	Set a timeout to run after scrolling ends
        */

        isScrolling = setTimeout(function() {

            document.dispatchEvent(scrollEnd);

        }, 300);

    }, false);

    timers.loaded = window.setInterval(function() {

        this.$menu = document.querySelector('.main-bar');
        this.$page = document.querySelector('.directus');

        if (this.$menu && this.$page) this.loaded();

    }, 1000);
})();