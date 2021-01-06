<template>
	<div class="modules-modules module-page-root">
		
		<v-header 
			:title="getContent('title')" 
			:breadcrumb="breadcrumb" 
			icon="view_module" 
			settings>
		</v-header>
		
		<div class="modules-module-loading" v-if="loading">
			<v-progress-linear background-color="--main-primary-color" color="--blue-grey-700" indeterminate>
		</div>
		
		<div class="modules-modules-content modules-content animated fadeIn" v-else>
			
			<div v-for="(row, index) in getContent('modules')"
				:class="`modules-modules-grid modules-modules-${index} animated fadeInUpSmall a-delay`" 
				:data-module="index"				 
				@click="onClick(row.path)">
				<div class="flex-item">
					<span class="v-icon icon"><i>{{ row.icon }}</i></span>
					<h3 class="font-accent modules-grid-title">{{ row.title }}</h3>
					<p class="lead modules-grid-description">{{ row.description }}</p>
					<div class="modules-modules-analytics">
						<v-spinner
							v-show="loading"
							line-fg-color="var(--blue-grey-300)"
							line-bg-color="var(--blue-grey-200)"
							class="spinner">
						</v-spinner>
						<p class="lead animated fadeIn" v-if="analytics[index]"><span class="font-accent">{{ analytics[index].total }}</span><span>{{ row.analytics }}</span></p>
					</div>
				</div>
			</div>
			
		</div>
		
		<v-info-sidebar></v-info-sidebar>
		
	</div>
</template>

<script>
	import { forEach, get, set, size } from 'lodash';
	
	import $meta from './meta.json';
	
	export default {
		name: 'Modules',
		data () {
			return {
				analytics: {},
				contents: $meta.contents,
				loading: true
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
			}
		},
		methods: {
			getContent (input) {
				let translation = get(this.contents, this.locale);
					translation = translation || get(this.contents, 'en-US');

				return get(translation, input);
			},
			loadModules () {
				this.loading = true;
												
				this.$api.api.get('/custom/analytics/modules').then((response) => {
					
					this.loading = false;
					
					this.analytics = response;
					
				}).catch((error) => {
					
					this.error = error;
					
					this.loading = false;
				});
			},
			onClick (path) {
				this.$router.push(path);
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
			grid-template-columns: repeat(3, 1fr);
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
				
				&[data-module="guides"] {
					grid-column: 1/3!important;
				}
				
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
	.v-spinner {
		margin: auto;
	}
	.icon {
		i {
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
	}
</style>