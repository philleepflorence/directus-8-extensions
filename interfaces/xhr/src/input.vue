<template>
	<div class="interface-xhr">
		<v-button class="interface-xhr-load" :disabled="required && disabled" @click="load">
			{{ options.button }}
		</v-button>
		
		<v-modal 
			v-if="modal" 
			:title="`XHR Response - ${ result }`"
			:buttons="buttons"
			@close="modalClose"
			@done="modalClose">
			<div class="interface-xhr-modal" v-if="response">
				<div class="interface-xhr-response" v-html="response"></div>
			</div>
		</v-modal>
				
		<v-spinner
			v-show="loading"
			line-fg-color="var(--blue-grey-300)"
			line-bg-color="var(--blue-grey-200)"
			class="spinner">
		</v-spinner>
	</div>
</template>

<script>
	import mixin from '@directus/extension-toolkit/mixins/interface';
	
	export default {
		name: "InterfaceXHR",
		mixins: [mixin],
		data() {
			return {
				buttons: {
					done: {
						text: "Close Modal"
					}
				},
				loading: false,
				modal: false,
				response: null,
				result: null
			};
		},
		computed: {
			fields () {
				return this.options.fields || [];
			}
		},
		methods: {
			load () {
				this.loading = true;
				
				let params = {};
				
				for (let field in this.fields) if (this.values[field]) params[field] = this.values[field];
												
				this.$api.api[this.options.method](this.options.url, params)
				.then((response) => {
					this.result = "Success";
					
					this.modalOpen(response);			
				})
				.catch((error) => {
					this.result = "Error";
					
					this.modalOpen(error);	
				});
			},
			modalClose () {
				this.modal = false;
			},
			modalOpen (response) {
				this.loading = false;
					
				this.response = response;
				
				this.modal = true;
			}
		}
	};
</script>

<style lang="scss">	
	.interface-xhr {
		position: relative;
		
		.interface-xhr-load {
			position: relative;
			display: block;
			width: 100%;
		}
		
		.interface-xhr-modal {
			padding: 1rem;
			line-height: 1.5;
			
			.interface-xhr-response {
				border: 2px solid var(--input-border-color);
				display: block;
				width: 100%;
				max-height: 65vh;
				margin: 1rem 0;
				padding: 1rem;
				overflow: scroll;
				white-space: pre-wrap;
			}
		}
		
		.v-modal {
			animation: fadeIn 650ms ease;
		}
		
		.spinner {
			position: absolute;
			left: 0;
			right: 0;
			margin: 0 auto;
			top: 12px;
		}
	}
</style>
