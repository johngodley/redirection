/**
 * External dependencies
 *
 * @format
 */

import { useEffect, useState } from 'react';
import { __ } from '@wordpress/i18n';
import { useSelector, useDispatch } from 'react-redux';

/**
 * Internal dependencies
 */

import { clearHttp, getHttp } from 'state/info/action';
import { STATUS_IN_PROGRESS, STATUS_COMPLETE } from 'state/settings/type';
import HttpCheck from 'component/http-check/response';
import './style.scss';
import { ExternalLink, createInterpolateElement } from 'wp-plugin-components';

function HttpTester( props ) {
	const dispatch = useDispatch();
	const [ url, setUrl ] = useState( '' );
	const { status, http } = useSelector( ( state ) => state.info );
	const shouldShow = status === STATUS_COMPLETE && ! http ? false : true;

	useEffect(() => {
		dispatch( clearHttp() );
	}, [ url ]);

	function submit( ev ) {
		ev.preventDefault();

		if ( url.length > 0 ) {
			dispatch( getHttp( url ) );
		}
	}

	return (
		<form className="http-tester" onSubmit={ submit }>
			<h3>{ __( 'Redirect Tester', 'redirection' ) }</h3>

			<p>
				{ createInterpolateElement(
					__(
						"Sometimes your browser can cache a URL, making it hard to know if it's working as expected. Use this service from {{link}}redirect.li{{/link}} to get accurate results.",
						'redirection'
					),
					{ link: <ExternalLink url="https://redirect.li" /> }
				) }
			</p>
			<div className="redirection-httptest__input">
				<span>{ __( 'URL', 'redirection' ) }:</span>

				<input
					className="regular-text"
					type="text"
					value={ url }
					onChange={ ( ev ) => setUrl( ev.target.value ) }
					disabled={ status === STATUS_IN_PROGRESS }
					placeholder={ __( 'Enter full URL, including http:// or https://', 'redirection' ) }
				/>
				<input
					type="submit"
					className="button-secondary"
					disabled={ status === STATUS_IN_PROGRESS || url.length === 0 }
					value={ __( 'Check', 'redirection' ) }
				/>
			</div>

			{ shouldShow && (
				<div className="redirection-httptest">
					<HttpCheck url={ url } />
				</div>
			) }
		</form>
	);
}

export default HttpTester;
