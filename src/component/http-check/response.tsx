import { __, sprintf } from '@wordpress/i18n';
import clsx from 'clsx';
import { Spinner, createInterpolateElement } from '@wp-plugin-components';
import { useHttpCheck } from 'lib/api/hooks';
import HttpDetails from './details';
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
	const { data: http, isLoading, isError, error } = useHttpCheck( url, { enabled: !! url } );
	const errorMessage = isError && error ? ( ( error as any ).message as string ) || '' : '';

	if ( ! isLoading && ! isError && ! http ) {
		return null;
	}

	const klass = clsx( {
		'redirection-httpcheck': true,
		'wpl-modal_loading': isLoading,
		'redirection-httpcheck_small': isError,
	} );

	return (
		<div className={ klass }>
			{ isLoading && <Spinner /> }
			{ isError && errorMessage && <HttpError error={ errorMessage } /> }

			{ ! isLoading && ! isError && http && (
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
