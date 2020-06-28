<template>
	<div class="interface-url">
		<v-input
			v-bind:id="name"
			type="url"
			class="custom-interface-url"
			v-bind:value="displayValue"
			v-bind:readonly="disabled"
			v-bind:placeholder="options.placeholder"
			v-bind:maxlength="length"
			v-bind:icon-left="options.iconLeft"
			v-bind:charactercount="options.showCharacterCount"
			v-on:input="input">
		</v-input>
		
		<span class="interface-url-upload" v-on:click.prevent.stop="load">
			<v-icon name="import_export"></v-icon>
		</span>
		
		<div class="interface-url-content" ref="content"></div>
		
		<v-spinner
			v-show="loading"
			line-fg-color="var(--blue-grey-300)"
			line-bg-color="var(--blue-grey-200)"
			class="spinner">
		</v-spinner>
	</div>
</template>

<script>
	import mixin from '@directus/extension-toolkit/mixins/interface';
	
	export default {
		name: "InterfaceURL",
		mixins: [mixin],
		data() {
			return {
				loading: false
			};
		},
		computed: {
			displayValue () {
				return this.value || '';
			},
			length () {
				return 255;
			}
		},
		methods: {
			input (value) {
				if (value && value.length) this.value = value;
			},
			load (value) {
				if (this.readonly === true || !this.value) return;
				
				this.loading = true;
												
				this.$api.api.get('/custom/metadata/import', {
					url: this.value
				})
				.then((response) => {
					
					this.loading = false;
					
					response.title = response.title || response.title;
					response.description = response.description || response.sitename;
					
					this.$events.emit("warning", {
						notify: `Resource Downloaded!`
					});
					
					if (response.url) {
						this.$emit('input', response.url);
					}
					
					if (response.content) {
						this.$refs.content.innerHTML = response.content;
					}
					
					if (this.options.mirroredTitle && response.title) {
						this.$emit('setfield', { field: this.options.mirroredTitle, value: response.title });
					}
					if (this.options.mirroredDescription && response.description) {
						this.$emit('setfield', { field: this.options.mirroredDescription, value: response.description });
					}
					if (this.options.mirroredContent && response.content) {
						this.$emit('setfield', { field: this.options.mirroredContent, value: response.content });
					}			
										
					if (this.options.mirroredImage && this.options.uploadImage && response.image) {
						this.upload(response);
					}
				})
				.catch((error) => {
					this.error = error;
					
					this.loading = false;
				});
			},
			preview () {
				let element = document.querySelector(`[data-field="${ this.options.mirroredImage }"]`).querySelector('.uploader');
				
				element.innerHTML = `<img src="${ response.image }" alt="custom-interface-url-image" class="custom-interface-url-image">`;
			},
			upload (response) {	
				this.$api.api.post('/files', {
					data: response.image,
					title: response.title,
					description: response.description,
					filename_download: response.imageinfo.basename
				})
				.then((e) => {					
					this.$events.emit("warning", {
						notify: `Image Resource Uploaded! <br>Select the image with the title -${ response.title }- from existing files to add!`
					});
				})
				.catch((error) => {
					this.error = error;
				});
			},
		}
	};
</script>

<style lang="scss">	
	.interface-url {
		position: relative;
	}
	.custom-interface-url {
		color: var(--blue-grey-200);
	}
	.custom-interface-url-image {
		display: block;
		width: 100%;
		height: auto;
	}
	.interface-url-upload {
		position: absolute;
		right: 2px;
		top: 6px;
		padding: 8px 10px;
		cursor: pointer;
		background-color: var(--blue-grey-900);
	}
	.interface-url .spinner {
		position: absolute;
		left: 0;
		right: 0;
		margin: 0 auto;
		top: 12px;
	}
	.interface-url-content:not(:empty) {
		background-color: var(--blue-grey-800);
		color: var(--blue-grey-400);
		padding: 1rem;
		margin: 1rem 0;
		max-height: 480px;
		overflow: auto;
	}
</style>
