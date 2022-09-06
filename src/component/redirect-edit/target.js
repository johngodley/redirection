/**
 * External dependencies
 */

import React from 'react';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { DropdownText } from '@wp-plugin-components';
import { RedirectionApi } from '../../lib/api-request';
import { apiFetch } from '@wp-plugin-lib';

function TargetUrl( props ) {
	const { onChange, url } = props;

	function getSuggestedUrls( url ) {
		return apiFetch( RedirectionApi.redirect.matchPost( url ) );
	}

	return (
		<DropdownText
			placeholder={ __( 'The target URL you want to redirect, or auto-complete on post name or permalink.', 'redirection' ) }
			onChange={ onChange }
			fetchData={ getSuggestedUrls }
			value={ url }
		/>
	);
}

export default TargetUrl;
