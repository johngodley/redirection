/**
 * Internal dependencies
 */

import { translate as __ } from 'lib/locale';
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
} from 'state/redirect/selector';

export const getMatches = () => [
	{
		value: MATCH_URL,
		name: __( 'URL only' ),
	},
	{
		value: MATCH_LOGIN,
		name: __( 'URL and login status' ),
	},
	{
		value: MATCH_ROLE,
		name: __( 'URL and role/capability' ),
	},
	{
		value: MATCH_REFERRER,
		name: __( 'URL and referrer' ),
	},
	{
		value: MATCH_AGENT,
		name: __( 'URL and user agent' ),
	},
	{
		value: MATCH_COOKIE,
		name: __( 'URL and cookie' ),
	},
	{
		value: MATCH_IP,
		name: __( 'URL and IP' ),
	},
	{
		value: MATCH_SERVER,
		name: __( 'URL and server' ),
	},
	{
		value: MATCH_HEADER,
		name: __( 'URL and HTTP header' ),
	},
	{
		value: MATCH_CUSTOM,
		name: __( 'URL and custom filter' ),
	},
	{
		value: MATCH_PAGE,
		name: __( 'URL and WordPress page type' ),
	},
];

export const getActions = () => [
	{
		value: ACTION_URL,
		name: __( 'Redirect to URL' ),
	},
	{
		value: ACTION_RANDOM,
		name: __( 'Redirect to random post' ),
	},
	{
		value: ACTION_PASS,
		name: __( 'Pass-through' ),
	},
	{
		value: ACTION_ERROR,
		name: __( 'Error (404)' ),
	},
	{
		value: ACTION_NOTHING,
		name: __( 'Do nothing (ignore)' ),
	},
];

export const getHttpCodes = () => [
	{
		value: 301,
		name: __( '301 - Moved Permanently' ),
	},
	{
		value: 302,
		name: __( '302 - Found' ),
	},
	{
		value: 303,
		name: __( '303 - See Other' ),
	},
	{
		value: 304,
		name: __( '304 - Not Modified' ),
	},
	{
		value: 307,
		name: __( '307 - Temporary Redirect' ),
	},
	{
		value: 308,
		name: __( '308 - Permanent Redirect' ),
	},
];

export const getHttpError = () => [
	{
		value: 400,
		name: __( '400 - Bad Request' ),
	},
	{
		value: 401,
		name: __( '401 - Unauthorized' ),
	},
	{
		value: 403,
		name: __( '403 - Forbidden' ),
	},
	{
		value: 404,
		name: __( '404 - Not Found' ),
	},
	{
		value: 410,
		name: __( '410 - Gone' ),
	},
	{
		value: 418,
		name: __( "418 - I'm a teapot" ),
	},
];

export const getSourceFlags = () => [
	{
		value: 'flag_regex',
		label: __( 'Regex' ),
	},
	{
		value: 'flag_trailing',
		label: __( 'Ignore Slash' ),
	},
	{
		value: 'flag_case',
		label: __( 'Ignore Case' ),
	},
];

export const FLAG_REGEX = 0;
export const FLAG_TRAILING = 1;
export const FLAG_CASE = 2;

export const getSourceQuery = () => [
	{
		value: 'exact',
		text: __( 'Exact match all parameters in any order' ),
	},
	{
		value: 'ignore',
		text: __( 'Ignore all parameters' ),
	},
	{
		value: 'pass',
		text: __( 'Ignore & pass parameters to the target' ),
	},
];
