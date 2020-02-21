<template>
	<div class="modules-dashboard">
		<v-header 
			:title="content('subtitle')" 
			:breadcrumb="breadcrumb" 
			icon="view_quilt" 
			settings>
		</v-header>
		
		<div class="modules-dashboard-content">
			
			<div v-for="(row, index) in content('modules')"
				:class="`modules-dashboard-grid modules-dashboard-${index} animated fadeIn a-delay`" 				 
				@click="onClick(row.path)">
				<div class="flex-item">
					<span class="v-icon icon"><i>{{ row.icon }}</i></span>
					<h3 class="font-accent">{{ row.title }}</h3>
					<p class="lead">{{ row.description }}</p>
					<div class="modules-dashboard-analytics">
						<v-spinner
							v-show="loading"
							line-fg-color="var(--blue-grey-300)"
							line-bg-color="var(--blue-grey-200)"
							class="spinner">
						</v-spinner>
						<p class="lead animated fadeIn font-accent" v-if="analytics[index]">{{ analytics[index].total }}</p>
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
	import { get } from 'lodash';
	
	export default {
		name: 'Dashboard',		
		computed: {
			breadcrumb () {
				return [];
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
												
				this.$api.api.get('/custom/analytics/dashboard')
				.then((response) => {
					
					this.loading = false;
					
					this.analytics = response;
				})
				.catch((error) => {
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
					"en-US" : {
						"title": "Dashboard",
						"subtitle": 'Dashboard - Modules Snapshot',
						"description": 'Directus Core &amp; Custom Modules',
						"modules": {
							"modules": {
								"title": "Modules",
								"description": "View assets, analytics, reports, guides, and tools",
								"path": "/app/ext/modules",
								"icon": "supervised_user_circle"
							},
							"files": {
								"title": "Files",
								"description": "View all uploaded files",
								"path": "/app/files",
								"icon": "cloud_done"
							},
							"collections": {
								"title": "Collections",
								"description": "View all collections and items",
								"path": "/app/collections",
								"icon": "view_module"
							},
							"users": {
								"title": "Users",
								"description": "Directus Users Directory",
								"path": "/app/users",
								"icon": "storage"
							}
						}						
					}				
				},
				loading: false
			};
		},
		metaInfo() {
			return {
				title: this.contents.subtitle
			};
		},
		mounted () {
			this.$content = this.$el.querySelector('.modules-dashboard-content');
			
			if (this.$content) this.render();
		}
	}
</script>

<style lang="scss" scoped>
	.modules-dashboard {
		padding: var(--page-padding);
		
		.modules-dashboard-content {
			display: grid;
			grid-template-columns: repeat(3, 1fr);
			grid-gap: 1rem;
			
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
					
					.modules-dashboard-analytics {
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