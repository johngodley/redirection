/* global Redirectioni10n */
/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';
import { translate as __ } from 'lib/locale';

/**
 * Internal dependencies
 */

import Faq from './faq';
import Newsletter from './newsletter';
import { loadSettings } from 'state/settings/action';

class Support extends React.Component {
	constructor( props ) {
		super( props );

		props.onLoadSettings();
	}

	render() {
		const { newsletter = false } = this.props.values ? this.props.values : {};

		return (
			<div>
				<h2>{ __( 'Need help?' ) }</h2>
				<p>{ __( 'First check the FAQ below. If you continue to have a problem then please disable all other plugins and check if the problem persists.' ) }</p>
				<p>{ __( 'You can report bugs and new suggestions in the Github repository. Please provide as much information as possible, with screenshots, to help explain your issue.' ) }</p>
				<p className="github">
					<a target="_blank" rel="noopener noreferrer" href="https://github.com/johngodley/redirection/issues">
						<img src={ Redirectioni10n.pluginBaseUrl + '/images/GitHub-Mark-64px.png' } width="32" height="32" />
					</a>
					<a target="_blank" rel="noopener noreferrer" href="https://github.com/johngodley/redirection/issues">https://github.com/johngodley/redirection/</a>
				</p>
				<p>{ __( "If you want to submit information that you don't want in a public repository then send it directly via {{email}}email{{/email}}.", {
					components: {
						email: <a href={ 'mailto:john@urbangiraffe.com?subject=Redirection%20Issue&body=' + encodeURIComponent( 'Redirection: ' + Redirectioni10n.versions ) } />
					}
				} ) }
				</p>
				<Faq />
				<Newsletter newsletter={ newsletter } />
			</div>
		);
	}
}

function mapDispatchToProps( dispatch ) {
	return {
		onLoadSettings: () => {
			dispatch( loadSettings() );
		},
	};
}

function mapStateToProps( state ) {
	const { values } = state.settings;

	return {
		values,
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( Support );
