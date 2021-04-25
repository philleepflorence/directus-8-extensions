import {
	forEach, 
	get, 
	set
} from 'lodash';

module.exports = {
	computed: {
		choices () {
			let fields = this.$props.fields || get(this.collections[this.collection], "fields");
			let choices = {};
			
			if (!fields) return null;
			
			forEach(fields, (item, field) => {
				let value = get(this.values, field);
				
				if (this.mirrored.includes(field) && value) {
					let options = get(item, 'options.choices');
					
					if (options) set(choices, field, get(item, `options.choices.${ value }`));
					else set(choices, field, value);
				}
			});
			
			return choices;
		},
		collection () {
			return this.$route.params.collection;
		},
		collections () {
			return this.$store.state.collections;
		},
		mirrored () {
			return this.options.mirrored || [];
		}
	}
};