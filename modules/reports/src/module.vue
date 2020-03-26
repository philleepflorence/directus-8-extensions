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
						<p class="lead modules-divider" v-if="!report.reports">{{ content('sections.reports.empty') }}</p>
					</div>
				</header>
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
					<div class="table">
						<div class="header">
							<div class="row">
								<div class="field" :data-type="field.type" :style="`width: ${ flexBasis }%;`" v-for="field in headers">{{ field.text }}</div>
							</div>
						</div>
						<div class="body">
							<div class="group" v-for="row in report.rows" @click.stop.prevent="onClickDetails">
								<div class="row">
									<div class="field" :data-type="field.type" :style="`width: ${ flexBasis }%;`" v-for="field in headers">
										<div class="value">{{ value(row, field.value) }}</div>
									</div>
								</div>
								<div class="details animated fadeIn">
									<section class="details-section" v-for="detail in row">
										<h5 class="details-title">{{ detail.key }}</h5>
										<div class="details-content" v-html="detail.value"></div>
									</section>
								</div>
							</div>
						</div>
					</div>
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
	import BarChart from './charts/bar.vue';
	import DoughnutChart from './charts/doughnut.vue';
	import PieChart from './charts/pie.vue';
	
	export default {
		name: 'ModulesAnalyticsApplication',
		components: {
			'bar-chart': BarChart,
			'doughnut-chart': DoughnutChart,
			'pie-chart': PieChart
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
				report: null
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
				
				if (this.startDate && this.endDate && date) set(params, `filters.${ date }.between`, [this.startDate, this.endDate]);
				
				let row = get(this.menu, this.$menu);
				
				if (this.$active) this.$active.classList.remove('active');
				
				this.$active = get(this.$refs, `${ this.$menu }.0`);
				
				if (this.$active) this.$active.classList.add('active');
				
				this.$api.api.get('/custom/collections/compile', params).then((response) => {
					
					let details = get(response, `data.${ this.$menu }`);
					
					row.rows = details.data;
					
					this.filters = get(row, 'options.render.filters');
					
					this.report = row;
																				
					this.loading = false;
					
				}).catch((error) => {
					
					this.error = error;
					
					this.loading = false;
				});	
			},
			onClickDetails (event) {
				if (this.$details) this.$details.classList.remove('active');
				
				let details = event.currentTarget.querySelector('.details');
				
				if (!details) return false;
				
				if (this.$details !== details) {
					details.classList.add('active');
					
					this.$details = details;
				}
				else this.$details = null;				
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
				let $parent = $legend.closest('.modules-reports-grid');
				let $overlay = $parent.querySelector('.modules-reports-overlay');
				
				let key = $legend.getAttribute('data-legend-key');
				let legend = get(this.legends, key);
				
				$overlay.innerHTML = `<span style="color: ${ legend.color };">${ legend.label }<br>${ legend.legend }: ${ legend.total } &mdash; ${ legend.ratio }</span>`;
				
				$overlay.classList.add('active');
				
				this.$overlay = $overlay;
			},
			value (row, value) {
				return get(row, value);
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
	.table {
		background-color: rgba(white, 0.01);
		border: var(--input-border-width) solid var(--table-row-border-color);
		border-radius: var(--border-radius);
		border-spacing: 0;
		border-bottom: none;
		width: 100%;
		
		.header {
			border-bottom: 2px solid var(--table-row-border-color);
			height: calc(var(--input-height) - 2px);
			
			.row {
				background-color: rgba(white, 0.1);
				height: calc(var(--input-height) - 2px);
				
				.field {
					color: var(--blue-grey-500);
					font-family: var(--main-font-accent);
					font-weight: 500;
				}
			}
		}
		.row {
			display: flex;
			align-items: center;
			
			.field {
				padding: 5px 5px;	
				
				.value {
					overflow: hidden;
					white-space: nowrap;
					text-overflow: ellipsis;
				}			
			}
		}				
		.details {
			display: none;
			background-color: rgba(white, 0.1);
			border-bottom: 2px solid var(--table-row-border-color);
			padding: 1.5rem 5px 0.5rem 5px;
			
			&.active {
				display: block;
			}
			
			.details-section {
				padding-bottom: 1.5rem;
				
				.details-title {
					color: var(--blue-grey-400);
					font-family: var(--main-font-accent);
					font-size: 1rem;
					margin-bottom: 0.3rem;
				}
				
				.details-content {
					white-space: pre-wrap;
				}
			}
		}
		.body {
			.row {
				cursor: pointer;
				position: relative;
				height: calc(var(--input-height) - 2px);
				border-bottom: 2px solid var(--table-row-border-color);
				
				&:hover {
					background-color: var(--highlight);
				}
			}
		}
	}
	
	.modules-reports {
		padding: var(--page-padding);
		position: relative;
		
		.modules-reports-section {
			position: relative;
		}
		
		.modules-reports-form {
			display: grid;
			grid-template-columns: repeat(3, 1fr);
			grid-gap: 1rem;
			
			.modules-reports-column {
				button {
					height: 52px;
				}
			}
		}
		
		.modules-reports-content {
			display: grid;
			grid-template-columns: repeat(4, 1fr);
			grid-gap: 1rem;
			
			& + .modules-reports-content {
				padding-top: 1rem;
			}
			
			&.modules-reports-browsers,
			&.modules-reports-views {
				grid-template-columns: repeat(2, 1fr);
			}
			
			&.modules-reports-locations,
			&.modules-reports-performance {
				grid-template-columns: repeat(3, 1fr);
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
				
				&[data-chart="sessions"] {
					grid-column: 3/5 !important;
					grid-row: 1/3 !important;
				}
				
				&[data-chart-type="bar"] {
					max-height: 60vh;
					
					&:nth-child(1) {
						grid-column: 1/3 !important;
					}
				}
				
				&.modules-reports-chart {
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
						width: 100%;
						padding: 0.75rem;
						text-align: center;
						color: var(--blue-grey-500);
						font-size: 12px;
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