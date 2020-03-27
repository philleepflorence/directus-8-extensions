<template>
	<div class="table">
		<div class="header">
			<div class="row">
				<div class="field" :data-type="field.type" :style="`width: ${ flexBasis }%;`" v-for="field in headers">{{ field.text }}</div>
			</div>
		</div>
		<div class="body">
			<div class="group" v-for="row in rows" @click.stop.prevent="onClickDetails">
				<div class="row">
					<div class="field" :data-type="field.type" :style="`width: ${ flexBasis }%;`" v-for="field in headers">
						<div class="value">{{ value(row, field.value) }}</div>
					</div>
				</div>
				<div class="details animated fadeIn">
					<section class="details-section" v-for="detail in row">
						<h5 class="details-title">{{ detail.key }}</h5>
						<div class="details-content" v-html="detail.value"></div>
					</section>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import { get, size } from 'lodash';
	
	export default {
		name: 'AppTable',
		props: [
			"headers",
			"rows"
		],
		data () {
			return {
				$details: null,
				$row: null
			};
		},
		computed: {
			flexBasis () {
				let len = size(this.headers);
				
				return Math.round(100 / len);
			}
		},
		methods: {
			onClickDetails (event) {
				if (this.$details) this.$details.classList.remove('active');
				if (this.$row) this.$row.classList.remove('active');
				
				let details = event.currentTarget.querySelector('.details');
				let row = event.currentTarget.querySelector('.row');
				
				if (!details || !row) return false;
				
				if (this.$details !== details) {
					details.classList.add('active');
					row.classList.add('active');
					
					this.$details = details;					
					this.$row = row;
				}
				else {
					this.$details = null;
					
					this.$row = null;
				}				
			},
			value (row, value) {
				return get(row, value);
			}
		},
		mounted () {
			
		}
	}
</script>

<style lang="scss">
	.table {
		background-color: rgba(white, 0.01);
		border: var(--input-border-width) solid var(--table-row-border-color);
		border-radius: var(--border-radius);
		border-spacing: 0;
		border-bottom: none;
		width: 100%;
		
		.header {
			border-bottom: 2px solid var(--table-row-border-color);
			height: calc(var(--input-height) - 2px);
			
			.row {
				background-color: rgba(white, 0.1);
				height: calc(var(--input-height) - 2px);
				
				.field {
					color: var(--blue-grey-500);
					font-family: var(--main-font-accent);
					font-weight: 500;
				}
			}
		}
		.row {
			display: flex;
			align-items: center;
			transition: all 400ms ease;
			
			.field {
				padding: 5px 5px;	
				
				.value {
					overflow: hidden;
					white-space: nowrap;
					text-overflow: ellipsis;
				}			
			}
		}				
		.details {
			display: none;
			background-color: rgba(black, 0.1);
			border-bottom: 2px solid var(--table-row-border-color);
			padding: 1.5rem 5px 0.5rem 5px;
			
			&.active {
				display: block;
			}
			
			.details-section {
				padding-bottom: 1.5rem;
				
				.details-title {
					color: var(--blue-grey-400);
					font-family: var(--main-font-accent);
					font-size: 1rem;
					margin-bottom: 0.3rem;
				}
				
				.details-content {
					position: relative;
					white-space: pre-wrap;
				}
			}
		}
		.body {
			.row {
				cursor: pointer;
				position: relative;
				height: calc(var(--input-height) - 2px);
				border-bottom: 2px solid var(--table-row-border-color);
				
				&:hover, &:active, &.active {
					background-color: var(--main-primary-color);
					color: white !important;
				}
			}
		}
	}
</style>