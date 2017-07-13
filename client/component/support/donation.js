/* global Redirectioni10n */
/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';
import ReactSimpleRange from 'react-simple-range';

const MIN = 16;
const MAX = 100;

class Donation extends React.Component {
	constructor( props ) {
		super( props );

		this.onDonate = this.handleDonation.bind( this );
		this.onChange = this.handleChange.bind( this );
		this.onInput = this.handleInput.bind( this );
		this.state = {
			donating: false,
			amount: 20,
		};
	}

	handleDonation() {
		this.setState( { donating: true } );
	}

	getReturnUrl() {
		return document.location.href + '#thanks';
	}

	renderAlreadySupported() {
		return (
			<div>
				<p>{ __( "You've already supported this plugin - thank you!" ) }</p>
				<button className="button-secondary" onClick={ this.onDonate }>{ __( "I'd like to donate some more" ) }</button>
			</div>
		);
	}

	renderNoSupported() {
		return (
			<div>
				<p>
					{ __( 'Redirection is free to use - life is wonderful and lovely! It has required a great deal of time and effort to develop and you can help support this development by {{strong}}making a small donation{{/strong}}.', {
						components: {
							strong: <strong />
						}
					} ) }
					{ __( 'You get some useful software and I get to carry on making it better.' ) }
				</p>

				<p>{ __( 'Please note I do not provide support and this is just a donation.' ) }</p>
				<button className="button-primary" onClick={ this.onDonate }>{ __( "Yes I'd like to donate" ) } &#128176;</button>
			</div>
		);
	}

	handleChange( data ) {
		if ( this.state.amount !== data.value ) {
			this.setState( { amount: parseInt( data.value, 10 ) } );
		}
	}

	handleInput( ev ) {
		const value = ev.target.value ? parseInt( ev.target.value, 10 ) : MIN;

		this.setState( { amount: Math.max( MIN, value ) } );
	}

	getAmountoji( amount ) {
		const amounts = [
			[ 100, '😍' ],
			[ 80, '😎' ],
			[ 60, '😊' ],
			[ 40, '😃' ],
			[ 20, '😀' ],
			[ 10, '🙂' ],
		];

		for ( let pos = 0; pos < amounts.length; pos++ ) {
			if ( amount >= amounts[ pos ][ 0 ] ) {
				return amounts[ pos ][ 1 ];
			}
		}

		return '';
	}

	renderDonating() {
		const buttonUrl = Redirectioni10n.pluginBaseUrl + '/images/donate.gif';
		const marks = { [ MIN ]: '' };

		for ( let x = 20; x <= MAX; x += 20 ) {
			marks[ x ] = '';
		}

		return (
			<div className="donation">
				<h2>{ __( 'Thank you for making a donation!' ) }</h2>

				<div>
					<ReactSimpleRange min={ MIN } max={ MAX } step={ 2 } defaultValue={ this.state.amount } value={ this.state.amount } onChange={ this.onChange } sliderSize={ 12 } thumbSize={ 18 } />
				</div>

				<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
					<input type="hidden" name="cmd" value="_xclick" />
					<input type="hidden" name="business" value="admin@urbangiraffe.com" />
					<input type="hidden" name="item_name" value="Redirection" />
					<input type="hidden" name="buyer_credit_promo_code" value="" />
					<input type="hidden" name="buyer_credit_product_category" value="" />
					<input type="hidden" name="buyer_credit_shipping_method" value="" />
					<input type="hidden" name="buyer_credit_user_address_change" value="" />
					<input type="hidden" name="no_shipping" value="1" />
					<input type="hidden" name="return" value={ this.getReturnUrl() } />
					<input type="hidden" name="no_note" value="1" />
					<input type="hidden" name="currency_code" value="USD" />
					<input type="hidden" name="tax" value="0" />
					<input type="hidden" name="lc" value="US" />
					<input type="hidden" name="bn" value="PP-DonationsBF" />
					<input type="image" src={ buttonUrl } name="submit" />

					<div className="donation-amount">
						$<input type="number" name="amount" min={ MIN } value={ this.state.amount } onChange={ this.onInput } />

						<span>{ this.getAmountoji( this.state.amount ) }</span>
					</div>
				</form>
			</div>
		);
	}

	render() {
		const { support } = this.props;

		if ( this.state.donating ) {
			return this.renderDonating();
		}

		return (
			<div>
				{ support ? this.renderAlreadySupported() : this.renderNoSupported() }
			</div>
		);
	}
}

Donation.propTypes = {
	support: PropTypes.bool,
};

export default Donation;
