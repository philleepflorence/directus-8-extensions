<template>
	<div class="interface-wysiwyg">
		
		<Editor
			ref="editorElement"
			:init="initOptions"
			:value="value"
			@onKeyUp="planToUpdateValue"
			@onExecCommand="updateValue"
			@onBlur="updateValue"
			@onPaste="updateValue"
			@onUndo="updateValue"
			@onRedo="updateValue">
		</Editor>
		
		<v-modal
			v-if="newInlineFile"
			title="File Upload"
			:buttons="{
				done: {
					text: $t('done')
				}
			}"
			@close="onCloseFilesModal"
			@done="callbackSelect">
			<div class="body">
				<v-ext-input
					id="file"
					name="file"
					:required="false"
					:readonly="false"
					:options="fileInputOptions"
					type="file"
					datatype="INT"
					:value="selectedFile"
					:relation="relation"
					:fields="null"
					collection="directus_files"
					:values="null"
					:length="10"
					:new-item="newItem"
					width="full"
					@input="selectedFile = $event">
				</v-ext-input>
			</div>
		</v-modal>
		
		<v-modal 
			v-if="fileSizes" 
			title="Files URL Loader"
			@close="onCloseFilesModal"
			@done="callbackSelect"
			:buttons="buttons">
			<div class="body">
				<header class="modules-divider">
					<h2 class="modules-divider">Information</h2>
					<hr />
					<p class="modules-divider">To use an existing image, select the size and filter images by title.</p>
					<p class="modules-divider">Contained images show the whole image, keeping the original image aspect ratio.</p>
					<p class="modules-divider">Cropped images are cropped to the width and height shown.</p>
				</header>
				<div class="form">
					<v-simple-select
						class="interface-wysiwyg-whitelist-select interface-wysiwyg-input"
						placeholder="Select Image Size and Property..." 
						@input="onSelectSize">
						<option value="original">Original - Use the original image size and do not resize!</option>
						<option 
							v-for="option in sizes"  
							:value="option.key">
							{{ `${ option.width } - ${ fitFormat(option.fit, option.width, option.height) }` }}
						</option>
					</v-simple-select>
					<v-input
						v-if="fileURL"
						class="interface-wysiwyg-files-url animated fadeIn interface-wysiwyg-input"
						:value="fileURL"
						readonly>
					</v-input>
					<v-ext-input
						v-if="imageSize"
						id="file"
						name="file"
						:required="false"
						:readonly="false"
						:options="fileInputOptions"
						type="file"
						datatype="INT"
						:value="selectedFile"
						:relation="relation"
						:fields="null"
						collection="directus_files"
						:values="null"
						:length="10"
						:new-item="newItem"
						width="full"
						@input="onInputFile">
					</v-ext-input>
				</div>
			</div>
		</v-modal>
		
	</div>
</template>

<script>
	import mixin from '@directus/extension-toolkit/mixins/interface';
	
	import 'tinymce/tinymce';
	import 'tinymce/themes/silver';
	import 'tinymce/plugins/media/plugin';
	import 'tinymce/plugins/table/plugin';
	import 'tinymce/plugins/hr/plugin';
	import 'tinymce/plugins/lists/plugin';
	import 'tinymce/plugins/image/plugin';
	import 'tinymce/plugins/imagetools/plugin';
	import 'tinymce/plugins/link/plugin';
	import 'tinymce/plugins/pagebreak/plugin';
	import 'tinymce/plugins/code/plugin';
	import 'tinymce/plugins/insertdatetime/plugin';
	import 'tinymce/plugins/autoresize/plugin';
	import 'tinymce/plugins/paste/plugin';
	import 'tinymce/plugins/preview/plugin';
	import 'tinymce/plugins/fullscreen/plugin';
	import 'tinymce/plugins/directionality/plugin';
	
	import Editor from '@tinymce/tinymce-vue';
	import { debounce, template, templateSettings } from 'lodash';
	
	function cssVar(name) {
		return getComputedStyle(document.body).getPropertyValue(name);
	}
	
	export default {
		name: "InterfaceWysiwygExtended",
		components: {
			Editor
		},
		mixins: [mixin],
		data() {
			return {
				buttons: {
					done: {
						text: "Get Image URL"
					}
				},
				fileSizes: false,
				fileURL: null,
				imageSize: null,
				newInlineFile: false,
				selectedFile: null,
				selectedName: null,
				callbackSelect: () => {},
				sizes: JSON.parse(this.$store.state.settings.values.asset_whitelist)
			};
		},
		computed: {
			fileInputOptions() {
				return {
					viewOptions: {
						content: 'description',
						src: 'data',
						subtitle: 'type',
						title: 'title'
					},
					viewType: 'cards'
				};
			},
			initOptions() {
				const styleFormats = this.getStyleFormats();
				let toolbarString = this.options.toolbar.join(' ');
	
				if (styleFormats) {
					toolbarString += ' styleselect';
				}
	
				return {
					skin: false,
					skin_url: false,
					content_css: false,
					content_style: this.contentStyle,
					plugins:
						'media table hr lists image link pagebreak code insertdatetime autoresize paste preview fullscreen directionality',
					branding: false,
					max_height: 1000,
					elementpath: false,
					statusbar: false,
					menubar: false,
					convert_urls: false,
					readonly: this.readonly,
					extended_valid_elements: 'audio[loop],source',
					toolbar: toolbarString,
					style_formats: styleFormats,
					file_picker_callback: this.onSelectFile,
					...this.options.tinymce_options
				};
			},
			contentStyle() {
				return `
			        body {
			          color: ${cssVar('--input-text-color')};
			          background-color: ${cssVar('--input-background-color')};
			          margin: 20px;
			          font-family: 'Roboto', sans-serif;
			          -webkit-font-smoothing: antialiased;
			          text-rendering: optimizeLegibility;
			          -moz-osx-font-smoothing: grayscale;
			        }
			        h1 {
			          font-family: 'Merriweather', serif;
			          font-size: 44px;
			          line-height: 52px;
			          font-weight: 300;
			          margin-bottom: 0;
			        }
			        h2 {
			          font-size: 34px;
			          line-height: 38px;
			          font-weight: 600;
			          margin-top: 60px;
			          margin-bottom: 0;
			        }
			        h3 {
			          font-size: 26px;
			          line-height: 31px;
			          font-weight: 600;
			          margin-top: 40px;
			          margin-bottom: 0;
			        }
			        h4 {
			          font-size: 22px;
			          line-height: 28px;
			          font-weight: 600;
			          margin-top: 40px;
			          margin-bottom: 0;
			        }
			        h5 {
			          font-size: 18px;
			          line-height: 26px;
			          font-weight: 600;
			          margin-top: 40px;
			          margin-bottom: 0;
			        }
			        h6 {
			          font-size: 16px;
			          line-height: 24px;
			          font-weight: 600;
			          margin-top: 40px;
			          margin-bottom: 0;
			        }
			        p {
			          font-family: 'Merriweather', serif;
			          font-size: 16px;
			          line-height: 32px;
			          margin-top: 20px;
			          margin-bottom: 20px;
			        }
			        a {
			          color: #546e7a;
			        }
			        ul,ol {
			          font-family: 'Merriweather', serif;
			          font-size: 18px;
			          line-height: 34px;
			          margin: 24px 0;
			        }
			        ul ul,
			        ol ol,
			        ul ol,
			        ol ul {
			          margin: 0;
			        }
			        b,strong {
			          font-weight: 600;
			        }
			        code {
			          font-size: 18px;
			          line-height: 34px;
			          padding: 2px 4px;
			          font-family: 'Roboto Mono', monospace;
			          background-color: #eceff1;
			          border-radius: 3px;
			          overflow-wrap: break-word;
			        }
			        pre {
			          font-size: 18px;
			          line-height: 24px;
			          padding: 20px;
			          font-family: 'Roboto Mono', monospace;
			          background-color: #eceff1;
			          border-radius: 3px;
			          overflow: auto;
			        }
			        blockquote {
			          font-family: 'Merriweather', serif;
			          font-size: 18px;
			          line-height: 34px;
			          border-left: 2px solid #546e7a;
			          padding-left: 10px;
			          margin-left: -10px;
			          font-style: italic;
			        }
			        video,
			        iframe,
			        img {
			          max-width: 100%;
			          border-radius: 3px;
			          height: auto;
			        }
			        hr {
			          border: 0;
			          margin-top: 52px;
			          margin-bottom: 56px;
			          text-align: center;
			        }
			        hr:after {
			          content: "...";
			          font-size: 28px;
			          letter-spacing: 16px;
			          line-height: 0;
			        }
			        table {
			          border-collapse: collapse;
			        }
			        table th,
			        table td {
			          border: 1px solid #cfd8dc;
			          padding: 0.4rem;
			        }
			        figure {
			          display: table;
			          margin: 1rem auto;
			        }
			        figure figcaption {
			          color: #999;
			          display: block;
			          margin-top: 0.25rem;
			          text-align: center;
			        }
				`;
			}
		},
		created () {
			_.templateSettings.interpolate = /{{([\s\S]+?)}}/g;
			
			this.planToUpdateValue = debounce(this.updateValue, 200);
		},
		methods: {
			fitFormat (input, width, height) {							
				if (input === "contain") return `Image is contained within a maximum of ${ width }x${ height }`;
				else return `Image is cropped and resized to ${ width }x${ height }`;
			},
			updateValue () {
				const editor = this.$refs.editorElement.editor;
				const newValue = editor.getContent();
				this.$emit('input', newValue);
			},
			getStyleFormats () {
				if (Array.isArray(this.options.custom_formats) && this.options.custom_formats.length > 0) return this.options.custom_formats;
	
				return null;
			},
			onCloseFilesModal () {
				this.fileSizes = false;
				
				/*
					restore tinymce dialog display
				*/ 
				
				document.querySelector('.tox.tox-tinymce-aux').style.display = 'block';
			},
			async onInputFile (input) {
				
				/*
					When a file is selected, get the file object if an ID is the input
				*/
				
				if (input && typeof input === "object" && input.id) this.selectedFile =  input;
				else if (input) {
					const { data: file } = await this.$api.getItem('directus_files', id);
					
					this.selectedFile = file;
				}
				
				if (!this.selectedFile) return false;
				
				let filepath;
				let size = this.sizes.filter(size => size.key === this.imageSize);
					size = size ? size[0] : size;			
				
				if (this.options.cdn && !size) {
					filepath = `${ this.options.cdn }/originals/${ this.selectedFile.filename_disk }`;
				}
				else if (this.options.cdn && size) {
					let template = String(this.options.cdn_template || "app/thumbnails/{{width}}/{{height}}/{{fit}}").replace(/^\/+/, '');
					let compiled = _.template(template);
					let path = compiled(size);
						
					filepath = `${ this.options.cdn }/${ path }/${ this.selectedFile.filename_disk }`;
				}
				else if (!this.options.cdn && !size) {
					filepath = `${ window.location.origin }${ this.selectedFile.data.asset_url }`;
				}
				else if (!this.options.cdn && size) {
					filepath = this.selectedFile.data.thumbnails.filter(thumbnail => thumbnail.key === this.imageSize);											
					filepath = filepath[0].url;
				}
				
				if (filepath) this.fileURL = filepath;		
			},
			onSelectFile (callback) {
				this.fileSizes = true;
				
				/*
					Prevents file selection to be hidden by tinymce dialog
				*/
				
				document.querySelector('.tox.tox-tinymce-aux').style.display = 'none';
				
				this.callbackSelect = (a, b, c) => {
					
					/*
						Send TinyMCE Callback with the selected filepath and title
					*/
					
					callback(this.fileURL, { alt: this.selectedFile.title });
					
					/*
						Restore tinymce dialog display
					*/
					
					document.querySelector('.tox.tox-tinymce-aux').style.display = 'block';
					
					/*
						Empty the selectedfile so the file isn't selected again when you add an additional file
					*/ 
					
					this.selectedFile = null;
					this.fileSizes = false;
				};
			},
			onSelectSize (input) {
				let preview = this.$el.querySelector('.interface-wysiwyg-whitelist-select .preview .placeholder');
								
				if (preview) preview.innerHTML = `Current Image Size and Fit: ${input}`;
				
				this.imageSize = input;
			}
		}
	};
</script>

<style lang="scss" scoped>
	.body {
		padding: 20px;
	}	
	/*
		The content CSS is not scoped, but is also not needed.
		The CSS below can be referenced for other core editor styles we might want to include in our override.
		
		@import "~tinymce/skins/ui/oxide/content.css";
		@import "~tinymce/skins/content/default/content.css";
	*/
	@import './skin.css';
	@import './wysiwyg.css';
	
	.interface-wysiwyg {
		.form {
			.interface-wysiwyg-input {
				margin-bottom: var(--page-padding);
			}
		}
	}
</style>