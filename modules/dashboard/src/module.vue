<template>
	<div class="modules-dashboard module-page-root">
		
		<v-header 
			:title="content('title')" 
			:breadcrumb="breadcrumb" 
			:icon="icon" 
			settings>
		</v-header>
		
		<div class="modules-module-loading" v-if="loading">
			<v-progress-linear background-color="--main-primary-color" color="--blue-grey-700" indeterminate>
		</div>
			
		<div class="modules-dashboard-content modules-content animated fadeIn" v-else>
			
			<header class="modules-dashboard-header modules-header">
				<h2 v-html="header.title"></h2>
				<p v-html="header.description"></p>
			</header>
			
			<v-details 
				:title="content('titles.modules')" 
				type="break" 
				open>
				<nav class="module-nav">
					<ul class="module-ul">
						<v-card
							v-for="(row, key, index) in content('modules')"
							element="li"
							:title="row.title"
							:subtitle="subtitle(row, key)"
							color="main-primary-color"
							:to="row.path"
							:icon="row.icon">	
							/*
								Use the title slot and v-tooltip to show the description.
							*/
						</v-card>				
					</ul>
				</nav>
			</v-details>
			
			<v-details 
				v-for="listing in collections"
				:title="listing.title" 
				type="break" 
				open>
				<nav class="module-nav">
					<ul class="module-ul">
						<v-card
							v-for="collection in listing.collections"
							element="li"
							:title="collection.title"
							:subtitle="collection.subtitle"
							color="main-primary-color"
							:to="collection.path"
							:icon="collection.icon">	
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
		name: 'Dashboard',	
		mixins: [
			$mixins
		],
		data () {
			return {
				contents: $meta.contents,
				icon: $meta.icon
			};
		},	
		computed: {
			breadcrumb () {
				return [];
			},
			header () {
				return {
					title: `Hello ${ this.user.first_name } ${ this.user.last_name }`,
					description: this.content('description')
				};
			},
			modules () {
				return this.$store.state.extensions.modules;
			}
		},
		metaInfo() {
			return {
				title: this.content('subtitle')
			};
		},
		created () {
			/*
				Send a list of collections to pull the items count from the analytics endpoint for the dashboard and application modules...
				Application can have last edited, comments, et al...
			*/
			this.load('dashboard');			
		}
	}
</script>

<style lang="scss">
	.module-page-root {
		padding: 1rem;
		
		.modules-dashboard-content 
		{
			padding: 1rem;
			
			.modules-dashboard-header
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