<template>
	<div class="input-single-file">
		
		<v-notice v-if="noFileAccess" class="notice">
			<span>This item is not available.</span>
			<button @click="$emit('input', null)">
				<v-icon v-tooltip="Deselect" name="clear"></v-icon>
			</button>
		</v-notice>

		<v-spinner v-else-if="image === null && value !== null"></v-spinner>

		<template v-else>
			<v-card
				v-if="Array.isArray(value) === false && value"
				class="card"
				:title="image.title"
				:subtitle="subtitle + subtitleExtra"
				:src="src"
				:icon="icon"
				text-background
				color="black"
				:options="cardOptions"
				:medium-image="previewWidth('half')"
				:big-image="previewWidth('full')"
				:only-show-on-hover="false"
				@download="downloadFile"
				@edit="editFile"
				@deselect="$emit('input', null)">
			</v-card>
			
			<v-upload
				v-else
				small
				:disabled="readonly"
				class="uploader"
				:accept="options.accept"
				:multiple="false"
				@upload="saveUpload">
			</v-upload>

			<div v-if="!value" class="buttons">
				<v-button type="button" :disabled="readonly" @click="existing = true">
					<v-icon name="playlist_add" left></v-icon>
					<span>Select an Existing File</span>
				</v-button>
			</div>

			<portal v-if="existing" to="modal">
				<v-modal
					title="Choose One"
					:buttons="contents.select.buttons"
					@cancel="existing = false"
					@close="existing = false"
					@done="existing = false">
					<div class="content">
						<div class="search">
							<v-input
								type="search"
								placeholder="Search for an item..."
								class="search-input"
								@input="onSearchInput">
							</v-input>
						</div>
						<v-items
							class="items"
							collection="directus_files"
							:view-type="viewType"
							:selection="value ? [value] : []"
							:filters="filters"
							:view-query="viewQuery"
							:view-options="viewOptions"
							@options="setViewOptions"
							@query="setViewQuery"
							@select="saveSelection">
						</v-items>
					</div>
				</v-modal>
			</portal>
			
		</template>
	</div>
</template>

<script>
	import mixin from '@directus/extension-toolkit/mixins/interface';
	import formatSize from './format-size';
	import getIcon from './get-icon';
	import { mapState } from 'vuex';
	import { debounce, get, isEmpty, set, size } from 'lodash';
	
	export default {
		name: "InterfaceFileExtended",
		mixins: [mixin],
		data() {
			return {
				existing: false,
				viewOptionsOverride: {},
				viewTypeOverride: null,
				viewQueryOverride: {},
				filtersOverride: [],
				image: null,
				noFileAccess: false,
				contents: {
					edit: {
						buttons: {
							save: {
								text: "Save",
								color: "accent"
							}
						}
					},
					select: {
						buttons: {
							done: {
								text: "Done"
							}
						}
					}
				}
			};
		},
		computed: {
			...mapState (['currentProjectKey']),
			cardOptions () {
				const options = {
					download: {
						text: "Download Image",
						icon: 'file_download'
					},
					deselect: {
						text: "Clear Image",
						icon: 'clear'
					},
					edit: {
						text: "Edit Image",
						icon: 'edit'
					}
				};
	
				if (this.options.allowDelete === true) {
					options.remove = {
						text: "Delete",
						icon: 'delete'
					};
				}
	
				return options;
			},
			subtitle () {
				if (!this.image) return '';
				
				if (this.options.viewOptions && this.options.viewOptions.subtitle) return this.image[this.options.viewOptions.subtitle];
	
				return this.image.uploaded_on;
			},
			subtitleExtra() {
				/*
					Image ? -> display dimensions and formatted filesize
				*/ 
				const content = this.image[ this.options.viewOptions.content ] || this.image.type;
				const size = this.image.filesize ? formatSize(this.image.filesize) : '--';
				
				if (this.image.type && this.image.type.startsWith('image')) return ` • ${ content } • ${ size }`;
				else return ` • ${ content }`;
			},
			src () {
				if (!this.image.type || !this.image.type.startsWith('image')) {
					return null;
				}
	
				if (this.image.type === 'image/svg+xml') {
					return this.image.data.asset_url;
				}
	
				const size = this.options.previewWidth === 'full' ? 'large' : 'medium';
				const fit = this.options.crop ? 'crop' : 'contain';
	
				return this.image.data?.thumbnails.find(
					thumb => thumb.key === `directus-${size}-${fit}`
				)?.url;
			},
			isImage () {
				return this.image.type && this.image.type.startsWith('image');
			},
			icon () {
				return this.image.type && !this.image.type.startsWith('image')
					? getIcon(this.image.type)
					: null;
			},
			viewOptions () {
				const viewOptions = this.options.viewOptions;
				return {
					...viewOptions,
					...this.viewOptionsOverride
				};
			},
			viewType () {
				if (this.viewTypeOverride) return this.viewTypeOverride;
				return this.options.viewType;
			},
			viewQuery () {
				const viewQuery = this.options.viewQuery;
	
				return {
					sort: '-id',
					...viewQuery,
					...this.viewQueryOverride
				};
			},
			filters () {
				return [
					...(this.options.filters || []),
					...this.fileTypeFilters,
					...this.filtersOverride
				];
			},
			fileTypeFilters () {
				if (
					!this.options.accept ||
					this.filtersOverride.length > 0 ||
					(this.options.filters || []).some(filter => filter.field === 'type')
				) {
					return [];
				}
	
				return [
					{
						field: 'type',
						operator: 'in',
						value: (this.options.accept || '').trim().split(/,\s*/)
					}
				];
			}
		},
		async created () {
			if (this.value) {
				await this.fetchImage();
			}
			this.onSearchInput = debounce(this.onSearchInput, 200);
		},
		watch: {
			value () {
				this.fetchImage();
			}
		},
		methods: {
			previewWidth (input) {
				if (this.options.previewWidth) return this.options.previewWidth === input;
				
				return input === "full";
			},
			async fetchImage () {
				this.noFileAccess = false;
				this.image = null;
	
				if (!this.value) return;
	
				let id;
	
				if (typeof this.value === 'object') {
					id = this.value.id;
				} 
				else {
					id = this.value;
				}
	
				try {
					const response = await this.$api.getFile(String(id));
					this.image = response.data;
				} 
				catch {
					this.noFileAccess = true;
				}
			},
			downloadFile () {
				window.open(this.image.data.full_url);
			},
			editFile () {
				/*
					Allow the user to go to the file to make edits
				*/
				if (this.image) this.$router.push(`/app/files/${ this.image.id }`);
			},
			saveUpload (response) {
				/*
					Add default values if applicable and update the file!
				*/				
				let file = {...response.data};
				
				if (Array.isArray(this.options.defaultValues) && this.options.defaultValues.length) {
					let update = {};
					
					for (const row of this.options.defaultValues) {
						const currvalue = get(file, row.field);
						const value = row.interface ? this.values[row.value] : row.value;
						
						if (!currvalue || isEmpty(currvalue)) set(update, row.field, value);
					}
					
					file = {...file, ...update};
															
					if (size(update)) this.$api.updateItem("directus_files", file.id, update);
				}
				
				this.image = file;
				 
				this.$emit('input', this.image.id);
			},
			setViewOptions (updates) {
				this.viewOptionsOverride = {
					...this.viewOptionsOverride,
					...updates
				};
			},
			setViewQuery (updates) {
				this.viewQueryOverride = {
					...this.viewQueryOverride,
					...updates
				};
			},
			onSearchInput(value) {
				this.setViewQuery({
					q: value
				});
			},
			saveSelection (value) {
				const file = value[value.length - 1];
	
				if (file) {
					this.image = file;
					this.$emit('input', file);
				} 
				else {
					this.image = null;
					this.$emit('input', null);
				}
	
				this.existing = false;
			},
			async removeFile () {
				const file = this.image;
				
				await this.$api.deleteItem('directus_files', file.id);
				
				this.$notify({
					title: 'Item Deleted',
					color: 'green',
					iconMain: 'check'
				});
				
				this.image = null;
				this.$emit('input', null);
			}
		}
	};
</script>

<style lang="scss" scoped>
	.card,
	.uploader {
		width: 100%;
		max-width: var(--width-x-large);
	}
	
	.uploader {
		height: 236px;
	}
	
	.buttons {
		margin-top: 24px;
	}
	
	button {
		display: inline-block;
		margin-left: 20px;
		&:first-of-type {
			margin-left: 0;
		}
	}
	
	.body {
		padding: 20px;
	}
	
	.search-input {
		border-bottom: 2px solid var(--input-border-color);
		&::v-deep input {
			border-radius: 0;
			border: none;
			padding-left: var(--page-padding);
			height: var(--header-height);
	
			&::placeholder {
				color: var(--input-placeholder-color);
			}
		}
	}
	
	.content {
		&::v-deep .v-layout {
			height: auto;
			max-height: none;
			overflow: hidden;
		}
	}
	
	.notice {
		display: flex;
		justify-content: space-between;
	}
</style>