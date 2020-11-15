<template>
	<div class="modules-comments module-page-root">
		
		<v-header 
			:title="content('title')" 
			:breadcrumb="breadcrumb" 
			:icon="icon" 
			settings>
		</v-header>
		
		<div class="modules-comments-content animated fadeIn">
			
			<div class="modules-comments-contents" v-if="loading">
				<v-spinner
					v-show="loading"
					line-fg-color="var(--blue-grey-300)"
					line-bg-color="var(--blue-grey-200)"
					class="spinner">
				</v-spinner>
				<div class="lead" v-show="!loading">
					<p v-for="row in content('disclaimer')">{{ row }}</p>
				</div>
			</div>
			
			<div class="modules-comments-results animated fadeIn" v-else-if="comments">
				<header class="modules-divider">
					<h2 class="modules-divider">{{ content('comments.headline') }}</h2>
					<hr />
					<p class="modules-divider">{{ content('comments.information') }}</p>
				</header>
				<div class="modules-comments-results">
					<app-table :headers="headers" :rows="comments" @click="onClickNavigate"></app-table>					
				</div>
			</div>
			
			<div class="modules-comments-contents" v-else-if="!comments">
				<div class="lead">
					<p v-for="row in content('comments.empty')">{{ row }}</p>
				</div>
			</div>
			
		</div>	

		<v-info-sidebar wide itemDetail>
			<section class="info-sidebar-section">
				<h2 class="font-accent">{{ content('title') }}</h2>
				<p class="p">{{ content('description') }}</p>
				<p class="lead info-sidebar-count" v-if="count">{{ count }}</p>
			</section>
			<section class="info-sidebar-section">
				<header class="info-sidebar-section">
					<p class="info-sidebar-row" v-for="row in content('disclaimer')">{{ row }}</p>
				</header>
			</section>
		</v-info-sidebar>
		
	</div>
</template>

<script>
	import { forEach, get, set, size, startCase } from 'lodash';
	
	import AppTable from './components/tables/table.vue';
	
	import $meta from './meta.json';
	
	export default {
		name: 'ModulesComments',
		components: {
			'app-table': AppTable
		},
		data () {
			return {
				comments: null,
				contents: $meta.contents,
				icon: $meta.icon,
				count: 0,
				keys: {
					element: 0
				},
				loading: true,
				query: null,
				timers: {
					input: 0
				}
			};
		},
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
			collections () {
				return get(this.$store.state, 'collections');
			},
			currentProjectKey () {
				return this.$store.state.currentProjectKey;
			},
			headers () {
				return [
					"date",
					"author",
					"collection",
					"comment"
				];
			},
			locale () {
				return get(this.$store.state, 'settings.values.default_locale');
			},
			url () {
				return [
					window.location.origin,
					window.location.pathname,
					"#"
				].join('');
			},
			user () {
				return get(this.$store.state, 'currentUser');
			}
		},
		methods: {
			collection (input) {
				let translations = get(this.collections, `${ input }.translation`);
				let collection;
				
				if (translations) collection = translations.filter(translation => translation.locale === this.user.locale);
				
				if (size(collection)) return collection[0].translation;
				
				return startCase(input);
			},
			content (input) {
				let translation = get(this.contents, this.locale);
					translation = translation || get(this.contents, 'en-US');

				return get(translation, input);
			},
			get (key) {
				if (!window.localStorage) return null;
				
				let value = window.localStorage.getItem(key);
				
				if (typeof value === 'string') return JSON.parse(value);
				
				return null;
			},
			onClickNavigate (row) {
				this.$router.push(row.path);
			},
			load () {
				this.loading = true;
												
				this.$api.getItems('directus_activity', {
					fields: "id,action,action_by.first_name,action_by.last_name,action_by.email,action_on,collection,item,comment",
					limit: 200,
					filter: {
						action: {
							eq: "comment"
						},
						comment_deleted_on: {
							null: true
						}
					}
				}).then((response) => {
					
					this.count = size(response.data);
					
					if (this.count) {
						let comments = [];
						
						forEach(response.data, (row) => {
							comments.push({
								date: row.action_on,
								author: `${ row.action_by.first_name } ${ row.action_by.last_name }`,
								collection: this.collection(row.collection),
								comment: row.comment,
								path: `/${ this.currentProjectKey }/collections/${ row.collection }/${ row.item }`
							});
						});
						
						this.comments = comments;
					}
										
					this.loading = false;	
					
					let date = new Date();
					let datetime = date.toISOString().slice(0, 19).replace('T', ' ');	
					
					this.set("comments.viewed", datetime);			
					
				}).catch((error) => {
					
					this.error = error;
					
					this.loading = false;
				});
			},
			set (key, input) {
				if (!window.localStorage) return null;
				
				if (input === null) return window.localStorage.removeItem(key);
				
				return window.localStorage.setItem(key, JSON.stringify(input));
			}
		},
		metaInfo() {
			return {
				title: this.content('subtitle')
			};
		},
		mounted () {
			this.load();
		}
	}
</script>

<style lang="scss" scoped>
	.modules-comments {
		padding: var(--page-padding);
					
		.modules-comments-comment {
			position: relative;
			
			button {
				background: var(--blue-grey-900);
				border: none !important;
				width: 40px;
				height: 40px;
				position: absolute;
				right: 2px;
				top: 2px;
			}
		}
		
		.modules-comments-content {
			
			.modules-comments-contents {
				display: flex;
				align-items: center;
				justify-content: center;
				min-height: calc(100vh - 250px);
				
				div.lead {
					color: var(--blue-grey-500);
					font-size: 2rem;
					font-weight: 300;
					
					p {
						margin-bottom: 0.5rem;
					}
				}
			}	
			
			div.modules-comments-results {
				position: relative;
				
				header.modules-comments-results {
					font-size: 1.5rem;
					font-weight: 300;
					margin-bottom: 3rem;
				}
				
				.modules-comments-result {
					cursor: pointer;
					margin-bottom: 3rem;
					
					.modules-comments-title {
						color: var(--main-primary-color) !important;
						font-size: 1.25rem;
					}
					
					small {
						display: block;
						color: var(--blue-grey-500);
						font-size: 0.875em;
						
						&.modules-comments-headline {
							font-weight: 500;
							padding-bottom: 0.25rem;
						}
					}
				}
			}		
		}
	}
</style>