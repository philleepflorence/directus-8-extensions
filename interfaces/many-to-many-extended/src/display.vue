<template>
	<div class="no-wrap many-to-many-extended">{{ itemCount }}</div>
</template>

<script>
	import mixin from '@directus/extension-toolkit/mixins/interface';
	export default {
		name: 'ReadonlyManyToManyExtended',
		mixins: [mixin],
		computed: {
			itemCount() {
				const render = this.$helpers.micromustache.compile(this.options.item_count);
				
				return render({
					count: (this.value || []).length
				});
			}
		}
	};
</script>

<style lang="scss" scoped>
	.many-to-many-extended {
		position: relative;
	}
</style>