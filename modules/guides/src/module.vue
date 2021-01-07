<template>
	<div class="modules-guides module-page-root">
		
		<v-header 
			:title="content('title')" 
			:breadcrumb="breadcrumb" 
			:icon="icon" 
			settings>
			<template name="buttons">
				<v-button v-if="buttons.back" @click="onClickRoute(-1)" background-color="--main-primary-color" icon rounded><v-icon name="arrow_back"></v-button>
				<v-button v-if="buttons.forward" @click="onClickRoute(+1)" background-color="--main-primary-color" icon rounded><v-icon name="arrow_forward"></v-button>
			</template>
		</v-header>
			
		<v-progress-linear 
			v-if="processing"
			color="var(--main-primary-color)" 
			background-color="var(--blue-grey-900)"
			:value="processing"
			:top="true"
			:absolute="true"
			height="5">
		</v-progress-linear>
		
		<div class="modules-module-loading" v-if="loading">
			<div class="flex-item">
				<v-spinner
					v-show="loading"
					line-fg-color="var(--blue-grey-300)"
					line-bg-color="var(--blue-grey-200)"
					class="spinner">
				</v-spinner>
			</div>
		</div>
		
		<div class="modules-module-content modules-content animated fadeIn" v-else-if="loaded">
			
			<section class="modules-module-markdown modules-module-section" :ref="`section`" v-if="section">
				<h1 class="modules-module-section" :id="section.slug" v-html="section.headline"></h1>
				<p class="text-muted modules-module-section" v-html="section.description"></p>
				<hr>
				<p v-for="(link, slug) in section.nav">
				<a class="info-sidebar-nav" href="#" :data-href="link" :data-curl="section.curl" @click.stop.prevent="onClickNavigation($event, link)">
					<span class="info-sidebar-nav-icon"><v-icon :name="getIcon(slug, group)" left></span>
					<span class="info-sidebar-nav-text">{{ formatStartCase(slug) }}</span>
				</a>
				</p>
			</section>
			
			<div class="modules-module-markdown" :ref="`markdown`" v-html="html" @click.stop.prevent="onClickContentLink" v-else-if="html"></div>
			
			<div class="modules-module-markdown" v-else-if="results">
				<ol class="modules-module-results">
					<li v-for="row in search">
						<p v-html="row.text"></p>
						<a href="#" @click.stop.prevent="onClickNavigation($event, row.path)">{{ formatStartCase(row.slug) }}</a>
					</li>
				</ol>
			</div>
			
		</div>

		<v-info-sidebar wide itemDetail>
			<section class="info-sidebar-section">
				<h2 class="font-accent">{{ content('title') }}</h2>
				<p class="p">{{ content('description') }}</p>
				<p class="p" v-for="disclaimer in content('disclaimer')" v-html="disclaimer"></p>
			</section>
			<section class="info-sidebar-section info-sidebar-search">
				<h2 class="font-accent">{{ content('form.search.headline') }}</h2>								
				<div class="info-sidebar-row">
					<v-input
						id="modules-help-info-sidebar-input"
						type="search"
						:placeholder="content('form.search.placeholder')"
						:model="query"
						@input="onInputSearch"
						@keyup.enter="onSubmitSearch">
					</v-input>
				</div>
				<div class="info-sidebar-row">
					<v-button
						id="modules-help-info-sidebar-button"
						type="button" 
						@click="onSubmitSearch"
						block>
						{{ content('form.search.submit') }}
					</v-button>
				</div>
			</section>
			<section class="info-sidebar-section info-sidebar-nav" v-if="viewresults">
				<div class="info-sidebar-row">
					<v-button
						id="modules-help-info-sidebar-results"
						type="button" 
						@click="onClickResults"
						block>
						{{ content('form.search.results') }}
					</v-button>
				</div>
			</section>
			<section class="info-sidebar-navigation" v-if="navigation">
				<nav class="info-sidebar-section info-sidebar-nav" v-for="(section, group) in navigation" v-if="section.nav" :data-section="group">
					<h2 class="font-accent" @click.stop.prevent="onClickNavigation($event, `/${ group }`)">{{ section.headline }}</h2>
					<a class="info-sidebar-nav" href="#" :data-href="link" v-for="(link, slug) in section.nav" :data-curl="section.curl" @click.stop.prevent="onClickNavigation($event, link)">
						<span class="info-sidebar-nav-icon"><v-icon :name="getIcon(slug, group)" left></span>
						<span class="info-sidebar-nav-text">{{ formatStartCase(slug) }}</span>
					</a>
				</nav>	
			</section>		
		</v-info-sidebar>
		
	</div>
</template>

<script>
	import { cloneDeep, forEach, get, kebabCase, set, size, startCase, trimStart } from 'lodash';
	import marked from 'marked';
	import sbd from 'sbd';
	
	import $meta from './meta.json';
	
	export default {
		name: 'ModulesGuides',
		data () {
			return {
				cache: {},
				contents: $meta.contents,
				curr: {
					nav: null,
					path: null,
					scrolled: null
				},
				div: null,
				html: null,
				section: null,
				query: null,
				icon: $meta.icon,
				navigation: null,
				menu: {},
				loading: false,
				loaded: false,
				processing: 0,
				search: [],
				results: null,
				markdown: {
					patterns: [
						{
							pattern: /:::tip/gm,
							text: '<blockquote data-tip>'
						},
						{
							pattern: /:::\stip/gm,
							text: '<blockquote data-tip>'
						},
						{
							pattern: /:::\swarning/gm,
							text: '<blockquote data-tip="warning">'
						},
						{
							pattern: /:::\sdanger/gm,
							text: '<blockquote data-tip="danger">'
						},
						{
							pattern: /:::/gm,
							text: '</blockquote>'
						},
						{
							pattern: /src="\.\.\/img/gm,
							text: `src="${ $meta.github.baseURL }/img`
						},
						{
							pattern: /^<p>\﻿#\s/gm,
							text: '<p class="h1">'
						},
						{
							pattern: /<blockquote data-tip>/gm,
							text: '<blockquote data-tip><p>'
						},
						{
							pattern: /<\/blockquote>/gm,
							text: '</p></blockquote>'
						},
						{
							pattern: /<br>\\*/gm,
							text: '</p><p>'
						}
					],
					options: {
						breaks: true,
						renderer: new marked.Renderer()
					}
				},
				routes: [],
				buttons: {
					back: false,
					forward: false
				}
			};
		},
		computed: {
			breadcrumb () {
				return [
					{
						name: 'Dashboard',
						path: `/${this.currentProjectKey}/ext/dashboard`
					},
					{
						name: 'Modules',
						path: `/${this.currentProjectKey}/ext/modules`
					}
				];
			},
			currentProjectKey () {
				return this.$store.state.currentProjectKey;
			},
			locale () {
				return get(this.$store.state, 'settings.values.default_locale');
			},
			params () {
				const path = window.location.hash.split("?");
				
				return new URLSearchParams(path.pop());
			},
			url () {
				return [
					window.location.origin,
					window.location.pathname,
					"#"
				].join('');
			},
			viewresults () {
				return this.results && this.query && this.html;
			}			
		},
		methods: {
			get (path, params, callback) {
				this.loading = true;
				
				let fragments = trimStart(path, '/').replace('.md', '').split('/');
				let key = `/${ fragments.join('/') }`;
				let cache = get(this.cache, key);
				
				if (fragments.length === 1) {
					this.loading = false;
					
					let section = get(this.navigation, fragments[0]);
					
					section = {...{ slug: fragments[0] }, ...section};
					
					return callback(null, section);
				}
				
				if (cache) {
					this.loading = false;
					
					return callback(cache, null);
				}
				
				this.$api.api.get('/custom/curl/content', params).then((response) => {
					
					let html = marked(response.content, this.markdown.options);
						html = this.render(html);
						
					set(this.cache, path.split('.').shift(), html);
					
					this.loading = false;
					
					return callback(html, null);
					
				}).catch((error) => {
					
					this.error = error;

					this.loading = false;
					
				});
			},
			getIcon (slug, group) {
				let icon = get(this.navigation, `${ group }.icons.${ slug }`);
				
				if (icon) return icon;
				
				return "book";
			},
			content (input) {
				let translation = get(this.contents, this.locale);
					translation = translation || get(this.contents, 'en-US');

				return get(translation, input);
			},
			formatStartCase (input) {
				return startCase(input);
			},
			loadItems () {				
				let item = this.params.get("item");
					item = item ? `/${ item.replace('.md', '/').replace('.', '/') }` : $meta.github.index;
				
				this.$api.getItems('contents_faqs', {
					filter: {
						application: "directus"
					},
					fields: "question,slug,answer,category,icon"
				})
				.then((response) => {					
					this.renderMenu(response.data, item);			
										
					this.loadDocs(item, null, true);									
				})
				.catch((error) => {
					this.error = error;
					
					this.renderMenu(null, item);
					
					this.loadDocs(item, null, true);
				});
			},
			loadDocs (path, element, cache) {				
				let route = {
					path: path,
					element: element
				};
				
				this.curr.path = path;
				
				window.scrollTo(0, 0);
				
				if (!this.$back && !this.$forward) this.routes.push(route);
				
				this.processMenu();
				
				let url = $meta.github.baseURL + path;
								
				this.get(path, {
					url: url
				}, 
				(html, section) => {
					this.section = section;
					this.html = html;				
					
					this.loaded = true;
					
					this.element = element;
					
					if (!this.$back) {
						forEach(this.routes, (row, index) => {
							if (row.path === route.path) this.$index = index;
						});						
					}
					
					this.$path = route;
					
					this.renderButtons();
					
					this.processMenu(path);					
										
					if (cache) this.loadPages(1, element);					
				});
			},
			loadPages (loaded) {			
				this.$notify({
					title: this.content('form.search.caching'),				
					color: 'orange',				
					iconMain: 'cloud_download',				
					delay: 2000				
				});
				
				let len = size(this.menu);
												
				forEach(this.menu, (path) => {
					let url = $meta.github.baseURL + path;
					
					this.get(path, {
						url: url
					}, 
					(html) => {
						loaded = loaded + 1;
						
						this.processing = Math.round((loaded / len) * 100);
						
						if (loaded >= len) this.processing = 0;
					});
				});			
			},
			onClickNavigation (e, link) {								
				if (this.$back) this.routes = this.routes.splice(0, this.$index);
								
				this.loadDocs(link);
			},
			onClickContentLink (e) {
				let href = e.target.href.replace(window.location.origin, '');
				
				if (!href) return false;
				
				if (href.indexOf('/admin/') === 0) return window.location.assign(href);
				else if (href.indexOf('#/app/') === 0) return this.$router.push(href.replace('#/', '/'));
				
				let path = e.target.getAttribute('href');
				let fragments = trimStart(this.$path.path, '/').split('/');
				
				if (fragments.length && path.indexOf('./') === 0) href = href.replace('/admin/', `/${ fragments[0] }/`);
				
				if (e.target.href && e.target.href.indexOf(window.location.origin) === 0) {
					href = href.replace('.html', '.md');
					href = href.split('#');
					
					this.loadDocs(href[0], href[1]);					
				}
				else window.open(e.target.href);				
			},
			onClickResults () {
				this.html = null;
			},
			onClickRoute (direction) {
				if (direction < 0) this.$back = true;
				else if (direction > 0) this.$forward = true;
				
				this.$index = (this.$index + direction);
				
				let route = this.routes[this.$index];				
				
				if (!route || !route.path) return false;			
												
				this.loadDocs(route.path, route.element);
			},	
			onInputSearch (input) {
				this.query = input;
				
				if (input === '') {
					this.query = null;
					
					this.loadDocs($meta.github.index);
				}
			},		
			onSubmitSearch (input) {			
				this.$notify({
					title: this.content('form.search.loading'),				
					color: 'orange',				
					iconMain: 'search',				
					delay: 2000				
				});	
				
				let loaded = 0;
				let len = size(this.menu);
				
				this.html = this.content('form.search.processing');
				
				this.processMenu(false);
				
				forEach(this.menu, (path) => {
					let url = $meta.github.baseURL + path;
					
					
					this.get(path, {
						url: url
					}, 
					(html) => {
						loaded = loaded + 1;
						
						this.processing = Math.round((loaded / len) * 100);
						
						if (loaded >= len) this.renderSearch();
					});
				});					
			},
			processHTML (input) {
				this.div.innerHTML = input;
				
				let headers = this.div.querySelectorAll("h1, h2, h3, h4, h5, h6");
				
				forEach(headers, (header) => {
					if (!header.id) header.id = kebabCase(header.textContent);
				});	
				
				if (size(headers)) return this.div.innerHTML;	
				
				return input;
			},
			processMenu (path) {
				if (path) {
					if (this.curr.nav) this.curr.nav.classList.remove('active');
				
					this.curr.nav = this.$el.querySelector(`.info-sidebar-nav [data-href="${ path }"]`);
					
					if (this.curr.nav) this.curr.nav.classList.add('active');
				}
				else if (!path && this.curr.nav) this.curr.nav.classList.remove('active');
			},			
			render (input) {
				this.markdown.patterns.forEach((pattern) => {
					input = input.replace(pattern.pattern, pattern.text)
				});
				
				return this.processHTML(input);
			},
			renderButtons () {
				let max = this.routes.length - 1;
				
				if (this.$index >= max) this.buttons.forward = false;
				else this.buttons.forward = true; 
				
				if (this.$index <= 0) this.buttons.back = false;
				else this.buttons.back = true;
				
				this.$back = false;
				this.$forward = false;
			},
			renderHeading (text, level) {
				let id = text.toLowerCase().replace(/[^\w]+/g, '-');
					id = `modules-guides-${ id }`.replace(/-{2,}/g, '-');
				
				return `<h${ level } id="${ id }">${ text }</h${ level }>`;
			},
			renderMenu (items, path) {
				this.navigation = cloneDeep($meta.github.navigation);
				
				if (items) {
					forEach(items, (item) => {
						const html = this.processHTML(item.answer);
						
						set(this.cache, `/${ item.category }/${ item.slug }`, html);
						
						set(this.navigation, `${ item.category }.icons.${ item.slug }`, item.icon);
						set(this.navigation, `${ item.category }.nav.${ item.slug }`, `/${ item.category }/${ item.slug }`);
					});
				}
				
				forEach(this.navigation, (section) => {
					forEach(section.nav, (value, key) => {
						set(this.menu, key, value);
					});
				});
				
				if (path) this.processMenu(path);
			},
			renderSearch () {
				
				this.search = [];
				let closingTag = new RegExp("(</.*?>)", "gi");
				let pattern = new RegExp('(' + this.query + ')', 'gi');
				
				forEach(this.cache, (html, path) => {
					let plaintext = this.stripHtml(html.replace(closingTag, " $1").replace(/\s\s+/g, ' '));
					let sentences = sbd.sentences(plaintext, {
						newline_boundaries: true,
						html_boundaries: true,
						sanitize: true
					});
					
					forEach(sentences, (sentence) => {
						if (sentence.search(pattern) > 0) this.search.push({
							path: `${ path }.md`,
							text: sentence.trim(),
							slug: path.split('/').pop().replace('.md', '')
						});
					});				
				});
				
				this.processing = 0;
				this.loading = false;
				
				this.results = this.search.length;
				
				if (!this.results) this.html = this.content('form.search.empty');
				else this.html = null;
			},
			scrollElement (element) {
				let $element = document.getElementById(element);
				console.log("debug - development: scrollElement", element, $element);
				
				if (!$element) return false;
				
				$element.classList.add('active');
				
				this.curr.scrolled = element;
				
				setTimeout(() => {
					$element.scrollIntoView({
						block: "center"
					});
				}, 300);
			},
			stripHtml (html) {
				let div = document.createElement("div");
				
				div.innerHTML = html;
				
				return div.textContent || div.innerText || "";
			}
		},
		metaInfo () {
			return {
				title: this.content('subtitle')
			};
		},
		mounted () {	
			this.markdown.options.renderer.heading = this.renderHeading;
			this.div = document.createElement("div");
			
			this.loadItems();
		},
		updated () {			
			if (this.curr.path && !this.curr.nav) this.processMenu(this.curr.path);
			
			let element = this.params.get("id");
			
			if (element && this.curr.scrolled !== element) this.scrollElement(element);
		}
	}
</script>

<style lang="scss">
	.modules-guides {
		position: relative;
		padding: var(--page-padding);
		
		.v-progress-linear.absolute.top {
			left: 0;
			
			.inner {
				transition: width 300ms linear;
			}
		}
			
		header.v-header {
			.v-button.rounded.icon {
				animation: fadeIn 650ms ease-in-out;
				margin-left: 1rem;
			}
		}
		
		.info-sidebar-nav {
			h2 {
				cursor: pointer;
			}
		}
		
		.info-sidebar-search {
			background: var(--blue-grey-850);
			border-bottom: 2px solid var(--input-border-color);
			padding: 20px 20px 30px 20px;
			margin: 0 -20px;
		}
		
		.modules-module-content {
			display: flex;
			padding-top: 0 !important;
		
			.modules-module-markdown {
				font-size: 1rem;
				width: 100%;
				max-width: 960px;
				
				h1, .h1 {
					font-size: 2.5rem;
					font-weight: 300;
					
					& + p {
						font-size: 1.5rem;
						font-weight: 300;
					}
				}
				
				h2, .h2 {
					font-size: 1.65rem;
				}
				
				h3, .h3 {
					font-size: 1.35rem;
				}
				
				h4, h5, h6,
				.h4, .h5, .h6 {
					font-size: 1.15rem;
				}
				
				h1, h2, h3, h4, h5, h6,
				.h1, .h2, .h3, .h4, .h5, .h6 {
					color: var(--blue-grey-500);
					font-family: var(--main-font-accent);
					line-height: 1.5em;
					margin-bottom: 0.5em;
					
					&:not(:first-child) {
						margin-top: 1.75em;
					}
					
					&.active {
						color: var(--main-primary-color) !important;
					}
					
					& + hr {
						margin-top: 0 !important;
						margin-bottom: 0.5rem !important;
						
						& + p {
							color: var(--blue-grey-500);
							font-size: 1.3rem;
							font-weight: 300;
						}
					}
				}
				
				p {
					&:not(:only-child) {
						margin-bottom: 1em;
					}
					
					&:empty {
						display: none !important;
						margin: 0;
					}
					
					&:only-child {
						color: var(--blue-grey-500);
						font-size: 2rem;
						font-weight: 300;
						line-height: 2.5rem;
					}
				}
				
				a {
					color: var(--main-primary-color) !important;
					font-weight: 500;
					
					&[href*="netlify"] {
						display: none !important;
					}
				}
				
				img {
					display: block;
					max-width: 100%;
					margin-bottom: 1rem;
					
					&[width="100"] {
						width: 100%;
					}
				}
				
				hr {
					border: 0;
					background: none;
					border-bottom: 2px solid var(--input-border-color);
					margin: 1.5rem 0;
				}
				
				strong {
					font-weight: 600;
				}
				
				ol, p, ul {
				    line-height: 2rem;
				}
				
				ol, ul, video {
				    & + p {
					    margin-top: 1em;
				    }
				}
				
				ol, ul {
					
				}
				
				blockquote {
				    font-size: 1rem;
				    color: var(--blue-grey-500);
				    border-left: .2rem solid var(--main-primary-color);
				    margin: 1rem 0;
				    padding: .25rem 0 .25rem 1rem;
				    
				    &[data-tip] {
					    background-color: var(--blue-grey-800);
						border-left-width: .3rem;
						border-left-style: solid;
						color: var(--blue-grey-300);
						font-size: 1.2rem;
						font-weight: 400;
						line-height: 2rem;
					    padding: 1.5rem;
				    }
				    
				    p {
					    margin: 0 !important;
					    
					    &:first-child:not(:only-child):not(:last-child):not(:empty) {
						    font-family: var(--main-font-accent);
						    font-size: 1.5rem;
						    font-weight: 500;
					    }
					    
					    &:not(:empty) + p {
						    margin-top: 1em !important;
					    }
				    }
				}
				
				pre {
					font-family: Consolas, Monaco, Andale Mono, Ubuntu Mono, monospace;
					font-size: 12px;
					position: relative;
					background-color: rgba(black, 0.4);
					line-height: 1.4;
				    padding: 1.25rem 1.5rem;
				    margin: .85rem 0;
				    overflow: auto;
				}
				
				table {
					border-collapse: collapse;
				    margin: 1rem 0;
				    display: block;
				    overflow-x: auto;
				    
				    tr {
					    border-top: 1px solid var(--blue-grey-800);
					    
					    td, th {
						    border: 1px solid var(--blue-grey-800);
							padding: .6em 1em;
							text-align: left;
					    }
				    }
				}
				
				.module-search-query {
					display: inline-block;
					background-color: var(--blue-grey-800);
					padding: 0 3px;
				}
				
				.modules-module-results {
					li {
						padding-bottom: 1rem;
						
						p {
							line-height: 1.2rem;
							margin-bottom: 0;
						}
					}
				}
				
				&.modules-module-section {
					h1.modules-module-section {
						margin-bottom: 0 !important;
					}
					
					p.modules-module-section {
						font-size: 1.25rem !important;
					}
					
					a {
						display: flex;
						align-items: center;
						text-decoration: none;
						
						span.info-sidebar-nav-icon {
							line-height: 24px !important;
						}
					}
				}
			}
		}
	}
	.v-spinner {
		margin: auto;
	}
	.modules-module-loading {
		display: flex;
		align-items: center;
		justify-content: center;
		min-height: calc(100vh - 350px);
	}
</style>