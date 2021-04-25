<template>
	<div class="interface-text-extended">
		<v-input
			v-bind:id="name"
			type="text"
			icon-right-color=""
			v-bind:value="displayValue"
			v-bind:readonly="readonly"
			v-bind:monospace="options.monospace"
			v-bind:placeholder="options.placeholder"
			v-bind:icon-left="options.iconLeft"
			v-bind:icon-right="options.iconRight"
			v-bind:maxlength="maxlength"
			v-bind:charactercount="options.showCharacterCount"
			@input="input">			
		</v-input>	
		<div class="interface-text-extended-note">
			<p class="type-note">
				<span class="status" ref="status">{{ messages.status }}</span>
				<span class="message" ref="message">{{ messages.message }}</span>
			</p>
		</div>	
	</div>
</template>

<script>
	import mixin from "@directus/extension-toolkit/mixins/interface";		
	import choices from "./mixins/choices.js";		
	import formatter from "./mixins/formatter.js";
	
	import {
		forEach, 
		size
	} from 'lodash';
		
	export default {
		name: "InterfaceTextInputExtended",
		mixins: [
			choices,
			formatter,
			mixin
		],
		data () {
			return {
				enabled: false,
				existing: false,
				messages: {
					fields: "",
					message: "",
					status: ""
				},
				timer: 0,
				updated: false
			};
		},		
		computed: {
			displayValue () {
				return this.value || '';
			},
			maxlength () {
				return this.$props.length || 250;
			},
			parameters () {
				return this.options.parameters || [];
			},
			readonly () {
				return !this.options.override;
			},
			template () {
				return this.options.template || '';
			}
		},
		methods: {
			build (status, message) {
				if (message) this.messages.message = message;
				if (status) this.messages.status = status;			
			},
			debounce (value, field, previous) {
				this.build(`Typing (${ field })...`, "--");
				
				window.clearTimeout(this.timer);
				
				this.timer = window.setTimeout(() => {
					this.build("Pending...", "--");
					
					this.input(value, field, previous);
				}, 1000);
			},
			input (value, field, previous) {
				if (this.options.override) {
					this.value = value;
					this.updated = true;
				}
				else if (this.mirrored.length === size(this.choices)) {
					this.value = this.$helpers.micromustache.render(this.template, this.choices);
				}
				
				if (this.enabled) this.process(value, field, previous);
				
				this.$emit('input', this.value);
			},
			process (value, field, previous) {
				this.parameters.forEach((item) => {
					if (this.mirrored.includes(item.field) && item.find) {
						this.value = this.value.replace(item.find, item.replace);
					}
				});
				
				if (this.options.trim) this.value = this.value.trim();
				
				if (this.options.format_value) this.value = this.formatter(this.value, this.options.format_value);
				
				this.build("Awaiting...", this.messages.fields);
			}
		},
		created () {
			this.enabled = this.mirrored.length && this.template.length;
			this.messages.fields = `Listening to fields: ${ this.mirrored.join(', ') }...`;
			
			this.mirrored.forEach((field) => {
				this.$watch(`values.${ field }`, (value, previous) => {
					if (!this.updated) this.debounce(value, field, previous);
				});
			});
		},
		mounted () {			
			if (this.enabled) this.build("Awaiting...", this.messages.fields);		
		}
	};
</script>

<style lang="scss" scoped>
	.interface-text-extended {
		.v-input {
			width: 100%;
			max-width: var(--width-medium);
		}
		.interface-text-extended-note {
			display: flex;
			margin-top: var(--input-note-margin);
			
			.status {
				color: var(--input-required-color);
				margin-right: 0.5rem;
			}
			
			.message {
				flex-grow: 1;
			}
		}
	}	
</style>