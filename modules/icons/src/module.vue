<template>
	<div class="modules-icons">
		<v-header 
			:title="content('title')" 
			:breadcrumb="breadcrumb" 
			icon="insert_emoticon" 
			settings>
		</v-header>
		
		<div class="modules-icons-loading" v-if="loading">
			<div class="flex-item">
				<v-spinner
					v-show="loading"
					line-fg-color="var(--blue-grey-300)"
					line-bg-color="var(--blue-grey-200)"
					class="spinner">
				</v-spinner>
			</div>
		</div>
		
		<div class="modules-icons-contents animated fadeIn" v-if="!loading">
			<div class="modules-icons-content">
				<button class="modules-icons-grid" v-for="row in icons" @click="onDetails(row.id)">
					<span v-html="row.icon"></span>
					<p>{{ row.name }}</p>
				</button>
			</div>
			<aside class="modules-icons-details animated fadeIn" v-show="details">
				<span class="bg" @click="onDetails(0)"></span>
				<div class="modules-icons-detail animated fadeInUpSmall flex-item" v-if="details">
					<div class="modules-icons-detail-row" v-for="(value, field) in details">
						<small class="font-accent">{{ formatField(field) }}</small>
						<div class="p" v-html="formatValue(value)"></div>
					</div>
				</div>
			</aside>
		</div>	

		<v-info-sidebar wide itemDetail>
			<section class="info-sidebar-section">
				<h2 class="font-accent">{{ content('title') }}</h2>
				<p class="p">{{ content('description') }}</p>
				<p class="lead info-sidebar-count" v-if="!loading">{{ count }}</p>
			</section>
			<section class="info-sidebar-section">
				<h2 class="font-accent">{{ content('form.headline') }}</h2>
				<div class="modules-icons-search">
					<div class="info-sidebar-row">
						<v-input
							id="modules-icons-search-input"
							type="search"
							:placeholder="content('form.placeholder')"
							:model="query"
							@input="onInput">
						</v-input>
					</div>
				</div>
			</section>
		</v-info-sidebar>
	</div>
</template>

<script>
	import { get, filter, forEach, set, size, startCase } from 'lodash';
	
	export default {
		name: 'Modules',
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
			icons () {
				if (!this.query.length) return this.data.icons;
				
				let icons = [];
				let query = this.query.toLowerCase();
				
				forEach(this.data.icons, (row) => {
					let string = [row.name, row.class, row.keywords].join(' ');
					
					if (string.indexOf(query) > -1) icons.push(row);
				});
				
				this.count = icons.length;
				
				return icons;
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
			formatField (input) {
				return startCase(input);
			},
			formatValue (input) {
				if (input) return input;
				
				return '--'; 
			},
			load () {
				this.loading = true;
												
				this.$api
					.getItems('app_icons', {
						fields: "id, font_family, name, icon, content, css_entity, class, keywords, version",
						limit: -1
					})
					.then((response) => {
						this.data.icons = response.data;
						this.count = size(response.data);
												
						this.loading = false;						
					})
					.catch(error => {
						this.error = error;
						this.loading = false;
					})
					.finally(() => {
						this.loading = false;
					});
			},
			onDetails (input) {
				if (input) {
					let details = filter(this.data.icons, (row) => {
						return row.id == input;
					});
					
					this.details = details[0];
				} 
				else this.details = null;
			},
			onInput (input) {
				clearTimeout(this.timers.input);
				
				this.timers.input = setTimeout(() => {
					this.query = input;
					console.log("debug - development", input);
				}, 
				300);
			}
		},
		data () {
			return {
				contents: {
					"en-US": {
						"title": "Icons",
						"subtitle": 'Icons - View all the Application Icons Available',
						"description": 'View all the Application Icons Available',
						"form": {
							"headline": 'Search Icons',
							"submit": 'Search Icons',
							"filter": 'Search Icons',
							"placeholder": 'Search Icons by Name and Keywords'
						}
					}										
				},
				count: 0,
				details: null,
				data: {
					icons: [],
				},
				loading: true,
				query: '',
				timers: {
					input: 0
				}
			};
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
	.modules-icons {
		padding: var(--page-padding-top) var(--page-padding) var(--page-padding-bottom);
		
		.modules-icons-contents {
			.modules-icons-search {
				margin-bottom: 1rem;
			}
			
			.modules-icons-content {
				display: grid;
				grid-template-columns: repeat(5, 1fr);
				grid-auto-rows: minmax(100px, max-content);
				grid-gap: 1rem;
				
				.modules-icons-grid {
					background-color: rgba(white, 0.1);
					cursor: pointer;
					text-align: center;
					
					p {
						font-size: 0.875rem;
						overflow: hidden;
						text-overflow: ellipsis;
						white-space: nowrap;
						padding: 0.5rem;
					}
				}
			}
			
			.modules-icons-details {
				position: absolute;
				top: 0;
				right: 0;
				bottom: 0;
				left: 0;
				display: flex;
				align-items: center;
				justify-content: center;
				
				span.bg {
					position: absolute;
					top: 0;
					right: 0;
					bottom: 0;
					left: 0;
					background-color: var(--module-background-color);
					opacity: 0.8;
				}
				
				div.modules-icons-detail {
					position: relative;
					width: 580px;
					max-width: 90vw;
					padding: 0 1rem;
					background: var(--blue-grey-800);
					
					div.modules-icons-detail-row {
						border-bottom: 1px solid var(--blue-grey-700);
						padding: 1rem 0;
						display: flex;
						align-items: center;
					
						small {
							text-transform: uppercase;
							width: 30%;
						}
						
						div.p {
							width: 70%;
						}
					
						&:last-child {
							border: none;
						}
					}
				}
			}
		}
	}
	.modules-icons-loading {
		display: flex;
		align-items: center;
		justify-content: center;
		min-height: calc(100vh - 150px);
	}
</style>