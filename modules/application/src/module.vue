<template>
	<div class="modules-modules module-page-root">
		
		<v-header 
			:title="getContent('subtitle')" 
			:breadcrumb="breadcrumb" 
			:icon="icon" 
			settings>
		</v-header>
		
		<div class="modules-module-loading" v-if="loading">
			<v-progress-linear background-color="--main-primary-color" color="--blue-grey-700" indeterminate>
		</div>
		
		<div class="modules-modules-content modules-content animated fadeIn" v-if="loaded">
			
			<div v-for="(row, index) in rows"
				:class="`modules-modules-grid modules-modules-${index} animated fadeInUpSmall a-delay`" 
				:style="`grid-column: ${ row.grid.column } !important; grid-row: ${ row.grid.row } !important;`"				 
				@click="onClick(row.path)">
				<div class="flex-item">
					<span class="v-icon icon"><v-icon :name="row.icon"></span>
					<h3 class="font-accent modules-grid-title">{{ row.title }}</h3>
					<p class="lead modules-grid-description" v-html="processContent(row.note)"></p>
					<div class="modules-modules-analytics">
						<p class="lead animated fadeIn" v-if="row.analytics"><span class="font-accent">{{ row.analytics.total }}</span><span>{{ row.analytics.text }}</span></p>
					</div>
				</div>
			</div>
			
		</div>
		
		<v-info-sidebar></v-info-sidebar>
		
	</div>
</template>

<script>
	import { filter, forEach, get, set, size, startCase } from 'lodash';
	
	import $meta from './meta.json';
	
	export default {
		name: 'Modules',
		data () {
			return {
				contents: $meta.contents,
				icon: $meta.icon,
				loading: true,
				loaded: false,
				rows: null
			};
		},
		computed: {
			breadcrumb () {
				return [
					{
						name: 'Dashboard',
						path: `/${this.currentProjectKey}/ext/dashboard`
					}
				];
			},
			currentProjectKey () {
				return this.$store.state.currentProjectKey;
			},
			locale () {
				return get(this.$store.state, 'settings.values.default_locale');
			},
			translation () {
				let translation = get(this.contents, this.locale);
					translation = translation || get(this.contents, 'en-US');
				
				return translation;
			}
		},
		methods: {
			getContent (input) {
				return get(this.translation, input);
			},
			loadModules () {
				this.loading = true;
												
				this.$api.api.get('/custom/analytics/project').then((response) => {
					
					if (!Array.isArray(response.data) || !size(response.data)) return this.$router.push('/app/ext/dashboard');
					
					response.data.forEach((row) => {
						let translation = filter(row.translation, (item) => { return item.locale === this.locale });
						
						if (!row.title && size(translation)) row.title = translation[0].translation;
						else if (!row.title) row.title = startCase(row.collection);
					});
					
					this.loading = false;
					
					this.rows = response.data;
					
					this.loaded = true;
					
				}).catch((error) => {
					
					this.error = error;
					
					this.loading = false;
				});
			},
			onClick (path) {
				this.$router.push(path);
			},
			processContent (input) {
				input = input.replace(/(\s-\s)/g, '<br>');
				
				return input;
			}
		},
		metaInfo() {
			return {
				title: this.getContent('subtitle')
			};
		},
		mounted () {
			this.loadModules();
			
			this.count = size(this.getContent('modules'));
		}
	}
</script>

<style lang="scss" scoped>
	.modules-modules {
		padding: 1rem;
		
		.modules-modules-content {
			display: grid;
			grid-template-columns: repeat(4, 1fr);
			grid-gap: 1rem;
						
			.modules-modules-grid {
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
					color: var(--page-text-color);
					padding: 1.5rem;
					
					h3 {
						font-size: 1.5rem;
						margin: 0 auto;
					}
					
					.lead {
						font-size: 1rem;
						
					}
					
					.icon {
						display: flex;
						align-items: center;
						justify-content: center;
						width: 50px;
						height: 50px;
						background-color: var(--main-primary-color);
						margin: 0 auto 1rem auto;
						border-radius: 50%;						
					}
					
					.modules-modules-analytics {
						position: relative;
						padding: 0.5rem 1rem;
						
						p.lead {
							color: var(--blue-grey-500);
							font-size: 1.5rem !important;
							font-weight: 400 !important;
							text-align: center;
						
							span + span {
								color: var(--blue-grey-600);
								font-size: 0.875rem;
								font-weight: 500 !important;
								text-transform: lowercase;
								display: block;
							}
						}
					}
				}
			}
		}	
	}
</style>