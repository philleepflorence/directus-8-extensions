<template>
	<div class="custom-interface-xhr no-wrap">{{ displayValue }}</div>
</template>

<script>
	import mixin from "@directus/extension-toolkit/mixins/interface";
	
	export default {
		mixins: [mixin],
		computed: {
			displayValue() {
				return '--';
			}
		}
	}
</script>

<style lang="scss" scoped>
	div.custom-interface-xhr {
		position: relative;
		color: var(--blue-grey-200);
	}
</style>
