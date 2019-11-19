/* global Redirectioni10n */
/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
import Highlighter from 'react-highlight-words';

/**
 * Internal dependencies
 */

import RowActions from 'component/table/row-action';
import { setSelected, updateGroup, performTableAction } from 'state/group/action';
import { STATUS_IN_PROGRESS, STATUS_SAVING } from 'state/settings/type';
import { getModuleName, getModules } from 'state/io/selector';
import Spinner from 'component/spinner';
import Select from 'component/select';
import Badge from 'component/badge';
import Column from 'component/table/column';
import { has_capability, CAP_GROUP_ADD, CAP_GROUP_DELETE, CAP_REDIRECT_MANAGE } from 'lib/capabilities';

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

	getMenu() {
		const { id, enabled } = this.props.item;
		const menu = [];

		if ( has_capability( CAP_GROUP_ADD ) ) {
			menu.push( [ __( 'Edit' ), this.onEdit ] );
		}

		if ( has_capability( CAP_GROUP_DELETE ) ) {
			menu.push( [ __( 'Delete' ), this.onDelete ] );
		}

		if ( has_capability( CAP_REDIRECT_MANAGE ) ) {
			menu.push( [ __( 'View Redirects' ), () => {}, Redirectioni10n.pluginRoot + '&' + encodeURIComponent( 'filterby[group]' ) + '=' + id ] );
		}

		if ( has_capability( CAP_GROUP_ADD ) ) {
			if ( enabled ) {
				menu.push( [ __( 'Disable' ), this.onDisable ] );
			} else {
				menu.push( [ __( 'Enable' ), this.onEnable ] );
			}
		}

		if ( menu.length === 0 ) {
			return [];
		}

		return menu
			.map( ( item, pos ) => <a key={ pos } href={ item[ 2 ] ? item[ 2 ] : '#' } onClick={ item[ 1 ] }>{ item[ 0 ] }</a> )
			.reduce( ( prev, curr ) => [ prev, ' | ', curr ] );
	}

	renderActions( saving ) {
		return (
			<RowActions disabled={ saving }>
				{ this.getMenu() }
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
							<td><input type="text" className="regular-text" name="name" value={ this.state.name } onChange={ this.onChange } /></td>
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
			return <Highlighter searchWords={ [ this.props.filters.name ] } textToHighlight={ name } autoEscape />;
		}

		return <strike>{ name }</strike>;
	}

	getStatus() {
		if ( this.props.item.enabled ) {
			return <div className="redirect-status redirect-status__enabled">✓</div>;
		}

		return <div className="redirect-status redirect-status__disabled">𐄂</div>;
	}

	enableModule( moduleId ) {
		this.props.setFilter( 'module', moduleId );
	}

	render() {
		const { name, redirects, id, module_id, enabled } = this.props.item;
		const { selected, status, currentDisplaySelected } = this.props;
		const isLoading = status === STATUS_IN_PROGRESS;
		const isSaving = status === STATUS_SAVING;
		const hideRow = ! enabled || isLoading || isSaving;

		return (
			<tr className={ hideRow ? 'disabled' : '' }>
				<th scope="row" className="check-column">
					{ ! isSaving && <input type="checkbox" name="item[]" value={ id } disabled={ isLoading } checked={ selected } onChange={ this.onSelected } /> }
					{ isSaving && <Spinner size="small" /> }
				</th>

				<Column enabled="status" className="column-status" selected={ currentDisplaySelected }>
					{ this.getStatus() }
				</Column>

				<Column enabled="name" className="column-primary column-name" selected={ currentDisplaySelected }>
					{ ! this.state.editing && this.getName( name, enabled ) }
					{ this.state.editing ? this.renderEdit() : this.renderActions( isSaving ) }
				</Column>

				<Column enabled="redirects" className="column-redirects" selected={ currentDisplaySelected }>
					{ redirects }
				</Column>

				<Column enabled="module" className="column-module" selected={ currentDisplaySelected }>
					<Badge
						onClick={ () => this.enableModule( module_id ) }
						title={ __( 'Filter on: %(type)s', { args: { type: getModuleName( module_id ) } } ) }
					>
						{ getModuleName( module_id ) }
					</Badge>
				</Column>
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
