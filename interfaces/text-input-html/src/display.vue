<template>
	<div class="no-wrap text-input-html" v-html="displayValue"></div>
</template>

<script>
	import mixin from "@directus/extension-toolkit/mixins/interface";
	export default {
		mixins: [mixin],
		computed: {
			displayValue() {
				let value = this.value;
				if (this.options.formatValue) {
					value = this.$helpers.formatTitle(value);
				}
				return value;
			}
		}
	};
</script>

<style lang="scss" scoped>
	.text-input-html {
		position: relative;
	}
</style>