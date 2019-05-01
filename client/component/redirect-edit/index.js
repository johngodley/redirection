/**
 * External dependencies
 */

import React from 'react';
import PropTypes from 'prop-types';
import { translate as __ } from 'lib/locale';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */

import TableRow from './table-row';
import RedirectSourceUrl from './source-url';
import RedirectSourceQuery from './source-query';
import RedirectGroup from './group';
import RedirectPosition from './position';
import RedirectTitle from './title';
import ActionCode from './action-code';
import ActionType from './action-type';
import MatchType from './match-type';
import MatchTarget from './match';
import ActionTarget from './action';
import { getWarningFromState, Warnings } from './warning';
import { updateRedirect, createRedirect, addToTop } from 'state/redirect/action';
import { getOption, getFlags } from 'state/settings/selector';
import { getGroup } from 'state/group/selector';
import {
	ACTION_URL,
	MATCH_URL,
	MATCH_LOGIN,
	hasUrlTarget,
	getMatchState,
	hasTargetData,
	getDefaultItem,
	getCodeForActionType,
} from 'state/redirect/selector';
import './style.scss';

class EditRedirect extends React.Component {
	static propTypes = {
		item: PropTypes.object.isRequired,
		onCancel: PropTypes.func,
		saveButton: PropTypes.string,
		childSave: PropTypes.func,
		callback: PropTypes.func,
	};

	constructor( props ) {
		super( props );

		const { url, match_data, match_type, action_type, action_data, group_id = 0, title, action_code, position = 0 } = props.item;
		const { flag_regex, flag_trailing, flag_case, flag_query } = match_data.source;

		this.state = {
			url,
			title,

			flag_regex,
			flag_trailing,
			flag_case,
			flag_query,

			match_type,
			action_type,
			action_code,
			action_data: getMatchState( match_type, action_data ),

			group_id: this.getValidGroup( group_id ),
			position,
		};

		this.state.warning = getWarningFromState( this.state );
		this.state.advanced = ! this.canShowAdvanced();

		this.ref = React.createRef();
	}

	getWarning( newState ) {
		return getWarningFromState( { ... this.state, ... newState } );
	}

	getValidGroup( group_id ) {
		const groups = this.props.group.rows;
		const { table } = this.props;

		// Return the group, if found
		if ( getGroup( groups, group_id ) ) {
			return group_id;
		}

		// Return the current group filter
		if ( table.filterBy === 'group' && parseInt( table.filter, 10 ) > 0 ) {
			return parseInt( table.filter, 10 );
		}

		if ( groups.length > 0 ) {
			// todo: Not sure what this is
			const def = groups.find( item => item.default );
			if ( def ) {
				return def.id;
			}

			// Return first group
			return groups[ 0 ].id;
		}

		return 0;
	}

	reset() {
		const source = this.props.flags;

		this.setState( {
			... getDefaultItem( '', this.state.group_id, source ),
			warning: [],
			id: this.state.id,
		} );
	}

	canShowAdvanced() {
		const { match_type, action_type, title, action_code } = this.state;

		return match_type === MATCH_URL && action_type === ACTION_URL && title === '' && action_code === 301;
	}

	onSave = ev => {
		ev.preventDefault();

		const { url, title, flag_regex, flag_trailing, flag_case, flag_query, match_type, action_type, group_id, action_code, position, action_data } = this.state;
		const groups = this.props.group.rows;

		const redirect = {
			id: parseInt( this.props.item.id, 10 ),
			url,
			title,
			match_data: {
				source: {
					flag_regex,
					flag_trailing,
					flag_case,
					flag_query,
				},
			},
			match_type,
			action_type,
			position,
			group_id: group_id > 0 ? group_id : groups[ 0 ].id,
			action_code: parseInt( action_code, 10 ),
			action_data: getMatchState( match_type, action_data ),
		};

		if ( redirect.id ) {
			this.props.onSave( redirect.id, redirect );
		} else {
			this.props.onCreate( redirect );
		}

		this.props.onCancel ? this.props.onCancel( ev ) : this.reset();

		if ( this.props.childSave ) {
			this.props.childSave();
		}
	}

	onToggleAdvanced = ev => {
		ev.preventDefault();
		this.onUpdateState( { advanced: ! this.state.advanced } );
	}

	onSetGroup = ev => {
		this.setState( { group_id: parseInt( ev.target.value, 10 ) } );
	}

	onFlagChange = option => {
		const options = option.map( item => item.value );
		const flags = {
			flag_regex: options.indexOf( 'flag_regex' ) !== -1 ? true : false,
			flag_case: options.indexOf( 'flag_case' ) !== -1 ? true : false,
			flag_trailing: options.indexOf( 'flag_trailing' ) !== -1 ? true : false,
		};

		this.onUpdateState( flags );
	}

	getInputState( ev ) {
		const { target } = ev;
		const value = target.type === 'checkbox' ? target.checked : target.value;

		return {
			[ target.name ]: value,
		};
	}

	onChangeMatch = ev => {
		const newState = this.getInputState( ev );

		// Reset action data for match type
		newState.action_data = getMatchState( newState.match_type, this.state.action_data );

		if ( newState.match_type === MATCH_LOGIN ) {
			// Reset action type for login matches
			newState.action_type = ACTION_URL;
		}

		this.onUpdateState( newState );
	}

	onChange = ev => {
		this.onUpdateState( this.getInputState( ev ) );
	}

	onChangeActionType = ev => {
		const action_type = this.getInputState( ev ).action_type;

		this.onUpdateState( {
			action_type,
			action_code: getCodeForActionType( action_type ),
		} );
	}

	onChangeActionData = ev => {
		const state = {
			action_data: {
				... this.state.action_data,
				... this.getInputState( ev ),
			},
		};

		this.onUpdateState( state );
	}

	onUpdateState( newState ) {
		// Update warning
		newState.warning = this.getWarning( newState );

		// Set state, ensuring any callback is triggered with our new height
		this.setState( newState, () => {
			if ( this.props.callback ) {
				this.props.callback( this.ref.current.clientHeight );
			}
		} );
	}

	canSave() {
		const { match_type, action_type, action_data, url } = this.state;
		const { autoTarget } = this.props;

		if ( url.length === 0 && ! autoTarget ) {
			return false;
		}

		if ( hasUrlTarget( action_type ) ) {
			return hasTargetData( match_type, action_data ) || autoTarget !== '';
		}

		return true;
	}

	renderItem() {
		const { url, advanced, flag_regex, action_type, match_type, action_data, flag_query, group_id, position, title, action_code } = this.state;
		const { autoFocus, group, flags } = this.props;

		return (
			<React.Fragment>
				<RedirectSourceUrl url={ url } flags={ this.state } defaultFlags={ flags } autoFocus={ autoFocus } onFlagChange={ this.onFlagChange } onChange={ this.onChange } />
				<RedirectSourceQuery query={ flag_query } regex={ flag_regex } onChange={ this.onChange } />
				{ advanced &&

					<React.Fragment>
						<RedirectTitle title={ title } onChange={ this.onChange } />
						<MatchType matchType={ match_type } onChange={ this.onChangeMatch } />
						<MatchTarget matchType={ match_type } actionData={ action_data } onChange={ this.onChangeActionData } />

						<TableRow title={ __( 'When matched' ) }>
							<ActionType actionType={ action_type } matchType={ match_type } onChange={ this.onChangeActionType } />
							<ActionCode actionType={ action_type } actionCode={ action_code } onChange={ this.onChange } />
						</TableRow>
					</React.Fragment>
				}

				<ActionTarget actionType={ action_type } matchType={ match_type } actionData={ action_data } onChange={ this.onChangeActionData } />

				<TableRow title={ __( 'Group' ) }>
					<RedirectGroup groups={ group.rows } currentGroup={ group_id } onChange={ this.onSetGroup } />
					{ advanced && <RedirectPosition position={ position } onChange={ this.onChange } /> }
				</TableRow>
			</React.Fragment>
		);
	}

	render() {
		const { warning } = this.state;
		const { saveButton = __( 'Save' ), onCancel, addTop, onClose } = this.props;

		return (
			<form onSubmit={ this.onSave } ref={ this.ref }>
				<table className="edit edit-redirection">
					<tbody>
						{ this.renderItem() }
						{ this.props.children && this.props.children }

						<TableRow>
							<div className="table-actions">
								<input className="button-primary" type="submit" name="save" value={ saveButton } disabled={ ! this.canSave() } /> &nbsp;
								{ onCancel && <input className="button-secondary" type="submit" name="cancel" value={ __( 'Cancel' ) } onClick={ onCancel } /> }
								{ addTop && ! onCancel && <input className="button-secondary" type="submit" name="cancel" value={ __( 'Close' ) } onClick={ onClose } /> }
								&nbsp;

								{ this.canShowAdvanced() && <a href="#" onClick={ this.onToggleAdvanced } className="redirection-edit_advanced" title={ __( 'Show advanced options' ) }><svg aria-hidden="true" role="img" focusable="false" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path d="M18 12h-2.18c-.17.7-.44 1.35-.81 1.93l1.54 1.54-2.1 2.1-1.54-1.54c-.58.36-1.23.63-1.91.79V19H8v-2.18c-.68-.16-1.33-.43-1.91-.79l-1.54 1.54-2.12-2.12 1.54-1.54c-.36-.58-.63-1.23-.79-1.91H1V9.03h2.17c.16-.7.44-1.35.8-1.94L2.43 5.55l2.1-2.1 1.54 1.54c.58-.37 1.24-.64 1.93-.81V2h3v2.18c.68.16 1.33.43 1.91.79l1.54-1.54 2.12 2.12-1.54 1.54c.36.59.64 1.24.8 1.94H18V12zm-8.5 1.5c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3 1.34 3 3 3z"></path></svg></a> }
							</div>
						</TableRow>

						<Warnings warnings={ warning } />
					</tbody>
				</table>
			</form>
		);
	}
}

function mapStateToProps( state ) {
	const { group, redirect: { addTop, table } } = state;

	return {
		group,
		addTop,
		table,
		autoTarget: getOption( state, 'auto_target' ),
		flags: getFlags( state ),
	};
}

function mapDispatchToProps( dispatch ) {
	return {
		onSave: ( id, redirect ) => {
			dispatch( updateRedirect( id, redirect ) );
		},
		onCreate: redirect => {
			dispatch( createRedirect( redirect ) );
		},
		onClose: ev => {
			ev.preventDefault();
			dispatch( addToTop( false ) );
		},
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps,
)( EditRedirect );
