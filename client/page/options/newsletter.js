/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */

import { saveSettings } from 'state/settings/action';
import ExternalLink from 'wp-plugin-components/external-link';

const Newsletter = props => {
	const { newsletter } = props;

	if ( newsletter ) {
		return (
			<div className="newsletter">
				<h3>{ __( 'Newsletter' )}</h3>

				<p>{ __( 'Thanks for subscribing! {{a}}Click here{{/a}} if you need to return to your subscription.', {
					components: {
						a: <ExternalLink url="https://tinyletter.com/redirection" />,
					}
				} ) }</p>
			</div>
		);
	}

	return (
		<div className="newsletter">
			<h3>{ __( 'Newsletter' )}</h3>

			<p>{ __( 'Want to keep up to date with changes to Redirection?' ) }</p>
			<p>{ __( 'Sign up for the tiny Redirection newsletter - a low volume newsletter about new features and changes to the plugin. Ideal if you want to test beta changes before release.' ) }</p>

			<form action="https://tinyletter.com/redirection" method="post" onSubmit={ props.onSubscribe }>
				<p>
					<label>{ __( 'Your email address:' ) } <input type="email" name="email" id="tlemail" /> <input type="submit" value="Subscribe" className="button-secondary" /></label>
					<input type="hidden" value="1" name="embed" /> <span><ExternalLink url="https://tinyletter.com/redirection">Powered by TinyLetter</ExternalLink></span>
				</p>
			</form>
		</div>
	);
};

Newsletter.propTypes = {
	newsletter: PropTypes.bool,
};

function mapDispatchToProps( dispatch ) {
	return {
		onSubscribe: () => {
			dispatch( saveSettings( { newsletter: true } ) );
		}
	};
}

export default connect(
	null,
	mapDispatchToProps
)( Newsletter );
