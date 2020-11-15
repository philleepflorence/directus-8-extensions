/*
 * This is a custom script file that is loaded for each instance on each page.
 *
 * Use to it add whatever client scripts your project might need
 */
(function () {
	/*
		All these timers are polyfills for event dispatchers!
		Mutations should also be events!
	*/
    let timers = {
        loaded: 0,
        logo: 0,
        mutation: 0,
        submit: 0,
        logout: 0
    };
    
    let index = {
	    tours: 0
    };
    
    const comments = () => {
	    let viewed = storage.get("comments.viewed");
	    let params = {
		    fields: "id",
		    limit: 1,
		    "filter[action][eq]": "comment"
	    };
	    
	    if (viewed) params["filter[action_on][gte]"] = viewed;
	    	    	    
	    get(`/${ this.projectKey }/activity`, 
	    params, 
	    (response) => {
		    if (response.data && response.data.length) this.$comments.setAttribute('data-badge', true);
		    else this.$comments.removeAttribute('data-badge');
	    });
    };

    const get = function (url, params, done) {
        let request = new XMLHttpRequest();

        request.addEventListener("load", function (response) {
            if (done) return done(JSON.parse(this.response));
        });
        
        if (params) {
	        params = new URLSearchParams(params).toString();	        
	        url = `${ url }?${ params }`;
        }

        request.open("GET", url, true);
        request.send();
    };
    
    const post = function (url, params, done) {
        let request = new XMLHttpRequest();

        request.addEventListener("load", function (response) {
            if (done) return done(JSON.parse(this.response));
        });
        
        params = new URLSearchParams(params).toString();

        request.open("POST", url, true);
        request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        request.send(params);
    };
    
    const storage = {
	    get: (key) => {
		    if (!window.localStorage) return null;
				
			let value = window.localStorage.getItem(key);
			
			if (typeof value === 'string') return JSON.parse(value);
			
			return null;
	    },
	    set: (key, input) => {
		    if (!window.localStorage) return null;
				
			if (input === null) return window.localStorage.removeItem(key);
			
			return window.localStorage.setItem(key, JSON.stringify(input));
	    }
    };

    const projectName = String(getComputedStyle(document.documentElement).getPropertyValue('--project-name')).replace(/"/g, '');
    const projectTagline = String(getComputedStyle(document.documentElement).getPropertyValue('--project-tagline')).replace(/"/g, '');
    const Public = window.location.hash.indexOf('/login') === 1;
    
    /*
	    Public Utilities!
	    TODO - Dispatch Login and Logout Events to replace intervals!
    */
    
    this.Public = () => {
	    const logo = document.querySelector('.container .logo');
        const submit = document.querySelector('form button[type="submit"]');

        if (!logo || !submit) return false;
        
        if (this.$hash && this.$hash !== window.location.hash) return window.location.reload();
        
        clearInterval(timers.logo);

        document.title = `${ projectName } - ${ projectTagline }`;

        logo.href = window.location.href;
        logo.target = "_self";
        
        logo.addEventListener('click', function () {
            return window.location.reload();
        });

        submit.addEventListener('click', this.run);
    };

    /*
	   Force dark mode for all users...
   */

    document.body.classList.add('dark');

    /*
	   Load all Required Collections and Tour Configuration
   */
	
	this.$data = {};

    this.load = () => {
	    
	    this.userID = this.$profile.getAttribute('href').split('/').pop();
	    this.$tours = null;
        
        get(`/${ this.projectKey }/custom/directus/app`, { user: this.userID }, function (response) {
	        if (!response.data) return false; 
	        
	        this.$data = response.data;       
	        
            this.collections();
            
            this.tour();
        });
    };

    /*
	   Mutation Events - Navigation UI Updates
   */

    this.bookmarks = () => {
        if (!this.$bookmarks) return false;
        
        let $menu = document.querySelector('.nav-bookmarks.menu-section');
        let $first = document.querySelector('.nav-menu.menu-section');
        let $title = document.querySelector('.title.has-breadcrumb h1.type-title');
        
        if (!$menu) return false;

        window._.forEach(this.$bookmarks, (bookmark) => {
            let collection = this.$hash.split('/')[3];
            let Collection = bookmark.getAttribute('href').split('/')[3];
            let textContent = bookmark.textContent.toLowerCase().split(' ');
            
            textContent.shift();
            
            textContent = textContent.join(' ');
            textContent = textContent.trim();

            bookmark.classList.remove("router-link-active");
            
            /*
            	Set Bookmark Icon - Where applicable!
            */ 

            if (this.$data.collections) {
                let row = this.$data.collections.filter(function (currow) {
	                if (currow.title) return currow.title.toLowerCase() === textContent;
                    
                    return currow.collection === textContent || currow.collection === Collection;
                });

                if (Array.isArray(row) && row.length) {
                    let icon = bookmark.querySelector('span.v-icon i');
                    	row = row.shift();

                    if (icon) icon.innerHTML = row.icon;
                }
            }

            /*
            	Set Active Bookmark!
            */

            if ($title && window.location.hash.indexOf(`#/${ this.projectKey }/collections/`) === 0 && collection === Collection) 
            	if (textContent === $title.textContent) 
            		bookmark.classList.add("router-link-active");
        });
        
        $menu.parentNode.insertBefore($menu, $first);
    };
    
    this.collections = () => {
	    if (Array.isArray(this.$data.collections)) {
            let $collections = [];
            
            this.$data.collections.forEach(function (row) {
                if (row.managed) {
	                $collections.push({
	                    collection: row.collection,
	                    note: row.note,
	                    icon: row.icon,
	                    title: row.title
	                });		                
                }
            });

            this.$data.collections = $collections;

            this.bookmarks();
        }
    };

    this.header = () => {
        let ID = this.$hash.split('?').shift().split('/').pop();

        if (this.$title.getAttribute('data-currpath') === this.$hash || !/\d/.test(ID)) return false;

        let input, fields = this.$page.querySelectorAll('.form [data-collection][data-field]');
        let title, titles = ['name', 'title'];

        for (let field of fields) {
            if (!input && field && field.getAttribute && titles.includes(field.getAttribute('data-field'))) {
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
    
    this.logo = () => {
	    let link = document.createElement("a");
	        link.href = `#/${ this.projectKey }/ext/application`;
	        
		this.$logo.appendChild(link);
    };

    this.mutation = (mutations) => {
        let hash = window.location.hash.split('?').shift();
        
        if (this.$hash !== hash) this.document.body.setAttribute('data-path', hash);

        if (this.$hash !== hash) setTimeout(this.bookmarks, 650);

        if (this.$hash !== hash) setTimeout(this.tour, 3550);

        this.$hash = hash;
        this.$title = this.$page.querySelector('.page-root header.v-header .title .type-title');
        
        let $page = this.$page.querySelector('.page-root');
        
        if ($page) {
	        let classList = [...$page.classList].join(" ");
	        
	        if (this.document.body.getAttribute('data-page-root') !== classList) this.document.body.setAttribute('data-page-root', classList);
        }

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
    
    /*
	    Pop State Events - Methods to call on history change
    */
    
    this.popstate = (event) => {	    
	    if (this.$comments) comments();
    };
    
    this.tour = () => {
	    if (Array.isArray(this.$data.tours)) {
            this.$data.toured = this.$data.toured || [];
            
            this.$tours = this.$data.tours.filter((tour) => {
	            let item = this.$data.toured.filter((row) => { return row.value === tour.element });
	            let $element = document.querySelector(tour.element);
	            
	            return item.length === 0 && $element;
            });
            
            this.tours();
        }
    };
    
    this.tours = (open) => {	    
	    let currtour = this.$tours[index.tours];
	    
	    if (!currtour) return false;
	    
	    let remainder = this.$tours.length - index.tours;
	    
	    let $button = document.querySelector('.directus-tour-button');	 
	    
	    if ($button) $button.remove();
	       
	    $button = document.createElement("span");
	    $button.innerHTML = '<span class="d-icon">add</span>';
	    $button.classList.add('directus-tour-button');
	    
	    let $element = document.querySelector(currtour.element);
	    	    
	    if (!$element) {
		    index.tours++;
		    
		    return this.tours();
	    }
	    
	    let positions = ['relative', 'absolute', 'fixed'];
	    let length = this.$tours.length;
	    
	    let $tourStart;
	    let $tourClose;
	    let $tourNext;
	    let $overlay;
	    
	    let $parent = currtour.parent ? document.querySelector(currtour.parent) : $element.parentElement;
	    let style = window.getComputedStyle($parent);
	    
	    if (!positions.includes(style.position)) $parent.style.position = 'relative';
	    	    	    
	    $parent.appendChild($button);
	    
	    this.tours.empty = () => {
		    $button.removeEventListener('click', this.tours.next);
		    $tourClose.removeEventListener('click', this.tours.close);
		    $tourNext.removeEventListener('click', this.tours.next);
		    
		    $parent.removeChild($button);
		    $parent.removeChild($overlay);
		    $parent.classList.remove('directus-tour-active');
		    
		    $tourClose = null;
		    $tourNext = null;
		    $element = null;
		    $parent = null;
		    $overlay = null;
		    $button = null;
		    
		    currtour = null;
	    };
	    
	    this.tours.close = (e) => {
		    if (e) e.stopPropagation();
		    if (e) e.preventDefault();
		    		    
		    this.tours.empty();	    
		    
		    $tourStart = document.createElement('span');
		    $tourStart.classList.add('directus-tour-start');
		    $tourStart.innerHTML = '<span class="d-icon">add</span>';
		    
		    this.$page.appendChild($tourStart);
		    
		    $tourStart.addEventListener('click', this.tours);
	    };
	    
	    this.tours.next = (e) => {
		    if (e) e.stopPropagation();
		    if (e) e.preventDefault();
		    
		    this.tours.empty();
		    
		    index.tours++;
		    
		    this.tours(true);
	    };
	    
	    this.tours.open = (e) => {
		    if (e) e.stopPropagation();
		    if (e) e.preventDefault();
		    
		    style = null;
		    
		    let icon = remainder > 1 ? 'navigate_next' : 'close';
		    
		    $overlay = document.createElement('div');
	    	$overlay.classList.add('v-overlay', 'directus-tour-overlay', 'active');
	    	$overlay.innerHTML = `
	    		<div id="directus-tour-close" class="overlay"></div>
	    		<section class="directus-tour-content">
		    		<header class="directus-tour-content">
		    			<h3 class="directus-tour-title">${ currtour.title }</h3>
		    			<p class="directus-tour-content">${ currtour.content }</p>
		    		</header>
	    			<footer class="directus-tour-content">
	    				<span class="d-count">${ index.tours + 1 } / ${ length }</span>
	    				<span id="directus-tour-next" class="d-icon"><span>${ icon }</span></span>
	    			</footer>
    			</section>
	    	`;
			
			$parent.appendChild($overlay);
			$parent.classList.add('directus-tour-active');
			
			$tourClose = document.getElementById('directus-tour-close');
			$tourNext = document.getElementById('directus-tour-next');
			
			if ($tourClose) $tourClose.addEventListener('click', this.tours.close);
			if ($tourNext) $tourNext.addEventListener('click', this.tours.next);
		    
		    $tourStart = document.querySelector('.directus-tour-start');
		    
		    if ($tourStart) $tourStart.removeEventListener('click', this.tours);
			if ($tourStart) $tourStart.remove();
			
			$button.removeEventListener('click', this.tours.open);
			$button.addEventListener('click', this.tours.next);
			
			post(`/${ this.projectKey }/custom/directus/metadata`, {
				user: this.userID,
				section: 'tour',
				key: currtour.key,
				value: currtour.element
			}, 
			(response) => {
				if (response && response.data) this.$data.toured.push(response.data);
			});
	    };
	    
	    if (open) this.tours.open();
	    else $button.addEventListener('click', this.tours.open);
	    
	    return false;
    };

    this.wysiwyg = (iframe) => {
        if (!iframe || iframe.getAttribute('data-rendered')) return false;

        iframe.setAttribute('data-rendered', Date.now());

        let StyleSheet = (window.location.origin + "/admin/lib/styles/wysiwyg.css");
        let link = document.createElement("link");
	        link.href = StyleSheet;
	        link.rel = "stylesheet";
	        link.type = "text/css";

        iframe.contentDocument.head.appendChild(link);
    };

    this.styles = function () {
	    let offset = window.innerWidth >= 1801 ? 1440 : (this.$page.offsetWidth || 1280);
        let cellWidth = Math.floor((offset - ( 32 * 2 ) - 86) * 0.2);
        let style = document.createElement('style');
	        style.id = 'v-table-toolbar-cell';
	        style.innerHTML = `.v-table .toolbar .cell, .v-table .body .cell { flex-basis: ${ cellWidth }px !important; }`;

        document.head.appendChild(style);
    };

    /*
	   Window Loaded Events
   */

    this.loaded = function () {
        clearInterval(timers.loaded);
        
        let hash = window.location.hash.split('?').shift(); 
        
        this.document.body.setAttribute('data-path', hash.replace('/#', ''));
        
        this.$container = this.$page.parentElement;

        this.$hash = window.location.hash;
        this.$bookmarks = this.$menu.querySelectorAll('ul li.bookmark a');

        this.bookmarks();

        let observer = new MutationObserver(this.mutation);

        observer.observe(this.$page, {
            childList: true,
            subtree: true
        });
        
        $logout.addEventListener('click', () => {
	        timers.logo = window.setInterval(this.Public, 1000);
        });
        
        this.$menu.addEventListener('mouseover', this.scrollbar);
        this.$menu.addEventListener('mouseout', this.scrollbar);
        
        this.$page.addEventListener('mouseover', this.scrollbar);
        this.$page.addEventListener('mouseout', this.scrollbar);
        
        window.addEventListener('popstate', this.popstate);
        
        this.popstate();

        this.load();

        this.styles();
        
        this.logo();
    };

    /*
	   Custom Window Events and Authenticated Events
	*/
   
	this.scrollbar = function (e) {	
		if (this.scrolling) return false;
			
		if (e.currentTarget.classList.contains('main-bar')) {
			if (e.type === 'mouseover') return e.currentTarget.classList.add('scrollbar-active');
			else if (e.type === 'mouseout') return e.currentTarget.classList.remove('scrollbar-active');
		}
		
		let content;
		let overflow = getComputedStyle(document.body).overflow;
				
		if (e.currentTarget.classList.contains('body') && e.currentTarget.scrollHeight > e.currentTarget.offsetHeight) content = e.currentTarget;
		
		if (!content) content = e.target.closest('.layout-cards') || e.target.closest('.body') || e.target.closest('.content') || e.target.closest('.interface-icon');
				
		if (content && e.type === 'mouseover' && content.scrollHeight > content.offsetHeight) return content.classList.add('scrollbar-active');
		else if (content && e.type === 'mouseout' && content.scrollHeight > content.offsetHeight) return content.classList.remove('scrollbar-active');
		
		let page = e.target.closest('.edit.page-root') || e.target.closest('.settings.page-root') || e.target.closest('.settings-fields.page-root') || e.target.closest('.module-page-root.page-root') || e.target.closest('.collections.page-root');
				
		if (page && e.type === 'mouseover' && overflow !== "hidden") return document.body.classList.add('scrollbar-active');
		else if (page && e.type === 'mouseout' && overflow !== "hidden") return document.body.classList.remove('scrollbar-active');
	};

    this.run = function () {

        let isScrolling;
        let scrollEnd = new Event('scrollend');
        
        this.projectKey = window.location.hash.replace("#/", "").split("/").shift();
        
        window.addEventListener('scroll', function (event) {

            /*
            	Clear our timeout throughout the scroll
            */

            window.clearTimeout(isScrolling);
            
            if (!this.scrolling) document.body.classList.add('scrolling');
            
            this.scrolling = true;

            /*
            	Set a timeout to run after scrolling ends
            */

            isScrolling = setTimeout(function () {

                document.dispatchEvent(scrollEnd);
                
                document.body.classList.remove('scrolling');
                
                this.scrolling = false;

            }, 300);

        }, false);

        timers.loaded = window.setInterval(function () {

            this.$comments = document.querySelector(`.module-bar a.link[href="#/${ this.projectKey }/ext/comments"]`);
            this.$menu = document.querySelector('.main-bar');
            this.$page = document.querySelector('.directus');
            this.$logo = document.querySelector('.module-bar .logo.v-logo');
            this.$profile = document.querySelector('.module-bar a.edit-user');
            this.$logout = document.querySelector('.module-bar button.sign-out');           
                        
            if (window.location.hash === "#/") window.location.hash = `#/${ this.projectKey }/ext/dashboard`;

            if (this.$menu && this.$page && this.$logo && this.$profile) this.loaded();
            
        }, 1000);
    }; 
    

    if (!Public) this.run();    
    else if (Public) timers.logo = window.setInterval(this.Public, 1000);
})();