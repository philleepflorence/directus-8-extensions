<template>
	<div class="no-wrap interface-many-to-one" v-html="displayValue"></div>
</template>

<script>
	import mixin from "@directus/extension-toolkit/mixins/interface";
	
	export default {
		mixins: [mixin],
		data () {
			return {
				loading: false,
				data: null
			};
		},
		computed: {
			displayValue () {
				let value = this.value;
	
				if (this.isPrimaryKey && this.data && this.loading === false) {
					value = this.data;
				}
	
				if (value) {
					return this.$helpers.micromustache.render(this.options.template, value);
				}
				
				const displayValue = /*this.options.display_value || */'--';
				console.log('Debug - Development: many-to-one => displayValue', this.options, this.$props);
	
				return `<em>${ displayValue }</em>`;
			},
			isPrimaryKey() {
				return typeof this.value !== "object";
			}
		},
		watch: {
			value () {
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
	.interface-many-to-one {
		position: relative;
		
		em {
			color: var(--input-placeholder-color);
		}
	}
</style>