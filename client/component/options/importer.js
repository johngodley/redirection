/* global Redirectioni10n */
/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */

import Select from 'component/wordpress/select';

class Importer extends React.Component {
	constructor( props ) {
		super( props );

		this.state = { group: 0 };
		this.onChange = this.handleInput.bind( this );
	}

	handleInput( event ) {
		const { target } = event;

		this.setState( { group: target.value } );
	}

	render() {
		return (
			<div className="wrap">
				<form action="" method="post" encType="multipart/form-data">
					<h2>{ __( 'Import' ) }</h2>

					<p>
						{ __( 'Here you can import redirections from an existing {{code}}.htaccess{{/code}} file, or a CSV file.', {
							components: {
								code: <code />
							}
						} ) }
					</p>

					<input type="file" name="upload" />	<Select items={ this.props.groups } name="group" value={ this.state.group } onChange={ this.onChange } />
					<input className="button-secondary" type="submit" name="import" value={ __( 'Upload' ) } />

					<h5>{ __( 'CSV Format' ) }</h5>
					<code>
						{ __( 'Source URL, Target URL, [Regex 0=false, 1=true], [HTTP Code]' ) }
					</code>

					<input type="hidden" name="_wpnonce" value={ Redirectioni10n.WP_API_nonce } />
				</form>
			</div>
		);
	}
}

function mapStateToProps( state ) {
	const { groups } = state.settings;

	return {
		groups,
	};
}

export default connect(
	mapStateToProps,
	null
)( Importer );
