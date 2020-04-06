<template>
	<div class="modules-module">
		
		<v-header 
			:title="getContent('title')" 
			:breadcrumb="breadcrumb" 
			:icon="icon" 
			settings>
		</v-header>
		
		<div class="modules-module-loading" v-if="loading">
			<v-progress-linear background-color="--main-primary-color" color="--blue-grey-700" indeterminate>
		</div>	
		
		<div class="modules-module-content animated fadeIn">
			
			<div class="modules-module-form">
			
				<header class="modules-divider">
					<h2 class="modules-divider">{{ getContent('form.headline') }}</h2>
					<hr />
					<p class="modules-divider" v-for="html in getContent('form.description')" v-html="html"></p>
				</header>
				
				<v-textarea
					id="modules-module-content-textarea"
					rows="10"
					:placeholder="getContent('form.message.placeholder')"
					@input="onInputMessage">
				</v-textarea>
				
				<v-button block @click="onSubmitMessage" :loading="processing">{{ getContent('form.submit.label') }}</v-button>
				
			</div>
			
		</div>	

		<v-info-sidebar wide itemDetail>
			<section class="info-sidebar-section">
				<h2 class="font-accent">{{ getContent('title') }}</h2>
				<p class="p">{{ getContent('description') }}</p>
			</section>
			<section class="info-sidebar-section">
				<h2 class="font-accent">{{ getContent('sections.mode.headline') }}</h2>
				<div class="info-sidebar-inputs">
					<div class="info-sidebar-input" v-for="input in getContent('sections.mode.inputs')">
						<div class="input">
							<v-checkbox :label="input.label" :value="input.value" :inputValue="form.modes" @change="onChangeMode(input.value)">
						</div>
					</div>
				</div>
			</section>
			<section class="info-sidebar-section">
				<h2 class="font-accent">{{ getContent('sections.users.headline') }}</h2>
				<div class="info-sidebar-inputs" v-if="users">
					<div class="info-sidebar-input" v-for="user in users">
						<div class="input">
							<v-checkbox v-model="form.users" :label="`${ user.first_name } ${ user.last_name }`" :value="user.id" :inputValue="form.users"  @change="onChangeMode(user.id)">
						</div>
					</div>
				</div>
			</section>				
		</v-info-sidebar>
		
	</div>
</template>

<script>
	import { forEach, get, set, size } from 'lodash';
		
	import $meta from './meta.json';
	
	export default {
		name: 'ModulesNotifications',
		data () {
			return {
				contents: $meta.contents,
				icon: $meta.icon,
				form: {
					modes: [],
					users: [],
					message: ''
				},
				keys: {
					element: 0
				},
				loading: false,
				loaded: false,
				processing: false,
				users: null
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
			},
			modes () {
				let modes = [];
				let inputs = this.getContent('sections.mode.inputs');
				
				forEach(inputs, input => modes.push(input.value));
				
				return modes;
			}
		},
		methods: {
			getContent (input) {
				let translation = get(this.contents, this.locale);
					translation = translation || get(this.contents, 'en-US');

				return get(translation, input);
			},
			loadUsers () {
				this.loading = true;
				
				let params = {
					limit: -1,
					status: "published",
					fields: "id,username,first_name,last_name,email,privilege.name,location,online"
				};
																
				this.$api.getItems('app_users', params).then((response) => {
					
					this.loading = false;
					
					let users = [];
					
					response.data.forEach(user => users.push(user));
					
					this.users = users;
					
				}).catch((error) => {
					
					this.loading = false;
					
					this.error = typeof error === "string" ? error : error.message;
					
					if (typeof this.error === "string") {
						this.$notify({
							title: this.stripTags(this.error),
							color: 'red',
							iconMain: 'report',
							delay: 5000
						});						
					}
				});
			},
			onChangeMode (input) {
				if (this.form.modes.includes(input)) this.form.modes.splice(this.form.modes.indexOf(input), 1);
				else this.form.modes.push(input);				
			},
			onChangeUser (input) {
				if (this.form.users.includes(input)) this.form.users.splice(this.form.users.indexOf(input), 1);
				else this.form.users.push(input);
			},
			onInputMessage (input) {
				this.form.message = input;
			},
			onSubmitMessage () {
				this.processing = true;
				
				this.$notify({
					title: this.getContent('form.processing'),
					color: 'orange',
					iconMain: 'cloud_queue',
					delay: 5000
				});
				
				this.$api.api.post('/custom/notifications/push', this.form).then((response) => {
					
					this.processing = false;
					
					this.$notify({
						title: this.stripTags(response.message),
						color: response.success ? 'green' : 'red',
						iconMain: 'cloud_down',
						delay: 10000
					});
					
				}).catch((error) => {
					
					this.processing = false;
					
					this.error = typeof error === "string" ? error : error.message;
					
					if (typeof this.error === "string") {
						this.$notify({
							title: this.stripTags(this.error),
							color: 'red',
							iconMain: 'report',
							delay: 5000
						});						
					}
				});
			},
			stripTags (input) {
				if (typeof input === "string") return input.replace(/(<([^>]+)>)/ig,"");
				
				return input
			}
		},
		metaInfo() {
			return {
				title: this.getContent('subtitle')
			};
		},
		mounted () {
			this.loadUsers();
		}
	}
</script>

<style lang="scss" scoped>
	.modules-module {
		padding: var(--page-padding);
		
		.modules-module-content {
			
			.modules-module-form {
				textarea {
					margin-bottom: 1rem;
					resize: none;
				}
			}
		}
		
		.info-sidebar-section {
			.info-sidebar-inputs {
				.info-sidebar-input {
					display: flex;
					align-items: center;
					margin: 10px 0;
					
					.input {
						flex-grow: 1;
						
						strong {
							display: block;
							color: var(--blue-grey-400);
							font-weight: 500;
							line-height: 1.75rem;
						}
						
						small {
							font-size: 0.875rem;
						}
					}
					
					.icon {
						flex-shrink: 1;
					}
				}
			}
		}
	}
</style>