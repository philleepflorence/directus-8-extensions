<template>
	<div class="modules-help">
		
		<v-header 
			:title="content('title')" 
			:breadcrumb="breadcrumb" 
			icon="question_answer" 
			settings>
		</v-header>
		
		<div class="modules-help-loading" v-if="loading">
			<div class="flex-item">
				<v-spinner
					v-show="loading"
					line-fg-color="var(--blue-grey-300)"
					line-bg-color="var(--blue-grey-200)"
					class="spinner">
				</v-spinner>
			</div>
		</div>
		
		<div class="modules-help-content animated fadeIn" v-else-if="rows">
			
			<v-details :title="content(`headlines.${ index }`)" type="break" v-for="(section, index) in rows" open>
				<ol class="modules-help-ol">
					<li class="modules-help-li" v-for="(row, key) in section">
						<section class="modules-help-section" :data-href="`${ index }-${ row.slug }`">
							<h3 class="lead font-accent">{{ row.question }}</h3>
							<div class="p" v-html="row.answer"></div>
						</section>
					</li>
				</ol>
			</v-details>
			
		</div>	

		<v-info-sidebar wide itemDetail>
			<section class="info-sidebar-section">
				<h2 class="font-accent">{{ content('title') }}</h2>
				<p class="p">{{ content('description') }}</p>
				<p class="lead info-sidebar-count" v-if="rows">{{ count }}</p>
			</section>
			<section class="info-sidebar-section">
				<h2 class="font-accent">{{ this.content('form.search.headline') }}</h2>
				<div class="info-sidebar-row">
					<v-input
						id="modules-help-info-sidebar-input"
						type="search"
						:placeholder="content('form.search.placeholder')"
						:model="query"
						@input="onInputSearch"
						@keyup.enter.stop.prevent="onSubmitSearch">
					</v-input>
				</div>
				<div class="info-sidebar-row">
					<v-button
						id="modules-help-info-sidebar-button"
						type="button" 
						@click.stop.prevent="onSubmitSearch"
						block>
						{{ content('form.search.submit') }}
					</v-button>
				</div>
			</section>
			<section class="info-sidebar-section" v-if="loaded" v-for="(section, index) in rows">
				<h2 class="font-accent">{{ content(`headlines.${ index }`) }}</h2>
				<nav class="info-sidebar-nav">
					<a class="info-sidebar-nav" href="#" v-for="(row, key) in section" @click.stop.prevent="onClickScroll(`${ index }-${ row.slug }`)">{{ row.question }}</a>
				</nav>
			</section>
		</v-info-sidebar>
		
	</div>
</template>

<script>
	import { forEach, get, set } from 'lodash';
	
	export default {
		name: 'Help',
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
												
				this.$api.getItems('contents_faqs', {
					limit: -1,
					filter: {
						application: {
							eq: 'directus'
						}
					}
				}).then((response) => {
					
					this.items = response.data;
					
					this.render();
					
				}).catch((error) => {
					
					this.error = error;
					
					this.loading = false;
				});
			},
			onClickScroll (input) {
				let $row = this.$el.querySelector(`[data-href="${ input }"]`);
				
				if (!$row) return false;
				
				let props = $row.getBoundingClientRect();
				let top = props.y - 70;
				
				if (top < 0) top = 0;
				
				window.scrollTo({
					top: top,
					behavior: 'smooth'
				});
			},
			onInputSearch (input) {
				this.query = input;
				
				if (input === '') this.render();
			},
			onSubmitSearch (input) {
				this.loading = true;
				
				this.render();
			},
			render () {
				this.rows = {};
				this.count = 0
					
				forEach(this.items, (row) => {
					let valid = true;
					
					if (this.query) {
						let string = Object.values(row).join(' ').toLowerCase();
						
						valid = string.indexOf(this.query) >= 0;
					}					
					
					if (valid) {
						set(this.rows, `${ row.category }.${ row.slug }`, row);
						
						this.count++;
					}
				});
				
				this.loading = false;
				
				return this.rows;
			}
		},
		data () {
			return {
				contents: {
					"en-US": {
						"title": "Help",
						"subtitle": 'Help - Collection of Help, Guide and FAQ Items',
						"description": 'Collection of Help, Guide and FAQ Items',
						"headlines": {
							"guide": "How To Guide",						
							"faqs": "Frequently Asked Questions"							
						},
						"form": {
							"search": {
								"headline": "Search",
								"placeholder": "Search FAQs and Guides",
								"submit": "Search"
							}
						}
					}						
				},
				count: 0,
				items: null,
				loaded: true,
				query: null,
				rows: null
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

<style lang="scss">
	.modules-help {
		padding: var(--page-padding);
		
		.modules-help-content {
		
			.modules-help-ol {
				color: var(--main-primary-color);
				list-style-type: lower-roman;
				padding-left: 1rem;	
				
				.modules-help-li {
					padding: 1rem;	
					margin-bottom: 1rem;
				}
				
				.modules-help-section {
					padding-bottom: 2rem;
			
					.lead {
						font-size: 2rem;
						margin-bottom: 0.5rem;
					}
					
					div.p {
						color: white;
						line-height: 2rem;
					
						a {
							color: var(--main-primary-color);
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
	.modules-help-loading {
		display: flex;
		align-items: center;
		justify-content: center;
		min-height: calc(100vh - 350px);
	}
</style>