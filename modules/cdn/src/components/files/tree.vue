<template>
	<ul class="modules-components-tree">
		<li :class="classParent" v-for="(row, index) in tree" v-if="row">
			<header class="modules-components-tree" v-if="row.name" :data-type="row.type" :data-index="index">
				<span data-status="open" v-if="row.children" @click="onClickToggle"><v-icon name="arrow_right"></v-icon></span>
				<span data-status="close" v-if="row.children" @click="onClickToggle"><v-icon name="arrow_drop_down"></v-icon></span>
				<span data-status="toggle" v-if="!row.children" @click="onClickToggle"><v-icon name="more_horiz"></v-icon></span>
				<p v-if="row.children">{{ row.name }}</p>
				<div v-else-if="row && row.size">
					<p>
						<span class="image" v-if="displayImage(row)">
							<img :src="row.cdn" :alt="row.name">
						</span> 
						<span class="name" v-else>{{ row.name }}</span> 
						<span class="muted">{{ row.type }}</span>
						<span class="muted">{{ row.size.value }}{{ row.size.unit }}</span>
						<span class="muted">{{ parseDate(row.modified) }}</span>						
					</p>	
					<aside class="link" v-if="link">
						<input type="text" :value="get(row, link)">
						<span class="copy" @click="onCopyClipboard"><v-icon name="content_copy"></v-icon></span>
					</aside>				
				</div>
			</header>
			<div class="modules-components-tree" v-if="row.children">
				<app-files-tree :tree="row.children" :mode="mode" :link="link"></app-files-tree>
			</div>
		</li>
	</ul>
</template>

<script>
	import { get } from 'lodash';

	export default {
		name: 'app-files-tree',
		props: [
			"link",
			"mode",
			"open",
			"tree"
			
		],
		computed: {
			classParent () {
				let classname = ['modules-components-tree'];
				
				if (this.open) classname.push('active');
				
				return classname;
			}
		},
		methods: {
			displayImage (input) {
				return this.mode === 'preview' && input.cdn && input.mime.indexOf('image/') === 0;
			},
			get (row, link) {
				return get(row, link);
			},
			onClickToggle (e) {
				if (!e.currentTarget) return false;
				
				let element = e.currentTarget.closest('.modules-components-tree');
				
				if (!element) return false;
				
				let index = element.getAttribute('data-index');
				let type = element.getAttribute('data-type');
				
				if (type === "file") {
					let $link = element.querySelector('aside.link');
					
					$link.classList.toggle('active');	
				}
				else {
					let $parent = element.closest('li.modules-components-tree');
					
					$parent.classList.toggle('active');						
				}
			},
			onCopyClipboard (e) {
				var input = e.currentTarget.parentElement.querySelector('input');
				
				input.select();
				input.setSelectionRange(0, 99999);
				
				document.execCommand("copy");
			},
			parseDate(input) {
				let d = new Date(input * 1000),
					month = '' + (d.getMonth() + 1),
					day = '' + d.getDate(),
					year = d.getFullYear();
				
				if (month.length < 2) month = '0' + month;
				if (day.length < 2) day = '0' + day;
				
				return [year, month, day].join('-');
			}
		},
		data () {
			return {
				details: null
			};
		}
	}
</script>

<style lang="scss">
	ul.modules-components-tree {
		position: relative;
		list-style-type: none;
		padding-left: 0;
		text-transform: lowercase !important;
		
		li.modules-components-tree {
			margin: 0.5rem 0;
			
			header.modules-components-tree {
				display: flex;
				align-items: center;						
				
				[data-status] {
					cursor: pointer;
					color: var(--main-primary-color);
					flex-shrink: 1;
					padding-top: 3px;
					min-width: 30px;
				}
				
				[data-status="close"] {
					display: none;
				}
				
				[data-status="details"],
				[data-status="toggle"] {
					color: var(--blue-grey-600);
				}
				
				div {
					position: relative;	
					display: block;
					width: 100%;
				}
				
				p {
					display: flex;
					align-items: center;
					line-height: 24px;
					flex-grow: 1;
					font-size: 1rem;
					
					span.name,
					span.image {
						flex-grow: 1;
						padding-right: 0.5rem;
					}
					
					span.image {
						img {
							display: block;
							max-width: 200px;
							height: auto;
						}
					}
					
					span.muted {
						color: var(--blue-grey-600);
						padding-left: 0.5rem;
						min-width: 150px;
						text-align: right;
					}
				}
					
				aside.link {
					position: absolute;
					top: 0;
					left: 0;
					display: none;					
					width: 100%;
					margin-top: -6px;
					
					&.active {
						display: block;
						animation: fadeIn 350ms ease;
					}
					
					input {
						display: block;
						height: 40px;
						width: 100%;
						padding: 0 3rem 0 1rem;
						background-color: var(--blue-grey-800);
						border: none;
						outline: none;
						
						&::selection {
							background-color: var(--main-primary-color);
						}
					}
					
					.copy {
						color: var(--blue-grey-600);
						position: absolute;
						cursor: pointer !important;
						right: 0.4rem;
						top: 50%;
						transform: translateY(-50%);
					}
				}
			}
			
			div.modules-components-tree {
				display: none;
			}
			
			ul.modules-components-tree {
				padding-left: 3rem;
			}
			
			&.active {
				& > header > [data-status="open"] {
					display: none !important;
				}
				
				& > header > [data-status="close"] {
					display: inline !important;
				}
				
				& > div.modules-components-tree {
					display: block !important;
				}
			}
		}
	}
</style>