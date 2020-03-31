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
		
		<div class="modules-help-content animated fadeIn" v-else-if="loaded && rows">
			
			<section class="modules-section" :data-section="content(`sections.${ index }.headline`)" v-for="(section, index) in rows">
				<header class="modules-divider">
					<h4 class="modules-divider" v-html="content(`sections.${ index }.headline`)"></h4>
					<hr />
					<div class="modules-divider">
						<p class="lead modules-divider" v-html="content(`sections.${ index }.description`)"></p>
					</div>
				</header>
				<div class="modules-cdn-content">
					<ol class="modules-help-ol">
						<li class="modules-help-li" v-for="(row, key) in section">
							<section class="modules-help-section" :data-href="`${ index }-${ row.slug }`">
								<h3 class="lead font-accent">{{ row.question }}</h3>
								<div class="p" v-html="row.answer"></div>
							</section>
						</li>
					</ol>
				</div>
			</section>
			
		</div>	

		<v-info-sidebar wide itemDetail>
			<section class="info-sidebar-section">
				<h2 class="font-accent">{{ content('title') }}</h2>
				<p class="p">{{ content('description') }}</p>
				<p class="lead info-sidebar-count" v-if="rows">{{ count }}</p>
			</section>
			<nav class="info-sidebar-section info-sidebar-nav" v-if="loaded">
				<h2 class="font-accent">{{ content('navigation') }}</h2>
				<a class="info-sidebar-nav" href="#" :data-section="content(`sections.${ index }.headline`)"  v-for="(section, index) in rows" @click.stop.prevent="onClickScroll(content(`sections.${ index }.headline`))">
					<span class="info-sidebar-nav-icon"><v-icon :name="content(`sections.${ index }.icon`)" left></span>
					<span class="info-sidebar-nav-text">{{ content(`sections.${ index }.headline`) }}</span>
				</a>
			</nav>
			<section class="info-sidebar-section" v-if="rows">
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
		</v-info-sidebar>
		
	</div>
</template>

<script>
	import { forEach, get, set, size } from 'lodash';
	
	import $meta from './meta.json';
	
	export default {
		name: 'Help',
		data () {
			return {
				contents: $meta.contents,
				count: 0,
				items: null,
				loaded: false,
				query: null,
				rows: null
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
				let $row = this.$el.querySelector(`.modules-section[data-section="${ input }"]`);
				let $nav = this.$el.querySelector(`a.info-sidebar-nav[data-section="${ input }"]`);
				
				if (this.$nav) this.$nav.classList.remove('active');
				
				if (!$row) return false;
				
				this.$nav = $nav;
				
				this.$nav.classList.add('active');
				
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
			onScrollEnd () {
				this.$sections = this.$sections || this.$el.querySelectorAll('.modules-section');
				let offset = 200;
				
				if (this.$nav) this.$nav.classList.remove('active');
				
				this.$nav = null;
				
				forEach(this.$sections, (section) => {
					let top = section.offsetTop - window.scrollY;
					let bottom = top + section.offsetHeight;
					
					if (top < offset && bottom > offset) this.$section = section;
				});
				
				if (window.scrollY && !this.$section) this.$section = get(this.$sections, size(this.$sections) - 1);
				
				if (this.$section) this.$nav = this.$el.querySelector(`a.info-sidebar-nav[data-section="${ this.$section.getAttribute('data-section') }"]`);
				
				if (this.$nav) this.$nav.classList.add('active');
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
				this.loaded = true;
				
				setTimeout(this.onScrollEnd, 500);
				
				return this.rows;
			}
		},
		metaInfo () {
			return {
				title: this.content('subtitle')
			};
		},
		mounted () {
			this.load();
			
			document.addEventListener('scrollend', this.onScrollEnd);
		},
		beforeDestroy () {
			document.removeEventListener('scrollend', this.onScrollEnd);
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