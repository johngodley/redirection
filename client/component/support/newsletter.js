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

const Newsletter = props => {
	const { newsletter } = props;

	if ( newsletter ) {
		return (
			<div className="newsletter">
				<h3>{ __( 'Newsletter' )}</h3>

				<p>{ __( 'Thanks for subscribing! {{a}}Click here{{/a}} if you need to return to your subscription.', {
					components: {
						a: <a target="_blank" rel="noopener noreferrer" href="https://tinyletter.com/redirection" />,
					}
				} ) }</p>
			</div>
		);
	}

	return (
		<div className="newsletter">
			<h3>{ __( 'Newsletter' )}</h3>

			<p>{ __( 'Want to keep up to date with changes to Redirection?' ) }</p>
			<p>{ __( 'Sign up for the tiny Redirection newsletter - a low volume newsletter about new features and changes to the plugin. Ideal if want to test beta changes before release.' ) }</p>

			<form action="https://tinyletter.com/redirection" method="post" onSubmit={ props.onSubscribe }>
				<p>
					<label>{ __( 'Your email address:' ) } <input type="email" name="email" id="tlemail" /> <input type="submit" value="Subscribe" className="button-secondary" /></label>
					<input type="hidden" value="1" name="embed" /> <span><a href="https://tinyletter.com/redirection" target="_blank" rel="noreferrer noopener">Powered by TinyLetter</a></span>
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
			dispatch( saveSettings( { newsletter: 'true' } ) );
		}
	};
}

export default connect(
	null,
	mapDispatchToProps
)( Newsletter );
