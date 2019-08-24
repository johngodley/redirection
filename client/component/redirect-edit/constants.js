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
	MATCH_LANGUAGE,
} from 'state/redirect/selector';

export const getMatches = () => [
	{
		value: MATCH_URL,
		label: __( 'URL only' ),
	},
	{
		value: MATCH_LOGIN,
		label: __( 'URL and login status' ),
	},
	{
		value: MATCH_ROLE,
		label: __( 'URL and role/capability' ),
	},
	{
		value: MATCH_REFERRER,
		label: __( 'URL and referrer' ),
	},
	{
		value: MATCH_AGENT,
		label: __( 'URL and user agent' ),
	},
	{
		value: MATCH_COOKIE,
		label: __( 'URL and cookie' ),
	},
	{
		value: MATCH_IP,
		label: __( 'URL and IP' ),
	},
	{
		value: MATCH_SERVER,
		label: __( 'URL and server' ),
	},
	{
		value: MATCH_HEADER,
		label: __( 'URL and HTTP header' ),
	},
	{
		value: MATCH_CUSTOM,
		label: __( 'URL and custom filter' ),
	},
	{
		value: MATCH_PAGE,
		label: __( 'URL and WordPress page type' ),
	},
	{
		value: MATCH_LANGUAGE,
		label: __( 'URL and language' ),
	},
];

export const getActions = () => [
	{
		value: ACTION_URL,
		label: __( 'Redirect to URL' ),
	},
	{
		value: ACTION_RANDOM,
		label: __( 'Redirect to random post' ),
	},
	{
		value: ACTION_PASS,
		label: __( 'Pass-through' ),
	},
	{
		value: ACTION_ERROR,
		label: __( 'Error (404)' ),
	},
	{
		value: ACTION_NOTHING,
		label: __( 'Do nothing (ignore)' ),
	},
];

export const getHttpCodes = () => [
	{
		value: 301,
		label: __( '301 - Moved Permanently' ),
	},
	{
		value: 302,
		label: __( '302 - Found' ),
	},
	{
		value: 303,
		label: __( '303 - See Other' ),
	},
	{
		value: 304,
		label: __( '304 - Not Modified' ),
	},
	{
		value: 307,
		label: __( '307 - Temporary Redirect' ),
	},
	{
		value: 308,
		label: __( '308 - Permanent Redirect' ),
	},
];

export const getHttpError = () => [
	{
		value: 400,
		label: __( '400 - Bad Request' ),
	},
	{
		value: 401,
		label: __( '401 - Unauthorized' ),
	},
	{
		value: 403,
		label: __( '403 - Forbidden' ),
	},
	{
		value: 404,
		label: __( '404 - Not Found' ),
	},
	{
		value: 410,
		label: __( '410 - Gone' ),
	},
	{
		value: 418,
		label: __( "418 - I'm a teapot" ),
	},
	{
		value: 500,
		label: __( '500 - Internal Server Error' ),
	},
	{
		value: 501,
		label: __( '501 - Not implemented' ),
	},
	{
		value: 502,
		label: __( '502 - Bad Gateway' ),
	},
	{
		value: 503,
		label: __( '503 - Service Unavailable' ),
	},
	{
		value: 504,
		label: __( '504 - Gateway Timeout' ),
	},
];

export const getAllHttpCodes = () => getHttpCodes().concat( getHttpError() );

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
		label: __( 'Exact match all parameters in any order' ),
	},
	{
		value: 'ignore',
		label: __( 'Ignore all parameters' ),
	},
	{
		value: 'pass',
		label: __( 'Ignore & pass parameters to the target' ),
	},
];
