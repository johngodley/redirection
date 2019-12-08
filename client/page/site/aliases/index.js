/**
 * External dependencies
 */

import React, { Fragment } from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

/**
 * Local dependencies
 */

import { getDomainOnly } from 'lib/url';
import Alias from './alias';

const ALIAS_MAX = 10;

const addAlias = ( aliases, onChange, ev ) => {
	ev.preventDefault();

	onChange( { aliases: aliases.concat( '' ).slice( 0, ALIAS_MAX ) } ); // XXX implement in API
}

const removeAlias = ( pos, aliases, onChange ) => {
	onChange( { aliases: [ ...aliases.slice( 0, pos ), ...aliases.slice( pos + 1 ) ] } );
}

const updateAlias = ( pos, aliases, onChange, ev ) => {
	const updatedAliases = aliases.slice();

	updatedAliases[ pos ] = ev.target.value;

	onChange( { aliases: updatedAliases } );
}

const SiteAliases = ( { aliases, siteDomain, onChange } ) => {
	return (
		<Fragment>
			<h3>{ __( 'Site Aliases' ) }</h3>

			<p>{ __( 'A site alias is another domain that you want to be redirected to this site. For example, an old domain, or a subdomain. This will redirect all URLs, including WordPress login and admin.' ) }</p>
			<p>{ __( 'You will need to configure your system (DNS and server) to pass requests for these domains to this WordPress install.' ) }</p>

			<table className="wp-list-table widefat fixed striped items redirect-aliases table-auto">
				<thead>
					<tr>
						<th>{ __( 'Aliased Domain' ) }</th>
						<th className="redirect-alias__item__asdomain">{ __( 'Alias' ) }</th>
						<th className="redirect-alias__delete"></th>
					</tr>
				</thead>

				<tbody>
					{ aliases.map( ( domain, key ) => (
						<Alias
							key={ key }
							domain={ domain }
							asDomain={ getDomainOnly( domain ).replace( /https?:\/\//, '' ) }
							onChange={ ( ev ) => updateAlias( key, aliases, onChange, ev ) }
							onDelete={ () => removeAlias( key, aliases, onChange ) }
							site={ siteDomain }
						/>
					) ) }
					{ aliases.length === 0 && <tr><td colSpan="3">{ __( 'No aliases' ) }</td></tr> }
				</tbody>
			</table>

			<p><button className="button-secondary" onClick={ ( ev ) => addAlias( aliases, onChange, ev ) }>{ __( 'Add Alias' ) }</button></p>
		</Fragment>
	)
};

SiteAliases.propTypes = {
	aliases: PropTypes.array.isRequired,
	siteDomain: PropTypes.string.isRequired,
	onChange: PropTypes.func.isRequired,
}

export default SiteAliases;
