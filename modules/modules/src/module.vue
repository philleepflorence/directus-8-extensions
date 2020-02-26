<template>
	<div class="modules-modules">
		
		<v-header 
			:title="content('subtitle')" 
			:breadcrumb="breadcrumb" 
			icon="view_quilt" 
			settings>
		</v-header>
		
		<div class="modules-modules-content animated fadeIn">
			
			<div v-for="(row, index) in content('modules')"
				:class="`modules-modules-grid modules-modules-${index} animated fadeInUpSmall a-delay`" 				 
				@click="onClick(row.path)">
				<div class="flex-item">
					<span class="v-icon icon"><i>{{ row.icon }}</i></span>
					<h3 class="font-accent">{{ row.title }}</h3>
					<p class="lead">{{ row.description }}</p>
					<div class="modules-modules-analytics">
						<v-spinner
							v-show="loading"
							line-fg-color="var(--blue-grey-300)"
							line-bg-color="var(--blue-grey-200)"
							class="spinner">
						</v-spinner>
						<p class="lead animated fadeIn font-accent" v-if="analytics[index]">{{ analytics[index].total }}<span>{{ row.analytics }}</span></p>
					</div>
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
	import { forEach, get, set } from 'lodash';
	
	export default {
		name: 'Modules',
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
			content (input) {
				let translation = get(this.contents, this.locale);
					translation = translation || get(this.contents, 'en-US');

				return get(translation, input);
			},
			load () {
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
						"title": "Modules",
						"subtitle": 'Modules - Collection of Custom Modules',
						"description": 'Collection of Custom Modules',
						"headlines": {
							"analytics": "Analytics - Reports",						
							"assets": "Assets - Application",						
							"guides": "Guides - Tools"
						},
						"modules": {
							"analytics": {
								"title": "Application Analytics",
								"description": "Analytics of the Application Visitors",
								"path": "/app/ext/analytics-app",
								"icon": "pie_chart",
								"analytics": "sessions"
							},
							"cdn": {
								"title": "CDN",
								"description": "View Static Assets in your CDN",
								"path": "/app/ext/cdn",
								"icon": "cloud",
								"analytics": "Items"
							},
							"icons": {
								"title": "Icons",
								"description": "View all Icons available to your applications",
								"path": "/app/ext/icons",
								"icon": "insert_emoticon",
								"analytics": "Icons"
							},
							"search": {
								"title": "Search",
								"description": "Search all visible collection items",
								"path": "/app/ext/search",
								"icon": "search",
								"analytics": "Collections"
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
			this.$content = this.$el.querySelector('.modules-modules-content');
			
			if (this.$content) this.render();
		}
	}
</script>

<style lang="scss" scoped>
	.modules-modules {
		padding: var(--page-padding);
		
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
				
				&.modules-modules-analytics {
					grid-column: 1/3 !important;
					grid-row: 1/2 !important;
				}
				
				&.modules-modules-search {
					grid-column: 2/4 !important;
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
					
					.modules-modules-analytics {
						position: relative;
						padding: 1rem;
						
						p.lead {
							color: rgba(white, 0.3);
							font-size: 2.5rem !important;
							font-weight: 300 !important;
							line-height: 1.75rem;
							padding-top: 0.5rem;
						
							span {
								display: block;
								color: rgba(white, 0.2);
								font-size: 1rem;
								font-weight: 400 !important;
								text-transform: lowercase;
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