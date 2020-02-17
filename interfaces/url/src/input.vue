<template>
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
		v-on:input="input"
		v-on:keyup.enter.prevent.stop="load"
	></v-input>
</template>

<script>
	import mixin from '@directus/extension-toolkit/mixins/interface';
	
	export default {
		name: "InterfaceURL",
		mixins: [mixin],
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
				this.value = value;
			},
			load (value) {
				if (this.readonly === true || !this.value) return;
												
				this.$api.api.get('/custom/metadata/import', {
					url: this.value
				})
				.then((response) => {
					
					response.title = response.title || response.title;
					response.description = response.description || response.sitename;
					
					this.$events.emit("warning", {
						notify: `Resource Downloaded!`
					});
					
					if (response.url) {
						this.$emit('input', response.url);
					}
					
					if (this.options.mirroredTitle && response.title) {
						this.$emit('setfield', { field: this.options.mirroredTitle, value: response.title });
					}
					if (this.options.mirroredDescription && response.description) {
						this.$emit('setfield', { field: this.options.mirroredDescription, value: response.description });
					}			
										
					if (this.options.mirroredImage && response.image) {
						this.upload(response);
					}
				})
				.catch((error) => {
					this.error = error;
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
				.then((response) => {					
					this.$events.emit("warning", {
						notify: `Image Resource Uploaded! Select the image with the title -${ response.title }- from existing files to add!`
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
	.custom-interface-url {
		color: var(--blue-grey-200);
	}
	.custom-interface-url-image {
		display: block;
		width: 100%;
		height: auto;
	}
</style>
