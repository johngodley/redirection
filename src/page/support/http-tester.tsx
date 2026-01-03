import { useEffect, useState } from 'react';
import { __ } from '@wordpress/i18n';
import HttpCheck from 'component/http-check/response';
import './style.scss';
import { ExternalLink, createInterpolateElement } from '@wp-plugin-components';
import { useInfoStore } from 'stores';
import { useHttpCheck } from 'lib/api/hooks/use-info';

function HttpTester() {
	const [ url, setUrl ] = useState( '' );
	const [ testUrl, setTestUrl ] = useState( '' );
	// Direct property access instead of destructuring
	const status = useInfoStore( ( state ) => state.status );
	const http = useInfoStore( ( state ) => state.http );
	const { clearHttp } = useInfoStore();
	const shouldShow = status === 'success' && ! http ? false : true;

	// Clear HTTP data when URL changes
	useEffect( () => {
		clearHttp();
	}, [ url, clearHttp ] );

	// Trigger HTTP check when testUrl is set
	useHttpCheck( testUrl, { enabled: !! testUrl } );

	function submit( ev: React.FormEvent ) {
		ev.preventDefault();

		if ( url.length > 0 ) {
			setTestUrl( url );
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
					disabled={ status === 'loading' }
					placeholder={ __( 'Enter full URL, including http:// or https://', 'redirection' ) }
				/>
				<input
					type="submit"
					className="button-secondary"
					disabled={ status === 'loading' || url.length === 0 }
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
