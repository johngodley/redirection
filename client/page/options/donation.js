/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { FormTable, TableRow } from 'component/form-table';
import './donation.scss';

const MIN = 16;
const MAX = 100;

class Donation extends React.Component {
	static propTypes = {
		support: PropTypes.bool.isRequired,
	};

	constructor( props ) {
		super( props );

		this.onDonate = this.handleDonation.bind( this );
		this.onChange = this.handleChange.bind( this );
		this.onBlur = this.handleBlur.bind( this );
		this.onInput = this.handleInput.bind( this );
		this.state = {
			support: props.support,
			amount: 20,
		};
	}

	handleBlur() {
		this.setState( { amount: Math.max( MIN, this.state.amount ) } );
	}

	handleDonation() {
		this.setState( { support: false } );
	}

	getReturnUrl() {
		return document.location.href + '#thanks';
	}

	handleChange( data ) {
		if ( this.state.amount !== data.value ) {
			this.setState( { amount: parseInt( data.value, 10 ) } );
		}
	}

	handleInput( ev ) {
		const value = ev.target.value ? parseInt( ev.target.value, 10 ) : MIN;

		this.setState( { amount: value } );
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

		return amounts[ amounts.length - 1 ][ 1 ];
	}

	renderSupported() {
		return (
			<div>
				{ __( "You've supported this plugin - thank you!" ) } &nbsp;
				<a href="#" onClick={ this.onDonate }>{ __( "I'd like to support some more." ) }</a>
			</div>
		);
	}

	renderUnsupported() {
		const marks = { [ MIN ]: '' };

		for ( let x = 20; x <= MAX; x += 20 ) {
			marks[ x ] = '';
		}

		return (
			<div>
				<label>
					<p>
						{ __( 'Redirection is free to use - life is wonderful and lovely! It has required a great deal of time and effort to develop and you can help support this development by {{strong}}making a small donation{{/strong}}.', {
							components: {
								strong: <strong />,
							},
						} ) }
						&nbsp;{ __( 'You get useful software and I get to carry on making it better.' ) }
					</p>
				</label>

				<input type="hidden" name="cmd" value="_xclick" />
				<input type="hidden" name="business" value="admin@urbangiraffe.com" />
				<input type="hidden" name="item_name" value="Redirection (WordPress Plugin)" />
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

				<div className="donation-amount">
					$<input type="number" name="amount" min={ MIN } value={ this.state.amount } onChange={ this.onInput } onBlur={ this.onBlur } />

					<span>{ this.getAmountoji( this.state.amount ) }</span>
					<input type="submit" className="button-primary" value={ __( 'Support 💰' ) } />
				</div>
			</div>
		);
	}

	render() {
		const { support } = this.state;

		return (
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" className="donation">
				<FormTable>
					<TableRow title={ __( 'Plugin Support' ) + ':' }>
						{ support ? this.renderSupported() : this.renderUnsupported() }
					</TableRow>
				</FormTable>
			</form>
		);
	}
}

export default Donation;
