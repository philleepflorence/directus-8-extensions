<template>
	<div class="interface-many-to-one interface-many-to-one-extended">
		<div v-if="relationSetup === false" class="notice">
			<p>
				<v-icon name="warning" />
			</p>
		</div>

		<template v-else>
			<div class="many-to-one-extended-select">
				<v-select
					:id="name"
					:class="{ 'select-loading': loading }"
					:name="name"
					:placeholder="options.placeholder || ''"
					:options="selectOptions"
					:disabled="readonly"
					:value="valuePK"
					:icon="options.icon"
					@input="$emit('input', $event)"
				/>
			</div>
			
			<div 
				class="many-to-one-extended-value">
				<span class="no-wrap" v-html="displayValue"></span>
			</div>

			<button
				v-if="button"
				class="interface-many-to-one-extended"
				type="button"
				@click="listingActive = true"
			></button>

			<v-spinner
				v-show="loading"
				line-fg-color="var(--blue-grey-300)"
				line-bg-color="var(--blue-grey-200)"
				class="spinner"
			></v-spinner>

			<v-item-select
				v-if="listingActive"
				:collection="relation.collection_one.collection"
				:fields="relatedFields"
				:filters="filters"
				single
				:value="stagedValue || valuePK"
				@input="stageValue"
				@done="closeListing"
				@cancel="cancelListing"
			/>
		</template>
	</div>
</template>

<script>
	import mixin from "@directus/extension-toolkit/mixins/interface";
	
	export default {
		name: "InterfaceManyToOneExtended",
		mixins: [mixin],
		data() {
			return {
				button: true,
				loading: false,
				error: null,
				items: [],
				count: null,	
				listingActive: false,
				selectionSaving: false,
				newSelected: null,	
				stagedValue: null
			};
		},
		computed: {
			displayValue() {
				let PK = this.relatedPrimaryKeyField;
				let property = this.value && this.value[PK] ? this.value[PK] : this.value;
				
				if (this.selectOptions && property) 
				{
					return this.selectOptions[property];
				}
	
				return null;
			},
			/*
				The filters to use when loading items from the related collections
			*/
			filters() {
				return this.options.filters || [];
			},
			/*
				If the relationship is fully setup. 
				If not, we can stop everything else and prevent a bunch of js errors
			*/
			relationSetup() {
				if (!this.relation) return false;
				return true;
			},
			/*
				The name of the field that holds the primary key in the related collection
			*/
			relatedPrimaryKeyField() {
				return _.find(this.relation.collection_one.fields, {
					primary_key: true
				}).field;
			},
			/*
				The current value stripped down to the primary key. 
				NOTE: the application will always fetch and therefore return the full populated object on initial load. 
				The item select component expects a primary key. 
				This will extract that. 
				If the value is already a primary key, we return that.
			*/
			valuePK() {
				if (_.isObject(this.value)) return this.value[this.relatedPrimaryKeyField];
	
				return this.value;
			},
			/*
				The fields that will be fetched and rendered in the item select modal. 
				Will be based on the visible_fields option in the interface settings. 
				NOTE: if that settings hasn't been configured, it will fallback on the fields that are in the dropdown template option
			*/
			relatedFields() {
				let visibleFields = this.options.visible_fields;
				
				if (!visibleFields) return null;
	
				return visibleFields.split(",").map(f => f.trim());
			},
	
			/*
				Returns an object { [primaryKey]: [label] } for the items that can be passed on to 
				a v-select component to render the dropdown
			*/ 
			
			selectOptions() {
				if (this.items.length === 0) return {};
				
				const render = this.$helpers.micromustache.compile(this.options.template);
				
				const items = _.mapValues(_.keyBy(this.items, this.relatedPrimaryKeyField), item => {
					return render(item);
				});
				
				return items;
			}
		},
		watch: {
			relation() {
				if (this.relationSetup) {
					this.fetchItems();
				}
			}
		},
		created() {
			if (this.relationSetup) {
				this.fetchItems();
			}
		},
		methods: {
			/*
				Parse the Filter Array into an Object
			*/
			formatFilters (filters) {
				const parsedFilters = {};

				filters.forEach(filter => {
					parsedFilters[filter.field] = {};
					parsedFilters[filter.field][filter.operator] = filter.value;
				});
			
				return parsedFilters;
			},
			/*
				We keep a local to-be-actually-staged copy of the value that's selected in the item-select component. 
				This means that we can ignore saving this and set this back to null once the user closes the modal without selecting everything
			*/ 
			stageValue(primaryKey) {
				this.stagedValue = primaryKey;
			},
			/*
				This interface stages only the primary key. 
				The application doesn't differentiate between 'saved value' 
				(eg what comes from the api) and 'staged value' (what this interface stages). 
				That means that everytime we stage a value, 
				the value passed through the value prop is now just a primary key, 
				instead of the full nested object. 
				In order to be able to render the preview of the selected item, we need to fetch its data.
			*/ 
			fetchItems() {
				if (this.relation == null) return;
	
				const collection = this.relation.collection_one.collection;
	
				this.loading = true;
	
				const params = {
					fields: "*.*",
					meta: "total_count",
					limit: 0,
					filter: this.formatFilters(this.filters)
				};
	
				return Promise.all([
					this.$api.getItems(collection, params),
					this.value ? this.$api.getItem(collection, this.valuePK) : null
				])
					.then(([{ meta, data: items }, singleRes]) => {
						if (singleRes) {
							this.items = [...items, singleRes.data];
						} else {
							this.items = items;
						}
	
						this.loading = false;
						this.count = meta.total_count;
					})
					.catch(error => {
						this.error = error;
						this.loading = false;
					});
			},
			/*
				Happens when the user clicks "done" in the item select modal. 
				This will stage the pre-staged value and close the modal
			*/ 
			closeListing() {
				this.$emit("input", this.stagedValue);
	
				/*
					Download a fresh copy of the data of the selected item so we can render the preview value in the dropdown
				*/ 
				
				const collection = this.relation.collection_one.collection;
	
				const params = {
					fields: "*.*",
					limit: 1,
					filter: this.formatFilters(this.filters)
				};
	
				this.loading = true;
	
				this.$api
					.getItem(collection, this.stagedValue, params)
					.then(res => res.data)
					.then(item => (this.items = [...this.items, item]))
					.catch(error => {
						this.error = error;
						this.loading = false;
					})
					.finally(() => (this.loading = false));
	
				this.stagedValue = null;
				this.listingActive = false;
			},
			/*
				Ignore the pre-staged value and close the modal. Don't stage any value
			*/ 
			cancelListing() {
				this.stagedValue = null;
				this.listingActive = false;
			}
		}
	};
</script>

<style lang="scss">
	.interface-many-to-one.interface-many-to-one-extended {
		position: relative;
		max-width: var(--width-medium);
	}
	
	.interface-many-to-one-extended .v-select {
		margin-top: 0;
	}
	
	.interface-many-to-one-extended .select-loading {
		opacity: 0.5;
	}
	
	.interface-many-to-one-extended .many-to-one-extended-value {
		position: absolute;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
		background: transparent;
	}
	
	.interface-many-to-one-extended .many-to-one-extended-value span {
		display: flex; 
		align-items: center;
		padding: 10px 10px 10px 38px;
	}
	
	.interface-many-to-one-extended .many-to-one-extended-select .value .no-wrap {
		display: none;
	}
	
	.interface-many-to-one-extended button {
		position: absolute;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
		background: transparent;
		border: var(--input-border-width) solid var(--input-border-color);
		border-radius: var(--border-radius);
		transition: border var(--fast) var(--transition);
	
		&:hover {
			transition: none;
			border-color: var(--input-border-color-hover);
		}
	}
	
	.interface-many-to-one-extended .spinner {
		position: absolute;
		left: 0;
		right: 0;
		margin: 0 auto;
		top: 50%;
		transform: translateY(-50%);
	}
	
	.interface-many-to-one-extended .search {
		position: sticky;
		left: 0;
		top: 0;
		z-index: 2;
		background-color: var(--page-background-color);
		&-input {
			border-bottom: 1px solid var(--modal-header-background-color);
			padding: 12px;
	
			& >>> input {
				border-radius: 0;
				border: none;
				padding-left: var(--page-padding);
				height: var(--header-height);
	
				&::placeholder {
					color: var(--input-placeholder-color);
				}
			}
		}
	}
	
	.interface-many-to-one-extended .items {
		height: calc(100% - var(--header-height) - 1px);
	}
</style>