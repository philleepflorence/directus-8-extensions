<template>
	<v-input
		type="number"
		:readonly="readonly"
		:placeholder="options.placeholder"
		:value="value"
		:icon-left="options.iconLeft"
		:icon-right="options.iconRight"
		:step="increment"
		:monospace="options.monospace"
		:min="options.min"
		:max="options.max"
		@input="onInput">
	</v-input>
</template>

<script>
	import mixin from '@directus/extension-toolkit/mixins/interface';
	export default {
		mixins: [
			mixin
		],
		computed: {
			increment () {
				if (this.options.step.length) return this.options.step;
				
				if (this.type === 'decimal') {
					const decimalPlaces = this.length && this.length.split(',')[1];
					
					return `0.${'1'.padStart(decimalPlaces, 0)}`;
				}
				
				return 1;
			}
		},
		methods: {
			onInput (input) {				
				if (this.options.min.length && Number(input) < Number(this.options.min)) {
					input = this.options.min;
					
					this.$el.querySelector('input').value = input;
				}
				
				if (this.options.max.length && Number(input) > Number(this.options.max)) {
					input = this.options.max;
					
					this.$el.querySelector('input').value = input;
				}
				
				if (this.options.maxlength.length && String(input).length > Number(this.options.maxlength)) {
					input = String(input).slice(0, this.options.maxlength);
					
					this.$el.querySelector('input').value = input;
				}
				
				this.$emit('input', input);				
			}
		}
	};
</script>

<style lang="scss" scoped>
	.v-input {
		width: 100%;
		max-width: var(--width-medium);
	}
</style>