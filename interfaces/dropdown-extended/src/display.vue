<template>
	<span class="no-wrap">{{ displayValue }}</span>
</template>

<script>
	import mixin from '@directus/extension-toolkit/mixins/interface';
	import { startCase } from 'lodash';
	export default {
		mixins: [mixin],
		computed: {
			choices() {
				return typeof this.options.choices === 'string'
					? JSON.parse(this.options.choices)
					: this.options.choices;
			},
			displayValue() {
				let value = '--';
				
				if (this.options.formatting && this.choices && this.choices[this.value]) value = this.choices[this.value];
				else value = this.value;
				
				if (this.options.format_value) value = startCase(value);
								
				return value;
			}
		}
	};
</script>