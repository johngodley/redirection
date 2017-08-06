/**
 * External dependencies
 */

export const getModules = () => [
	{
		value: 1,
		text: 'WordPress',
	},
	{
		value: 2,
		text: 'Apache',
	},
	{
		value: 3,
		text: 'Nginx'
	},
];

export const getModuleName = moduleId => {
	const result = getModules().find( item => item.value === parseInt( moduleId, 10 ) );

	return result ? result.text : '';
};
