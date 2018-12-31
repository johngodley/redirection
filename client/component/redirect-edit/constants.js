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
		text: __( 'URL only' ),
	},
	{
		value: MATCH_LOGIN,
		text: __( 'URL and login status' ),
	},
	{
		value: MATCH_ROLE,
		text: __( 'URL and role/capability' ),
	},
	{
		value: MATCH_REFERRER,
		text: __( 'URL and referrer' ),
	},
	{
		value: MATCH_AGENT,
		text: __( 'URL and user agent' ),
	},
	{
		value: MATCH_COOKIE,
		text: __( 'URL and cookie' ),
	},
	{
		value: MATCH_IP,
		text: __( 'URL and IP' ),
	},
	{
		value: MATCH_SERVER,
		text: __( 'URL and server' ),
	},
	{
		value: MATCH_HEADER,
		text: __( 'URL and HTTP header' ),
	},
	{
		value: MATCH_CUSTOM,
		text: __( 'URL and custom filter' ),
	},
	{
		value: MATCH_PAGE,
		text: __( 'URL and WordPress page type' ),
	},
];

export const getActions = () => [
	{
		value: ACTION_URL,
		text: __( 'Redirect to URL' ),
	},
	{
		value: ACTION_RANDOM,
		text: __( 'Redirect to random post' ),
	},
	{
		value: ACTION_PASS,
		text: __( 'Pass-through' ),
	},
	{
		value: ACTION_ERROR,
		text: __( 'Error (404)' ),
	},
	{
		value: ACTION_NOTHING,
		text: __( 'Do nothing (ignore)' ),
	},
];

export const getHttpCodes = () => [
	{
		value: 301,
		text: __( '301 - Moved Permanently' ),
	},
	{
		value: 302,
		text: __( '302 - Found' ),
	},
	{
		value: 303,
		text: __( '303 - See Other' ),
	},
	{
		value: 304,
		text: __( '304 - Not Modified' ),
	},
	{
		value: 307,
		text: __( '307 - Temporary Redirect' ),
	},
	{
		value: 308,
		text: __( '308 - Permanent Redirect' ),
	},
];

export const getHttpError = () => [
	{
		value: 400,
		text: __( '400 - Bad Request' ),
	},
	{
		value: 401,
		text: __( '401 - Unauthorized' ),
	},
	{
		value: 403,
		text: __( '403 - Forbidden' ),
	},
	{
		value: 404,
		text: __( '404 - Not Found' ),
	},
	{
		value: 410,
		text: __( '410 - Gone' ),
	},
	{
		value: 418,
		text: __( "418 - I'm a teapot" ),
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
