import { useState } from 'react';
import { __ } from '@wordpress/i18n';

const RAW_HIDE_LENGTH = 500;

interface ApiError {
	request?: {
		raw?: string;
	};
}

interface ApiResultRawProps {
	error: ApiError;
}

function doesNeedHiding( request: { raw?: string } | undefined ): boolean {
	return !! ( request && request.raw && request.raw.length > RAW_HIDE_LENGTH );
}

function ApiResultRaw( { error }: ApiResultRawProps ) {
	const { request } = error;
	const needToHide = doesNeedHiding( request );
	const [ hide, setHide ] = useState( needToHide );

	const onShow = ( ev: React.MouseEvent< HTMLButtonElement > ) => {
		ev.preventDefault();
		setHide( false );
	};

	const onHide = ( ev: React.MouseEvent< HTMLButtonElement > ) => {
		ev.preventDefault();
		setHide( true );
	};

	if ( request && request.raw ) {
		return (
			<>
				{ needToHide && hide && (
					<button className="api-result-hide" type="button" onClick={ onShow }>
						{ __( 'Show Full', 'redirection' ) }
					</button>
				) }
				{ needToHide && ! hide && (
					<button className="api-result-hide" type="button" onClick={ onHide }>
						{ __( 'Hide', 'redirection' ) }
					</button>
				) }
				<pre>{ hide ? request.raw.substring( 0, RAW_HIDE_LENGTH ) + ' ...' : request.raw }</pre>
			</>
		);
	}

	return null;
}

export default ApiResultRaw;
