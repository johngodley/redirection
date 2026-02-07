import { Fragment } from 'react';
import { __ } from '@wordpress/i18n';
import { getDomainOnly } from 'lib/url';
import Alias from './alias';

const ALIAS_MAX = 20;

interface SiteAliasesProps {
	aliases: string[];
	siteDomain: string;
	onChange: ( value: { aliases: string[] } ) => void;
}

const addAlias = (
	aliases: string[],
	onChange: ( value: { aliases: string[] } ) => void,
	ev: React.MouseEvent< HTMLButtonElement >
) => {
	ev.preventDefault();

	onChange( { aliases: aliases.concat( '' ).slice( 0, ALIAS_MAX ) } );
};

const removeAlias = ( pos: number, aliases: string[], onChange: ( value: { aliases: string[] } ) => void ) => {
	onChange( { aliases: [ ...aliases.slice( 0, pos ), ...aliases.slice( pos + 1 ) ] } );
};

const updateAlias = (
	pos: number,
	aliases: string[],
	onChange: ( value: { aliases: string[] } ) => void,
	ev: React.ChangeEvent< HTMLInputElement >
) => {
	const updatedAliases = aliases.slice();

	updatedAliases[ pos ] = ev.target.value;

	onChange( { aliases: updatedAliases } );
};

const SiteAliases = ( { aliases, siteDomain, onChange }: SiteAliasesProps ) => {
	return (
		<Fragment>
			<h3>{ __( 'Site Aliases', 'redirection' ) }</h3>

			<p>
				{ __(
					'A site alias is another domain that you want to be redirected to this site. For example, an old domain, or a subdomain. This will redirect all URLs, including WordPress login and admin.',
					'redirection'
				) }
			</p>
			<p>
				{ __(
					'You will need to configure your system (DNS and server) to pass requests for these domains to this WordPress install.',
					'redirection'
				) }
			</p>

			<table className="wp-list-table widefat fixed striped items redirect-aliases table-auto">
				<thead>
					<tr>
						<th>{ __( 'Aliased Domain', 'redirection' ) }</th>
						<th className="redirect-alias__item__asdomain">{ __( 'Alias', 'redirection' ) }</th>
						<th className="redirect-alias__delete"></th>
					</tr>
				</thead>

				<tbody>
					{ aliases.map( ( domain, key ) => (
						<Alias
							key={ key }
							domain={ domain }
							asDomain={ ( getDomainOnly( domain ) || '' ).replace( /https?:\/\//, '' ) }
							onChange={ ( ev ) => updateAlias( key, aliases, onChange, ev ) }
							onDelete={ () => removeAlias( key, aliases, onChange ) }
							site={ siteDomain }
						/>
					) ) }
					{ aliases.length === 0 && (
						<tr>
							<td colSpan={ 3 }>{ __( 'No aliases', 'redirection' ) }</td>
						</tr>
					) }
				</tbody>
			</table>

			<p>
				<button className="button-secondary" onClick={ ( ev ) => addAlias( aliases, onChange, ev ) }>
					{ __( 'Add Alias', 'redirection' ) }
				</button>
			</p>
		</Fragment>
	);
};

export default SiteAliases;
