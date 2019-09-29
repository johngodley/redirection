/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */

import EditRedirect from 'component/redirect-edit';
import Modal from 'component/modal';
import { getDefaultItem, MATCH_IP } from 'state/redirect/selector';
import { getFlags } from 'state/settings/selector';
import { deleteExact } from 'state/error/action';

class CreateRedirect extends React.Component {
	static propTypes = {
		onClose: PropTypes.func.isRequired,
		create: PropTypes.object.isRequired,
		transform: PropTypes.func,
	};

	static defaultProps = {
		transform: null,
	};

	constructor( props ) {
		super( props );

		this.state = {
			deleteLog: false,
			height: 0,
		};
	}

	onDeleteLog = ev => {
		this.setState( { deleteLog: ev.target.checked } );
	}

	onDelete = () => {
		const selected = this.getSelected();
		const { deleteLog } = this.state;

		if ( deleteLog ) {
			this.props.onDelete( selected );
		}
	}

	setHeight = height => {
		this.setState( { height } );
	}

	getSelected() {
		const { transform } = this.props;

		return transform ? this.props.selected.map( transform ) : this.props.selected;
	}

	render() {
		const { onClose, create, defaultFlags } = this.props;
		const selected = this.getSelected();
		const item = { ... getDefaultItem( selected[ 0 ], 0, defaultFlags ), ... create };

		if ( item.match_type === MATCH_IP ) {
			item.url = '^/.*$';
			item.match_data.source.flag_regex = true;
		} else if ( selected.length > 1 ) {
			item.url = selected;
		}

		return (
			<Modal onClose={ onClose }>
				<div className="add-new">
					<EditRedirect item={ item } saveButton={ __( 'Add Redirect' ) } onCancel={ onClose } childSave={ this.onDelete } autoFocus callback={ this.setHeight }>
						<tr>
							<th>{ __( 'Delete Log Entries' ) }</th>
							<td className="edit-left" style={ { padding: '7px 0px' } }>
								<label>
									<input type="checkbox" name="delete_log" checked={ this.state.deleteLog } onChange={ this.onDeleteLog } />

									{ selected.length === 1 ? __( 'Delete all logs for this entry' ) : __( 'Delete all logs for these entries' ) }
								</label>
							</td>
						</tr>
					</EditRedirect>
				</div>
			</Modal>
		);
	}
}

function mapDispatchToProps( dispatch ) {
	return {
		onDelete: selected => {
			dispatch( deleteExact( selected ) );
		},
	};
}

function mapStateToProps( state ) {
	const { selected } = state.error.table;

	return {
		selected,
		defaultFlags: getFlags( state ),
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( CreateRedirect );
