import grammar from 'language-grammar-api';

module.exports = {
	data () {
		return {
			grammar: null,
			grammar_params: {
				endpoint: 'https://languagetool.org/api/v2'
			}
		};
	},	
	methods: {
		async grammarCheck (text) {
			this.build("Processing...");
			
			const check = await this.grammar.check({
				language: 'en-US',
				text: text
			});
			
			this.build("Processed...OK!");
			
			if (!Array.isArray(check.matches) || !check.matches,length) return text;
			
			let string = "";
			let find = [];
			let replace = [];
			let messages = [];
			
			check.matches.forEach((match) => {
				find.push(match.replacements[0].value);
				replace.push(text.substring(match.offset, match.length));
				messages.push(match.message);
			});
			
			find.forEach((str, index) => {
				string = text.replace(str, replace[index]);
			});
			
			messages = [...new Set(messages)];
			
			this.build("Processed...UPDATED!", messages.join("<br>"));			
							
			return string;
		}
	},
	beforeMount () {
		if (this.options.grammar) this.grammar = new grammar(this.grammar_params);
	}
};