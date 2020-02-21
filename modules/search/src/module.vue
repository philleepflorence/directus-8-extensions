<template>
	<div class="modules-search">
		
		<v-header 
			:title="content('subtitle')" 
			:breadcrumb="breadcrumb" 
			icon="view_quilt" 
			settings>
		</v-header>
		
		<div class="modules-search-content animated fadeIn">
			
			<div class="modules-search-search">
				<v-input
					id="modules-search-search-input"
					type="url"
					class="modules-search-search-input"
					:placeholder="content('input.placeholder')"
					:value="query"
					:model="query"
					@input="onInput">
				</v-input>
			</div>
			
			<div class="modules-search-contents" v-if="!results">
				<v-spinner
					v-show="loading"
					line-fg-color="var(--blue-grey-300)"
					line-bg-color="var(--blue-grey-200)"
					class="spinner">
				</v-spinner>
				<p class="lead" v-html="content('disclaimer')" v-show="!loading"></p>
			</div>
			
			<div class="modules-search-results animated fadeIn" v-if="results">
				<header class="modules-search-results">{{ content('results') }} {{ results.meta.total }}</header>
				<div class="modules-search-results">
					<section class="modules-search-result" v-for="row in results.data" @click="onClickNavigate(row.path)">
						<h4>{{ row.title }}</h4>
						<p>{{ row.description }}</p>
						<small>{{ url }}{{ row.path }}</small>
					</section>
				</div>
			</div>
			
		</div>	

		<v-info-sidebar wide>
			<h2 class="type-note">{{ this.content('title') }}</h2>
			<span class="type-note">{{ this.content('description') }}</span>
		</v-info-sidebar>
		
	</div>
</template>

<script>
	import { get, set } from 'lodash';
	
	export default {
		name: 'ModulesSearch',
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
			url () {
				return [
					window.location.origin,
					window.location.pathname,
					"#"
				].join('');
			}
		},
		methods: {
			content (input) {
				let translation = get(this.contents, this.locale);
					translation = translation || get(this.contents, 'en-US');

				return get(translation, input);
			},
			get (key) {
				if (!window.sessionStorage) return null;
				
				let value = window.sessionStorage.getItem(key);
				
				if (typeof value === 'string') return JSON.parse(value);
				
				return null;
			},
			onClickNavigate (input) {
				this.$router.push(input);
			},
			onInput (input) {
				clearTimeout(this.timers.input);
				
				this.timers.input = setTimeout(() => {
					if (input && input.length) this.search(input);
					else this.results = null;
				}, 
				1000);
			},
			search (input) {
				this.loading = true;
				
				this.set('extensions.modules.search.query', input);
				
				this.query = input;
												
				this.$api.api.get('/custom/search/database', {
					query: input
				}).then((response) => {
					
					this.loading = false;
					
					this.set('extensions.modules.search.results', response);
					
					this.results = response;
					
				}).catch((error) => {
					
					this.error = error;
					
					this.loading = false;
				});
			},
			set (key, input) {
				if (!window.sessionStorage) return null;
				
				return window.sessionStorage.setItem(key, JSON.stringify(input));
			}
		},
		data () {
			return {
				contents: {
					"en-US": {
						"title": "Search",
						"subtitle": "Search - Find Items in all visible Collections",
						"description": "Find Items in all visible Collections",
						"disclaimer": "You must have access to the collection(s) you wish to search. <br>Only a maximum of 200 items per collection are returned for each search query. <br>NOTE: CoreDirectus Collections are not included in search results. <br>To begin a new search, enter your search query above.",
						"results": "Number of items that contain the query: ",
						"input": {
							"placeholder": "Search all visible collections"
						}
					}						
				},
				loading: false,
				query: null,
				results: null,
				timers: {
					input: 0
				}
			};
		},
		metaInfo() {
			return {
				title: this.content('subtitle')
			};
		},
		mounted () {
			this.query = this.get('extensions.modules.search.query');
			this.results = this.get('extensions.modules.search.results');
		}
	}
</script>

<style lang="scss" scoped>
	.modules-search {
		padding: var(--page-padding-top) var(--page-padding) var(--page-padding-bottom);
		
		.modules-search-content {			
			.modules-search-search {
				margin-bottom: 1rem;
			}
			
			.modules-search-contents {
				display: flex;
				align-items: center;
				justify-content: center;
				min-height: calc(100vh - 150px);
				
				p.lead {
					color: var(--blue-grey-500);
					font-size: 2rem;
					font-weight: 300;
				}
			}	
			
			div.modules-search-results {
				position: relative;
				
				header.modules-search-results {
					font-size: 1.5rem;
					font-weight: 300;
					margin-bottom: 1.5rem;
				}
				
				.modules-search-result {
					cursor: pointer;
					margin-bottom: 1.5rem;
					
					small {
						color: var(--blue-grey-500);
						font-size: 0.875em;
					}
				}
			}		
		}
	}
</style>