import {
	get,
	unescape
} from 'lodash';

module.exports = {
	data () {
		return {
			analytics: {},
			loading: true
		};
	},
	computed: {
		currentProjectKey () {
			return this.$store.state.currentProjectKey;
		},
		locale () {
			if (this.user.locale) return this.user.locale;
			
			return get(this.$store.state, 'settings.values.default_locale');
		},
		user () {
			return this.$store.state.currentUser;
		}
	},
	methods: {
		content (input) {
			let translation = get(this.contents, this.locale);
				translation = translation || get(this.contents, 'en-US');

			return get(translation, input);
		},
		load (module) {
			this.loading = true;
				
			this.$api.api.get(`/custom/analytics/${ module }`).then((response) => {
				
				this.loading = false;
				
				this.analytics = response;
				
			}).catch((error) => {
				
				this.error = error;
				
				this.loading = false;
			});
		},
		subtitle (item, key) {
			let analytics = get(this.analytics, `${ key }.total`);
			
			if (analytics) return `${ analytics } ${ item.analytics }`;
			
			return item.description;
		},
		translation (translations, key, value) {
			if (!Array.isArray(translations)) return value;
			
			let translation = translations.filter(item => item.locale == this.user.locale);
				translation = translation.pop();
				
			if (!key) return translation;
			
			return translation[key] || value;
		},
		unescape (string) {
			return unescape(string);
		}
	},
	beforeMount () {
		let $dashboard = document.querySelector('[href="#/app/ext/dashboard"]');
		let $modules = document.querySelector('[href="#/app/ext/modules"]');
		let $active = 'router-link-active';
		
		if (
			$modules 
			&& $dashboard
			&& !$modules.classList.contains($active) 
			&& !$dashboard.classList.contains($active)
		) $modules.classList.add($active);
	}
};