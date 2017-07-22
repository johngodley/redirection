/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';

class ApacheConfigure extends React.Component {
	constructor( props ) {
		super( props );

		this.state = {
			location: props.data.location,
			canonical: props.data.canonical,
		};

		this.onSave = this.handleSave.bind( this );
		this.onChange = this.handleChange.bind( this );
	}

	handleSave() {
		this.props.onSave( 2, this.state );
	}

	handleChange( ev ) {
		const { target } = ev;
		const value = target.value;

		this.setState( { [ target.name ]: value } );
	}

	render() {
		const { onClose, data } = this.props;
		const { installed } = data;

		return (
			<table className="edit">
				<tbody>
					<tr>
						<th width="170">{ __( '.htaccess Location' ) }</th>
						<td>
							<input type="text" name="location" value={ this.state.location } onChange={ this.onChange } />

							<p className="sub">
								{ __( 'If you want Redirection to automatically update your {{code}}.htaccess{{/code}} file then enter the full path and filename here. You can also download the file and update it manually.', {
									components: {
										code: <code />,
									}
								} ) }
							</p>
							<p className="sub">
								{ __( 'WordPress is installed in: {{code}}%s{{/code}}', {
									args: installed,
									components: {
										code: <code />,
									}
								} ) }
							</p>
						</td>
					</tr>
					<tr>
						<th>{ __( 'Canonical URL' ) }:</th>
						<td>
							<select name="canonical" value={ this.state.canonical } onChange={ this.onChange }>
								<option value="">{ __( 'Default server' ) }</option>
								<option value="nowww">{ __( 'Remove WWW' ) }</option>
								<option value="www">{ __( 'Add WWW' ) }</option>
							</select>

							<p className="sub">{ __( 'Automatically remove or add www to your site.' ) }</p>
						</td>
					</tr>
					<tr>
						<th width="70"></th>
						<td>
							<div className="table-actions">
								<input className="button-primary" type="submit" name="save" value={ __( 'Save' ) } onClick={ this.onSave } /> &nbsp;
								<input className="button-secondary" type="submit" name="cancel" value={ __( 'Cancel' ) } onClick={ onClose } />
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		);
	}
}

export default ApacheConfigure;
