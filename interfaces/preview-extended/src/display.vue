<template>
	<div class="custom-interface-url no-wrap">
		<a :href="displayValue" class="no-wrap" target="_blank">{{ displayValue }}</a>
	</div>
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
	div.custom-interface-preview {
		position: relative;
		
		a {
			color: var(--main-primary-color);
			font-weight: 500;
			text-decoration: none;
		}
	}
</style>
