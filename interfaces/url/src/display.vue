<template>
	<div class="custom-interface-url no-wrap">{{ displayValue }}</div>
</template>

<script>
	import mixin from "@directus/extension-toolkit/mixins/interface";
	
	export default {
	mixins: [mixin],
	computed: {
		displayValue() {
			if (this.value)
				return this.value.toLowerCase();
			}
		}
	}
</script>

<style lang="scss" scoped>
	div.custom-interface-url {
		position: relative;
		color: var(--blue-grey-200);
	}
</style>
