module.exports = {
	root: true,
	'extends': 'wpcalypso/react',
	parser: 'babel-eslint',
	env: {
		browser: true,
		mocha: true,
		node: true
	},
	globals: {
		asyncRequire: true,
		PROJECT_NAME: true
	},
	rules: {
		camelcase: 0, // REST API objects include underscores
		'max-len': 0,
		'no-unused-expressions': 0, // Allows Chai `expect` expressions
		'wpcalypso/import-no-redux-combine-reducers': 0,
		'wpcalypso/jsx-classname-namespace': 0
	}
};
