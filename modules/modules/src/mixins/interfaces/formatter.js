import {
	camelCase, 
	capitalize,
	kebabCase, 
	lowerCase, 
	pascalCase, 
	snakeCase, 
	startCase
} from 'lodash';

module.exports = {
	methods: {
		formatter (string, format) {
			const titleCase = (str) => {
				const lowerRegex = /^(a|an|and|as|at|but|by|en|for|if|in|nor|of|on|or|per|the|to|v.?|vs.?|via)$/i;
				const alphaRegex = /([A-Za-z0-9\u00C0-\u00FF])/;
				const splitRegex = /([ :–—-])/;
				
				str = str.split(splitRegex).map((current, index, array) => {
					let smallWord = current.search(lowerRegex) > -1,
						firstLastWord = index !== 0 && index !== array.length - 1,
						titleEndSubtitleStart = array[index - 3] !== ':' && array[index + 1] !== ':',
						smallWordHyphen = (array[index + 1] !== '-' || (array[index - 1] === '-' && array[index + 1] === '-')),
						capitalized = current.substr(1).search(/[A-Z]|\../) > -1,
						url = array[index + 1] === ':' && array[index + 2] !== ''
					
					if (smallWord && firstLastWord && titleEndSubtitleStart && smallWordHyphen) return current.toLowerCase();
				
					if (capitalized || url) return current;
				
					return current.replace(alphaRegex, match => match.toUpperCase());
				});
				
				return str.join(' ');
			};
			
			const formats = {
				camelCase: string => camelCase(string),
				capitalize: string => capitalize(string),
				kebabCase: string => kebabCase(string),
				lowerCase: string => lowerCase(string),
				pascalCase: string => pascalCase(string),
				snakeCase: string => snakeCase(string),
				startCase: string => startCase(string),
				titleCase: string => titleCase(string)
			};
			
			return formats[format](string);
		}
	}
};