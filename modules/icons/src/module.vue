<template>
	<div class="modules-icons">
		<v-header 
			:title="contents.subtitle" 
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
			<div class="modules-icons-search">
				<v-input
					id="modules-icons-search-input"
					type="url"
					class="modules-icons-search-input"
					:placeholder="contents.input.placeholder"
					@input="onInput">
				</v-input>
			</div>
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

		<v-info-sidebar wide>
			<h2 class="type-note">{{ this.contents.title}}</h2>
			<span class="type-note">{{ this.contents.description }}</span>
		</v-info-sidebar>
	</div>
</template>

<script>
	import { get, filter, forEach, set, startCase } from 'lodash';
	
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
				
				return icons;
			}
		},
		methods: {
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
				}, 
				300);
			}
		},
		data () {
			return {
				contents: {
					title: "Icons",
					subtitle: 'Icons - View all the Application Icons Available',
					description: 'View all the Application Icons Available',
					input: {
						placeholder: 'Search Icons by Name and Keywords'
					}										
				},
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
				title: this.contents.subtitle
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