/* global Redirectioni10n */
/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';

const Help = () => {
	return (
		<div>
			<h2>{ __( 'Need help?' ) }</h2>
			<p>{ __( 'First check the FAQ below. If you continue to have a problem then please disable all other plugins and check if the problem persists.' ) }</p>
			<p>{ __( 'You can report bugs and new suggestions in the Github repository. Please provide as much information as possible, with screenshots, to help explain your issue.' ) }</p>
			<div className="inline-notice inline-general">
				<p className="github">
					<a target="_blank" rel="noopener noreferrer" href="https://github.com/johngodley/redirection/issues">
						<img src={ Redirectioni10n.pluginBaseUrl + '/images/GitHub-Mark-64px.png' } width="32" height="32" />
					</a>
					<a target="_blank" rel="noopener noreferrer" href="https://github.com/johngodley/redirection/issues">https://github.com/johngodley/redirection/</a>
				</p>
			</div>
			<p>{ __( 'Please note that any support is provide on as-time-is-available basis and is not guaranteed. I do not provide paid support.' ) }</p>
			<p>{ __( "If you want to submit information that you don't want in a public repository then send it directly via {{email}}email{{/email}}.", {
				components: {
					email: <a href={ 'mailto:john@urbangiraffe.com?subject=Redirection%20Issue&body=' + encodeURIComponent( 'Redirection: ' + Redirectioni10n.versions ) } />
				}
			} ) }
			</p>
		</div>
	);
};

export default Help;
