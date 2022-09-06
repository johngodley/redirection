/**
 * External dependencies
 */

import { __ } from '@wordpress/i18n';
import { useDispatch, useSelector } from 'react-redux';

/**
 * Internal dependencies
 */
import getFirstApi from './first-api';
import { saveSettings, finishUpgrade } from '../../state/settings/action';
import { ExternalLink, createInterpolateElement } from '@wp-plugin-components';

const WEEK = 7;
const NEVER = -1;

export default function StepFinish( { step, setStep, options, setOptions } ) {
	const dispatch = useDispatch();
	const { apiTest } = useSelector( ( state ) => state.settings );

	function onFinish() {
		const { ip, log, monitor } = options.settings;

		dispatch(
			saveSettings( {
				expire_redirect: log ? WEEK : NEVER,
				expire_404: log ? WEEK : NEVER,
				ip_logging: ip ? 1 : 0,
				rest_api: getFirstApi( apiTest ),
				monitor_types: monitor ? [ 'post', 'page' ] : undefined,
				monitor_post: monitor ? 1 : 0,
			} )
		);

		dispatch( finishUpgrade() );
	}

	return (
		<div>
			<h2>{ __( 'Installation Complete', 'redirection' ) }</h2>

			<p>{ __( 'Redirection is now installed!', 'redirection' ) }</p>

			<p>{ createInterpolateElement(
				__( 'Please take a moment to consult the {{support}}support site{{/support}} for information about how to use Redirection.', 'redirection' ),
				{
					support: <ExternalLink url="https://redirection.me" />
				}
			) }</p>

			<button className="button button-primary" onClick={ onFinish } type="button">
				{ __( 'Ready to begin! 🎉', 'redirection' ) }
			</button>
		</div>
	);
}
