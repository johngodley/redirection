/* global Redirectioni10n */
/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

import RowActions from 'component/table/row-action';
import { setSelected, updateGroup, performTableAction } from 'state/group/action';
import { STATUS_IN_PROGRESS, STATUS_SAVING } from 'state/settings/type';
import { getModuleName, getModules } from 'state/io/selector';
import Spinner from 'component/spinner';
import Select from 'component/select';

class GroupRow extends React.Component {
	constructor( props ) {
		super( props );

		this.state = { editing: false, name: props.item.name, moduleId: props.item.module_id };
	}

	onEdit = ev => {
		ev.preventDefault();
		this.setState( { editing: ! this.state.editing } );
	}

	onDelete = ev => {
		ev.preventDefault();
		this.props.onTableAction( 'delete', this.props.item.id );
	}

	onDisable = ev => {
		ev.preventDefault();
		this.props.onTableAction( 'disable', this.props.item.id );
	}

	onEnable = ev => {
		ev.preventDefault();
		this.props.onTableAction( 'enable', this.props.item.id );
	}

	onSelected = () => {
		this.props.onSetSelected( [ this.props.item.id ] );
	}

	onChange = ev => {
		const { target } = ev;

		this.setState( { name: target.value } );
	}

	onSave = ev => {
		this.onEdit( ev );
		this.props.onSaveGroup( this.props.item.id, { name: this.state.name, moduleId: this.state.moduleId } );
	}

	onSelect = ev => {
		const { target } = ev;

		this.setState( { moduleId: parseInt( target.value, 10 ) } );
	}

	renderLoader() {
		return (
			<div className="loader-wrapper">
				<div className="placeholder-loading loading-small" style={ { top: '0px' } }>
				</div>
			</div>
		);
	}

	renderActions( saving ) {
		const { id, enabled } = this.props.item;

		return (
			<RowActions disabled={ saving }>
				<a href="#" onClick={ this.onEdit }>{ __( 'Edit' ) }</a> |&nbsp;
				<a href="#" onClick={ this.onDelete }>{ __( 'Delete' ) }</a> |&nbsp;
				<a href={ Redirectioni10n.pluginRoot + '&filterby=group&filter=' + id }>{ __( 'View Redirects' ) }</a> |&nbsp;
				{ enabled && <a href="#" onClick={ this.onDisable }>{ __( 'Disable' ) }</a> }
				{ ! enabled && <a href="#" onClick={ this.onEnable }>{ __( 'Enable' ) }</a> }
			</RowActions>
		);
	}

	renderEdit() {
		return (
			<form onSubmit={ this.onSave } >
				<table className="edit-groups">
					<tbody>
						<tr>
							<th width="70">{ __( 'Name' ) }</th>
							<td><input type="text" name="name" value={ this.state.name } onChange={ this.onChange } /></td>
						</tr>
						<tr>
							<th width="70">{ __( 'Module' ) }</th>
							<td>
								<Select name="module_id" value={ this.state.moduleId } onChange={ this.onSelect } items={ getModules() } />
							</td>
						</tr>
						<tr>
							<th width="70"></th>
							<td>
								<div className="table-actions">
									<input className="button-primary" type="submit" name="save" value={ __( 'Save' ) } /> &nbsp;
									<input className="button-secondary" type="submit" name="cancel" value={ __( 'Cancel' ) } onClick={ this.onEdit } />
								</div>

								{ parseInt( this.state.moduleId, 10 ) === 2 && <p><br />{ __( 'Note that you will need to set the Apache module path in your Redirection options.' ) }</p> }
							</td>
						</tr>
					</tbody>
				</table>
			</form>
		);
	}

	getName( name, enabled ) {
		if ( enabled ) {
			return name;
		}

		return <strike>{ name }</strike>;
	}

	render() {
		const { name, redirects, id, module_id, enabled } = this.props.item;
		const { selected, status } = this.props;
		const isLoading = status === STATUS_IN_PROGRESS;
		const isSaving = status === STATUS_SAVING;
		const hideRow = ! enabled || isLoading || isSaving;

		return (
			<tr className={ hideRow ? 'disabled' : '' }>
				<th scope="row" className="check-column">
					{ ! isSaving && <input type="checkbox" name="item[]" value={ id } disabled={ isLoading } checked={ selected } onChange={ this.onSelected } /> }
					{ isSaving && <Spinner size="small" /> }
				</th>
				<td className="column-primary column-name">
					{ ! this.state.editing && this.getName( name, enabled ) }
					{ this.state.editing ? this.renderEdit() : this.renderActions( isSaving ) }
				</td>
				<td className="column-redirects">
					{ redirects }
				</td>
				<td className="column-module">
					{ getModuleName( module_id ) }
				</td>
			</tr>
		);
	}
}

GroupRow.propTypes = {
	item: PropTypes.object.isRequired,
	selected: PropTypes.bool.isRequired,
	status: PropTypes.string.isRequired,
};

function mapDispatchToProps( dispatch ) {
	return {
		onSetSelected: items => {
			dispatch( setSelected( items ) );
		},
		onSaveGroup: ( id, item ) => {
			dispatch( updateGroup( id, item ) );
		},
		onTableAction: ( action, ids ) => {
			dispatch( performTableAction( action, ids ) );
		},
	};
}

export default connect(
	null,
	mapDispatchToProps,
)( GroupRow );
