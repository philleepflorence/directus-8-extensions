<template>
	<div class="modules-modules module-page-root">
		
		<v-header 
			:title="content('title')" 
			:breadcrumb="breadcrumb" 
			icon="view_module" 
			settings>
		</v-header>
		
		<div class="modules-module-loading" v-if="loading">
			<v-progress-linear background-color="--main-primary-color" color="--blue-grey-700" indeterminate>
		</div>
		
		<div class="modules-modules-content modules-content animated fadeIn" v-else>
			
			<header class="modules-dashboard-header modules-header">
				<h2 v-html="header.title"></h2>
				<p v-html="header.description"></p>
			</header>
			
			<v-details 
				v-for="(group, name) in content('modules')"
				:title="unescape(group.title)" 
				type="break" 
				open>
				<nav class="module-nav">
					<ul class="module-ul">
						<v-card
							v-for="(module, key) in group.modules"
							element="li"
							:title="unescape(module.title)"
							:subtitle="subtitle(module, key)"
							color="main-primary-color"
							:to="module.path"
							:icon="module.icon">	
						</v-card>
					</ul>
				</nav>
			</v-details>
			
		</div>
		
		<v-info-sidebar></v-info-sidebar>
		
	</div>
</template>

<script>
	import $meta from './meta.json';
	import $mixins from "./mixins/modules/analytics.js";
	
	export default {
		name: 'Modules',
		mixins: [
			$mixins
		],
		data () {
			return {
				contents: $meta.contents
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
			header () {
				return {
					title: `Hello ${ this.user.first_name } ${ this.user.last_name }`,
					description: this.content('description')
				};
			}
		},
		metaInfo() {
			return {
				title: this.content('subtitle')
			};
		},
		created () {
			this.load('modules');
		}
	}
</script>

<style lang="scss">
	.module-page-root {
		padding: 1rem;
		
		.modules-content 
		{
			padding: 1rem;
			
			.modules-header
			{
				padding-bottom: 4rem;
				
				h2 {
					color: var(--heading-text-color);
					font-family: var(--main-font-body);
					font-size: 28px;
					line-height: 32px;
					font-weight: 300;
					margin-bottom: 12px;
				}
				
				p {
					color: var(--note-text-color);
					font-size: var(--input-font-size);
					max-width: 768px;
				}
			}
			
			.v-details 
			{
				.content 
				{
					.module-nav 
					{
						.module-ul 
						{
							padding: 0;
							display: grid;
							grid-template-columns: repeat(auto-fill, var(--card-size));
							grid-gap: var(--card-horizontal-gap);
							
							.v-card {
								display: block;
								
								a 
								{
									.body 
									{
										.title {
											color: var(--blue-grey-300);
											font-family: var(--main-font-body);
										}
										
										.subtitle {
											font-style: normal;
										}
									}
								}
								
								@for $i from 1 through 10
								{
									&:nth-child(#{$i})
									{
										.header {
											background: rgba(var(--main-primary-color-rgb), (0.75 - ($i * 0.05))) !important;
										}
									}
								}
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
</style>