/**
 * Internal dependencies
 */

import { __ } from '@wordpress/i18n';
import {
	ACTION_URL,
	ACTION_PASS,
	ACTION_NOTHING,
	ACTION_RANDOM,
	ACTION_ERROR,

	MATCH_URL,
	MATCH_LOGIN,
	MATCH_REFERRER,
	MATCH_AGENT,
	MATCH_COOKIE,
	MATCH_HEADER,
	MATCH_CUSTOM,
	MATCH_ROLE,
	MATCH_SERVER,
	MATCH_IP,
	MATCH_PAGE,
	MATCH_LANGUAGE,
} from '../../state/redirect/selector';

export const getMatches = () => [
	{
		value: MATCH_URL,
		label: __( 'URL only', 'redirection' ),
	},
	{
		value: MATCH_LOGIN,
		label: __( 'URL and login status', 'redirection' ),
	},
	{
		value: MATCH_ROLE,
		label: __( 'URL and role/capability', 'redirection' ),
	},
	{
		value: MATCH_REFERRER,
		label: __( 'URL and referrer', 'redirection' ),
	},
	{
		value: MATCH_AGENT,
		label: __( 'URL and user agent', 'redirection' ),
	},
	{
		value: MATCH_COOKIE,
		label: __( 'URL and cookie', 'redirection' ),
	},
	{
		value: MATCH_IP,
		label: __( 'URL and IP', 'redirection' ),
	},
	{
		value: MATCH_SERVER,
		label: __( 'URL and server', 'redirection' ),
	},
	{
		value: MATCH_HEADER,
		label: __( 'URL and HTTP header', 'redirection' ),
	},
	{
		value: MATCH_CUSTOM,
		label: __( 'URL and custom filter', 'redirection' ),
	},
	{
		value: MATCH_PAGE,
		label: __( 'URL and WordPress page type', 'redirection' ),
	},
	{
		value: MATCH_LANGUAGE,
		label: __( 'URL and language', 'redirection' ),
	},
];

export const getActions = () => [
	{
		value: ACTION_URL,
		label: __( 'Redirect to URL', 'redirection' ),
	},
	{
		value: ACTION_RANDOM,
		label: __( 'Redirect to random post', 'redirection' ),
	},
	{
		value: ACTION_PASS,
		label: __( 'Pass-through', 'redirection' ),
	},
	{
		value: ACTION_ERROR,
		label: __( 'Error (404)', 'redirection' ),
	},
	{
		value: ACTION_NOTHING,
		label: __( 'Do nothing (ignore)', 'redirection' ),
	},
];

export const getHttpCodes = () => [
	{
		value: '301',
		label: __( '301 - Moved Permanently', 'redirection' ),
	},
	{
		value: '302',
		label: __( '302 - Found', 'redirection' ),
	},
	{
		value: '303',
		label: __( '303 - See Other', 'redirection' ),
	},
	{
		value: '304',
		label: __( '304 - Not Modified', 'redirection' ),
	},
	{
		value: '307',
		label: __( '307 - Temporary Redirect', 'redirection' ),
	},
	{
		value: '308',
		label: __( '308 - Permanent Redirect', 'redirection' ),
	},
];

export const getHttpError = () => [
	{
		value: '400',
		label: __( '400 - Bad Request', 'redirection' ),
	},
	{
		value: '401',
		label: __( '401 - Unauthorized', 'redirection' ),
	},
	{
		value: '403',
		label: __( '403 - Forbidden', 'redirection' ),
	},
	{
		value: '404',
		label: __( '404 - Not Found', 'redirection' ),
	},
	{
		value: '410',
		label: __( '410 - Gone', 'redirection' ),
	},
	{
		value: '418',
		label: __( "418 - I'm a teapot", 'redirection' ),
	},
	{
		value: '451',
		label: __( '451 - Unavailable For Legal Reasons', 'redirection' ),
	},
	{
		value: '500',
		label: __( '500 - Internal Server Error', 'redirection' ),
	},
	{
		value: '501',
		label: __( '501 - Not implemented', 'redirection' ),
	},
	{
		value: '502',
		label: __( '502 - Bad Gateway', 'redirection' ),
	},
	{
		value: '503',
		label: __( '503 - Service Unavailable', 'redirection' ),
	},
	{
		value: '504',
		label: __( '504 - Gateway Timeout', 'redirection' ),
	},
];

export const getAllHttpCodes = () => getHttpCodes().concat( getHttpError() );

export const getSourceFlags = () => [
	{
		value: 'flag_regex',
		label: __( 'Regex', 'redirection' ),
	},
	{
		value: 'flag_trailing',
		label: __( 'Ignore Slash', 'redirection' ),
	},
	{
		value: 'flag_case',
		label: __( 'Ignore Case', 'redirection' ),
	},
];

export const FLAG_REGEX = 0;
export const FLAG_TRAILING = 1;
export const FLAG_CASE = 2;

export const getSourceQuery = () => [
	{
		value: 'exactorder',
		label: __( 'Exact match', 'redirection' ),
	},
	{
		value: 'exact',
		label: __( 'Exact match in any order', 'redirection' ),
	},
	{
		value: 'ignore',
		label: __( 'Ignore all parameters', 'redirection' ),
	},
	{
		value: 'pass',
		label: __( 'Ignore & pass parameters to the target', 'redirection' ),
	},
];
