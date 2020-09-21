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
			v-on:input="input"
			v-on:keyup.enter="load">
		</v-input>
		
		<span class="interface-url-upload" v-on:click.prevent.stop="load">
			<v-icon name="error" color="--danger-dark" v-if="!value"></v-icon>
			<v-icon name="open_in_new" color="--main-primary-color" v-else-if="existing"></v-icon>
			<v-icon name="cloud_download" color="--main-primary-color" v-else-if="!existing"></v-icon>
		</span>
		
		<v-modal 
			v-if="modal" 
			:title="`URL Importer Preview - ${ content.sitename }`"
			:buttons="buttons"
			@close="onCloseModal"
			@done="onCloseModal">
			<div class="interface-url-modal" v-if="content">
				<header class="modules-divider">
					<h2 class="modules-divider">{{ content.title }}</h2>
					<hr />
					<p class="modules-divider">{{ content.description }}</p>
				</header>
				
				<header class="modules-divider" v-if="content.image">
					<h4 class="modules-divider">Main Image</h4>
					<hr />
					<p class="modules-divider">This image should be uploaded automatically depending on the interface settings. Check the existing files if the file was upload.</p>
				</header>
				<figure class="interface-url-figure bg-pattern" v-if="content.content">
					<img class="interface-url-image" :src="content.image" :alt="content.image">
					<figcaption>{{ content.image }}<br><small>If the image above is not shown in the existing Files, copy the Image URL and use the File Uploader to upload the image...</small></figcaption>
				</figure>
				
				<header class="modules-divider" v-if="content.image">
					<h4 class="modules-divider">Available Text Content</h4>
					<hr />
					<p class="modules-divider">You may copy any part or all of the text in the content below and place in the content interface.</p>
				</header>
				<aside class="interface-url-html body" v-if="content.content">
					<p v-for="paragraph in content.content" v-html="paragraph"></p>
				</aside>
				
				<header class="modules-divider" v-if="content.image">
					<h4 class="modules-divider">Additional Images</h4>
					<hr />
					<p class="modules-divider">These images are not uploaded automatically, instead you will have to use the URL to (download and) upload the image.</p>
				</header>
				<figure class="interface-url-figure sm bg-pattern" v-if="content.images" v-for="image in content.images">
					<img class="interface-url-image" :src="image" :alt="image">
					<figcaption>{{ image }}<br><small>Copy the Image URL and use the File Uploader to upload the image...</small></figcaption>
				</figure>
			</div>
		</v-modal>
				
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
				buttons: {
					done: {
						text: "Close Preview"
					}
				},
				content: null,
				currentValue: null,
				existing: false,
				loading: false,
				modal: false,
				url: null,
				value: null
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
				else this.value = null;
			},
			load (value) {
				if (this.readonly === true || !this.value) return;
				
				if (this.existing) return this.modal = true;
				
				this.loading = true;
												
				this.$api.api.get('/custom/metadata/import', {
					url: this.value,
					encode: false,
					params: {
						images: this.options.images || 3,
						backgrounds: this.options.backgroundImages || 3
					}
				})
				.then((response) => {
					
					this.url = this.value;
					
					this.loading = false;
					
					response.description = response.description || response.sitename;
										
					this.content = {...response};
					
					this.modal = true;
					
					this.$events.emit("success", {
						notify: `Resource Downloaded!`
					});
					
					if (response.url) this.$emit('input', response.url);
					
					let $title = document.querySelector(`[data-field="${ this.options.mirroredTitle }"]`);
					let title = this.values[this.options.mirroredTitle];
					let isTitle = $title && !title && !this.existing && this.options.mirroredTitle && response.title;
					
					let $description = document.querySelector(`[data-field="${ this.options.mirroredDescription }"]`);
					let description = this.values[this.options.mirroredDescription];
					let isDescription = $description && !description && !this.existing && this.options.mirroredDescription && response.description;
					
					let $content = document.querySelector(`[data-field="${ this.options.mirroredContent }"]`);
					let content = this.values[this.options.mirroredContent];
					let isContent = $content && !content && !this.existing && this.options.mirroredContent && response.content;
					
					let $image = document.querySelector(`[data-field="${ this.options.mirroredImage }"]`);
					let image = this.values[this.options.mirroredImage];
					let isImage = $image && !image && !this.existing && this.options.uploadImage && response.image;
															
					if (isTitle) this.$emit('setfield', { field: this.options.mirroredTitle, value: response.title });
					
					if (isDescription) this.$emit('setfield', { field: this.options.mirroredDescription, value: response.description });
					
					if (isContent) this.$emit('setfield', { field: this.options.mirroredContent, value: response.content });
															
					if (isImage) this.upload(response, image, $image);
					
					this.existing = true;
				})
				.catch((error) => {
					this.error = error;
					
					this.loading = false;
				});
			},
			onCloseModal () {
				this.modal = false;
			},
			preview () {
				let element = document.querySelector(`[data-field="${ this.options.mirroredImage }"]`).querySelector('.uploader');
				
				element.innerHTML = `<img src="${ response.image }" alt="custom-interface-url-image" class="custom-interface-url-image">`;
			},
			upload (response, image, $image) {	
				let params = {
					data: response.image,
					title: response.title,
					description: response.description,
					filename_download: response.imageinfo.basename
				};
				
				if (this.options.mirroredImageSettings) params = {...this.options.mirroredImageSettings, ...params};
				
				if (response.sitename && Array.isArray(params.tags)) params.tags.push(response.sitename);
				
				this.$api.api.post('/files', params)
				.then((file) => {
										
					this.$events.emit("warning", {
						notify: `Image Resource Uploaded... Select the image with the id and title -${ file.data.id }: ${ response.title }- from the existing files!`
					});
										
					if (file.data.id && !image && this.options.mirroredImage) this.$emit('setfield', { field: this.options.mirroredImage, value: file.data.id });
				})
				.catch((error) => {
					this.error = error;
				});
			},
			stripHTML (html) {
				html = html.replace(/></g, '> <');
				
				let div = document.createElement("div");
				
				div.innerHTML = html.replace(/<img .*?>/g, "").replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, "");
				
				let text = div.textContent || div.innerText || "";
				
				return text.trim().replace(/\n\s*\n/g, '<br>');
			}
		},
		mounted () {			
			if (this.value) this.existing = this.url === this.value;
			
			if (this.value) this.currentValue = this.value;
		}
	};
</script>

<style lang="scss">	
	.interface-url {
		position: relative;
	}
	.interface-url .v-input input {
		padding-right: 70px;
	}
	.interface-url .v-input .char-count {
		right: 50px;
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
	.interface-url-modal {
		padding: 2rem;
		line-height: 1.6;
	}
	.interface-url-image {
		display: block !important;
		max-width: 100% !important;
		max-height: 480px !important;
		margin: 0 auto;
		background-color: white;
	}
	.interface-url-figure {
		margin: 1rem 0;
	}
	.interface-url-figure figcaption {
		padding: 1rem;
		background-color: var(--blue-grey-800);
		text-align: center;
	}
	.v-modal {
		animation: fadeIn 650ms ease;
	}
	aside.interface-url-html {
		border: 2px solid var(--input-border-color);
		display: block;
		width: 100%;
		max-height: 600px;
		margin: 1rem 0;
		padding: 1rem;
		overflow: scroll;
	}
	aside.interface-url-html p {
		margin-bottom: 1rem;
	}
	aside.interface-url-html p:last-child {
		margin-bottom: 0;
	}
	.modules-divider:not(:first-child) {
		margin-top: 3.5rem;
		margin-bottom: 0.5rem;
	}
</style>
