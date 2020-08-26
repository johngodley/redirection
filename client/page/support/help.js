/* global Redirectioni10n */
/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';

import ExternalLink from 'wp-plugin-components/external-link';

const Help = () => {
	return (
		<div>
			<h2>{ __( 'Need help?' ) }</h2>
			<p>
				{ __( 'Full documentation for Redirection can be found at {{site}}https://redirection.me{{/site}}. If you have a problem please check the {{faq}}FAQ{{/faq}} first.', {
					components: {
						site: <ExternalLink url="https://redirection.me" />,
						faq: <ExternalLink url="https://redirection.me/support/faq/" />,
					},
				} ) }
			</p>
			<p><strong>{ __( 'If you want to report a bug please read the {{report}}Reporting Bugs{{/report}} guide.', {
				components: {
					report: <ExternalLink url="https://redirection.me/support/reporting-bugs/" />,
				},
			} ) }</strong></p>
			<div className="inline-notice inline-general">
				<p className="github">
					<ExternalLink url="https://github.com/johngodley/redirection/issues">
						<img src={ Redirectioni10n.pluginBaseUrl + '/images/GitHub-Mark-64px.png' } width="32" height="32" />
					</ExternalLink>
					<ExternalLink url="https://github.com/johngodley/redirection/issues">
						https://github.com/johngodley/redirection/
					</ExternalLink>
				</p>
			</div>
			<p>{ __( 'Please note that any support is provide on as-time-is-available basis and is not guaranteed. I do not provide paid support.' ) }</p>
			<p>{ __( "If you want to submit information that you don't want in a public repository then send it directly via {{email}}email{{/email}} - include as much information as you can!", {
				components: {
					email: <a href={ 'mailto:john@redirection.me?subject=Redirection%20Issue&body=' + encodeURIComponent( 'Redirection: ' + Redirectioni10n.versions ) } />,
				},
			} ) }
			</p>
			<h2>{ __( 'Need to search and replace?' ) }</h2>
			<p>{ __( 'The companion plugin Search Regex allows you to search and replace data on your site. It also supports Redirection, and is handy if you want to bulk update a lot of redirects.' ) }</p>
		</div>
	);
};

export default Help;
