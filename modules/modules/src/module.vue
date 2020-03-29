<template>
	<div class="modules-modules">
		
		<v-header 
			:title="content('title')" 
			:breadcrumb="breadcrumb" 
			icon="view_module" 
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
						<p class="lead animated fadeIn font-accent" v-if="analytics[index]"><span>{{ analytics[index].total }}</span><span>{{ row.analytics }}</span></p>
					</div>
				</div>
			</div>
			
		</div>	

		<v-info-sidebar wide>
			<section class="info-sidebar-section">
				<h2 class="font-accent">{{ content('title') }}</h2>
				<p class="p">{{ content('description') }}</p>
				<p class="lead info-sidebar-count" v-if="count">{{ count }}</p>
			</section>
		</v-info-sidebar>
		
	</div>
</template>

<script>
	import { forEach, get, set, size } from 'lodash';
	
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
								"description": "Application Visitors and Performance Analytics",
								"path": "/app/ext/analytics",
								"icon": "pie_chart",
								"analytics": "sessions"
							},
							"reports": {
								"title": "Application Reports",
								"description": "Application Data and Modules Reports",
								"path": "/app/ext/reports",
								"icon": "bar_chart",
								"analytics": "Reports"
							},
							"cdn": {
								"title": "CDN",
								"description": "CDN Static Assets and Documents",
								"path": "/app/ext/cdn",
								"icon": "cloud",
								"analytics": "Items"
							},
							"faqs": {
								"title": "Help and FAQs",
								"description": "Get help with common tasks and more",
								"path": "/app/ext/help",
								"icon": "question_answer",
								"analytics": "Items"
							},
							"icons": {
								"title": "Icons",
								"description": "Icons available to the applications",
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
			
			this.count = size(this.content('modules'));
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
				
/*
				&.modules-modules-analytics {
					grid-row: 1/3 !important;
				}
*/
				
				.flex-item {
					flex-grow: 1;
					text-align: center;
					color: var(--main-primary-color);
					
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
					
					.modules-modules-analytics {
						position: relative;
						padding: 1rem;
						
						p.lead {
							display: flex;
							align-items: center;
							justify-content: center;
							color: rgba(white, 0.3);
							font-size: 2rem !important;
							font-weight: 300 !important;
						
							span + span {
								color: rgba(white, 0.2);
								font-size: 1rem;
								font-weight: 400 !important;
								text-transform: lowercase;
								padding-left: 0.3rem;
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