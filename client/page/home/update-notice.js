/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';
import { useDispatch } from 'react-redux'

/**
 * Internal dependencies
 */
import { ExternalLink, Notice, Button } from 'wp-plugin-components';
import { saveSettings } from 'state/settings/action';
import { CAP_REDIRECT_MANAGE, has_capability } from 'lib/capabilities';

function UpdateNotice() {
	const { update_notice = false } = Redirectioni10n;
	const dispatch = useDispatch();

	function dismiss() {
		dispatch( saveSettings( { update_notice: Redirectioni10n.update_notice } ) );
		Redirectioni10n.update_notice = false;
	}

	if ( ! update_notice || ! has_capability( CAP_REDIRECT_MANAGE ) ) {
		return null;
	}

	return (
		<Notice>
			<p>
				{ __( 'Version %s installed! Please read the {{url}}release notes{{/url}} for details.', {
					args: update_notice,
					components: {
						url: (
							<ExternalLink
								url={ 'https://redirection.me/blog/redirection-version-' + update_notice }
							/>
						),
					},
				} ) }
				&nbsp;
				<Button onClick={ dismiss }>{ __( 'OK' ) }</Button>
			</p>
		</Notice>
	);
}

export default UpdateNotice;
