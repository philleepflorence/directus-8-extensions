<template>
	<div class="modules-dashboard">
		
		<v-header 
			:title="getContent('title')" 
			:breadcrumb="breadcrumb" 
			:icon="icon" 
			settings>
		</v-header>
		
		<div class="modules-module-loading" v-if="loading">
			<v-progress-linear background-color="--main-primary-color" color="--blue-grey-700" indeterminate>
		</div>
		
		<div class="modules-dashboard-content">
			
			<div v-for="(row, index) in getContent('modules')"
				:class="`modules-dashboard-grid modules-dashboard-${index} animated fadeInUpSmall a-delay`" 				 
				@click="onClick(row.path)">
				<div class="flex-item">
					<span class="v-icon icon"><i>{{ row.icon }}</i></span>
					<h3 class="font-accent" v-html="row.title"></h3>
					<p class="lead" v-html="row.description"></p>
					<div class="modules-dashboard-analytics">
						<v-spinner
							v-show="loading"
							line-fg-color="var(--blue-grey-300)"
							line-bg-color="var(--blue-grey-200)"
							class="spinner">
						</v-spinner>
						<p class="lead animated fadeIn font-accent" v-if="analytics[index]"><span>{{ analytics[index].total }}</span> <span>{{ row.analytics }}</span></p>
					</div>
				</div>
			</div>
			
		</div>
		
	</div>
</template>

<script>
	import { get } from 'lodash';
	
	import $meta from './meta.json';
	
	export default {
		name: 'Dashboard',	
		data () {
			return {
				analytics: {},
				contents: $meta.contents,
				icon: $meta.icon,
				loading: true
			};
		},	
		computed: {
			breadcrumb () {
				return [];
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
			loadAnalytics () {
				this.loading = true;
												
				this.$api.api.get('/custom/analytics/dashboard').then((response) => {
					
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
			this.loadAnalytics();
		}
	}
</script>

<style lang="scss" scoped>
	.modules-dashboard {
		padding: var(--page-padding);
		margin-right: -64px;
		
		.modules-dashboard-content {
			display: grid;
			grid-template-columns: repeat(3, 1fr);
			grid-gap: 1rem;
			min-height: calc(100vh - 120px) !important;
			
			.modules-dashboard-grid {
				background-color: rgba(white, 0.1);
				cursor: pointer;
				display: flex;
				align-items: center;
				justify-content: center;
				position: relative;
				overflow: hidden;
				animation-duration: 600ms;
				
				&.modules-dashboard-modules {
					grid-column: 1/1 !important;
					grid-row: 1/3 !important;
				}
				
				&.modules-dashboard-files {
					grid-column: 2/4 !important;
				}
				
				&.modules-dashboard-settings {
					grid-column: 1/3 !important;
				}
				
				.flex-item {
					flex-grow: 1;
					text-align: center;
					color: var(--main-primary-color);
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
						background: rgba(white, 0.1);
						margin: 0 auto 1rem auto;
						border-radius: 50%;						
					}
					
					.modules-dashboard-analytics {
						position: relative;
						padding: 1rem;
						
						p.lead {
							color: var(--main-primary-color);
							font-size: 2.5rem !important;
							font-weight: 300 !important;
							text-align: center;
						
							span + span {
								color: rgba(white, 0.3);
								font-size: 1.25rem;
								font-weight: 400 !important;
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