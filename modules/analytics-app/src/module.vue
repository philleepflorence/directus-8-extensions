<template>
	<div class="modules-analytics">
		
		<v-header 
			:title="content('subtitle')" 
			:breadcrumb="breadcrumb" 
			icon="view_quilt" 
			settings>
		</v-header>
		
		<!-- Sessions -->
		
		<div class="modules-analytics-section animated fadeIn a-delay">
		
			<v-details :title="content('charts.titles.sessions')" type="break" open>
			
				<div class="modules-analytics-content modules-analytics-sessions animated fadeIn" v-if="loaded">
					
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
					
					<div class="modules-analytics-grid modules-analytics-chart animated fadeIn a-delay" data-chart="sessions" v-if="chart('sessions', 'Sessions')">
						<doughnut-chart v-bind:chartdata="charts.sessions" v-bind:options="charts.options"></doughnut-chart>
						<footer class="modules-analytics-chart">
							<p class="modules-analytics-chart">
								<span class="modules-analytics-chart-legend font-accent" v-for="legend in legends.sessions" v-bind:style="`color: ${ legend.color };`">{{ legend.legend }}</span>
							</p>
							<p class="modules-analytics-chart">{{ content('charts.headlines.sessions') }}</p>
						</footer>
					</div>
					
				</div>
			
			</v-details>
		
		</div>	
		
		<!-- Locations -->
		
		<div class="modules-analytics-section animated fadeIn a-delay">
		
			<v-details :title="content('charts.titles.locations')" type="break" open>
			
				<!-- Locations - Charts -->
				
				<div class="modules-analytics-content modules-analytics-locations animated fadeIn" v-if="loaded">
					
					<div class="modules-analytics-grid modules-analytics-chart animated fadeIn a-delay" data-chart="locations" v-for="(row, key) in analytics.data.locations" v-if="chart(`locations.${ key }`)">
						<pie-chart v-bind:chartdata="charts.locations[key]" v-bind:options="charts.options"></pie-chart>
						<footer class="modules-analytics-chart">
							<p class="modules-analytics-chart">
								<span class="modules-analytics-chart-legend font-accent" v-for="legend in legends.locations[key]" v-bind:style="`color: ${ legend.color };`">{{ legend.legend }}</span>
							</p>
							<p class="modules-analytics-chart">{{ content(`sections.locations.${ key }.description`) }}</p>
						</footer>
					</div>
					
				</div>
			
			</v-details>
		
		</div>	
		
		<!-- Browsers -->
		
		<div class="modules-analytics-section animated fadeIn a-delay">
		
			<v-details :title="content('charts.titles.browsers')" type="break" open>
			
				<!-- Browsers - Charts -->
				
				<div class="modules-analytics-content modules-analytics-browsers animated fadeIn" v-if="loaded">
					
					<div class="modules-analytics-grid modules-analytics-chart animated fadeIn a-delay" data-chart="browsers" v-for="(row, key) in analytics.data.browsers" v-if="chart(`browsers.${ key }`)">
						<doughnut-chart v-bind:chartdata="charts.browsers[key]" v-bind:options="charts.options"></doughnut-chart>
						<footer class="modules-analytics-chart">
							<p class="modules-analytics-chart">
								<span class="modules-analytics-chart-legend font-accent" v-for="legend in legends.browsers[key]" v-bind:style="`color: ${ legend.color };`">{{ legend.legend }}</span>
							</p>
							<p class="modules-analytics-chart">{{ content(`sections.browsers.${ key }.description`) }}</p>
						</footer>
					</div>
					
				</div>
			
			</v-details>
		
		</div>

		<v-info-sidebar wide>
			<h2 class="type-note">{{ this.content('title') }}</h2>
			<span class="type-note">{{ this.content('description') }}</span>
		</v-info-sidebar>
		
	</div>
</template>

<script>
	import { forEach, get, set, shuffle, size, startCase } from 'lodash';
	import DoughnutChart from './charts/doughnut.vue';
	import PieChart from './charts/pie.vue';
	
	export default {
		name: 'ModulesAnalyticsApplication',
		components: {
			'doughnut-chart': DoughnutChart,
			'pie-chart': PieChart
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
			chart (input, label) {
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
					
					data.labels.push(title);
					data.datasets[0].data.push(row.ratio);
					
					set(this.legends, `${ input }.${ key }`, {
						legend: title,
						color: this.charts.colors[keys.indexOf(key)]
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
					
					this.loading = false;
					
					this.analytics = response;
					
					this.loaded = true;
					
				}).catch((error) => {
					
					this.error = error;
					
					this.loading = false;
				});
			}
		},
		data () {
			return {
				analytics: {},
				charts: {
					options: {
						responsive: true,
						maintainAspectRatio: true,
						legend: {
							display: false
						}
					},
					colors: [
						"#f44336",
						"#ff9800",
						"#ff5722",
						"#795548",
						"#ffeb3b",
						"#4caf50",
						"#2196f3",
						"#3f51b5"
					]
				},
				contents: {
					"en-US": {
						"title": "Analytics - Application",
						"subtitle": 'Analytics - Report of the Application Visitors',
						"description": 'Analytics Report of the Application Visitors',
						"charts": {
							"titles": {
								"sessions": "Visitors and Sessions",
								"locations": "Locations",
								"browsers": "Browsers and Devices"
							},
							"headlines": {
								"sessions": "Visitors via Website vs Downloaded App"
							}
						},
						"sections": {
							"sessions": {
								"sessions": {
									"headline": "Sessions",
									"description": "Number of times the application was loaded",
									"property": "meta.total"
								},
								"visitors": {
									"headline": "Unique Visitors",
									"description": "Number of unique visitors to the App",
									"property": "meta.visitors"
								},
								"website": {
									"headline": "Websites",
									"description": "Number of times the application was loaded in a Browser",
									"property": "data.sessions.website.total"
								},
								"pwa": {
									"headline": "PWAs",
									"description": "Number of times the application was loaded as an App",
									"property": "data.sessions.pwa.total"
								}
							},
							"locations": {
								"time_zone": {
									"headline": "Time Zones",
									"description": "The time zones of all the visitors to the app"
								},
								"country": {
									"headline": "Countries",
									"description": "The countries of all the visitors to the app"
								},
								"region": {
									"headline": "Regions",
									"description": "The regions, states, or provinces of all the visitors to the app"
								}
							},
							"browsers": {
								"name": {
									"headline": "Browsers",
									"description": "The browsers with which visitors view the website or app"
								},
								"engine_name": {
									"headline": "Browser Engines",
									"description": "The browser engines with which visitors view the website or app"
								},
								"device_vendor": {
									"headline": "Device Manufacturers",
									"description": "The manufacturers of the devices with which visitors view the website or app"
								},
								"device_model": {
									"headline": "Device",
									"description": "The devices with which visitors view the website or app"
								},
								"device_type": {
									"headline": "Device Type",
									"description": "The type of devices with which visitors view the website or app"
								},
								"operating_system_name": {
									"headline": "Operating System",
									"description": "The operating systems of the devices with which visitors view the website or app"
								}
							}
						}
					}						
				},
				endDate: null,
				startDate: null,
				legends: {},
				loaded: false,
				loading: false
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
	.modules-analytics {
		padding: var(--page-padding);
		
		.modules-analytics-content {
			display: grid;
			grid-template-columns: repeat(4, 1fr);
			grid-gap: 1rem;
			
			&.modules-analytics-browsers,
			&.modules-analytics-locations {
				grid-template-columns: repeat(3, 1fr);
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
				
				&.modules-analytics-chart {
					padding-bottom: 6rem !important;
					
					footer.modules-analytics-chart {
						position: absolute;
						bottom: 0;
						width: 100%;
						padding: 0.75rem;
						text-align: center;
						color: var(--main-primary-color);
						
						span.modules-analytics-chart-legend {
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
						font-size: 2rem;
						margin: 0.5rem auto;
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
</style>