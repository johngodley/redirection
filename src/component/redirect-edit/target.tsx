import { __ } from '@wordpress/i18n';

import { DropdownText } from '@wp-plugin-components';
import { RedirectionApi } from 'lib/api-request';
import apiFetch from '@wp-plugin-lib/api-fetch';

interface TargetUrlProps {
	onChange: ( value: string ) => void;
	url?: string | string[];
}

function TargetUrl( props: TargetUrlProps ) {
	const { onChange, url } = props;

	function getSuggestedUrls( searchUrl: string ) {
		return apiFetch( RedirectionApi.redirect.matchPost( searchUrl ) );
	}

	return (
		<DropdownText
			placeholder={ __(
				'The target URL you want to redirect, or auto-complete on post name or permalink.',
				'redirection'
			) }
			onChange={ onChange }
			fetchData={ getSuggestedUrls }
			value={ Array.isArray( url ) ? url.join( '' ) : url || '' }
		/>
	);
}

export default TargetUrl;
