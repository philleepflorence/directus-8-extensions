<template>
	<div class="modules-search">
		
		<v-header 
			:title="content('title')" 
			:breadcrumb="breadcrumb" 
			:icon="icon" 
			settings>
		</v-header>
		
		<div class="modules-search-content animated fadeIn">
			
			<div class="modules-search-contents" v-if="!results">
				<v-spinner
					v-show="loading"
					line-fg-color="var(--blue-grey-300)"
					line-bg-color="var(--blue-grey-200)"
					class="spinner">
				</v-spinner>
				<div class="lead" v-show="!loading">
					<p v-for="row in content('disclaimer')">{{ row }}</p>
				</div>
			</div>
			
			<div class="modules-search-results animated fadeIn" v-if="results">
				<header class="modules-divider">
					<h2 class="modules-divider">{{ content('results.headline') }}</h2>
					<hr />
					<p class="modules-divider">{{ content('results.information') }} {{ results.meta.total }}</p>
				</header>
				<div class="modules-search-results">
					<app-table :headers="headers" :rows="results.data" @click="onClickNavigate"></app-table>					
				</div>
			</div>
			
		</div>	

		<v-info-sidebar wide itemDetail>
			<section class="info-sidebar-section">
				<h2 class="font-accent">{{ content('title') }}</h2>
				<p class="p">{{ content('description') }}</p>
				<p class="lead info-sidebar-count" v-if="count">{{ count }}</p>
			</section>
			<section class="info-sidebar-section">
				<div class="modules-search-search">
					<v-input
						id="modules-search-search-input"
						type="search"
						class="modules-search-search-input"
						:placeholder="content('input.placeholder')"
						:value="query"
						:model="query"
						@input="onInput">
					</v-input>
					<v-button
						id="modules-search-search-button"
						v-if="results"
						@click="onClickClear"
						rounded
						icon>
						<v-icon name="close"></v-icon>
					</v-button>
				</div>
			</section>
			<section class="info-sidebar-section">
				<header class="info-sidebar-section">
					<p class="info-sidebar-row" v-for="row in content('disclaimer')">{{ row }}</p>
				</header>
			</section>
			<nav class="info-sidebar-section info-sidebar-nav">
				<h2 class="font-accent">{{ content('navigation.headline') }}</h2>
				<a class="info-sidebar-nav" :href="row.path" v-for="row in content('navigation.rows')">
					<span class="info-sidebar-nav-icon"><v-icon :name="row.icon" left></span>
					<span class="info-sidebar-nav-text">{{ row.text }}</span>
				</a>
			</nav>
		</v-info-sidebar>
		
	</div>
</template>

<script>
	import { get, set, size } from 'lodash';
	
	import AppTable from './components/tables/table.vue';
	
	import $meta from './meta.json';
	
	export default {
		name: 'ModulesSearch',
		components: {
			'app-table': AppTable
		},
		data () {
			return {
				contents: $meta.contents,
				icon: $meta.icon,
				count: 0,
				keys: {
					element: 0
				},
				loading: false,
				query: null,
				results: null,
				timers: {
					input: 0
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
			headers () {
				let row = get(this.results.data, '0');
				
				return Object.keys(row);
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
			onClickClear () {
				this.query = null;
				this.results = null;				
				
				this.set('extensions.modules.search.query', null);				
				this.set('extensions.modules.search.results', null);
			},
			onClickNavigate (row) {
				this.$router.push(row.path);
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
					
					this.count = size(results.data);
					
				}).catch((error) => {
					
					this.error = error;
					
					this.loading = false;
				});
			},
			set (key, input) {
				if (!window.sessionStorage) return null;
				
				if (input === null) return window.sessionStorage.removeItem(key);
				
				return window.sessionStorage.setItem(key, JSON.stringify(input));
			}
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
		padding: var(--page-padding);
					
		.modules-search-search {
			position: relative;
			
			button {
				background: var(--blue-grey-900);
				border: none !important;
				width: 40px;
				height: 40px;
				position: absolute;
				right: 2px;
				top: 2px;
			}
		}
		
		.modules-search-content {
			
			.modules-search-contents {
				display: flex;
				align-items: center;
				justify-content: center;
				min-height: calc(100vh - 250px);
				
				div.lead {
					color: var(--blue-grey-500);
					font-size: 2rem;
					font-weight: 300;
					
					p {
						margin-bottom: 0.5rem;
					}
				}
			}	
			
			div.modules-search-results {
				position: relative;
				
				header.modules-search-results {
					font-size: 1.5rem;
					font-weight: 300;
					margin-bottom: 3rem;
				}
				
				.modules-search-result {
					cursor: pointer;
					margin-bottom: 3rem;
					
					.modules-search-title {
						color: var(--main-primary-color) !important;
						font-size: 1.25rem;
					}
					
					small {
						display: block;
						color: var(--blue-grey-500);
						font-size: 0.875em;
						
						&.modules-search-headline {
							font-weight: 500;
							padding-bottom: 0.25rem;
						}
					}
				}
			}		
		}
	}
</style>