<template>
	<v-input
		v-bind:id="name"
		type="text"
		icon-right-color=""
		v-bind:value="value || ''"
		v-bind:readonly="readonly"
		v-bind:monospace="options.monospace"
		v-bind:placeholder="options.placeholder"
		v-bind:icon-left="options.iconLeft"
		v-bind:icon-right="options.iconRight"
		v-bind:maxlength="length ? +length : null"
		v-bind:charactercount="options.showCharacterCount"
		@input="updateValue"></v-input>
</template>

<script>
	import mixin from "@directus/extension-toolkit/mixins/interface";
	export default {
		mixins: [mixin],
		methods: {
	    	updateValue(rawValue) {
				let value = rawValue;
				if (this.options.trim) {
	        		if (!this.value || value.length > this.value.length) {
						value = value.trim();
	        		}
	    		}
				this.$emit("input", value);
			}
		}
	};
</script>

<style lang="scss" scoped>
	.v-input {
		width: 100%;
		max-width: var(--width-medium);
	}
</style>