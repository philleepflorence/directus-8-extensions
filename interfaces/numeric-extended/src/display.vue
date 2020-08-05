<template>
	<span v-if="options.localized" class="interface-numeric no-wrap">{{ $n(value) }}</span>
	<span v-else class="interface-numeric no-wrap">{{ value }}</span>
</template>

<script>
	import mixin from '@directus/extension-toolkit/mixins/interface';
	export default {
		mixins: [mixin]
	};
</script>

<style lang="scss" scoped>
	.interface-numeric {
		position: relative;
	}
</style>