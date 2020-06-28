<template>
	<div class="custom-interface-map no-wrap" v-html="displayValue"></div>
</template>

<script>
	import mixin from '@directus/extension-toolkit/mixins/interface';
	export default {
		name: 'ReadonlyMapExtended',
		mixins: [mixin],
		computed: {
			displayValue() {
				let display = this.options.display;
				
				if (this.value && display) {
					return this.value[display].toLowerCase();
				}
				else if (this.value) {
					return `${ this.value.lat } &mdash; ${ this.value.lng }`;
				}
				
				return '--';
			}
		}
	};
</script>

<style lang="scss">
	.custom-interface-map {
		position: relative;
	}
</style>