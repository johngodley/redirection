/**
 * External dependencies
 */

import { useSelector } from 'react-redux';
import { __, sprintf } from '@wordpress/i18n';
import classnames from 'classnames';

/**
 * Internal dependencies
 */
import { Spinner, createInterpolateElement } from '@wp-plugin-components';
import HttpDetails from './details';
import { STATUS_IN_PROGRESS, STATUS_FAILED, STATUS_COMPLETE } from '../../state/settings/type';
import './style.scss';

function HttpError( { error } ) {
	return (
		<div className="wpl-modal_error">
			<h2>{ __( 'Error', 'redirection' ) }</h2>
			<p>{ __( 'Something went wrong obtaining this information. It may work in the future.', 'redirection' ) }</p>
			<p>
				<code>{ error.message }</code>
			</p>
		</div>
	);
}

export default function HttpCheckResponse( { url, desiredCode = 0, desiredTarget = null } ) {
	const { status, error, http } = useSelector( ( state ) => state.info );
	const klass = classnames( {
		'redirection-httpcheck': true,
		'wpl-modal_loading': status === STATUS_IN_PROGRESS,
		'redirection-httpcheck_small': status === STATUS_FAILED,
	} );

	if ( status === STATUS_COMPLETE && !http ) {
		return null;
	}

	return (
		<div className={ klass }>
			{ status === STATUS_IN_PROGRESS && <Spinner /> }
			{ status === STATUS_FAILED && <HttpError error={ error } /> }

			{ status === STATUS_COMPLETE && http && (
				<>
					<h2>
						{ createInterpolateElement(
							sprintf(
								/* translators: %s: URL being checked */
								__( 'Check redirect for: {{code}}%s{{/code}}', 'redirection' ), url
							),
							{
								code: <code />,
							},
						) }
					</h2>

					<HttpDetails
						http={ http }
						url={ url }
						desiredCode={ desiredCode }
						desiredTarget={ desiredTarget }
					/>
				</>
			) }
		</div>
	);
}
