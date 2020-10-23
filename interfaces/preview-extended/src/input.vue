<template>
	<div class="interface-preview">
		<v-input
			v-bind:id="name"
			type="url"
			readonly="readonly"
			v-bind:value="displayValue"
			v-bind:placeholder="options.url_template"
			v-bind:maxlength="length"
			v-bind:icon-left="options.icon"
			v-bind:charactercount="options.showCharacterCount"
			v-on:input="input"
			v-on:keyup.enter="launch">
		</v-input>
		
		<span class="interface-preview-launch" v-on:click.prevent.stop="launch">
			<v-icon name="power_settings_new" color="--danger-dark" v-if="!value"></v-icon>
			<v-icon name="open_in_browser" color="--main-primary-color" v-else-if="value"></v-icon>
		</span>
	</div>
</template>

<script>
	import mixin from '@directus/extension-toolkit/mixins/interface';
	
	import { cloneDeep, debounce } from 'lodash';
	
	export default {
		name: "InterfacePreviewExtended",
		mixins: [mixin],
		data() {
			return {
				existing: false,				
				value: null
			};
		},
		computed: {
			displayValue () {
				return this.value || '';
			},
			fields () {
				return this.options.mirroredFields || [];
			},
			template () {
				return this.options.url_template || '';
			}
		},
		methods: {
			launch () {				
				if (this.existing || !this.value) this.update();
				else if (this.value) window.open(this.value, '_blank');
			},
			async load () {
				if (!Array.isArray(this.options.parameters)) return this.update(this.values);
				
				let values = cloneDeep(this.values);
				
				for (const row of this.options.parameters) {
					const value = this.values[row.property];
					
					if (typeof value === "number") {
						const response = await this.$api.getItem(row.collection, value, {
							fields: row.fields
						});
						
						values[row.property] = response.data;
					}
				}
				
				this.update(values);
			},
			update (values) {
				this.value = this.$helpers.micromustache.render(this.template, values);
				
				this.$emit('input', this.value);
				
				return false;
			}
		},
		created () {
			this.fields.forEach((field) => {
				this.$watch(`values.${ field }`, this.load);
			});
		},
		mounted () {			
			this.existing = typeof this.values.id === "number";
						
			if (this.existing) this.load();		
		}
	};
</script>

<style lang="scss">	
	.interface-preview {
		position: relative;
		
		.v-input {
			input {
				padding-right: 70px;
			}
		}
		
		.interface-preview-launch {
			position: absolute;
			right: 2px;
			top: 6px;
			padding: 8px 10px;
			cursor: pointer;
		}
	}	
</style>
