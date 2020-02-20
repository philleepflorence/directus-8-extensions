<template>
	<div class="modules-modules">
		<v-header 
			:title="contents.subtitle" 
			:breadcrumb="breadcrumb" 
			icon="view_module" 
			settings>
		</v-header>
		
		<div class="modules-modules-loading" v-if="loading">
			<div class="flex-item">
				<v-spinner
					v-show="loading"
					line-fg-color="var(--blue-grey-300)"
					line-bg-color="var(--blue-grey-200)"
					class="spinner">
				</v-spinner>
			</div>
		</div>
		
		<div class="modules-modules-contents animated fadeIn" v-if="!loading">
			<v-details :title="contents.headlines.assets" type="break" open>
				<nav class="animated fadeIn">
					<ul>					
						<v-card
							:title="contents.modules.cdn.title"
							:subtitle="contents.modules.cdn.subtitle"
							element="li"
							:to="`/${currentProjectKey}/ext/cdn`"
							icon="cloud">
						</v-card>
						<v-card
							:title="contents.modules.icons.title"
							:subtitle="contents.modules.icons.subtitle"
							element="li"
							:to="`/${currentProjectKey}/ext/icons`"
							icon="insert_emoticon">
						</v-card>
					</ul>
				</nav>
			</v-details>
			
			<v-details :title="contents.headlines.analytics" type="break" open>
				
			</v-details>	
			
			<v-details :title="contents.headlines.guides" type="break" open>
				
			</v-details>
		</div>	

		<v-info-sidebar wide>
			<h2 class="type-note">{{ this.contents.title}}</h2>
			<span class="type-note">{{ this.contents.description }}</span>
		</v-info-sidebar>
	</div>
</template>

<script>
	import { forEach, set } from 'lodash';
	
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
			}
		},
		methods: {
			load () {
				this.loading = true;
												
				this.$api.api.get('/custom/analytics/modules')
				.then((response) => {
					
					this.loading = false;
					
					forEach(response, (row, module) => {
						set(this.contents.modules, `${ module }.subtitle`, `${ row.total } Items`);
					});
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
				contents: {
					title: "Modules",
					subtitle: 'Modules - Collection of Custom Modules',
					description: 'Collection of Custom Modules',
					headlines: {
						analytics: "Analytics - Reports",						
						assets: "Assets - Application",						
						guides: "Guides - Tools"
					},
					modules: {
						cdn: {
							title: "CDN - Assets",
							subtitle: ""
						},
						icons: {
							title: "Icons - Application",
							subtitle: ""
						}
					}						
				},
				loading: true
			};
		},
		metaInfo() {
			return {
				title: this.contents.subtitle
			};
		},
		mounted () {
			this.load();
		}
	}
</script>

<style lang="scss" scoped>
	.modules-modules {
		padding: var(--page-padding-top) var(--page-padding) var(--page-padding-bottom);
		
		.content nav ul {
			padding: 0;
			display: grid;
			grid-template-columns: repeat(auto-fill,var(--card-size));
			grid-gap: var(--card-horizontal-gap);
		}
	}
	.modules-modules-loading {
		display: flex;
		align-items: center;
		justify-content: center;
		min-height: calc(100vh - 150px);
	}
</style>