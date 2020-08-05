<template>
	<div class="modules-module module-page-root">
		
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
			
			<div class="modules-module-markdown" :ref="`markdown`" v-html="html" @click.stop.prevent="onClickContentLink" v-if="html"></div>
			
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
				<p class="p">{{ content('disclaimer') }}</p>
			</section>
			<section class="info-sidebar-section">
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
			<nav class="info-sidebar-section info-sidebar-nav" v-for="(section, group) in navigation">
				<h2 class="font-accent">{{ section.headline }}</h2>
				<a class="info-sidebar-nav" href="#" :data-href="link" v-for="(link, slug) in section.nav" @click.stop.prevent="onClickNavigation($event, link)">
					<span class="info-sidebar-nav-icon"><v-icon :name="getIcon(slug, group)" left></span>
					<span class="info-sidebar-nav-text">{{ formatStartCase(slug) }}</span>
				</a>
			</nav>			
		</v-info-sidebar>
		
	</div>
</template>

<script>
	import { forEach, get, set, size, startCase, trimStart } from 'lodash';
	import marked from 'marked';
	import sbd from 'sbd';
	
	import $meta from './meta.json';
	
	export default {
		name: 'ModulesGuides',
		data () {
			return {
				cache: {},
				contents: $meta.contents,
				html: null,
				query: null,
				icon: $meta.icon,
				navigation: $meta.github.navigation,
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
			viewresults () {
				return this.results && this.query && this.html;
			}			
		},
		methods: {
			get (path, params, callback) {
				this.loading = true;
				
				let cache = get(this.cache, path.split('.').shift());
				
				if (cache) {
					this.loading = false;
					
					return callback(cache);
				}
				
				this.$api.api.get('/custom/curl/content', params).then((response) => {
					
					let html = marked(response.content, this.markdown.options);
						html = this.render(html);
						
					set(this.cache, path.split('.').shift(), html);
					
					this.loading = false;
					
					return callback(html);
					
				}).catch((error) => {
					
					this.error = error;

					this.loading = false;
					
				});
			},
			getIcon (slug, group) {
				let icon = get($meta.github.navigation, `${ group }.icons.${ slug }`);
				
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
			load (path, element, cache) {				
				let route = {
					path: path,
					element: element
				};
				
				window.scrollTo(0, 0);
				
				if (!this.$back && !this.$forward) this.routes.push(route);
				
				this.processMenu(path);
				
				let url = $meta.github.baseURL + path;
								
				this.get(path, {
					url: url
				}, 
				(html) => {
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
					
					if (cache) this.loadPages(1);
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
				
				this.load(link);
			},
			onClickContentLink (e) {
				let href = e.target.href.replace(window.location.origin, '');
				
				if (!href) return false;
				
				let path = e.target.getAttribute('href');
				let fragments = trimStart(this.$path.path, '/').split('/');
				
				if (fragments.length && path.indexOf('./') === 0) href = href.replace('/admin/', `/${ fragments[0] }/`);
				
				if (e.target.href && e.target.href.indexOf(window.location.origin) === 0) {
					href = href.replace('.html', '.md');
					href = href.split('#');
					
					this.load(href[0], href[1]);					
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
												
				this.load(route.path, route.element);
			},	
			onInputSearch (input) {
				this.query = input;
				
				if (input === '') {
					this.query = null;
					
					this.load($meta.github.index);
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
			processMenu (path) {
				if (path) {
					if (this.$nav) this.$nav.classList.remove('active');
				
					this.$nav = this.$el.querySelector(`[data-href="${ path }"]`);
					
					if (this.$nav) this.$nav.classList.add('active');
				}
				else if (!path && this.$nav) this.$nav.classList.remove('active');
			},			
			render (input) {
				this.markdown.patterns.forEach((pattern) => {
					input = input.replace(pattern.pattern, pattern.text)
				});
				
				return input;
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
			scrollElement () {
				let element = document.getElementById(`modules-guides-${ this.element }`);
				
				if (!element) return false;
				
				setTimeout(() => {
					window.scrollTo({
						top: element.offsetTop + window.scrollY - 100,
						behavior: 'smooth'
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
			
			forEach(this.navigation, (section) => {
				forEach(section.nav, (value, key) => {
					set(this.menu, key, value);
				});
			});
			
			this.load($meta.github.index, null, true);
		},
		updated () {
			if (this.element) this.scrollElement();
		}
	}
</script>

<style lang="scss">
	.modules-module {
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
		
		.modules-module-content {
			display: flex;
			align-items: center;
			padding-top: 0 !important;
		
			.modules-module-markdown {
				border: 1px solid var(--blue-grey-800);
				font-size: 1rem;
				width: 100%;
				max-width: 960px;
				margin: 0 auto;
				padding: var(--page-padding);
				
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
						margin-top: 1.5em;
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