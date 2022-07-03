/**
 * External dependencies
 */

import { __ } from '@wordpress/i18n';

import { ExternalLink, createInterpolateElement } from 'wp-plugin-components';

const Help = () => {
	return (
		<div>
			<h2>{ __( 'Need help?', 'redirection' ) }</h2>
			<p>
				{ createInterpolateElement(
					__(
						'Full documentation for Redirection can be found at {{site}}https://redirection.me{{/site}}. If you have a problem please check the {{faq}}FAQ{{/faq}} first.',
						'redirection'
					),
					{
						site: <ExternalLink url="https://redirection.me" />,
						faq: <ExternalLink url="https://redirection.me/support/faq/" />,
					}
				) }
			</p>
			<p>
				<strong>
					{ createInterpolateElement(
						__( 'If you want to report a bug please read the {{report}}Reporting Bugs{{/report}} guide.', 'redirection' ),
						{
							report: <ExternalLink url="https://redirection.me/support/reporting-bugs/" />,
						},
					) }
				</strong>
			</p>
			<div className="inline-notice inline-general">
				<p className="github">
					<ExternalLink url="https://github.com/johngodley/redirection/issues">
						<img
							src={ window.Redirectioni10n.pluginBaseUrl + '/images/GitHub-Mark-64px.png' }
							width="32"
							height="32"
						/>
					</ExternalLink>
					<ExternalLink url="https://github.com/johngodley/redirection/issues">
						https://github.com/johngodley/redirection/
					</ExternalLink>
				</p>
			</div>
			<p>
				{ __(
					'Please note that any support is provide on as-time-is-available basis and is not guaranteed. I do not provide paid support.',
					'redirection'
				) }
			</p>
			<p>
				{ createInterpolateElement(
					__(
						"If you want to submit information that you don't want in a public repository then send it directly via {{email}}email{{/email}} - include as much information as you can!",
						'redirection'
					),
					{
						email: (
							<a
								href={
									'mailto:john@redirection.me?subject=Redirection%20Issue&body=' +
									encodeURIComponent( 'Redirection: ' + window.Redirectioni10n.versions )
								}
							/>
						),
					}
				) }
			</p>
			<h2>{ __( 'Need to search and replace?', 'redirection' ) }</h2>
			<p>
				{ __(
					'The companion plugin Search Regex allows you to search and replace data on your site. It also supports Redirection, and is handy if you want to bulk update a lot of redirects.',
					'redirection'
				) }
			</p>
		</div>
	);
};

export default Help;
