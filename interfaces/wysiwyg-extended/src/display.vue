<template>
	<div class="no-wrap wysiwyg-extended" v-html="cleanValue"></div>
</template>

<script>
	import mixin from '@directus/extension-toolkit/mixins/interface';
	
	export default {
		name: 'DisplayWysiwygExtended',
		mixins: [mixin],
		computed: {
			cleanValue() {				
				return this.value ? this.value.replace(/<\/?[^>]+(>|$)/g, '').substring(0, 200).trim() : '';
			}
		}
	};
</script>

<style lang="scss" scoped>
	.wysiwyg-extended {
		position: relative;
	}
</style>