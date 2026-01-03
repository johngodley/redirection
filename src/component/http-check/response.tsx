import { __, sprintf } from '@wordpress/i18n';
import clsx from 'clsx';
import { Spinner, createInterpolateElement } from '@wp-plugin-components';
import HttpDetails from './details';
import { useInfoStore } from 'stores';
import './style.scss';

interface HttpErrorProps {
	error: string;
}

interface HttpCheckResponseProps {
	url: string;
	desiredCode?: number;
	desiredTarget?: any;
}

function HttpError( { error }: HttpErrorProps ) {
	return (
		<div className="wpl-modal_error">
			<h2>{ __( 'Error', 'redirection' ) }</h2>
			<p>
				{ __( 'Something went wrong obtaining this information. It may work in the future.', 'redirection' ) }
			</p>
			<p>
				<code>{ error }</code>
			</p>
		</div>
	);
}

export default function HttpCheckResponse( { url, desiredCode = 0, desiredTarget = null }: HttpCheckResponseProps ) {
	// Direct property access instead of destructuring
	const http = useInfoStore( ( state ) => state.http );
	const status = useInfoStore( ( state ) => state.status );
	const error = useInfoStore( ( state ) => state.error );

	if ( status === 'success' && ! http ) {
		return null;
	}

	const klass = clsx( {
		'redirection-httpcheck': true,
		'wpl-modal_loading': status === 'loading',
		'redirection-httpcheck_small': status === 'error',
	} );

	return (
		<div className={ klass }>
			{ status === 'loading' && <Spinner /> }
			{ status === 'error' && error && <HttpError error={ error } /> }

			{ status === 'success' && http && (
				<>
					<h2>
						{ createInterpolateElement(
							sprintf(
								// translators: %s is the URL being checked
								__( 'Check redirect for: {{code}}%s{{/code}}', 'redirection' ),
								url
							),
							{
								code: <code />,
							}
						) }
					</h2>

					<HttpDetails
						http={ http as any }
						url={ url }
						desiredCode={ desiredCode }
						desiredTarget={ desiredTarget }
					/>
				</>
			) }
		</div>
	);
}
