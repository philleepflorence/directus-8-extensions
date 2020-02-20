<template>
	<div class="interface-dropdown interface-dropdown-extended" :key="keys.element">
		
		<small v-if="parseError" class="notice">
			<v-icon name="warning" />
			<span>
				{{ messageError }}
				<br />
				{{ parseError }}
			</span>
		</small>
		
		<div class="interface-dropdown-extended-content" v-else>
			
			<v-select
				v-if="!loading"
				:id="name"
				:value="value"
				:options="choices"
				:placeholder="options.placeholder"
				:icon="options.icon"
				:other="options.allow_other"
				@input="$emit('input', $event)">
			</v-select>
		
			<v-spinner
				v-if="loading"
				line-fg-color="var(--blue-grey-300)"
				line-bg-color="var(--blue-grey-200)"
				class="spinner">
			</v-spinner>
		
		</div>
		
	</div>
</template>

<script>
	import mixin from '@directus/extension-toolkit/mixins/interface';
	import { sortBy } from 'lodash';
	
	export default {
		name: "InterfaceDropdownExtended",
		mixins: [mixin],
		data() {
			return {
				keys: {
					element: 100,
					select: 0
				},
				loading: true,
				dropdown: null,
				messageError: 'Unable to load options...',
				parseError: null
			};
		},
		computed: {
			choices () {
				let choices = this.options.choices;
				let dropdown = this.dropdown;
								
				if (!choices && !dropdown) return {};
				
				if (typeof this.options.choices === 'string') {
					try {
						choices = JSON.parse(this.options.choices);
					} catch (error) {
						this.parseError = error.toString();
					}
				}
				
				if (dropdown) choices = {...choices, ...dropdown};
				
				return choices;
			}
		},
		created () {
			this.fetchItems();
		},
		methods: {
			/*
				Parse the Filter Array into an Object
			*/
			formatFilters (filters) {
				const parsedFilters = {};
				
				if (Array.isArray(filters)) {
					this.options.filters.forEach(filter => {
						parsedFilters[filter.field] = {};
						parsedFilters[filter.field][filter.operator] = filter.value;
					});
				}
			
				return parsedFilters;
			},
			/*
				Fetch additional dropdown options and merge - if applicable
				TODO - After there is GROUP BY, remove limit and add GROUP BY
			*/
			fetchItems () {
				if (!this.options.collection || !this.options.field) return;
		
				this.loading = true;
	
				const params = {
					fields: this.options.field,
					meta: "total_count",
					limit: -1,
					group: this.options.field,
					filter: this.formatFilters(this.options.filters)
				};
	
				this.$api
					.getItems(this.options.collection, params)
					.then((response) => {
						this.dropdown = {};
						
						if (Array.isArray(response.data)) {
							response.data.forEach((row) => {
								let value = row[this.options.field];
								
								if (!this.dropdown[value]) this.dropdown[value] = value;
							});
						}
						
						this.dropdown = sortBy(this.dropdown, [function(o) { return o; }]);
						
						this.loading = false;						
					})
					.catch(error => {
						this.error = error;
						this.loading = false;
					})
					.finally(() => {
						this.loading = false;
					});
			}
		}
	};
</script>

<style lang="scss" scoped>
	.v-select {
		margin-top: 0;
		max-width: var(--width-medium);
	}
	.notice {
		display: flex;
		align-items: center;
		i {
			margin-right: 1rem;
		}
	}
	.interface-dropdown {
		position: relative;
	}
	.interface-dropdown .spinner {
		position: absolute;
		left: 0;
		right: 0;
		margin: 0 auto;
		top: 12px;
	}
</style>