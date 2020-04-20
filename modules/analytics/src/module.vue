<template>
	<div class="modules-analytics">
		
		<v-header 
			:title="content('title')" 
			:breadcrumb="breadcrumb" 
			:icon="icon" 
			settings>
		</v-header>
						
		<div class="modules-analytics-loading" v-if="loading">
			<div class="flex-item">
				<v-spinner
					v-show="loading"
					line-fg-color="var(--blue-grey-300)"
					line-bg-color="var(--blue-grey-200)"
					class="spinner">
				</v-spinner>
			</div>
		</div>
		
		<div class="modules-analytics-loading" v-else-if="loaded && !analytics.meta.total">
			<div class="flex-item w-100 animated fadeInUpSmall">
				<p class="lead" v-html="content('form.empty.message')"></p>
			</div>
		</div>
		
		<div class="modules-analytics-loaded" v-else-if="loaded">
			
			<!-- Sessions -->
			
			<div class="modules-analytics-section animated fadeIn a-delay" :data-section="kebabCase(content('charts.titles.sessions.text'))">
			
				<section class="modules-section">
				
					<header class="modules-divider">
						<h4 class="modules-divider">{{ content('charts.titles.sessions.text') }}</h4>
						<hr />
						<div class="modules-divider">
							<p class="lead modules-divider">{{ content('charts.titles.sessions.description') }}</p>
						</div>
					</header>
					
					<div class="modules-analytics-content modules-analytics-sessions animated fadeIn">
						
						<!-- Sessions - Overview -->
						
						<div class="modules-analytics-grid animated fadeIn a-delay" v-for="row in content('sections.sessions')">
							<div class="flex-item">
								<h3 class="font-accent">{{ row.headline }}</h3>
								<p class="lead">{{ row.description }}</p>
								<div class="modules-analytics-analytics">
									<p class="lead animated fadeIn font-accent">{{ details(row.property) }}</p>
								</div>
							</div>
						</div>
						
						<!-- Sessions - Charts -->
						
						<div class="modules-analytics-grid animated fadeIn a-delay" data-chart="sessions" v-if="chart('sessions', 'Sessions')">
							<div class="modules-analytics-chart">
								<app-doughnut-chart :chartdata="charts.sessions" :options="charts.options"></app-doughnut-chart>
								<aside class="modules-analytics-overlay"><span></span></aside>
								<footer class="modules-analytics-chart">
									<p class="modules-analytics-chart modules-analytics-chart-legend">
										<span class="modules-analytics-chart-legend font-accent" v-for="legend in legends.sessions" :data-label="legend.label" :data-legend-key="legend.key" :data-tooltip="`${ legend.legend }: ${ legend.ratio }`" :style="`color: ${ legend.color };`">{{ legend.legend }}</span>
									</p>
									<p class="modules-analytics-chart">{{ content('charts.headlines.sessions') }}</p>
								</footer>
							</div>
						</div>
						
					</div>
				
				</section>
			
			</div>	
			
			<!-- Performance and Views -->
			
			<div class="modules-analytics-section animated fadeIn a-delay" :data-section="kebabCase(content('charts.titles.performance.text'))">
			
				<section class="modules-section">
				
					<header class="modules-divider">
						<h4 class="modules-divider">{{ content('charts.titles.performance.text') }}</h4>
						<hr />
						<div class="modules-divider">
							<p class="lead modules-divider">{{ content('charts.titles.performance.description') }}</p>
						</div>
					</header>
						
					<!-- Performance - Overview -->
				
					<div class="modules-analytics-content modules-analytics-performance animated fadeIn" v-for="(section, index) in content('sections.performance')">
						
						<div class="modules-analytics-grid animated fadeIn a-delay" v-for="(description, name) in section">
							<div class="flex-item">
								<h3 class="font-accent text-capitalize">{{ index }} &mdash; {{ name }}</h3>
								<p class="lead">{{ description }}</p>
								<div class="modules-analytics-analytics">
									<p class="lead animated fadeIn font-accent">{{ seconds( details(`data.performance.${ index }.${ name }`) ) }}</p>
								</div>
							</div>
						</div>
						
					</div>
					
					<!-- Views - Charts -->
					
					<div class="modules-analytics-content modules-analytics-views animated fadeIn">
						
						<div class="modules-analytics-grid animated fadeIn a-delay" data-chart-type="bar" data-chart-type="bar" v-for="(row, key) in analytics.data.views" v-if="chart(`views.${ key }`, null, true)">
							<div class="modules-analytics-chart">
								<app-bar-chart :chartdata="charts.views[key]" :options="charts.options"></app-bar-chart>
								<aside class="modules-analytics-overlay"><span></span></aside>
								<footer class="modules-analytics-chart">
									<p class="modules-analytics-chart modules-analytics-chart-legend">
										<span class="modules-analytics-chart-legend font-accent" v-for="legend in legends.views[key]" :data-label="legend.label" :data-legend-key="legend.key" :data-tooltip="`${ legend.label }: ${ legend.total }`" :style="`color: ${ legend.color };`">{{ legend.legend }}</span>
									</p>
									<p class="modules-analytics-chart">{{ content(`sections.views.${ key }.description`) }}</p>
								</footer>
							</div>
						</div>
						
					</div>
				
				</section>
			
			</div>	
			
			<!-- Locations -->
			
			<div class="modules-analytics-section animated fadeIn a-delay" :data-section="kebabCase(content('charts.titles.locations.text'))">
			
				<section class="modules-section">
				
					<header class="modules-divider">
						<h4 class="modules-divider">{{ content('charts.titles.locations.text') }}</h4>
						<hr />
						<div class="modules-divider">
							<p class="lead modules-divider">{{ content('charts.titles.locations.description') }}</p>
						</div>
					</header>
				
					<!-- Locations - Charts -->
					
					<div class="modules-analytics-content modules-analytics-locations animated fadeIn">
						
						<div class="modules-analytics-grid modules-analytics-chart animated fadeIn a-delay" data-chart="locations" v-for="(row, key) in analytics.data.locations" v-if="chart(`locations.${ key }`)">
							<div class="modules-analytics-chart">
								<app-pie-chart :chartdata="charts.locations[key]" :options="charts.options"></app-pie-chart>
								<aside class="modules-analytics-overlay"><span></span></aside>
								<footer class="modules-analytics-chart">
									<p class="modules-analytics-chart modules-analytics-chart-legend">
										<span class="modules-analytics-chart-legend font-accent" v-for="legend in legends.locations[key]" :data-label="legend.label" :data-legend-key="legend.key" :data-tooltip="`${ legend.legend }: ${ legend.ratio }`" :style="`color: ${ legend.color };`">{{ legend.legend }}</span>
									</p>
									<p class="modules-analytics-chart">{{ content(`sections.locations.${ key }.description`) }}</p>
								</footer>
							</div>
						</div>
						
					</div>
				
				</section>
			
			</div>	
			
			<!-- Browsers -->
			
			<div class="modules-analytics-section animated fadeIn a-delay" :data-section="kebabCase(content('charts.titles.browsers.text'))">
			
				<section class="modules-section">
				
					<header class="modules-divider">
						<h4 class="modules-divider">{{ content('charts.titles.browsers.text') }}</h4>
						<hr />
						<div class="modules-divider">
							<p class="lead modules-divider">{{ content('charts.titles.browsers.description') }}</p>
						</div>
					</header>
				
					<!-- Browsers - Charts -->
					
					<div class="modules-analytics-content modules-analytics-browsers animated fadeIn">
						
						<div class="modules-analytics-grid modules-analytics-chart animated fadeIn a-delay" data-chart="browsers" :data-chart-type="bar(row) ? 'bar' : 'pie'" v-for="(row, key) in analytics.data.browsers" v-if="chart(`browsers.${ key }`, null, bar(row))">
							<div class="modules-analytics-chart">
								<app-bar-chart :chartdata="charts.browsers[key]" :options="charts.options" v-if="bar(row)"></app-bar-chart>
								<app-doughnut-chart :chartdata="charts.browsers[key]" :options="charts.options" v-else></app-doughnut-chart>
								<aside class="modules-analytics-overlay"><span></span></aside>
								<footer class="modules-analytics-chart">
									<p class="modules-analytics-chart modules-analytics-chart-legend">
										<span class="modules-analytics-chart-legend font-accent" v-for="legend in legends.browsers[key]" :data-label="legend.label" :data-legend-key="legend.key" :data-tooltip="`${ bar(row) ? legend.label : legend.legend }: ${ bar(row) ? legend.total : legend.ratio }`" :style="`color: ${ legend.color };`">{{ legend.legend }}</span>
									</p>
									<p class="modules-analytics-chart">{{ content(`sections.browsers.${ key }.description`) }}</p>
								</footer>
							</div>
						</div>
						
					</div>
				
				</section>
			
			</div>
			
		</div>		

		<v-info-sidebar wide itemDetail>
			<section class="info-sidebar-section">
				<h2 class="font-accent">{{ content('title') }}</h2>
				<p class="p">{{ content('description') }}</p>
			</section>
			<nav class="info-sidebar-section info-sidebar-nav" v-if="loaded && analytics.meta.total">
				<h2 class="font-accent">{{ content('navigation.scroll') }}</h2>
				<a class="info-sidebar-nav" href="#" v-for="(row, key) in content('charts.titles')" :data-section="kebabCase(row.text)" @click.stop.prevent="onClickScroll(kebabCase(row.text))">
					<span class="info-sidebar-nav-icon"><v-icon :name="row.icon" left></span>
					<span class="info-sidebar-nav-text">{{ row.text }}</span>
				</a>
			</nav>	
			<section class="info-sidebar-section">
				<h2 class="font-accent">{{ content('form.headline') }}</h2>
				<div class="info-sidebar-row">
					<v-input
						id="modules-analytics-start-date-input"
						type="date"
						:placeholder="content('form.startDate.placeholder')"
						:model="startDate"
						:value="startDate"
						@input="onInputStartDate">
					</v-input>
				</div>
				<div class="info-sidebar-row">
					<v-input
						id="modules-analytics-end-date-input"
						type="date"
						:placeholder="content('form.endDate.placeholder')"
						:model="endDate"
						:value="endDate"
						@input="onInputEndDate">
					</v-input>
				</div>
				<div class="info-sidebar-row">
					<v-button
						id="modules-analytics-button"
						type="button"
						@click="onSubmit"
						block>{{ content('form.submit.label') }}
					</v-button>
				</div>
			</section>	
			<nav class="info-sidebar-section info-sidebar-nav">
				<h2 class="font-accent">{{ content('navigation.headline') }}</h2>
				<a class="info-sidebar-nav" :href="row.path" v-for="row in content('navigation.rows')">
					<span class="info-sidebar-nav-icon"><v-icon :name="row.icon" left></span>
					<span class="info-sidebar-nav-text">{{ row.text }}</span>
				</a>
			</nav>	
		</v-info-sidebar>
		
	</div>
</template>

<script>
	import { forEach, get, kebabCase, set, shuffle, size, startCase } from 'lodash';
	import BarChart from './components/charts/bar.vue';
	import DoughnutChart from './components/charts/doughnut.vue';
	import PieChart from './components/charts/pie.vue';
	
	import $meta from './meta.json';
	
	export default {
		name: 'ModulesAnalytics',
		components: {
			'app-bar-chart': BarChart,
			'app-doughnut-chart': DoughnutChart,
			'app-pie-chart': PieChart
		},
		data () {
			return {
				analytics: {},
				charts: {
					options: {
						responsive: true,
						maintainAspectRatio: false,
						legend: {
							display: false
						},
						tooltips: {
							enabled: false,
							custom: this.tooltip
						}
					},
					colors: [
						"#f44336",
						"#ff9800",
						"#ff5722",
						"#ffeb3b",
						"#4caf50",
						"#2196f3",
						"#3f51b5"
					]
				},
				contents: $meta.contents,
				icon: $meta.icon,
				endDate: null,
				startDate: null,
				legends: {},
				loaded: false,
				loading: false
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
			bar (input) {
				return size(input) > 4;
			},
			chart (input, label, type) {
				let $input = get(this.analytics, `data.${ input }`);
							
				if (!$input) return null;
				
				label = label || startCase(input);
				
				let colors = this.charts.colors.slice();
				
				if (size(colors) < size($input)) {
					let len = Math.ceil(size($input) / size(colors));
					
					while(len > 0) {
						colors = colors.concat(colors);
						
						len--;
					}
				}
				
				let data = {
					labels: [],
					datasets: [{
						label: label,
						backgroundColor: colors.slice(0, size($input)),
						borderWidth: 0,
						data: []
					}]
				};
				
				let keys = Object.keys($input);
				
				forEach($input, (row, key) => {
					let title = row.title || key.toUpperCase();
					let total = type === true ? row.total : row.ratio;
					
					data.labels.push(title);
					data.datasets[0].data.push(total);
					
					set(this.legends, `${ input }.${ key }`, {
						key: `${ input }.${ key }`,
						legend: title,
						color: this.charts.colors[keys.indexOf(key)],
						total: row.total,
						ratio: row.ratio,
						label: label
					});
				});
				
				set(this.charts, input, data);
								
				return data;
			},
			content (input) {
				let translation = get(this.contents, this.locale);
					translation = translation || get(this.contents, 'en-US');

				return get(translation, input);
			},
			details (input) {
				return get(this.analytics, input);
			},
			kebabCase (input) {
				return kebabCase(input);
			},
			load () {
				this.loading = true;
				let params = {};	
				
				if (this.startDate && this.endDate) {
					params = {
						start_date: this.startDate,
						end_date: this.endDate
					};
				}
											
				this.$api.api.get('/custom/analytics/application', params).then((response) => {
					
					let startDate = get(response, 'meta.start_date');
					let endDate = get(response, 'meta.end_date');
					
					this.startDate = startDate.split(' ')[0];
					this.endDate = endDate.split(' ')[0];
					
					this.loading = false;
					
					this.analytics = response;
					
					this.loaded = true;
					
					setTimeout(this.onScrollEnd, 500);
					
				}).catch((error) => {
					
					this.error = error;
					
					this.loading = false;
				});
			},
			onClickScroll (input) {
				let $row = this.$el.querySelector(`.modules-analytics-section[data-section="${ input }"]`);
				let $nav = this.$el.querySelector(`a.info-sidebar-nav[data-section="${ input }"]`);
				
				if (this.$nav) this.$nav.classList.remove('active');
				
				if (!$row) return false;
				
				this.$nav = $nav;
				
				this.$nav.classList.add('active');
								
				let props = $row.getBoundingClientRect();
				let top = window.scrollY + (props.y - 100);
				
				if (top < 0) top = 0;
				
				window.scrollTo({
					top: top,
					behavior: 'smooth'
				});
			},
			onInputStartDate (input) {
				this.startDate = input;
			},
			onInputEndDate (input) {
				this.endDate = input;
			},
			onScrollEnd () {
				this.$sections = this.$sections || this.$el.querySelectorAll('.modules-analytics-section');
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
			onSubmit (e) {
				this.loaded = false;
				
				if (this.startDate && this.endDate) this.load();
			},
			seconds (input) {
				input = parseInt(input);
				
				if (!input) return '';
								
				input = parseFloat(input / 1000).toFixed(2);
				
				return `${ input }s`;
			},
			tooltip (input) {
				let body = get(input.body, '0.lines.0');
				let title = get(input.title, '0');
				
				if (this.$overlay) this.$overlay.classList.remove('active');
				
				if (!body) return;
				
				let $legend = this.$el.querySelector(`[data-tooltip="${ body }"]`);
				let $parent = $legend.closest('.modules-analytics-grid');
				let $overlay = $parent.querySelector('.modules-analytics-overlay');
				
				let key = $legend.getAttribute('data-legend-key');
				let legend = get(this.legends, key);
				
				$overlay.innerHTML = `<span style="color: ${ legend.color };">${ legend.label }<br>${ legend.legend }: ${ legend.total } &mdash; ${ legend.ratio }</span>`;
				
				$overlay.classList.add('active');
				
				this.$overlay = $overlay;
			}
		},
		metaInfo() {
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
	.modules-analytics {
		padding: var(--page-padding);
		position: relative;
		
		.modules-analytics-section {
			position: relative;
		}
		
		.modules-analytics-form {
			display: grid;
			grid-template-columns: repeat(3, 1fr);
			grid-gap: 1rem;
			
			.modules-analytics-column {
				button {
					height: 52px;
				}
			}
		}
		
		.modules-analytics-content {
			display: grid;
			grid-template-columns: repeat(4, 1fr);
			grid-gap: 1rem;
			
			& + .modules-analytics-content {
				padding-top: 1rem;
			}
			
			&.modules-analytics-browsers {
				grid-template-columns: repeat(2, 1fr);
			}
			
			&.modules-analytics-views {
				grid-template-columns: repeat(2, 1fr);
			}
			
			&.modules-analytics-performance {
				grid-template-columns: repeat(3, 1fr);
			}
			
			&.modules-analytics-locations {
				grid-template-columns: repeat(2, 1fr);
				
				.modules-analytics-grid {
					&:nth-child(3) {
						grid-column: 1/3 !important;
					}
				}
			}
			
			&.modules-analytics-sessions {
				.modules-analytics-grid {
					&:nth-child(1),
					&:nth-child(2),
					&:nth-child(3) {
						grid-column: 1/3 !important;
					}
					
					&:nth-child(4) {
						grid-column: 3/5 !important;
					}
				
					&[data-chart-type="bar"] {
						max-height: 60vh;
						
						&:nth-child(1) {
							grid-column: 1/3 !important;
						}
					}
				}
			}
			
			&.modules-analytics-views {
				.modules-analytics-grid {
					&[data-chart-type="bar"] {
						grid-column: 1/3 !important;
					}
				}
			}
			
			.modules-analytics-grid {
				background-color: rgba(white, 0.1);
				cursor: pointer;
				display: flex;
				align-items: center;
				justify-content: center;
				position: relative;
				overflow: hidden;
				animation-duration: 600ms;
				padding: 1.5rem 1rem;
				
				&[data-chart="sessions"] {
					grid-column: 3/5 !important;
					grid-row: 1/3 !important;
				}
				
				div.modules-analytics-chart {
					position: relative;
					width: 100%;
					padding-bottom: 6rem !important;
					
					&[data-chart-type="bar"] {
						padding-bottom: 3rem !important;
					}
					
					& > div {
						max-height: 100%;
						max-width: 100%;
					}
					
					&[data-chart-type="bar"] {
						& > div {
							min-height: 100%;
							min-width: 100%;
						}
						
						footer.modules-analytics-chart {
							span.modules-analytics-chart-legend {
								display: none !important;
							}
						}
					}
					
					aside.modules-analytics-overlay {
						position: absolute;
						top: -1.5rem;
						left: -1.5rem;
						bottom: -1.5rem;
						right: -1.5rem;
						background-color: rgba(#303030, 0.8);
						display: flex;
						align-items: center;
						justify-content: center;
						text-align: center;
						pointer-events: none;
						opacity: 0;
						transition: opacity 500ms ease;
						
						&.active {
							opacity: 1;
						}
						
						span {
							animation: fadeIn 650ms ease;	
							font-family: var(--main-font-accent);	
							line-height: 2rem;		
							text-transform: capitalize;			
							
							&:after {
								content: '%';
							}
						}
					}
					
					footer.modules-analytics-chart {
						position: absolute;
						bottom: -1rem;
						left: -1rem;
						width: calc(100% + 2rem);
						text-align: center;
						color: var(--blue-grey-500);
						font-size: 0.875rem;
						font-weight: 500;
						
						p.modules-analytics-chart {
							background: rgba(black, 0.2);
							border-top: 1px solid var(--blue-grey-800);
							padding: 0.5rem;
							
							&.modules-analytics-chart-legend {
								overflow-x: auto !important;
								white-space: nowrap;
						
								span.modules-analytics-chart-legend {
									color: var(--main-primary-color);
									display: inline-block;
									font-weight: 500;
									padding: 0.25rem 1rem;
									text-transform: capitalize;
								}
							}
							
							&:last-child {
								background: transparent;
							}
						}
					}
				}
				
				.flex-item {
					flex-grow: 1;
					text-align: center;
					color: var(--main-primary-color);
					padding: 1rem;
					
					h3 {
						font-size: 1.5rem;
						margin: 0 auto 0.3rem auto;
					}
					
					.lead {
						font-size: 1rem;
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
					
					.modules-analytics-analytics {
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
	}
	.modules-analytics-loading {
		display: flex;
		align-items: center;
		justify-content: center;
		min-height: calc(100vh - 350px);
		
		.w-100 {
			flex-grow: 1;
		}
	}
</style>