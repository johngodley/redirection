import { Fragment } from 'react';
import { __ } from '@wordpress/i18n';
import { getDomainAndPathOnly } from 'lib/url';

interface RelocateSiteProps {
	relocate: string;
	siteDomain: string;
	onChange: ( value: { relocate: string } ) => void;
}

const getExample = ( siteDomain: string, relocate: string ): JSX.Element | null => {
	if ( relocate ) {
		const asDomain = getDomainAndPathOnly( relocate );

		if ( asDomain && asDomain.length > 0 ) {
			return (
				<p>
					<code>{ siteDomain }</code> ⇒ <code>{ asDomain }</code>
				</p>
			);
		}
	}
	return null;
};

const RelocateSite = ( { relocate, siteDomain, onChange }: RelocateSiteProps ) => {
	const relocateExample = getExample( siteDomain, relocate );

	return (
		<Fragment>
			<h3>{ __( 'Relocate Site', 'redirection' ) }</h3>
			<p>
				{ __(
					'Want to redirect the entire site? Enter a domain to redirect everything, except WordPress login and admin. Enabling this option will disable any site aliases or canonical settings.',
					'redirection'
				) }
			</p>

			<p>
				<strong>{ __( 'Relocate to domain', 'redirection' ) }:</strong>{ ' ' }
				<input
					type="text"
					className="regular-text"
					name="relocate"
					value={ relocate ? relocate : '' }
					onChange={ ( ev ) => onChange( { relocate: ev.target.value } ) }
				/>
			</p>

			{ relocateExample }
		</Fragment>
	);
};

export default RelocateSite;
