<template>
	<ul class="modules-components-tree">
		<li class="modules-components-tree" v-for="(row, index) in tree" v-if="row">
			<header class="modules-components-tree" v-if="row.name" @click="onClickToggle" :data-type="row.type" :data-index="index">
				<span data-status="open" v-if="row.children"><v-icon name="arrow_right"></v-icon></span>
				<span data-status="close" v-if="row.children"><v-icon name="arrow_drop_down"></v-icon></span>
				<span data-status="toggle" v-if="!row.children"><v-icon name="more_horiz"></v-icon></span>
				<p v-if="row.children">{{ row.name }}</p>
				<p v-else-if="row && row.size">
					<span class="name">{{ row.name }}</span> 
					<span class="muted">{{ row.type }}</span>
					<span class="muted">{{ row.size.value }}{{ row.size.unit }}</span>
					<span class="muted">{{ parseDate(row.modified) }}</span>
				</p>
			</header>
			<div class="modules-components-tree" v-if="row.children">
				<v-tree :tree="row.children"></v-tree>
			</div>
		</li>
	</ul>
</template>

<script>
	import { get } from 'lodash';

	export default {
		name: 'v-tree',
		props: [
			"tree"
		],
		computed: {
			
		},
		methods: {
			onClickToggle (e) {
				if (!e.currentTarget) return false;
				
				let index = e.currentTarget.getAttribute('data-index');
				let type = e.currentTarget.getAttribute('data-type');
				
				if (type === "file") return false;
				
				let $parent = e.currentTarget.closest('li.modules-components-tree');
				
				$parent.classList.toggle('active');	
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
				cursor: pointer;
				
				[data-status] {
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
				
				p {
					display: flex;
					align-items: center;
					line-height: 24px;
					flex-grow: 1;
					font-size: 1rem;
					
					span.name {
						flex-grow: 1;
						padding-right: 0.5rem;
					}
					
					span.muted {
						color: var(--blue-grey-600);
						padding-left: 0.5rem;
						min-width: 150px;
						text-align: right;
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