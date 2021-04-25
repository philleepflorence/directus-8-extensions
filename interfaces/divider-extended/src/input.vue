<template>
	<div class="interface-divider">
		<div :class="[`${options.style}-style`, { margin: options.margin }]">
			<section class="interface-divider">
				<header class="interface-divider-header">
					<h2 v-if="templates.title">{{ templates.title }}</h2>
					<aside v-if="contents.collection.icon">
						<v-icon :name="contents.collection.icon"></v-icon>
					</aside>
				</header>
				<hr v-if="options.hr">
				<div 
					class="interface-divider-description"
					ref="description">
					<p 
						class="description" 
						v-if="templates.description" 
						v-html="templates.description"></p>
					<p 
						class="description" 
						v-if="templates.descriptions" 
						v-for="description in templates.descriptions" 
						v-html="description"></p>
				</div>
				
				<nav 
					class="interface-divider-menu" 
					v-if="templates.menu"
					ref="menu">
					<a 
						v-for="(button, name) in templates.menu"
						:class="['interface-divider-nav', { active: active === name }]"  
						href="#" 
						@click.stop.prevent="onMenu($event, name)" 
						ref="menu">
						<aside class="icon">
							<v-icon :name="button.icon"></v-icon>
						</aside>
						<span class="text">{{ button.text }}</span>
					</a>
				</nav>
				
				<aside 
					class="interface-divider-steps"
					v-if="templates.steps"
					v-show="active === 'guide'"
					ref="steps">
					<dl
						v-for="(dd, dt) in templates.steps"
						class="interface-divider-step">
						<dt>{{ dt }}</dt>
						<dd>{{ dd }}</dd>
					</dl>
				</aside>
			</section>
			
			<v-modal 
				v-if="modal" 
				:title="templates.title"
				:buttons="config.buttons.modal"
				@close="onCloseModal"
				@done="onCloseModal">
				<div class="interface-divider-modal">
					<div class="interface-divider-content">{{ contents.modal.error }}</div>
				</div>
			</v-modal>
		</div>
	</div>
</template>

<script>
	import mixin from '@directus/extension-toolkit/mixins/interface';
	import {
		debounce,
		get, 
		forEach, 
		set,
		sortBy
	} from 'lodash';
	
	import $meta from './meta.json';
	
	export default {
		name: "InterfaceDivider",
		mixins: [mixin],
		data () {
			return {
				active: null,
				title: null,
				existing: false,
				loading: false,
				modal: false,
				updated: null
			};
		},
		computed: {
			collection () {
				let collection = get(this.$store.state.collections, this.$route.params.collection);
				
				return collection;
			},
			config () {
				return {
					buttons: {
						modal: {
							done: {
								text: this.contents.modal.close
							}
						}
					},
					menu: {
						documentation: {
							icon: "book"
						},
						guide: {
							icon: "playlist_add_check"
						},
						template: {
							icon: "format_align_justify"	
						},
						edit: {
							icon: "edit"
						},
						preview: {
							icon: "remove_red_eye"
						},
						tools: {
							icon: "build"
						}
					}
				};
			},
			contents () {
				const { first_name, last_name, role, locale } = this.user;
				let contents = get($meta.contents, locale);
					contents = {...contents, ...{
						user: {
							first_name: first_name,
							last_name: last_name,
							role: {
								name: role.name,
								description: role.description
							}
						},
						fields: {},
						collection: {
							icon: this.collection.icon,
							collection: this.collection.collection,
							name: this.translation(
								this.collection.translation, 
								'translation', 
								this.collection.collection
							)
						}
					}};
				
				forEach(this.fields, (field) => {
					if (field.interface !== "divider") {
						let name = this.translation(field.translation, "translation", field.name);
						let keys = ['field', 'note', 'type', 'length', 'datatype', 'sort'];
						
						keys.forEach((key) => {
							set(contents.fields, `${ field.field }.${ key }`, field[key]);
						});
						
						set(contents.fields, `${ field.field }.name`, name);
						set(contents.fields, `${ field.field }.value`, get(this.values, field.field));
					}
				});
				
				return contents;
			},
			displayValue () {
				return this.value || '--';
			},
			templates () {
				let templates = {
					descriptions: [],
					menu: {},
					steps: {}
				};
				let keys = ['steps', 'menu', 'descriptions'];
				let mode = this.newItem ? 'create': 'update';
				
				forEach(this.options, (items, key) => {
					if (keys.includes(key)) {
						forEach(items, (item, prop) => {
							if (key === 'descriptions' && item.split(',').includes(mode)) {
								templates.descriptions.push(this.render(prop, this.contents));
							}
							else if (key === 'menu') {
								set(templates, `${ key }.${ prop }`, get(this.config.menu, prop));
								set(templates, `${ key }.${ prop }.text`, this.render(item, this.contents));
							}
							else {
								set(templates, `${ key }.${ prop }`, this.render(item, this.contents));
							}
						});
					}
				});
				
				templates.title = this.render(this.options.title, this.contents);
				templates.description = this.render(this.options.description, this.contents);
					
				return templates;
			},
			user () {
				return this.$store.state.currentUser;
			}
		},
		methods: {
			load (endpoint) {
				/*
					Load from documentation or template...getItem
					TODO: Loading from a URL...
				*/
			},
			onCloseModal () {
				this.modal = false;
			},
			onMenu (e, name) {	
				if (this.active === name) return this.active = null;
					
				this.active = name;
				
				console.log('debug - development: divider => onMenu', this.active, name);	
			},
			render (string, object) {
				return this.$helpers.micromustache.render(string, object);
			},
			style () {
				let menu = this.$refs.menu.getBoundingClientRect();
				
				this.$refs.steps.style.minHeight = `${ window.innerHeight - menu.bottom }px`;
			},
			translation (translations, key, value) {
				if (!Array.isArray(translations)) return value;
				
				let translation = translations.filter(item => item.locale == this.user.locale);
					translation = translation.pop();
					
				if (!key) return translation;
				
				return translation[key] || value;
			}
		},
		mounted () {
			this.$store.state.sidebars.info = false;
			this.style();
			
			try {
				console.log('debug - development: divider => this.templates.steps', this.templates.steps);
			} catch (e) {
				console.log('debug - development: divider => error', e.message);
			}
		}
	};
</script>

<style lang="scss">
	.interface-divider 
	{
		position: relative;
		
		.small-style {
			position: relative;
			padding-top: 12px;
			
			&.margin {
				margin-top: 48px;
			}
		
			section.interface-divider 
			{
				h2 {
					position: absolute;
					top: 0px;
					left: 50%;
					transform: translateX(-50%);
					color: var(--heading-text-color);
					font-size: 16px;
					line-height: 18px;
					background-color: var(--page-background-color);
					padding: 0 12px;
				}
				p {
					position: absolute;
					top: 28px;
					left: 50%;
					transform: translateX(-50%);
					max-width: 560px;
				}				
			}
		}
		.medium-style {
			padding-top: 12px;
			
			&.margin {
				margin-top: 48px;
			}
		
			section.interface-divider 
			{
				h2 {
					color: var(--heading-text-color);
					font-size: 22px;
					line-height: 28px;
					font-weight: 400;
					margin-bottom: 12px;
				}
				p {
					max-width: 560px;
					margin-top: 12px;
				}				
			}				
		}
		.large-style {
			padding-top: 12px;
			
			&.margin {
				margin-top: 48px;
			}
		
			section.interface-divider 
			{
				h2 {
					color: var(--heading-text-color);
					font-size: 28px;
					line-height: 32px;
					font-weight: 300;
					margin-bottom: 12px;
				}
				p {
					max-width: 768px;
					margin-top: 16px;
					line-height: 1.4em;					
				}				
			}
		}
		section.interface-divider {
			p.description {
				color: var(--note-text-color);
				font-size: var(--input-font-size);
				
				em {
					color: var(--main-primary-color);
					font-style: normal;
				}
			}
		}	
		header.interface-divider-header {
			display: flex;
			align-items: center;
			justify-content: space-between;
			
			aside {
				color: var(--main-primary-color);
			}
		}	
		nav.interface-divider-menu {
			display: flex;
			border-top: 1px solid var(--input-border-color);
			border-bottom: 1px solid var(--input-border-color);
			margin: 20px 0;
			padding: 10px 0;
			
			.interface-divider-nav {
				color: var(--input-required-color);
				padding: 0 10px;
				flex-grow: 1;
				text-decoration: none;
				text-align: center;
				font-size: 1rem;
				font-weight: 500;
				cursor: pointer;
				
				&.active {
					color: var(--main-primary-color);
				}
				
				& + a {
					border-left: 1px solid var(--input-border-color);
				}
				
				@media screen and (max-width: 1024px) 
				{
					.text {
						display: none;
					}
				}
				
				@media screen and (min-width: 1024px) 
				{
					.icon {
						display: none;
					}
				}
			}
		}
		hr {
			border: 0;
			background: none;
			border-bottom: 2px solid var(--input-border-color);
		}
		.interface-divider-steps 
		{
			display: flex;
			flex-flow: row wrap;
			justify-content: center;
			border-bottom: 1px solid var(--input-border-color);
			padding: 1rem 0;
			
			.interface-divider-step 
			{
				margin: 0;
				padding: 1rem 0;
				width: 100%;
				
				dt {
					color: var(--blue-grey-500);
					font-size: 1rem;
					font-weight: 500;
					padding-bottom: 0.25rem;
				}
				
				dd {
					color: var(--blue-grey-600);
					font-size: 1rem;
				}
			}
		}
		.interface-divider-content
		{
			padding: 2rem;
			
			h1, .h1 {
				font-size: 2.5rem;
				font-weight: 300;
				
				& + p {
					font-size: 1.5rem;
					font-weight: 300;
				}
			}
			
			h2, .h2 {
				font-size: 1.65rem;
			}
			
			h3, .h3 {
				font-size: 1.35rem;
			}
			
			h4, h5, h6,
			.h4, .h5, .h6 {
				font-size: 1.15rem;
			}
			
			h1, h2, h3, h4, h5, h6,
			.h1, .h2, .h3, .h4, .h5, .h6 {
				color: var(--blue-grey-500);
				font-family: var(--main-font-accent);
				line-height: 1.5em;
				margin-bottom: 0.5em;
				
				&:not(:first-child) {
					margin-top: 1.75em;
				}
				
				&.active {
					color: var(--main-primary-color) !important;
				}
				
				& + hr {
					margin-top: 0 !important;
					margin-bottom: 0.5rem !important;
					
					& + p {
						color: var(--blue-grey-500);
						font-size: 1.3rem;
						font-weight: 300;
					}
				}
			}
			
			p {
				&:not(:only-child) {
					margin-bottom: 1em;
				}
				
				&:empty {
					display: none !important;
					margin: 0;
				}
				
				&:only-child {
					color: var(--blue-grey-500);
					font-size: 2rem;
					font-weight: 300;
					line-height: 2.5rem;
				}
				
				&.updated {
					display: flex;
					flex-flow: column wrap;
					
					span.d-label {
						color: var(--main-primary-color);
						font-weight: 500;
					}
				}
			}
			
			a {
				color: var(--main-primary-color) !important;
				font-weight: 500;
				
				&[href*="netlify"] {
					display: none !important;
				}
			}
			
			img {
				display: block;
				max-width: 100%;
				margin-bottom: 1rem;
				
				&[width="100"] {
					width: 100%;
				}
			}
			
			hr {
				border: 0;
				background: none;
				border-bottom: 2px solid var(--input-border-color);
				margin: 1.5rem 0;
			}
			
			strong {
				font-weight: 600;
			}
			
			ol, p, ul {
			    line-height: 2rem;
			}
			
			ol, ul, video {
			    & + p {
				    margin-top: 1em;
			    }
			}
			
			blockquote {
			    font-size: 1rem;
			    color: var(--blue-grey-500);
			    border-left: .2rem solid var(--main-primary-color);
			    margin: 1rem 0;
			    padding: .25rem 0 .25rem 1rem;
			    
			    &[data-tip] {
				    background-color: var(--blue-grey-800);
					border-left-width: .3rem;
					border-left-style: solid;
					color: var(--blue-grey-300);
					font-size: 1.2rem;
					font-weight: 400;
					line-height: 2rem;
				    padding: 1.5rem;
			    }
			    
			    p {
				    margin: 0 !important;
				    
				    &:first-child:not(:only-child):not(:last-child):not(:empty) {
					    font-family: var(--main-font-accent);
					    font-size: 1.5rem;
					    font-weight: 500;
				    }
				    
				    &:not(:empty) + p {
					    margin-top: 1em !important;
				    }
			    }
			}
			
			pre {
				font-family: Consolas, Monaco, Andale Mono, Ubuntu Mono, monospace;
				font-size: 12px;
				position: relative;
				background-color: rgba(black, 0.4);
				line-height: 1.4;
			    padding: 1.25rem 1.5rem;
			    margin: .85rem 0;
			    overflow: auto;
			}
			
			table {
				border-collapse: collapse;
			    margin: 1rem 0;
			    display: block;
			    overflow-x: auto;
			    
			    tr {
				    border-top: 1px solid var(--blue-grey-800);
				    
				    td, th {
					    border: 1px solid var(--blue-grey-800);
						padding: .6em 1em;
						text-align: left;
				    }
			    }
			}

		}
	}
</style>