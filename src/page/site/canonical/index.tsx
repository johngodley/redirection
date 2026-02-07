import { __, sprintf } from '@wordpress/i18n';
import { createInterpolateElement } from '@wp-plugin-components';

interface WWWOption {
	label: string | JSX.Element;
	value: string;
}

interface CanonicalSettingsProps {
	https: boolean;
	preferredDomain: string;
	siteDomain: string;
	onChange: ( value: { [ key: string ]: string | boolean } ) => void;
}

const getWWW = ( site: string ): WWWOption[] => [
	{
		// translators: %(site)s is the site domain
		label: createInterpolateElement(
			sprintf(
				// translators: %(site)s is the site domain
				__( "Don't set a preferred domain - {{code}}%(site)s{{/code}}", 'redirection' ),
				{ site }
			),
			{
				code: <code />,
			}
		),
		value: '',
	},
	{
		// translators: %(site)s is the site domain without www, %(siteWWW)s is the site domain with www
		label: createInterpolateElement(
			sprintf(
				// translators: %(site)s is the site domain without www, %(siteWWW)s is the site domain with www
				__(
					'Remove www from domain - {{code}}%(siteWWW)s{{/code}} ⇒ {{code}}%(site)s{{/code}}',
					'redirection'
				),
				{
					site: site.replace( 'www.', '' ),
					siteWWW: site.replace( 'www.', '' ).replace( '://', '://www.' ),
				}
			),
			{
				code: <code />,
			}
		),
		value: 'nowww',
	},
	{
		// translators: %(site)s is the site domain without www, %(siteWWW)s is the site domain with www
		label: createInterpolateElement(
			sprintf(
				// translators: %(site)s is the site domain without www, %(siteWWW)s is the site domain with www
				__( 'Add www to domain - {{code}}%(site)s{{/code}} ⇒ {{code}}%(siteWWW)s{{/code}}', 'redirection' ),
				{
					site: site.replace( 'www.', '' ),
					siteWWW: site.replace( 'www.', '' ).replace( '://', '://www.' ),
				}
			),
			{
				code: <code />,
			}
		),
		value: 'www',
	},
];

function showAlert( domain: string, https: boolean, preferred: string ): boolean {
	if ( https && ! domain.includes( 'https:' ) ) {
		return true;
	}

	if ( preferred === 'www' && ! domain.includes( 'www.' ) ) {
		return true;
	}

	if ( preferred === 'nowww' && domain.includes( 'www.' ) ) {
		return true;
	}

	return false;
}

function getCanonical( domain: string, https: boolean, preferred: string ): string {
	domain = domain.replace( /https?:\/\//, '' );

	if ( preferred === 'www' ) {
		domain = 'www.' + domain.replace( 'www.', '' );
	} else if ( preferred === 'nowww' ) {
		domain = domain.replace( 'www.', '' );
	}

	return ( https ? 'https://' : 'http://' ) + domain;
}

function CanonicalSettings( { https, preferredDomain, siteDomain, onChange }: CanonicalSettingsProps ) {
	const alert = showAlert( siteDomain, https, preferredDomain );
	const changePreferred = ( ev: React.ChangeEvent< HTMLInputElement > ) => {
		onChange( { [ ev.target.name ]: ev.target.value } );
	};
	const changeHttps = ( ev: React.ChangeEvent< HTMLInputElement > ) => {
		onChange( { [ ev.target.name ]: ev.target.checked } );
	};

	return (
		<>
			<h3>{ __( 'Canonical Settings', 'redirection' ) }</h3>
			<p>
				<input id="canonical-https" type="checkbox" name="https" onChange={ changeHttps } checked={ https } />
				&nbsp;
				<label htmlFor="canonical-https">
					{ createInterpolateElement(
						sprintf(
							// translators: %(site)s is HTTP site URL, %(siteHTTPS)s is HTTPS site URL
							__(
								'Force a redirect from HTTP to HTTPS - {{code}}%(site)s{{/code}} ⇒ {{code}}%(siteHTTPS)s{{/code}}',
								'redirection'
							),
							{
								site: siteDomain.replace( 'https', 'http' ),
								siteHTTPS: siteDomain.replace( 'http:', 'https:' ),
							}
						),
						{
							code: <code />,
						}
					) }
				</label>
			</p>

			{ https && (
				<div className="inline-notice inline-warning">
					<p>
						{ createInterpolateElement(
							// translators: %(strong)s is the warning title
							__(
								'{{strong}}Warning{{/strong}}: ensure your HTTPS is working before forcing a redirect.',
								'redirection'
							),
							{
								strong: <strong />,
							}
						) }
					</p>
				</div>
			) }

			<p>{ __( 'Preferred domain', 'redirection' ) }:</p>
			{ getWWW( siteDomain ).map( ( preferred ) => {
				const inputId = `canonical-preferred-${ preferred.value || 'none' }`;

				return (
					<p key={ preferred.value }>
						<input
							id={ inputId }
							type="radio"
							name="preferred_domain"
							value={ preferred.value }
							onChange={ changePreferred }
							checked={ preferred.value === preferredDomain }
						/>{ ' ' }
						<label htmlFor={ inputId }>{ preferred.label }</label>
					</p>
				);
			} ) }

			{ alert && (
				<div className="inline-notice inline-error">
					<p>
						{ /* translators: %(current)s is current site URL, %(site)s is recommended canonical URL */ }
						{ createInterpolateElement(
							sprintf(
								// translators: %(current)s is current site URL, %(site)s is recommended canonical URL
								__(
									'You should update your site URL to match your canonical settings: {{code}}%(current)s{{/code}} ⇒ {{code}}%(site)s{{/code}}',
									'redirection'
								),
								{
									current: siteDomain,
									site: getCanonical( siteDomain, https, preferredDomain ),
								}
							),
							{
								code: <code />,
							}
						) }
					</p>
				</div>
			) }
		</>
	);
}

export default CanonicalSettings;
