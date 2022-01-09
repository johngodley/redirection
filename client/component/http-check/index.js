/**
 * External dependencies
 */

import React, { useEffect } from 'react';
import { useDispatch } from 'react-redux';
import { translate as __ } from 'i18n-calypso';

/**
 * Internal dependencies
 */
import { getHttp, clearHttp } from 'state/info/action';
import HttpCheckResponse from './response';
import './style.scss';

export default function HttpCheck( { url, desiredCode = 0, desiredTarget = null } ) {
	const dispatch = useDispatch();

	useEffect(() => {
		dispatch( getHttp( url ) );

		return () => {
			dispatch( clearHttp() );
		};
	}, []);

	return (
		<HttpCheckResponse url={ url } desiredCode={ desiredCode } desiredTarget={ desiredTarget }/>
	);
}
