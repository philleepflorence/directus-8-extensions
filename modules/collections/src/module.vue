<template>
	<div class="modules-module module-page-root">
		<v-header 
			:title="getContent('title')" 
			:breadcrumb="breadcrumb" 
			:icon="icon" 
			settings>
		</v-header>
		
		<div class="modules-module-loading" v-if="loading">
			<v-progress-linear background-color="--main-primary-color" color="--blue-grey-700" indeterminate>
		</div>	
		
		<div class="modules-module-introduction animated fadeIn" v-if="!collection">
			<div class="flex-item">
				<p class="lead" v-html="getContent('description')"></p>
				<p class="lead" v-html="message"></p>
			</div>
		</div>
		
		<div class="modules-module-contents animated fadeIn" v-if="collection">
			
			<header class="modules-divider">
				<h2 class="modules-divider" v-html="getContent('modules.who.headline')"></h2>
				<hr />
				<p class="modules-divider" v-html="collection.collection.collection"></p>
			</header>
			
			<header class="modules-divider">
				<h2 class="modules-divider" v-html="getContent('modules.what.headline')"></h2>
				<hr />
				<p class="modules-divider" v-html="collection.description"></p>
			</header>
			
			<header class="modules-divider">
				<h2 class="modules-divider" v-html="getContent('modules.when.headline')"></h2>
				<hr />
				<p class="modules-divider" v-for="content in collection.options.contents" v-html="content"></p>
			</header>
			
			<header class="modules-divider">
				<h2 class="modules-divider" v-html="getContent('modules.how.headline')"></h2>
				<hr />
				<p class="modules-divider" v-for="description in getContent('modules.how.description')" v-html="description"></p>
			</header>
			
			<section class="modules-module-collection">
				<header class="modules-module-collection">
					<span class="text font-accent">
						<a :href="`#/${ currentProjectKey }/collections/${ collection.collection.collection }`">{{ collection.title }}</a>
					</span>
					<span class="icon"><v-icon :name="collection.icon" left></span>
				</header>
				<div class="modules-module-collection bg" :data-field="field.field" :data-field="field.collection" :data-relationship="field.relationship ? true: false" v-for="field in collection.fields">
					<p class="field" :data-relationship="field.relationship ? true: false">
						<span class="text">{{ field.title }}</span>
						<span class="icon" v-if="!field.relationship && field.note" @click.stop="onClickField(field.field)"><v-icon name="more_horiz"></span>
						<span class="horizontal-line bg" v-if="field.relationship"></span>
					</p>
					<footer class="modules-module-collection" :ref="`details-${ field.field }`" :data-relationship="field.relationship ? true: false" v-if="field.note">
						<span v-html="field.note"></span>
					</footer>
					<aside class="modules-module-collection animated fadeIn" v-if="field.relationship">
						<header class="modules-module-collection">
							<span class="text font-accent">
								<a :href="`#/${ currentProjectKey }/collections/${ field.relationship.collection.collection }`">{{ field.relationship.collection.title }}</a>
							</span>
							<span class="icon"><v-icon :name="field.relationship.collection.icon" left></span>
						</header>
						<div class="modules-module-collection">
							<p class="field bg">{{ field.relationship.field }}</p>
						</div>						
					</aside>
				</div>
			</section>
			
		</div>	

		<v-info-sidebar wide itemDetail>
			<section class="info-sidebar-section">
				<h2 class="font-accent" v-html="getContent('title')"></h2>
				<p class="p" v-html="getContent('description')"></p>
			</section>	
			<nav class="info-sidebar-section info-sidebar-nav" v-if="!menu">
				<h2 class="font-accent" v-html="getContent('navigation.loading')"></h2>
			</nav>
			<nav class="info-sidebar-section info-sidebar-nav" v-if="menu">
				<h2 class="font-accent" v-html="getContent('navigation.headline')"></h2>
				<a class="info-sidebar-nav" href="#" v-for="row in menu" :ref="`menu-${ row.slug }`" @click.stop.prevent="onClickCollection(row)">
					<span class="info-sidebar-nav-icon"><v-icon :name="row.icon" left></span>
					<span class="info-sidebar-nav-text">{{ row.title }}</span>
				</a>
			</nav>			
		</v-info-sidebar>
	</div>
</template>

<script>
	import { cloneDeep, get, forEach, kebabCase, set, size, startCase } from 'lodash';
	
	import $meta from './meta.json';
		
	export default {
		name: 'ModulesCollections',
		data () {
			return {
				collection: null,
				contents: $meta.contents,
				icon: $meta.icon,
				loaded: false,
				loading: true,
				message: null,
				tree: null,
				collections: {},
				menu: null,
				rendered: false,
				colors: [
					"#f44336",
					"#ff9800",
					"#ff5722",
					"#ffeb3b",
					"#4caf50",
					"#2196f3",
					"#3f51b5"
				],
				formats: {
					fields: [
						"id",
						"url"
					]
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
			}
		},
		methods: {
			formatCase (input) {
				if (this.formats.fields.includes(input)) return input.toUpperCase();
				
				return startCase(input);
			},
			getContent (input) {
				let translation = get(this.contents, this.locale);
					translation = translation || get(this.contents, 'en-US');

				return get(translation, input);
			},
			loadCollections () {
				this.message = this.getContent('loaders.collections');
								
				let params = {
					filter: {
						managed: 1
					}
				};
												
				this.$api.getItems('directus_collections', params)
				.then((response) => {
					
					forEach(response.data, (row) => {
						
						if (row.translation) {
							row.translation.forEach((translation) => {
								if (translation.locale === this.locale) row.title = translation.translation;
							});
						}
						
						set(this.collections, row.collection, row);
					});
									
					this.loadMenu();				
				})
				.catch((error) => {
					this.error = error;
				});
			},
			loadMenu () {
				this.message = this.getContent('loaders.menu');
				
				let collections = cloneDeep(this.collections);
								
				let params = {
					status: "published",
					filter: {
						type: "treemap"
					}
				};
																
				this.$api.getItems('app_collections_configuration', params)
				.then((response) => {
					
					forEach(response.data, (row) => {
						let collection = get(this.collections, row.collection);
						
						row.icon = collection.icon;
						row.title = collection.title;
					});
					
					this.menu = response.data;	
					
					this.loading = false;
					this.loaded = true;	
					
					this.message = this.getContent('introduction');								
				})
				.catch((error) => {
					this.error = error;
				});
			},
			onClickCollection (input) {
				if (this.$active) this.$active.classList.remove('active');
				
				this.rendered = false;
				
				let collections = cloneDeep(this.collections);
				
				this.$active = this.$refs[`menu-${ input.slug }`][0];
				
				this.$active.classList.add('active');
				
				let collection = get(collections, input.collection);
				let fields = {};
				let $input = cloneDeep(input);
								
				let $collection = {
					title: $input.title,
					description: collection.note, 
					icon: $input.icon,
					options: $input.options,
					collection: get(collections, $input.collection)
				};
				
				forEach($collection.collection.fields, (field) => {
					if ((!field.hidden_detail && !field.hidden_browse) || field.field === "id") {
						let currcollection = get(collections, field.collection);
						let relationships = get($input.options.relationships, field.field);
						
						if (relationships) {
							let _collection = relationships.collection;
							
							relationships.collection = {
								title: get(collections, `${ _collection }.title`), 
								icon: get(collections, `${ _collection }.icon`), 
								collection: get(collections, `${ _collection }.collection`), 
								note: get(collections, `${ _collection }.note`)
							};							
						}

						set(fields, field.field, {
							title: this.formatCase(field.field),
							collection: {
								collection: field.collection,
								icon: field.icon,
								note: field.note
							},
							field: field.field,
							note: field.note,
							relationship: relationships
						});						
					}
				});
				
				$collection.collection = {
					collection: $collection.collection.collection,
					icon: $collection.collection.icon,
					note: $collection.collection.note
				};
				
				$collection.fields = fields;
				
				this.collection = {...$collection};						
			},
			onClickField (input) {
				let $details = get(this.$refs, `details-${ input }.0`);
				
				if ($details) $details.classList.toggle('active');
			},
			renderTree ($rows) {
				let index = 0;
				let colors = this.colors.slice();
				let total = $rows.length;
				
				if (size(colors) < total) {
					let len = Math.ceil(total / size(colors));
					
					while(len > 0) {
						colors = colors.concat(colors);
						
						len--;
					}
				}
								
				this.rendered = true;
							
				forEach($rows, (element) => {
					let $relationship = element.querySelector('aside.modules-module-collection');
					let $horizontalRight = element.querySelector('span.horizontal-line');
					let $bg = element.querySelectorAll('.bg');
					
					element.removeAttribute('style');
					
					forEach($bg, (bg) => {							
						bg.removeAttribute('style');
					});
					
					if ($relationship) {												
						$relationship.style.top = `-${ $relationship.offsetHeight }px`;
						
						let currcolor = get(colors, index);
						
						element.style.backgroundColor = currcolor;
						
						forEach($bg, (bg) => {							
							bg.style.backgroundColor = currcolor;
						});
						
						index++;
					} 					
				});
			}
		},
		metaInfo() {
			return {
				title: this.getContent('subtitle')
			};
		},
		mounted () {
			this.loadCollections();
		},
		updated () {
			let $rows = this.$el.querySelectorAll('div.modules-module-collection[data-field]');
			
			if ($rows.length && !this.rendered) this.renderTree($rows);
		}
	}
</script>

<style lang="scss">
	.modules-module {
		padding: var(--page-padding-top) var(--page-padding) var(--page-padding-bottom);
		position: relative;
		
		.modules-module-loading {
			position: absolute;
			top: 1rem;
			left: 0;
			width: 100%;
		}
		
		.modules-module-introduction {
			display: flex;
			align-items: center;
			min-height: calc(100vh - 150px);
			
			.lead:not(:last-child) {
				margin-bottom: 1rem;
			}
		}
		
		.modules-module-contents {
			position: relative;
			
			section.modules-module-collection {
				width: calc((100vw - 300px) * 0.3);
				
				@media (min-width: 2010px) {
					width: 480px;
				}
				
				header.modules-module-collection {
					background-color: var(--blue-grey-700);
					border-bottom: 1px solid var(--blue-grey-900);
					display: flex;
					align-items: center;
					padding: 10px;
					width: 100%;
					font-size: var(--font-size-h4);
					
					span.icon {
						width: 2rem;
						text-align: left;
						padding-left: 4px;
					}
					
					span.text {
						flex-grow: 1;
						
						a {
							text-decoration: none;
						}
					}
				}
				
				div.modules-module-collection {
					position: relative;
					background-color: var(--blue-grey-800);
					border-bottom: 1px solid var(--blue-grey-900);
					
					p {
						position: relative;
						display: flex;
						align-items: center;
						padding: 10px;
						
						&[data-relationship] {
							color: #202020;
							font-weight: 500;
						}
						
						span.text {
							flex-grow: 1;
						}
						
						span.icon {
							width: 2rem;
							text-align: left;
							cursor: pointer;
							background: transparent !important;
						}
					}
					
					span.horizontal-line {
						background-color: var(--blue-grey-700);
						position: absolute;
						width: calc((100vw - 300px) * 0.15);
						height: 2px;
						left: 100%;
						top: 50%;
						
						@media (min-width: 2010px) {
							width: 240px;
						}
					}
					
					aside.modules-module-collection {
						position: relative;
						left: calc(100% + ((100vw - 300px) * 0.15));
						top: -65px;
						
						@media (min-width: 2010px) {
							left: 720px;
						}
						
						p {
							text-transform: uppercase;
							color: #202020;
							font-weight: 500;
						}
					}
					
					footer.modules-module-collection {
						display: none;
						padding: 10px;
						font-size: 0.875rem;						
						font-weight: 500;
						
						&[data-relationship] {
							display: block;
							position: absolute;
							width: 100%;
							bottom: 0;
							color: #202020;
						}
						
						&.active {
							display: block !important;
						}
					}
				}
			}
		}
	}	
</style>