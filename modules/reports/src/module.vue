<template>
	<div class="modules-reports">
		
		<v-header 
			:title="content('title')" 
			:breadcrumb="breadcrumb" 
			icon="bar_chart" 
			settings>
		</v-header>
						
		<div class="modules-reports-loading" v-if="loading">
			<div class="flex-item">
				<v-spinner
					v-show="loading"
					line-fg-color="var(--blue-grey-300)"
					line-bg-color="var(--blue-grey-200)"
					class="spinner">
				</v-spinner>
			</div>
		</div>
		
		<div class="modules-reports-loading" v-else-if="loaded && !report">
			<div class="flex-item w-100 animated fadeIn">
				<p class="lead" v-html="content('introduction')"></p>
			</div>
		</div>
		
		<div class="modules-reports-loaded animated fadeIn" v-else-if="report">
			
			<header class="modules-divider">
				<h2 class="modules-divider">{{ report.name }}</h2>
				<hr />
				<p class="modules-divider">{{ report.description }}</p>
			</header>
			
			<!-- Reports - Analytics -->
			
			<section class="modules-section">
				<header class="modules-divider">
					<h4 class="modules-divider">{{ content('sections.reports.headline') }}</h4>
					<hr />
					<div class="modules-divider">
						<p class="lead modules-divider" v-if="!reports">{{ content('sections.reports.empty') }}</p>
					</div>
				</header>
				<div class="modules-reports-content animated fadeIn" v-if="reports">					
					
					<div v-for="(row, index) in reports"  class="modules-reports-grid animated fadeIn a-delay" :style="`grid-column: ${ row.grid } !important;`" :data-chart="row.type">
						
						<!-- Reports: Totals and Snapshots -->
						
						<div class="flex-item" v-if="row.chart == 'overview'">
							<h3 class="font-accent text-capitalize">{{ row.title }}</h3>
							<p class="lead">{{ row.description }}</p>
							<div class="modules-reports-analytics">
								<p class="lead animated fadeIn font-accent">{{ row.total }}</p>
							</div>
						</div>
						
						<!-- Charts -->
						
						<div class="modules-reports-chart" :data-chart-type="row.chart.type" v-else-if="row.type == 'chart'">
						
							<!-- Doughnut Chart -->
							
							<app-doughnut-chart :chartdata="row.chart.data" :options="charts.options" v-if="row.chart.type == 'doughnut'"></app-doughnut-chart>
							
							<!-- Bar Chart -->
							
							<app-bar-chart :chartdata="row.chart.data" :options="charts.options" v-if="row.chart.type == 'bar'"></app-bar-chart>
							
							<!-- Pie Chart -->
							
							<app-pie-chart :chartdata="row.chart.data" :options="charts.options" v-if="row.chart.type == 'pie'"></app-pie-chart>
							
							<aside class="modules-reports-overlay"><span></span></aside>
							<footer class="modules-reports-chart" v-if="row.legend">
								<p class="modules-reports-chart">
									<span class="modules-reports-chart-legend font-accent" v-for="legend in row.legend" :data-label="legend.label" :data-legend-key="legend.key" :data-tooltip="`${ legend.legend }: ${ legend.total }`" :style="`color: ${ legend.color };`">{{ legend.legend }}</span>
								</p>
								<p class="modules-analytics-chart">{{ row.description }}</p>
							</footer>
						
						</div>
					</div>
					
				</div>
			</section>
			
			<!-- Rows - Table Display -->
			
			<section class="modules-section">
				<header class="modules-divider">
					<h4 class="modules-divider">{{ content('sections.rows.headline') }}</h4>
					<hr />
					<div class="modules-divider">
						<p class="lead modules-divider" v-if="!report.rows">{{ content('sections.rows.empty') }}</p>
						<p class="lead modules-divider" v-else>{{ content('sections.rows.description') }}</p>
					</div>
				</header>
				<div class="modules-section-content">
					<app-table :headers="headers" :rows="report.rows"></app-table>					
				</div>
			</section>
			
		</div>		

		<v-info-sidebar wide itemDetail>
			<section class="info-sidebar-section">
				<h2 class="font-accent">{{ content('title') }}</h2>
				<p class="p">{{ content('description') }}</p>
			</section>
			<section class="info-sidebar-section" v-if="filters.date">
				<h2 class="font-accent" v-html="filters.date.headline"></h2>
				<div class="info-sidebar-row">
					<v-input
						id="modules-reports-start-date-input"
						type="date"
						:placeholder="content('form.startDate.placeholder')"
						:model="startDate"
						:value="startDate"
						@input="onInputStartDate">
					</v-input>
				</div>
				<div class="info-sidebar-row">
					<v-input
						id="modules-reports-end-date-input"
						type="date"
						:placeholder="content('form.endDate.placeholder')"
						:model="endDate"
						:value="endDate"
						@input="onInputEndDate">
					</v-input>
				</div>
				<div class="info-sidebar-row">
					<v-button
						id="modules-reports-button"
						type="button"
						@click="onClickFilter"
						block>{{ content('form.submit.label') }}
					</v-button>
				</div>
			</section>	
			<nav class="info-sidebar-section info-sidebar-nav" v-if="menu">
				<a class="info-sidebar-nav" href="#" v-for="row in menu" :ref="row.slug" @click.stop.prevent="onClickReport(row.slug)">{{ row.name }}</a>
			</nav>		
		</v-info-sidebar>
		
	</div>
</template>

<script>
	import { filter, forEach, get, kebabCase, set, shuffle, size, startCase } from 'lodash';
	import AppTable from './components/table.vue';
	import BarChart from './components/bar.vue';
	import DoughnutChart from './components/doughnut.vue';
	import PieChart from './components/pie.vue';
	
	export default {
		name: 'ModulesAnalyticsApplication',
		components: {
			'app-table': AppTable,
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
				contents: {
					"en-US": {
						"title": "Reports",
						"subtitle": 'Reports - Analytics Report of the Application Data',
						"description": 'Analysis and Reports of the various Application Data and modules',
						"introduction": "Please select from one of the available reports listed! <br>Be sure the report has been confirgured by your webmaster.",
						"form": {
							"empty": {
								"message": "Oooops! <br>No reports configuration is available. <br>Please try creating reports configuration for the collections you wish to view."
							},
							"headline": "Start and End Dates",
							"startDate": {
								"placeholder": "Reports Start Date"
							},
							"endDate": {
								"placeholder": "Reports End Date"
							},
							"submit": {
								"label": "Update Reports"
							}
						},
						"sections": {
							"reports": {
								"headline": "Reports",
								"description": "Analysis, Charts, and Reports",
								"empty": "No reports configuration found!"
							},
							"rows": {
								"headline": "Rows",
								"description": "Click or tap on each row for more details.",
								"empty": "No reports data found!"
							}
						}
					}						
				},
				filters: {},
				endDate: null,
				startDate: null,
				legends: {},
				loaded: false,
				loading: false,
				menu: null,
				report: null,
				reports: null
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
			flexBasis () {
				let len = size(this.headers);
				
				return Math.round(100 / len);
			},
			headers () {
				let fields = get(this.report, 'options.render.fields');
				let filtered = filter(fields, (row) => {
					return !row.details;
				});
				
				return filtered;
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
			loadMenu () {
				this.loading = true;
				let params = {
					status: 'published',
					filter: {
						type: {
							eq: 'reports'
						}
					}
				};
											
				this.$api.getItems('app_collections_configuration', params).then((response) => {
					
					let menu = {};
					
					forEach(response.data, (row) => {
						set(menu, row.slug, row);
					});
					
					this.menu = menu;				
					
					this.loaded = true;
					
					this.loading = false;
					
				}).catch((error) => {
					
					this.error = error;
					
					this.loading = false;
				});
			},
			loadReport () {
				this.loading = false;
				this.report = null;
				
				let params = {
					process: true,
					filter: true,
					collection: this.$menu,
					mode: 'reports'
				};
				
				let date = get(this.filters, 'date.field');	
				
				if (this.startDate && this.endDate && date) set(params, `filters.${ this.$menu }.${ date }.between`, `${ this.startDate },${ this.endDate }`);
				
				let row = get(this.menu, this.$menu);
				
				if (this.$active) this.$active.classList.remove('active');
				
				this.$active = get(this.$refs, `${ this.$menu }.0`);
				
				if (this.$active) this.$active.classList.add('active');
				
				this.$api.api.get('/custom/collections/compile', params).then((response) => {
					
					let details = get(response, `data.${ this.$menu }`);
					
					row.rows = details.data;
					
					this.filters = get(row, 'options.render.filters');
					
					let charts = get(row, 'options.render.charts');
					
					if (charts) this.process(charts, row.rows);
										
					this.report = row;
																				
					this.loading = false;
					
				}).catch((error) => {
					
					this.error = error;
					
					this.loading = false;
				});	
			},
			onClickFilter () {				
				this.loadReport();		
			},
			onClickReport (input) {
				this.filters = {};
				
				this.$menu = input;
				
				this.loadReport();		
			},
			onInputStartDate (input) {
				this.startDate = input;
			},
			onInputEndDate (input) {
				this.endDate = input;
			},
			process (rows, items) {
				let charts = [];
				let colors = this.charts.colors.slice();
				let total = size(items);
				
				if (size(colors) < total) {
					let len = Math.ceil(total / size(colors));
					
					while(len > 0) {
						colors = colors.concat(colors);
						
						len--;
					}
				}
								
				const methods = {
					chart (row, index) {
						let counts = {};
						
						/* Set the total aggregate for each chart item */
						
						forEach(items, (item) => {
							let count = get(item, row.field);
							
							if (!counts[count]) counts[count] = 1;
							else counts[count]++;
						});
						
						
						/* Build the legend for each chart item */
						
						forEach(counts, (count, legend) => {							
							set(row, `legend.${ kebabCase(legend) }`, {
								legend: legend,
								key: `[${ index }].legend.${ kebabCase(legend) }`,
								color: colors[Object.keys(counts).indexOf(legend)],
								total: count,
								ratio: Number( ((count / total) * 100).toFixed(2) ),
								label: row.title
							});
						});
						
						set(row, 'chart', {
							type: row.chart,
							data: {
								labels: Object.keys(counts),
								datasets: [{
									label: row.title,
									backgroundColor: colors.slice(0, total),
									borderWidth: 0,
									data: Object.values(counts)									
								}]
							}
						});
						
						charts.push(row);
					},
					count (row) {
						let counts = [];
						
						forEach(items, (item) => {
							let count = get(item, row.field);
							
							if (!counts.includes(count)) counts.push(count);
						});
						
						row.total = size(counts);
						
						charts.push(row);
					},
					total (row) {
						row.total = total;
						
						charts.push(row);
					}
				};
				
				forEach(rows, (row, index) => {
					let method = get(methods, row.type);
										
					if (typeof method === "function") method({
						title: row.title,
						description: row.description,
						field: row.field,
						type: row.type,
						chart: row.chart,
						grid: row.grid
					}, index);
				});	
								
				this.reports = charts;	
				
				return charts;		
			},
			tooltip (input) {
				let body = get(input.body, '0.lines.0');
				let title = get(input.title, '0');
				
				if (this.$overlay) this.$overlay.classList.remove('active');
				
				if (!body) return;
				
				let $legend = this.$el.querySelector(`[data-tooltip="${ body }"]`);
				let $parent = $legend.closest('.modules-reports-grid');
				let $overlay = $parent.querySelector('.modules-reports-overlay');
				
				let key = $legend.getAttribute('data-legend-key');
				let legend = get(this.reports, key);
				
				if (!legend) return; 
				
				$overlay.innerHTML = `<span style="color: ${ legend.color };">${ legend.label.toUpperCase() }<br>${ legend.legend }: ${ legend.total } &mdash; ${ legend.ratio }</span>`;
				
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
			this.loadMenu();
		}
	}
</script>

<style lang="scss">
	.modules-reports {
		padding: var(--page-padding);
		position: relative;
		
		.modules-reports-section {
			position: relative;
		}
		
		.modules-reports-content {
			display: grid;
			grid-template-columns: repeat(6, 1fr);
			grid-gap: 1rem;
			
			& + .modules-reports-content {
				padding-top: 1rem;
			}
			
			.modules-reports-grid {
				background-color: rgba(white, 0.1);
				cursor: pointer;
				display: flex;
				align-items: center;
				justify-content: center;
				position: relative;
				overflow: hidden;
				animation-duration: 600ms;
				padding: 1.5rem 1rem;
								
				div.modules-reports-chart {
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
						
						footer.modules-reports-chart {
							span.modules-reports-chart-legend {
								display: none !important;
							}
						}
					}
					
					aside.modules-reports-overlay {
						position: absolute;
						top: 0;
						left: 0;
						bottom: 0;
						right: 0;
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
					
					footer.modules-reports-chart {
						position: absolute;
						bottom: 0;
						left: 0;
						width: 100%;
						padding: 0.75rem;
						text-align: center;
						color: var(--blue-grey-500);
						font-size: 0.9rem;
						font-weight: 500;
						
						span.modules-reports-chart-legend {
							color: var(--main-primary-color);
							display: inline-block;
							font-weight: 500;
							padding: 0.5rem 1rem;
							text-transform: capitalize;
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
					
					.modules-reports-analytics {
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
	.modules-reports-loading {
		display: flex;
		align-items: center;
		justify-content: center;
		min-height: calc(100vh - 350px);
		
		.w-100 {
			flex-grow: 1;
		}
	}
</style>