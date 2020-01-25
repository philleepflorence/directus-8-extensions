<template>
	<div class="no-wrap many-to-one-extended" v-html="displayValue"></div>
</template>

<script>
	import mixin from "@directus/extension-toolkit/mixins/interface";
	
	export default {
		mixins: [mixin],
		data() {
			return {
				loading: false,
				data: null
			};
		},
		computed: {
			displayValue() {
				let value = this.value;
	
				if (this.isPrimaryKey && this.data && this.loading === false) {
					value = this.data;
				}
	
				if (value) {
					return this.$helpers.micromustache.render(this.options.template, value);
				}
	
				return "--";
			},
			isPrimaryKey() {
				return typeof this.value !== "object";
			}
		},
		watch: {
			value() {
				if (this.isPrimaryKey) {
					this.fetchRelationalData();
				}
			}
		},
		methods: {
			async fetchRelationalData() {
				if (this.relation?.collection_one?.collection) {
					this.loading = true;
	
					const res = await this.$api.getItem(
						this.relation.collection_one.collection,
						this.value
					);
	
					this.data = res.data;
	
					this.loading = false;
				}
			}
		}
	};
</script>

<style lang="scss" scoped>
	.many-to-one-extended {
		position: relative;
	}
</style>