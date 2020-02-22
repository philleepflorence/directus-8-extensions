<template>
	<div class="modules-analytics">
		
		<v-header 
			:title="content('subtitle')" 
			:breadcrumb="breadcrumb" 
			icon="view_quilt" 
			settings>
		</v-header>
		
		<div class="modules-analytics-content animated fadeIn">
			
			
			
		</div>	

		<v-info-sidebar wide>
			<h2 class="type-note">{{ this.content('title') }}</h2>
			<span class="type-note">{{ this.content('description') }}</span>
		</v-info-sidebar>
		
	</div>
</template>

<script>
	import { forEach, get, set } from 'lodash';
	
	export default {
		name: 'ModulesAnalyticsApplication',
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
			load () {
				this.loading = true;
												
				this.$api.api.get('/custom/analytics/application').then((response) => {
					
					this.loading = false;
					
					this.analytics = response;
					
				}).catch((error) => {
					
					this.error = error;
					
					this.loading = false;
				});
			},
			onClick (path) {
				this.$router.push(path);
			},
			render () {
				let rect = this.$content.getBoundingClientRect();
				let height = window.innerHeight - rect.y - 10;
				
				this.$content.style.minHeight = `${ height }px`;
				
				this.load();
			}
		},
		data () {
			return {
				analytics: {},
				contents: {
					"en-US": {
						"title": "Analytics - Application",
						"subtitle": 'Analytics - Report of the Application Visitors',
						"description": 'Analytics Report of the Application Visitors',
						"modules": {
							"cdn": {
								"browsers": "Browsers",
								"description": "Analytics of the Browsers used to load the application",
								"icon": "cloud"
							},
							"devices": {
								"title": "Devices",
								"description": "Analytics of the Devices used to load the application",
								"icon": "insert_emoticon"
							},
							"search": {
								"title": "Visitors",
								"description": "Analytics of the Visitors to the application",
								"icon": "search"
							}
						}
					}						
				},
				loading: false
			};
		},
		metaInfo() {
			return {
				title: this.content('subtitle')
			};
		},
		mounted () {
			this.$content = this.$el.querySelector('.modules-analytics-content');
			
			if (this.$content) this.render();
		}
	}
</script>

<style lang="scss" scoped>
	.modules-analytics {
		padding: var(--page-padding);
		
		.modules-analytics-content {
			display: grid;
			grid-template-columns: repeat(3, 1fr);
			grid-gap: 1rem;
			
			.modules-analytics-grid {
				background-color: rgba(white, 0.1);
				cursor: pointer;
				display: flex;
				align-items: center;
				justify-content: center;
				position: relative;
				overflow: hidden;
				animation-duration: 600ms;
				
				&.modules-analytics-cdn {
					grid-column: 1/3 !important;
					grid-row: 1/2 !important;
				}
				
				&.modules-analytics-icons {
					grid-column: 1/3 !important;
				}
				
				&.modules-analytics-search {
					grid-row: 1/3 !important;
				}
				
				.flex-item {
					flex-grow: 1;
					text-align: center;
					color: var(--main-primary-color);
					
					h3 {
						font-size: 2rem;
						margin: 0.5rem auto;
					}
					
					.lead {
						font-size: 1.25rem;
					}
					
					.icon {
						display: flex;
						align-items: center;
						justify-content: center;
						width: 60px;
						height: 60px;
						background: rgba(white, 0.1);
						margin: 0 auto 2rem auto;
						border-radius: 50%;						
					}
					
					.modules-analytics-analytics {
						position: relative;
						padding: 1rem;
						
						p.lead {
							color: rgba(white, 0.3);
							font-size: 2.5rem !important;
							font-weight: 300 !important;
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