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
	
	import { debounce } from 'lodash';
	
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
			update (newVal, oldVal) {
				this.value = this.$helpers.micromustache.render(this.template, this.values);
				
				this.$emit('input', this.value);
			}
		},
		created () {
			this.fields.forEach((field) => {
				this.$watch(`values.${ field }`, this.update);
			});
		},
		mounted () {			
			this.existing = typeof this.values.id === "number";
			
			if (this.existing) this.update();		
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
