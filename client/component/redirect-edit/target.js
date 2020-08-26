/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';

/**
 * Internal dependencies
 */
import { DropdownText } from 'wp-plugin-components';
import { RedirectionApi, getApi } from 'lib/api';

function TargetUrl( props ) {
	const { onChange, url } = props;

	function getSuggestedUrls( url ) {
		return getApi( RedirectionApi.redirect.matchPost( url ) );
	}

	return (
		<DropdownText
			placeholder={ __( 'The target URL you want to redirect, or auto-complete on post name or permalink.' ) }
			onChange={ onChange }
			fetchData={ getSuggestedUrls }
			value={ url }
		/>
	);
}

export default TargetUrl;
