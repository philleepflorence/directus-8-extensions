/*
 * This is a custom script file that is loaded for each instance on each page.
 *
 * Use to it add whatever client scripts your project might need
 */
(function() {
    const timers = {
        loaded: 0
    };
    
    /*
	   Force dark mode for all users...
   */

    document.body.classList.add('dark');

    /*
	   Load all Required Collections
   */

    this.load = function() {

        this.get("/app/collections", function(response) {
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
    
    this.header = (input) => {
	    if (this.$currHash === this.$hash) return false;
	    
	    this.$currHash = window.location.hash.split('?').shift();
	    
	    let title = (input.id.indexOf('-title') > 0 || input.id.indexOf('-name') > 0) ? input.value : null;
	    let ID = this.$hash.split('?').shift().split('/').pop();
	    let HTML = `${ this.$title.innerHTML } - ${ ID }.`;	
	    
	    if (title) HTML = `${ HTML } ${ title }`; 
	    
	    if (HTML && this.$title && !this.$title.getAttribute('data-title') !== HTML) {
		    this.$title.innerHTML = HTML;		    
		    this.$title.setAttribute('data-title', HTML);
	    }	    
    };

    this.mutation = (mutations) => {
        let hash = window.location.hash.split('?').shift();

        if (this.$hash !== hash) setTimeout(this.bookmarks, 650);

        this.$hash = hash;
        this.$title = this.$page.querySelector('.page-root header.v-header .title .type-title');

        for (mutation of mutations) {
	        let input = mutation.target.querySelector('.field input');
	        
	        if (this.$title && input) this.header(input);
	        
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
	    let cellWidth = Math.floor((this.$page.offsetWidth - 135) * 0.2);
	    let style = document.createElement('style');
	    	style.innerHTML = `.v-table .toolbar .cell, .v-table .body .cell { flex-basis: ${ cellWidth }px !important; }`;
	    
	    document.head.appendChild(style);
    };

    /*
	   Custom Window Events
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

    this.get = function(url, done) {
        var request = new XMLHttpRequest();

        request.addEventListener("load", function(response) {
            return done(JSON.parse(this.response));
        });

        request.open("GET", url);
        request.send();
    };
})();