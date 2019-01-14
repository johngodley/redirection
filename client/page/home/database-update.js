/* global Redirectioni10n */
/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';

/**
 * Internal dependencies
 */
import { getExportUrl } from 'state/io/selector';
import Database from 'component/database';
import ExternalLink from 'component/external-link';
import Error from 'component/error';
import { STATUS_FAILED } from 'state/settings/type';

function getUpgradeNotice() {
	const { current, next } = Redirectioni10n.database;

	if ( current === next ) {
		return __( 'A database upgrade is in progress. Please continue to finish.' );
	}

	return __( 'Your current database is version %(current)s, the latest is %(latest)s. Please update to use new features.', {
		args: {
			current: Redirectioni10n.database.current,
			latest: Redirectioni10n.database.next,
		},
	} );
}

const NeedUpdate = ( { onShowUpgrade, showDatabase, result } ) => {
	if ( showDatabase ) {
		return (
			<React.Fragment>
				{ result === STATUS_FAILED && <Error /> }

				<div className="wizard-wrapper">
					<div className="wizard">
						<Database />
					</div>
				</div>
			</React.Fragment>
		);
	}

	return (
		<div className="wrap redirection">
			<h1 className="wp-heading-inline">{ __( 'Update Required' ) }</h1>

			<div className="error">
				<h3>{ __( 'Redirection database needs updating' ) }</h3>
				<p>{ getUpgradeNotice() }</p>

				<p>{ __( 'As with any upgrade you should make a backup. You can do this by {{download}}downloading a copy{{/download}} of your Redirection data.', {
					components: {
						download: <ExternalLink url={ getExportUrl( 'all', 'json' ) } />,
					},
				} ) }</p>

				<p><input className="button-primary" type="submit" value={ __( 'Upgrade Database' ) } onClick={ onShowUpgrade } /></p>
			</div>
		</div>
	);
};

export default NeedUpdate;
