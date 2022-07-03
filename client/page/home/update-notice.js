/**
 * External dependencies
 */

import { __, sprintf } from '@wordpress/i18n';
import { useDispatch } from 'react-redux'

/**
 * Internal dependencies
 */
import { ExternalLink, Notice, Button, createInterpolateElement } from 'wp-plugin-components';
import { saveSettings } from 'state/settings/action';
import { CAP_OPTION_MANAGE, has_capability } from 'lib/capabilities';

function UpdateNotice() {
	const { update_notice = false } = window.Redirectioni10n;
	const dispatch = useDispatch();

	function dismiss() {
		dispatch( saveSettings( { update_notice: window.Redirectioni10n.update_notice } ) );
		window.Redirectioni10n.update_notice = false;
	}

	if ( ! update_notice || ! has_capability( CAP_OPTION_MANAGE ) ) {
		return null;
	}

	return (
		<Notice>
			<p>
				{ createInterpolateElement(
					sprintf(
						__( 'Version %s installed! Please read the {{url}}release notes{{/url}} for details.', 'redirection' ),
						update_notice
					),
					{
						url: (
							<ExternalLink
								url={ 'https://redirection.me/blog/redirection-version-' + update_notice.replace( '.', '-' ) + '/' }
							/>
						),
					},
				) }
				&nbsp;
				<Button onClick={ dismiss }>{ __( 'OK', 'redirection' ) }</Button>
			</p>
		</Notice>
	);
}

export default UpdateNotice;
