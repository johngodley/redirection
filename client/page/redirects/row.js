/**
 * External dependencies
 */

import React from 'react';
import { translate as __, numberFormat } from 'lib/locale';
import { connect } from 'react-redux';
import classnames from 'classnames';
import PropTypes from 'prop-types';
import Highlighter from 'react-highlight-words';

/**
 * Internal dependencies
 */

import RowActions from 'component/table/row-action';
import EditRedirect from 'component/redirect-edit';
import Modal from 'component/modal';
import Badge from 'component/badge';
import Spinner from 'component/spinner';
import HttpCheck from 'component/http-check';
import ExternalLink from 'component/external-link';
import Column from 'component/table/column';
import { getSourceFlags, getMatches, getActions } from 'component/redirect-edit/constants';
import { setSelected, performTableAction } from 'state/redirect/action';
import { MATCH_URL, MATCH_SERVER, CODE_PASS, CODE_NOTHING } from 'state/redirect/selector';
import { STATUS_SAVING, STATUS_IN_PROGRESS } from 'state/settings/type';
import { isEnabled } from 'component/table/utils';

const RedirectFlag = ( { name, className } ) => ( <Badge className={ classnames( 'redirect-source__flag', className ) }>{ name }</Badge> );

class RedirectRow extends React.Component {
	static propTypes = {
		item: PropTypes.object.isRequired,
		selected: PropTypes.bool.isRequired,
		status: PropTypes.string.isRequired,
		defaultFlags: PropTypes.object,
	};

	constructor( props ) {
		super( props );

		this.state = {
			editing: false,
			showCheck: false,
		};
	}

	onEdit = ev => {
		ev.preventDefault();
		this.setState( { editing: true } );
	}

	onCancel = ev => {
		ev.preventDefault();
		this.setState( { editing: false } );
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

	onCheck = ev => {
		ev.preventDefault();
		this.setState( { showCheck: true } );
	}

	getMenu() {
		const { enabled, regex, action_type } = this.props.item;
		const menu = [];

		if ( enabled ) {
			menu.push( [ __( 'Edit' ), this.onEdit ] );
		}

		menu.push( [ __( 'Delete' ), this.onDelete ] );

		if ( enabled ) {
			menu.push( [ __( 'Disable' ), this.onDisable ] );

			if ( ! regex && action_type === 'url' ) {
				menu.push( [ __( 'Check Redirect' ), this.onCheck ] );
			}
		} else {
			menu.push( [ __( 'Enable' ), this.onEnable ] );
		}

		return menu
			.map( ( item, pos ) => <a key={ pos } href="#" onClick={ item[ 1 ] }>{ item[ 0 ] }</a> )
			.reduce( ( prev, curr ) => [ prev, ' | ', curr ] );
	}

	getStatus() {
		if ( this.props.item.enabled ) {
			return <div className="redirect-status redirect-status__enabled">✓</div>;
		}

		return <div className="redirect-status redirect-status__disabled">𐄂</div>;
	}

	getGroup() {
		const { item, group } = this.props;
		const foundGroup = group.rows.find( found => found.id === item.group_id );

		if ( foundGroup ) {
			return (
				<div className="redirect-column-wrap">
					{ foundGroup.name } <Badge>{ foundGroup.moduleName }</Badge>
				</div>
			);
		}

		return null;
	}

	getMatchType() {
		const { match_type } = this.props.item;
		const found = getMatches().find( item => item.value === match_type );

		return found ? found.label : '-';
	}

	getActionType() {
		const { action_type } = this.props.item;
		const found = getActions().find( item => item.value === action_type );

		return found ? found.label : '-';
	}

	getCode() {
		const { action_code, action_type } = this.props.item;

		if ( action_type === CODE_PASS ) {
			return __( 'pass' );
		}

		if ( action_type === CODE_NOTHING ) {
			return '-';
		}

		return action_code;
	}

	getTarget() {
		const { match_type, action_data } = this.props.item;

		if ( match_type === MATCH_URL ) {
			return <Highlighter searchWords={ [ this.props.filters.target ] } textToHighlight={ action_data.url } />;
		}

		return null;
	}

	getServerUrl( url, matchType ) {
		if ( matchType === MATCH_SERVER ) {
			const { action_data } = this.props.item;

			return action_data.server + url;
		}

		return url;
	}

	wrapEnabled( source ) {
		if ( this.props.item.enabled ) {
			return source;
		}

		return <strike>source</strike>;
	}

	getName( url, title ) {
		const { currentDisplaySelected } = this.props;
		const { match_type } = this.props.item;
		const parts = [];
		const serverUrl = <Highlighter searchWords={ [ this.props.filters.url ] } textToHighlight={ this.getServerUrl( url, match_type ) } />;
		const titled = <Highlighter searchWords={ [ this.props.filters.title ] } textToHighlight={ title } />;

		if ( isEnabled( currentDisplaySelected, 'title' ) && ! isEnabled( currentDisplaySelected, 'source' ) ) {
			// Source or title
			parts.push( <p key="0">{ this.getAsLink( url, this.wrapEnabled( titled || serverUrl ) ) }</p> );
		} else {
			if ( isEnabled( currentDisplaySelected, 'title' ) && title ) {
				parts.push( <p key="1">{ this.getAsLink( url, this.wrapEnabled( titled ) ) }</p> );
			}

			if ( isEnabled( currentDisplaySelected, 'source' ) && serverUrl ) {
				parts.push( <p key="2">{ this.getAsLink( url, this.wrapEnabled( serverUrl ) ) }</p> );
			}
		}

		return parts;
	}

	getAsLink( url, content ) {
		const { match_type, regex } = this.props.item;

		if ( regex ) {
			return content;
		}

		return (
			<ExternalLink url={ this.getServerUrl( url, match_type ) }>
				{ content }
			</ExternalLink>
		);
	}

	renderFlags() {
		const { match_data: { source } } = this.props.item;
		const { defaultFlags } = this.props;

		return Object.keys( source )
			.filter( key => defaultFlags[ key ] !== source[ key ] && key !== 'flag_query' )
			.map( key => {
				const displayName = getSourceFlags().find( item => item.value === key );

				return <RedirectFlag key={ key } name={ displayName.label } className={ 'redirect-source__' + key } />;
			} );
	}

	renderQuery() {
		const { match_data: { source } } = this.props.item;
		const { defaultFlags } = this.props;

		if ( defaultFlags.flag_query !== source.flag_query ) {
			let name = __( 'Exact Query' );

			if ( source.flag_query === 'ignore' ) {
				name = __( 'Ignore Query' );
			} else if ( source.flag_query === 'pass' ) {
				name = __( 'Ignore & Pass Query' );
			}

			return <RedirectFlag name={ name } />;
		}

		return null;
	}

	renderSource( url, title, saving ) {
		const { currentDisplaySelected } = this.props;

		return (
			<td className="column-primary column-url has-row-actions">
				<div className="redirect-column-wrap">
					<div className="redirect-source__details">
						{ this.getName( url, title ) }

						{ isEnabled( currentDisplaySelected, 'target' ) && <span className="target">{ this.getTarget() }</span> }

						<RowActions disabled={ saving }>
							{ this.getMenu() }
						</RowActions>
					</div>

					<div className="redirect-source__flags">
						{ isEnabled( currentDisplaySelected, 'flags' ) && this.renderFlags() }
						{ isEnabled( currentDisplaySelected, 'query' ) && this.renderQuery() }
					</div>
				</div>
			</td>
		);
	}

	getColumnCount( selected ) {
		const total = selected.length;
		const merged = [ 'source', 'flags', 'query', 'title', 'target' ].filter( item => selected.indexOf( item ) !== -1 );

		return total - ( merged.length > 0 ? merged.length - 1 : 0 );
	}

	renderEditColumns() {
		const { currentDisplaySelected } = this.props;

		return (
			<td className="column-primary column-url redirect-edit" colSpan={ this.getColumnCount( currentDisplaySelected ) }>
				<EditRedirect item={ this.props.item } onCancel={ this.onCancel } />
			</td>
		);
	}

	closeCheck = () => {
		this.setState( { showCheck: false } );
	}

	renderCheck() {
		return (
			<Modal onClose={ this.closeCheck } padding={ false }>
				<HttpCheck item={ this.props.item } />
			</Modal>
		);
	}

	renderViewColumns( isSaving ) {
		const { url, hits, last_access, title, position } = this.props.item;
		const { currentDisplaySelected } = this.props;

		return (
			<React.Fragment>
				<Column enabled="status" className="column-status" selected={ currentDisplaySelected }>
					{ this.getStatus() }
				</Column>

				{ this.renderSource( url, title, isSaving ) }

				<Column enabled="match_type" className="column-match_type" selected={ currentDisplaySelected }>
					{ this.getMatchType() }
				</Column>

				<Column enabled="action_type" className="column-action_type" selected={ currentDisplaySelected }>
					{ this.getActionType() }
				</Column>

				<Column enabled="code" className="column-code" selected={ currentDisplaySelected }>
					{ this.getCode() }
				</Column>

				<Column enabled="group" className="column-group" selected={ currentDisplaySelected }>
					{ this.getGroup() }
				</Column>

				<Column enabled="position" className="column-position" selected={ currentDisplaySelected }>
					{ numberFormat( position ) }
					{ this.state.showCheck && this.renderCheck() }
				</Column>

				<Column enabled="last_count" className="column-last_count" selected={ currentDisplaySelected }>
					{ numberFormat( hits ) }
				</Column>

				<Column enabled="last_access" className="column-last_access" selected={ currentDisplaySelected }>
					{ last_access }
				</Column>
			</React.Fragment>
		);
	}

	render() {
		const { id, enabled } = this.props.item;
		const { selected, status } = this.props;
		const isLoading = status === STATUS_IN_PROGRESS;
		const isSaving = status === STATUS_SAVING;
		const hideRow = ! enabled || isLoading || isSaving;
		const classes = classnames( {
			disabled: hideRow,
		} );

		return (
			<tr className={ classes }>
				<th scope="row" className="check-column">
					{ ! isSaving && <input type="checkbox" name="item[]" value={ id } disabled={ isLoading } checked={ selected } onChange={ this.onSelected } /> }
					{ isSaving && <Spinner size="small" /> }
				</th>

				{ this.state.editing ? this.renderEditColumns() : this.renderViewColumns( isSaving ) }
			</tr>
		);
	}
}

function mapDispatchToProps( dispatch ) {
	return {
		onSetSelected: items => {
			dispatch( setSelected( items ) );
		},
		onTableAction: ( action, ids ) => {
			dispatch( performTableAction( action, ids ) );
		},
	};
}

function mapStateToProps( state ) {
	const { group } = state;

	return {
		group,
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps,
)( RedirectRow );
