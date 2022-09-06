/* global Redirectioni10n */
/**
 * External dependencies
 */

import { apiFetch } from '@wp-plugin-lib';

export const getModules = () => [
	{
		value: 1,
		label: 'WordPress',
	},
	{
		value: 2,
		label: 'Apache',
	},
	{
		value: 3,
		label: 'Nginx',
	},
];

export const getModuleName = ( moduleId ) => {
	const result = getModules().find( ( item ) => item.value === parseInt( moduleId, 10 ) );

	return result ? result.label : '';
};

export const getExportUrl = ( moduleId, modType ) =>
	Redirectioni10n.pluginRoot +
	'&sub=io&export=' +
	moduleId +
	'&exporter=' +
	modType +
	'&_wpnonce=' +
	apiFetch.nonceMiddleware.nonce;
