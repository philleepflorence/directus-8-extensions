<template>
	<div class="modules-cdn module-page-root">
		<v-header 
			:title="content('title')" 
			:breadcrumb="breadcrumb" 
			:icon="icon" 
			settings>
		</v-header>
		
		<div class="modules-cdn-loading" v-if="loading">
			<div class="flex-item">
				<v-spinner
					v-show="loading"
					line-fg-color="var(--blue-grey-300)"
					line-bg-color="var(--blue-grey-200)"
					class="spinner">
				</v-spinner>
			</div>
		</div>
		
		<div class="modules-cdn-contents animated fadeIn" v-if="!loading">
			
			<!-- Analytics -->
			
			<div class="modules-cdn-row" :data-section="kebabCase(content('headlines.analytics'))">
				
				<section class="modules-section">
					<header class="modules-divider">
						<h4 class="modules-divider" v-html="content('headlines.analytics')"></h4>
						<hr />
						<div class="modules-divider">
							<p class="lead modules-divider" v-html="content('descriptions.analytics')"></p>
						</div>
					</header>
					<div class="modules-cdn-content">
						<div class="modules-cdn-grid animated fadeIn a-delay" 
							v-for="row in analytics"
							:data-value="row.text">
							<div class="flex-item" :style="`color: ${ row.color };`">
								<span class="v-icon icon"><i>{{ row.icon }}</i></span>
								<p class="lead">{{ row.total }}</p>
								<p>{{ row.text }}</p>
							</div>
						</div>
					</div>
				</section>
			
			</div>
			
			<!-- Assets - File Tree View -->
			
			<div class="modules-cdn-row" :data-section="kebabCase(content('headlines.assets'))">
				
				<section class="modules-section">
					<header class="modules-divider">
						<h4 class="modules-divider" v-html="content('headlines.assets')"></h4>
						<hr />
						<div class="modules-divider">
							<p class="lead modules-divider" v-html="content('descriptions.assets')"></p>
						</div>
					</header>
					
					<nav class="modules-divider">
						<a class="modules-divider active" href="#" data-mode="list" @click.stop.prevent="onClickToggleMode('list')">
							<v-icon name="list">
						</a>
						<a class="modules-divider" href="#" data-mode="preview" @click.stop.prevent="onClickToggleMode('preview')">
							<v-icon name="view_list">
						</a>
					</nav>
					
					<div class="modules-cdn-tree" v-if="tree">
						<app-files-tree :tree="tree" :open="true" :mode="mode" link="cdn"></app-files-tree>
					</div>
					
					<section 
						class="modules-cdn-section animated fadeInRightSmall a-delay" 
						v-for="(file, index) in files" 
						:data-directory="file.name" 
						v-else-if="files">
						<h3 class="font-accent" @click="onToggle(file.name)">
							<span class="icon expand"><i>unfold_more</i></span>
							<span class="icon collapse"><i>unfold_less</i></span>
							<span>{{ file.name }}</span>
							<span class="flex-fill">{{ count(file.files) }}</span>
						</h3>
						<div class="modules-cdn-files" v-if="file.files">
							<header class="modules-cdn-files-header">
								<p class="font-accent">{{ content('headlines.name') }}</p>
								<p class="font-accent">{{ content('headlines.type') }}</p>
								<p class="font-accent">{{ content('headlines.modified') }}</p>
								<p class="font-accent">{{ content('headlines.size') }}</p>
							</header>
							<div class="modules-cdn-files-section" v-for="(row, key) in file.files" :data-type="row.type">
								<p class="font-main">{{ row.name }}</p>
								<p class="font-main">{{ row.type }}</p>
								<p class="font-main">{{ parseDate(row.modified) }}</p>
								<p class="font-main">{{ row.size.value }} {{ row.size.unit }}</p>
								<nav v-if="row.type == 'file'">
									<a :href="row.cdn">
										<button class="icon"><i>more_horiz</i></button>
									</a>
								</nav>
							</div>
						</div>
					</section>
				</section>
			
			</div>
		</div>	

		<v-info-sidebar wide itemDetail>
			<section class="info-sidebar-section">
				<h2 class="font-accent">{{ content('title') }}</h2>
				<p class="p">{{ content('description') }}</p>
				<p class="p dark" v-if="!loaded">{{ content('loading') }}</p>
			</section>
			<nav class="info-sidebar-section info-sidebar-nav" v-if="!loading">
				<a class="info-sidebar-nav" href="#" :data-section="kebabCase(content('headlines.analytics'))" @click.stop.prevent="onClickScroll(kebabCase(content('headlines.analytics')))">
					<span class="info-sidebar-nav-icon"><v-icon name="view_quilt" left></span>
					<span class="info-sidebar-nav-text" v-html="content('headlines.analytics')"></span>
				</a>
				<a class="info-sidebar-nav" href="#" :data-section="kebabCase(content('headlines.assets'))" @click.stop.prevent="onClickScroll(kebabCase(content('headlines.assets')))">
					<span class="info-sidebar-nav-icon"><v-icon name="account_tree" left></span>
					<span class="info-sidebar-nav-text" v-html="content('headlines.assets')"></span>
				</a>
			</nav>	
			<section class="info-sidebar-section" v-if="!loading">
				<h2 class="font-accent">{{ this.content('form.search.headline') }}</h2>
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
			<section class="info-sidebar-section" v-if="loaded">
				<h2 class="font-accent">{{ this.content('form.thumbnailer.headline') }}</h2>
				<div class="info-sidebar-row">
					<v-button
						id="modules-help-info-sidebar-thumbnailer-button"
						type="button"
						:loading="processing" 
						@click="onSubmitThumbnailer"
						block>
						{{ content('form.thumbnailer.submit') }}
					</v-button>
				</div>
			</section>	
		</v-info-sidebar>
	</div>
</template>

<script>
	import { cloneDeep, get, forEach, kebabCase, map, set, size } from 'lodash';
	import TreeComponent from './components/files/tree.vue';
	
	import $meta from './meta.json';
		
	export default {
		name: 'CDN',
		components: {
			'app-files-tree': TreeComponent
		},
		data () {
			return {
				analytics: {},
				colors: [
					"#f44336",
					"#ff9800",
					"#ff5722",
					"#ffeb3b",
					"#4caf50",
					"#2196f3",
					"#3f51b5"
				],
				contents: $meta.contents,
				icon: $meta.icon,
				icons: {
					files: "insert_drive_file",
					directories: "folder",
					images: "camera_alt",
					scripts: "code",
					fonts: "text_fields",
					text: "notes"
				},
				types: {
					images: [
						"jpg",
						"jpeg",
						"png"
					],
					scripts: [
						"css",
						"dist",
						"hb",
						"js",
						"less",
						"map",
						"php",
						"scss",
						"sql"
					],
					fonts: [
						"eot",
						"svg",
						"ttf",
						"woff",
						"woff2"
					],
					text: [
						"csv",
						"handlebars",
						"html",
						"json",
						"markdown",
						"md"
					]
				},
				loaded: false,
				loading: false,
				processing: false,
				query: null,
				tree: null,
				mode: 'list'
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
			}
		},
		methods: {
			content (input) {
				let translation = get(this.contents, this.locale);
					translation = translation || get(this.contents, 'en-US');

				return get(translation, input);
			},
			count (input) {
				if (Array.isArray(input)) return input.length;
				
				return 0;
			},
			kebabCase (input) {
				return kebabCase(input);
			},
			items () {	
				this.tree = null;
				
				if (this.query) {
					this.files = [];
					
					forEach(this.response.data, (row) => {
						let string = [row.name, row.path].join(' ').toLowerCase();
						let currow = cloneDeep(row);				
						let valid = string.indexOf(this.query) >= 0;
						let files = [];		
						
						if (Array.isArray(currow.files)) {
							forEach(currow.files, (file) => {
								string = [file.name, file.path, file.type].join(' ').toLowerCase();
								
								if (string.indexOf(this.query) >= 0) files.push(file);	
							});
							
							currow.files = files;
						}
						
						if (valid || files.length) this.files.push(currow);	
											
					});
				}
				else this.files = cloneDeep(this.response.data);	
				
				this.tree = {};
					
				forEach(this.files, (row) => {
					let currkey = row.folder.replace(/\./g, '-').split('/').join('.children.');
					let files = [...row.files];
					
					row.files = null;
					
					set(this.tree, currkey, row);		
					
					if (Array.isArray(files)) {
						forEach(files, (file) => {							
							let currindex = file.folder.replace(/\./g, '-').split('/').join('.children.');

							set(this.tree, currindex, file);	
						});
					}	
										
				});
				
				this.loading = false;	
				
				setTimeout(this.onScrollEnd, 500);		
				
				return this.files;
			},
			load () {
				this.loading = true;
				
				let analytics = {};
												
				this.$api.api.get('/custom/cdn/files')
				.then((response) => {
					console.log(response);
					
					this.loading = false;
					this.loaded = true;
					
					forEach (response.meta, (value, index) => {
						if (typeof value === "number") set(analytics, index, value);
					});
					
					forEach (response.meta.types, (value, index) => {
						let type;
						
						forEach(this.types, (a, b) => {
							if (a.includes(index)) type = b;
						});
						type = type || 'scripts';
						
						let currvalue = get(analytics, type);
						
						if (!currvalue) set(analytics, type, value);
						else set(analytics, type, (value + currvalue));
					});
					
					let colors = this.colors.slice();
				
					if (size(colors) < size(analytics)) {
						let len = Math.ceil(size(analytics) / size(colors));
						
						while(len > 0) {
							colors = colors.concat(colors);
							
							len--;
						}
					}
					
					let currindex = 0;
					
					forEach (analytics, (value, index) => {
						set(analytics, index, {
							total: value,
							text: index,
							color: colors[currindex],
							icon: this.icons[index]
						});
						
						currindex++;
					});
					
					this.analytics = analytics;
					this.response = response;	
					this.items();				
				})
				.catch((error) => {
					this.error = error;
					
					this.loading = false;
				});
			},
			number (input) {
				return typeof input === 'number';
			},
			onClickScroll (input) {
				let $row = this.$el.querySelector(`.modules-cdn-row[data-section="${ input }"]`);
				let $nav = this.$el.querySelector(`a.info-sidebar-nav[data-section="${ input }"]`);
				
				if (this.$nav) this.$nav.classList.remove('active');
				
				if (!$row) return false;
				
				this.$nav = $nav;
				
				this.$nav.classList.add('active');
				
				let props = $row.getBoundingClientRect();
				let top = window.scrollY + (props.y - 100);
				
				if (top < 0) top = 0;
				
				window.scrollTo({
					top: top,
					behavior: 'smooth'
				});
			},
			onClickToggleMode (input) {
				let $active = this.$el.querySelector(`[data-mode].active`);
				let $nav = this.$el.querySelector(`[data-mode="${ input }"]`);
				
				if ($active === $nav) return false;
				
				$active.classList.remove('active');
				
				$nav.classList.add('active');
				
				this.mode = input;
			},
			onInputSearch (input) {
				this.query = input;
				
				if (input === '') this.items();
			},
			onSubmitSearch (e) {
				this.loading = true;
				
				this.items();
			},
			onSubmitThumbnailer (e) {
				this.processing = true;
				
				this.$api.api.get('/custom/thumbnailer/sizes')
				.then((response) => {
					
					this.processing = false;
					
					let message = get(response, 'meta.processed_files') ? this.content('form.thumbnailer.success') : this.content('form.thumbnailer.error');
					
					this.$notify({
						title: message,				
						color: 'green',				
						iconMain: 'build',				
						delay: 5000				
					});				
				})
				.catch((error) => {
					this.error = error;
					
					this.loading = processing;
				});
			},
			onScrollEnd () {
				this.$sections = this.$sections || this.$el.querySelectorAll('.modules-cdn-row');
				let offset = 200;
				
				if (this.$nav) this.$nav.classList.remove('active');
				
				this.$nav = null;
				
				forEach(this.$sections, (section) => {
					let top = section.offsetTop - window.scrollY;
					let bottom = top + section.offsetHeight;
					
					if (top < offset && bottom > offset) this.$section = section;
				});
				
				if (window.scrollY && !this.$section) this.$section = get(this.$sections, size(this.$sections) - 1);
				
				if (this.$section) this.$nav = this.$el.querySelector(`a.info-sidebar-nav[data-section="${ this.$section.getAttribute('data-section') }"]`);
				
				if (this.$nav) this.$nav.classList.add('active');
			},
			onToggle (dir) {
				let $parent = this.$el.querySelector(`[data-directory="${ dir }"]`);
				
				if ($parent) $parent.classList.toggle('active');
			},
			parseDate(input) {
				let d = new Date(input * 1000),
					month = '' + (d.getMonth() + 1),
					day = '' + d.getDate(),
					year = d.getFullYear();
				
				if (month.length < 2) month = '0' + month;
				if (day.length < 2) day = '0' + day;
				
				return [year, month, day].join('-');
			},
			render () {
				let rect = this.$loading.getBoundingClientRect();
				let height = window.innerHeight - rect.y - 10;
				
				this.$loading.style.minHeight = `${ height }px`;
				
				this.load();
			}
		},
		metaInfo() {
			return {
				title: this.content('subtitle')
			};
		},
		mounted () {
			this.$loading = this.$el.querySelector('.modules-cdn-loading');
			
			if (this.$loading) this.render();
			
			this.load();
			
			document.addEventListener('scrollend', this.onScrollEnd);
		},
		beforeDestroy () {
			document.removeEventListener('scrollend', this.onScrollEnd);
		}
	}
</script>

<style lang="scss" scoped>
	.modules-cdn {
		padding: var(--page-padding-top) var(--page-padding) var(--page-padding-bottom);
		position: relative;
		
		.modules-cdn-row {
			position: relative;
		}
		
		.modules-cdn-loading {
			display: flex;
			align-items: center;
			justify-content: center;
			min-height: calc(100vh - 150px);
		}
		
		.content nav ul {
			padding: 0;
			display: grid;
			grid-template-columns: repeat(auto-fill,var(--card-size));
			grid-gap: var(--card-horizontal-gap);
		}
		
		.modules-cdn-section {
			animation-duration: 600ms;
			background: rgba(white, 0.05);
			border-top: 1px solid var(--blue-grey-800);
			
			&:last-child {
				border-bottom: 1px solid var(--blue-grey-800);
			}
			
			h3 {
				cursor: pointer;
				display: flex;
				align-items: center;
				font-size: 1.25rem;
				padding: 0.9rem 0;
				
				span.icon {
					opacity: 0.5;
					padding: 0 0.5rem;
				}
				
				span.collapse {
					display: none;
				}
					
				span.flex-fill {
					flex-grow: 1 !important;
					padding-right: 1rem;
					text-align: right;
				}
			}
			
			div.modules-cdn-files {
				display: none;
			}
			
			&.active {
				h3 {
					span.collapse {
						display: block;
					}
					
					span.expand {
						display: none;
					}
				}
				
				div.modules-cdn-files {
					display: block;
				}
			}
			
			.modules-cdn-files-header,
			.modules-cdn-files-section {
				display: flex;
				flex-flow: row;
				border-top: 1px solid var(--blue-grey-800);
				background: rgba(white, 0.05);
				position: relative;
				
				p {
					width: 20%;
					padding: 0.9rem;
					
					&:first-child {
						width: 40%;
					}
				}
				
				nav {
					position: absolute;
					right: 0;
					height: 100%;
					padding: 0 0.5rem;
				
					button {					
						height: 100%;
						margin: 0 0.5rem;
					}
				}
				
				&[data-type="dir"] {
					p:first-child {
						color: var(--main-primary-color) !important;
						font-weight: 500 !important;
						text-transform: lowercase !important;
					}
				}
			}
		}
		
		.modules-cdn-content {
			display: grid;
			grid-template-columns: repeat(4, 1fr);
			grid-auto-rows: minmax(200px, max-content);
			grid-gap: 1rem;
				
			@media (min-width: 1280px) {
				[data-value="files"] {
					grid-column: 1/3 !important;
					grid-row: 1/3 !important;
				}
				
				[data-value="directories"] {
					grid-column: 3/5 !important;
				}
				
				[data-value="images"] {
					grid-column: 1/3 !important;
				}
				
				[data-value="fonts"] {
					grid-column: 3/5 !important;
				}
			}
				
			.modules-cdn-grid {
				background-color: rgba(white, 0.1);
				cursor: pointer;
				display: flex;
				align-items: center;
				justify-content: center;
				position: relative;
				overflow: hidden;
				animation-duration: 600ms;
				
				.flex-item {
					flex-grow: 1;
					text-align: center;
					color: var(--main-primary-color);
					
					.icon {
						display: flex;
						align-items: center;
						justify-content: center;
						width: 50px;
						height: 50px;
						background: rgba(white, 0.1);
						margin: 0 auto 0.5rem auto;
						border-radius: 50%;
					}
					
					p.lead {
						font-size: 2rem;
					}
				}
			}
		}
	}
	.icon i {
		font-size: 24px;
	    font-family: Material Icons;
	    font-weight: 400;
	    font-style: normal;
	    display: inline-block;
	    line-height: 1;
	    text-transform: none;
	    letter-spacing: normal;
	    word-wrap: normal;
	    white-space: nowrap;
	    font-feature-settings: "liga";
	    vertical-align: middle;
	}	
</style>