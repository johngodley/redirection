/**
 * External dependencies
 */

import React, { Fragment } from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

/**
 * Local dependencies
 */

import { getDomainAndPathOnly } from 'lib/url';

const getExample = ( siteDomain, relocate ) => {
	if ( relocate ) {
		const asDomain = getDomainAndPathOnly( relocate );

		if ( asDomain.length > 0 ) {
			return <p><code>{ siteDomain }</code> → <code>{ asDomain }</code></p>;
		}
	}
	return null;
};

const RelocateSite = ( { relocate, siteDomain, onChange } ) => {
	const relocateExample = getExample( siteDomain, relocate );

	return (
		<Fragment>
			<h3>{ __( 'Relocate Site' ) }</h3>
			<p>{ __( 'Want to redirect the entire site? Enter a domain to redirect everything, except WordPress login and admin. Enabling this option will disable any site aliases or canonical settings.' ) }</p>

			<p><strong>{ __( 'Relocate to domain' ) }:</strong> <input type="text" className="regular-text" name="relocate" value={ relocate ? relocate : '' } onChange={ ev => onChange( { relocate: ev.target.value } ) } /></p>

			{ relocateExample }
		</Fragment>
	);
}

RelocateSite.propTypes = {
	relocate: PropTypes.string.isRequired,
	siteDomain: PropTypes.string.isRequired,
	onChange: PropTypes.func.isRequired,
};

export default RelocateSite;
