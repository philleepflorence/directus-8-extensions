<template>
	<div class="table">
		
		<div class="header">
			<div class="row" v-if="grouped">
				<div class="field" :data-type="field.type" :style="`width: ${ flexBasis }%;`" v-for="field in headers">{{ field.text }}</div>
			</div>
			<div class="row" v-else>
				<div class="field" data-type="string" :style="`width: ${ flexBasis }%;`" v-for="field in headers">{{ field }}</div>
			</div>
		</div>
		
		<div class="body" v-if="grouped">
			<div class="group" v-for="row in rows" @click.stop.prevent="onClickDetails($event, row)">
				<div class="row">
					<div class="field" :data-type="field.type" :style="`width: ${ flexBasis }%;`" v-for="field in headers">
						<div class="value">{{ value(row, field.value) }}</div>
					</div>
				</div>
				<div class="details animated fadeIn" v-if="details">
					<section class="details-section" v-for="(detail, field) in row">
						<h5 class="details-title">{{ formatKey(detail, field) }}</h5>
						<div class="details-content" v-html="formatValue(detail, field)"></div>
					</section>
				</div>
			</div>
		</div>
		
		<div class="body" v-else>
			<div class="row" v-for="row in rows" @click.stop.prevent="onClickDetails($event, row)">
				<div class="field" data-type="string" :style="`width: ${ flexBasis }%;`" v-for="field in headers">
					<div class="value">{{ value(row, field) }}</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import { get, size, startCase } from 'lodash';
	
	export default {
		name: 'AppTable',
		props: [
			"details",
			"headers",
			"rows"
		],
		data () {
			return {
				grouped: false,
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
			formatKey (detail, field) {
				if (get(this.details, 'key')) return get(detail, this.details.key);
				
				return startCase(field);
			},
			formatValue (detail, field) {
				if (get(this.details, 'value')) return get(detail, this.details.value);
				
				let currOption = get(this.details, `fields.${ field }`);
				
				if (currOption) return get(detail, currOption);
				
				if (detail !== "null" || typeof detail === "undefined") return detail;
				
				return '--';
			},
			onClickDetails (event, input) {
				if (!this.details) return this.$emit('click', input);
				
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
			value (row, input) {
				let value = get(row, input);
				
				if (!size(value)) return '--';
				
				return value;
			}
		},
		created () {
			this.grouped = typeof get(this.headers, 0) === 'object' && size(get(this.headers, 0)) > 0;
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
					text-transform: capitalize;
				}
			}
		}
		.row {
			display: flex;
			align-items: center;
			transition: all 400ms ease;
			padding: 0 5px;
			
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
			padding: 1.5rem 10px 0.5rem 10px;
			
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