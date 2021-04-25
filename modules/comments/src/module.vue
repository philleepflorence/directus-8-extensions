<template>
	<div class="modules-comments module-page-root" v-bind:key="keys.module">
		
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
			
			<div class="modules-comments-results animated fadeIn" v-else-if="comments" v-bind:key="keys.comments">
				<details class="v-details break" open="open" v-for="(items, title) in comments" v-if="rows(items)">
					<summary>{{ title }}</summary>
					<div class="content">
						<div class="v-activity">
							<article class="activity-item" v-for="item in items" v-bind:ref="item.id" v-bind:data-id="item.id" v-bind:data-item="item.item" v-bind:data-collection="item.collection">
								<span class="indicator"></span>
								<div class="container">
									<div class="content">
										<p class="name">{{ item.author }} <small class="small">@ {{ item.time }}</small></p>
										<p class="comment">
											<span class="reply" v-if="item.reply">{{ item.reply.comment }}</span>
											<span class="comment" v-on:dblclick="onClickEdit($event, item)">{{ item.comment }}</span>
											<v-icon name="done" v-on:click="onClickUpdate($event, item)"></v-icon>
										</p>
										<small class="small">{{ item.collection_name }}</small>
									</div>
									<nav class="buttons">
										<v-icon name="more_vert"></v-icon>
										<v-icon name="arrow_forward" v-on:click="onClickNavigate(item)"></v-icon>
										<v-icon name="reply" v-on:click="onClickReply($event, false)"></v-icon>
										<v-icon name="clear" v-on:click="onClickDelete(item)"></v-icon>
									</nav>
									<form class="form">
										<div class="group">
											<div class="inputs">
												<v-input v-bind:placeholder="content('form.reply.hint')"></v-input>
												<v-button>
													<v-icon name="done" v-on:click="onClickReply($event, item)"></v-icon>
												</v-button>
											</div>
										</div>
									</form>
								</div>
							</article>
						</div>
					</div>					
				</details>
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
	import { forEach, get, set, size, startCase, unset } from 'lodash';
	
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
					comments: 0,
					module: 10
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
			params () {
				const path = window.location.hash.split("?");
				
				return new URLSearchParams(path.pop());
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
			onClickEdit (e, row) {
				const button = e.currentTarget.nextElementSibling;
				
				e.currentTarget.setAttribute('contenteditable', 'true');
				
				button.setAttribute('data-active', true);
			},
			onClickDelete (row) {
				const date = new Date().toISOString().slice(0, 19).replace('T', ' ');
				
				this.$api.api.delete(`activity/comment/${ row.id }`);
				
				unset(this.comments, `${ row.date }.${ row.time }`);
				
				this.keys.comments++;
			},
			onClickNavigate (row) {
				this.$router.push(row.path);
			},
			onClickReply (e, row) {
				const container = e.currentTarget.closest('.container');
				const form = container.querySelector('.form');
				const input = form.getElementsByTagName('input')[0];
				
				if (form && row === false) {
					form.classList.add('active');
					input.focus();
				}
				else if (!input.value) {
					form.classList.remove('active');
				}
				else if (input.value) {
					form.classList.remove('active');
					
					this.$api.api.post('activity/comment', {
						collection: row.collection,
						comment: input.value,
						item: row.item,
						reply: row.id
					}).then((response) => {
						this.load(false);
					});
				}
			},
			onClickUpdate (e, row) {
				let button = e.target;
				
				if (!button.classList.contains('v-icon')) button = button.parentElement;
				
				let span = button.previousElementSibling;
				let comment = span.innerHTML.trim();
				
				button.removeAttribute('data-active');
				span.removeAttribute('contenteditable');
				
				if (comment === row.comment) return false;
				
				this.$api.api.update(`activity/comment/${ row.id }`, {
					comment: comment
				});
			},
			load (render) {
				if (render) this.loading = true;
												
				this.$api.api.get(`activity`, {
					fields: "id,action,action_by.first_name,action_by.last_name,action_by.email,action_on,collection,item,comment,reply.comment",
					limit: 200,
					sort: "-action_on",
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
						let comments = {};
						
						forEach(response.data, (row) => {
							const date = new Date(row.action_on);
							const path = row.item ? `/${ this.currentProjectKey }/collections/${ row.collection }/${ row.item }` : `/${ this.currentProjectKey }/collections/${ row.collection }`;
							const data = {...row, ...{
								date: date.toDateString(),
								time: date.toTimeString().split(' ').shift(),
								author: `${ row.action_by.first_name } ${ row.action_by.last_name }`,
								collection_name: this.collection(row.collection),
								path: path
							}};
							
							set(comments, `${ data.date }.${ data.time }`, data);
						});
						
						this.comments = comments;
					}
										
					if (render) this.loading = false;	
					
					let date = new Date();
					let datetime = date.toISOString().slice(0, 19).replace('T', ' ');	
					
					this.set("comments.viewed", datetime);	
					
					if (render) setTimeout(this.render, 500);		
					
				}).catch((error) => {
					
					this.error = error;
					
					this.loading = false;
				});
			},
			render () {
				const comment = this.params.get("comment");
				const element = comment && this.$refs[comment] ? this.$refs[comment][0] : null;
				
				if (element) {
					element.classList.add('active');
					element.scrollIntoView({
						behavior: "smooth",
						block: "center", 
						inline: "nearest"
					});
				}
			},
			rows (items) {
				return size(items);
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
			this.load(true);
		}
	}
</script>

<style lang="scss">
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
				padding-top: 3rem;
				
				.v-details {
					border-top: 2px solid var(--input-border-color);
					margin-bottom: 80px;
					position: relative;
					
					summary {
						cursor: pointer;
						display: inline-block;
						list-style: none;
						position: absolute;
						top: 0;
						
						&::-webkit-details-marker {
							display: none;
						}
						
						&:after {
							color: var(--blue-grey-500);
						}
					}
					
					.v-activity {
						position: relative;
						
						&::before {
							content: '';
							position: absolute;
							left: 0;
							top: 0;
							height: 100%;
							width: 2px;
							background-color: var(--input-border-color);
							z-index: -1;
						}
						
						article {
							display: flex;
							margin: 15px 0;
							position: relative;
						
							span.indicator {
								position: absolute;
								top: 50%;
								display: inline-block;
								width: 6px;
								height: 6px;
								background-color: var(--input-border-color-hover);
								margin-top: -4px;
							}
							
							.container {
								position: relative;
								display: flex;
								width: 100%;
							
								div.content {
									background-color: rgba(0, 0, 0, 0.1);
									flex-grow: 1;
									margin-left: 15px;
									padding: 15px;
									
									.name {
										color: var(--blue-grey-500);
										font-weight: 500;
									}
									
									.comment {
										display: block;
										margin: 5px 0;
										
										.comment {
											cursor: text;
											word-wrap: break-word;
											word-break: break-word;
											hyphens: auto;
											white-space: pre-wrap;
																					
											&[contenteditable] {
												color: var(--main-primary-color) !important;
											}
										}
										
										.v-icon {
											color: var(--main-primary-color) !important;
											display: none !important;
											padding-left: 5px;
											
											&[data-active] {
												display: inline-block !important;
											}
										}
										
										.reply {
											border-left: 2px solid var(--blue-grey-800);
											color: var(--blue-grey-600);
											font-size: 0.875rem;
											margin-top: 5px;
											padding: 0 10px 0 5px;
											word-wrap: break-word;
											word-break: break-word;
											hyphens: auto;
											white-space: pre-wrap;
										}
									}
									
									.small {
										color: var(--blue-grey-600);
										font-size: 0.875rem;
										font-weight: 600;
									}
								}
								
								nav.buttons {
									background-color: rgba(0, 0, 0, 0.1);
									display: flex;
									flex-direction: column;
									justify-content: center;
									margin: 0 0 0 1px;
									padding: 8px 10px; 
									
									span.v-icon {
										color: var(--blue-grey-700) !important;
										
										&:hover {
											color: var(--main-primary-color) !important;
										}
										
										&:active {
											color: var(--blue-grey-800) !important;
										}
										
										&[role="button"] {
											display: none !important;
										}
									}
									
									&:hover {
										justify-content: space-between;
										
										span.v-icon {
											display: none !important;
											
											&[role="button"] {
												animation: fadeIn 300ms ease-in;
												display: inline-block !important;
											}
										}
									}
								}
								
								form.form {
									animation: fadeIn 300ms ease-in;
									background-color: var(--blue-grey-800);
									display: none;
									align-items: center;
									position: absolute;
									top: 0;
									right: 0;
									bottom: 0;
									left: 15px;
									padding: 15px;
									
									&.active {
										display: flex;
									}
									
									.group {
										flex-grow: 1;
										
										.inputs {
											display: flex;
											align-items: center;
																					
											.v-input {
												flex-grow: 1;
												
												input {
													height: 40px;
												}
											}
											
											.v-button {
												border-radius: 20px;
												height: 40px;
												width: 40px;
												margin: 0 0 0 10px !important;
												min-width: 40px !important;
												padding: 0px !important;
											}											
										}
										
										.hint {
											color: var(--blue-grey-600);
											font-size: 12px;
											font-weight: 500;
											padding-top: 10px;
										}										
									}
								}								
							}
							
							&.active {
								.container {
									div.content, nav.buttons {
										border-bottom: 2px solid var(--main-primary-color);
									}
								}
							}
						}
					}
				}
			}		
		}
	}
</style>