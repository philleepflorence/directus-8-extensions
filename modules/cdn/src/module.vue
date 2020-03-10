<template>
	<div class="modules-cdn">
		<v-header 
			:title="content('title')" 
			:breadcrumb="breadcrumb" 
			icon="view_module" 
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
			
				<v-details :title="content('headlines.analytics')" type="break" open>
					<div class="modules-cdn-content">
						<div class="modules-cdn-grid animated fadeIn a-delay" 
							v-for="(value, key) in analytics"
							:data-value="key">
							<div class="flex-item">
								<span class="v-icon icon"><i>{{ icons[key] }}</i></span>
								<p class="lead">{{ value }}</p>
								<p class="font-accent">{{ key }}</p>
							</div>
						</div>
					</div>
				</v-details>
			
			</div>
			
			<!-- Assets - File Tree View -->
			
			<div class="modules-cdn-row" :data-section="kebabCase(content('headlines.assets'))">
				
				<v-details :title="content('headlines.assets')" type="break" open>
					
					<div class="modules-cdn-tree" v-if="tree">
						<v-tree :tree="tree"></v-tree>
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
				</v-details>
			
			</div>
		</div>	

		<v-info-sidebar wide itemDetail>
			<section class="info-sidebar-section">
				<h2 class="font-accent">{{ content('title') }}</h2>
				<p class="p">{{ content('description') }}</p>
			</section>
			<section class="info-sidebar-section">
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
			<nav class="info-sidebar-section info-sidebar-nav" v-if="!loading">
				<a class="info-sidebar-nav" href="#" @click.stop.prevent="onClickScroll(kebabCase(content('headlines.analytics')))">{{ content('headlines.analytics') }}</a>
				<a class="info-sidebar-nav" href="#" @click.stop.prevent="onClickScroll(kebabCase(content('headlines.assets')))">{{ content('headlines.assets') }}</a>
			</nav>		
		</v-info-sidebar>
	</div>
</template>

<script>
	import { cloneDeep, get, forEach, kebabCase, map, set, size } from 'lodash';
	import TreeComponent from './components/tree.vue';
	import VueTreeNavigation  from 'vue-tree-navigation';
		
	export default {
		name: 'CDN',
		components: {
			'v-tree': TreeComponent
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
				
				return this.files;
			},
			load () {
				this.loading = true;
												
				this.$api.api.get('/custom/cdn/files')
				.then((response) => {
					
					this.loading = false;
					
					forEach (response.meta, (value, index) => {
						if (typeof value === "number") set(this.analytics, index, value);
					});
					
					forEach (response.meta.types, (value, index) => {
						let type;
						
						forEach(this.types, (a, b) => {
							if (a.includes(index)) type = b;
						});
						type = type || 'scripts';
						
						let currvalue = get(this.analytics, type);
						
						if (!currvalue) set(this.analytics, type, value);
						else set(this.analytics, type, (value + currvalue));
					});
					
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
				
				if (!$row) return false;
				
				let props = $row.getBoundingClientRect();
				let top = window.scrollY + (props.y - 100);
				
				if (top < 0) top = 0;
				
				window.scrollTo({
					top: top,
					behavior: 'smooth'
				});
			},
			onInputSearch (input) {
				this.query = input;
				
				if (input === '') this.items();
			},
			onSubmitSearch (e) {
				this.loading = true;
				
				this.items();
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
		data () {
			return {
				analytics: {},
				contents: {
					"en-US": {
						"title": "CDN",
						"subtitle": 'CDN - Publicly accessible assets and files',
						"description": 'Publicly accessible assets and files',
						"headlines": {
							"analytics": "CDN Analytics and Snapshot",						
							"assets": "Assets and Files",
							"name": "Name",
							"type": "Type",
							"modified": "Modified",
							"size": "Size"
						},
						"form": {
							"search": {
								"headline": "Search",
								"placeholder": "Search Assets and Files",
								"submit": "Search"
							}
						}
					}						
				},
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
				loading: false,
				query: null,
				tree: null
			};
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
			grid-template-columns: repeat(5, 1fr);
			grid-auto-rows: minmax(200px, max-content);
			grid-gap: 1rem;
				
			@media (min-width: 960px) {
				[data-value="files"] {
					grid-column: 1/3 !important;
					grid-row: 1/3 !important;
				}
			}
				
			@media (min-width: 960px) {
				[data-value="directories"] {
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