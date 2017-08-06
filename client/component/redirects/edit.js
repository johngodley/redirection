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

import MatchAgent from './match/agent';
import MatchReferrer from './match/referrer';
import ActionAgent from './action/agent';
import ActionReferrer from './action/referrer';
import ActionLogin from './action/login';
import ActionUrl from './action/url';
import Select from 'component/wordpress/select';
import { nestedGroups } from 'state/group/selector';
import { saveRedirect } from 'state/redirect/action';
import {
	ACTION_URL,
	ACTION_PASS,
	ACTION_NOTHING,
	ACTION_RANDOM,
	ACTION_ERROR,
	MATCH_URL,
	MATCH_LOGIN,
	MATCH_REFERRER,
	MATCH_AGENT,
	getActionData,
	hasUrlTarget,
} from 'state/redirect/selector';

const MATCHES = [
	{
		value: MATCH_URL,
		name: __( 'URL only' ),
	},
	{
		value: MATCH_LOGIN,
		name: __( 'URL and login status' ),
	},
	{
		value: MATCH_REFERRER,
		name: __( 'URL and referrer' ),
	},
	{
		value: MATCH_AGENT,
		name: __( 'URL and user agent' ),
	},
];

const ACTIONS = [
	{
		value: ACTION_URL,
		name: __( 'Redirect to URL' ),
	},
	{
		value: ACTION_RANDOM,
		name: __( 'Redirect to random post' ),
	},
	{
		value: ACTION_PASS,
		name: __( 'Pass-through' ),
	},
	{
		value: ACTION_ERROR,
		name: __( 'Error (404)' ),
	},
	{
		value: ACTION_NOTHING,
		name: __( 'Do nothing' ),
	},
];

const HTTP_REDIRECT = [
	{
		value: 301,
		name: __( '301 - Moved Permanently' ),
	},
	{
		value: 302,
		name: __( '302 - Found' ),
	},
	{
		value: 307,
		name: __( '307 - Temporary Redirect' ),
	},
	{
		value: 308,
		name: __( '308 - Permanent Redirect' ),
	},
];

const HTTP_ERROR = [
	{
		value: 401,
		name: __( '401 - Unauthorized' ),
	},
	{
		value: 404,
		name: __( '404 - Not Found' ),
	},
	{
		value: 410,
		name: __( '410 - Gone' ),
	},
];

class EditRedirect extends React.Component {
	constructor( props ) {
		super( props );

		this.handleSave = this.onSave.bind( this );
		this.handleChange = this.onChange.bind( this );
		this.handleGroup = this.onGroup.bind( this );
		this.handleData = this.onSetData.bind( this );
		this.handleAdvanced = this.onAdvanced.bind( this );

		const { url, regex, match_type, action_type, action_data, group_id = 0, title, action_code, position } = props.item;
		const { logged_in = '', logged_out = '' } = action_data;

		this.state = {
			url,
			title,
			regex,
			match_type,
			action_type,
			action_code,
			action_data,
			group_id,
			position,

			login: {
				logged_in,
				logged_out,
			},
			target: typeof action_data === 'string' ? action_data : '',
			agent: this.getAgentState( action_data ),
			referrer: this.getReferrerState( action_data ),
		};

		this.state.advanced = ! this.canShowAdvanced();
	}

	canShowAdvanced() {
		const { match_type, action_type } = this.state;

		return match_type === MATCH_URL && action_type === ACTION_URL;
	}

	getAgentState( action_data ) {
		const { agent = '', regex = false, url_from = '', url_notfrom = '' } = action_data;

		return {
			agent,
			regex,
			url_from,
			url_notfrom,
		};
	}

	getReferrerState( action_data ) {
		const { referrer = '', regex = false, url_from = '', url_notfrom = '' } = action_data;

		return {
			referrer,
			regex,
			url_from,
			url_notfrom,
		};
	}

	onSetData( name, subname, value ) {
		if ( value !== undefined ) {
			this.setState( { [ name ]: Object.assign( {}, this.state[ name ], { [ subname ]: value } ) } );
		} else {
			this.setState( { [ name ]: subname } );
		}
	}

	onSave( ev ) {
		ev.preventDefault();

		const { url, title, regex, match_type, action_type, group_id, action_code, position } = this.state;
		const groups = this.props.group.rows;

		const redirect = {
			id: parseInt( this.props.item.id, 10 ),
			url,
			title,
			regex,
			match_type,
			action_type,
			position,
			group_id: group_id > 0 ? group_id : groups[ 0 ].id,
			action_code: this.getCode() ? parseInt( action_code, 10 ) : 0,
			action_data: getActionData( this.state ),
		};

		this.props.onSave( redirect );

		if ( this.props.onCancel ) {
			this.props.onCancel();
		}
	}

	onAdvanced( ev ) {
		ev.preventDefault();

		this.setState( { advanced: ! this.state.advanced } );
	}

	onGroup( ev ) {
		this.setState( { group_id: parseInt( ev.target.value, 10 ) } );
	}

	onChange( ev ) {
		const { target } = ev;
		const value = target.type === 'checkbox' ? target.checked : target.value;

		this.setState( { [ target.name ]: value } );

		if ( target.name === 'action_type' && target.value === ACTION_URL ) {
			this.setState( { action_code: 301 } );
		}

		if ( target.name === 'action_type' && target.value === ACTION_ERROR ) {
			this.setState( { action_code: 404 } );
		}

		if ( target.name === 'match_type' && target.value === MATCH_LOGIN ) {
			this.setState( { action_type: ACTION_URL } );
		}
	}

	getCode() {
		if ( this.state.action_type === ACTION_ERROR ) {
			return (
				<select name="action_code" value={ this.state.action_code } onChange={ this.handleChange }>
					{ HTTP_ERROR.map( item => <option key={ item.value } value={ item.value }>{ item.name }</option> ) }
				</select>
			);
		}

		if ( this.state.action_type === ACTION_URL || this.state.action_type === ACTION_RANDOM ) {
			return (
				<select name="action_code" value={ this.state.action_code } onChange={ this.handleChange }>
					{ HTTP_REDIRECT.map( item => <option key={ item.value } value={ item.value }>{ item.name }</option> ) }
				</select>
			);
		}

		return null;
	}

	getMatchExtra() {
		const { match_type } = this.state;

		switch ( match_type ) {
			case MATCH_AGENT:
				return <MatchAgent agent={ this.state.agent.agent } regex={ this.state.agent.regex } onChange={ this.handleData } />;

			case MATCH_REFERRER:
				return <MatchReferrer referrer={ this.state.referrer.referrer } regex={ this.state.referrer.regex } onChange={ this.handleData } />;
		}

		return null;
	}

	getTarget() {
		const { match_type, action_type } = this.state;

		if ( hasUrlTarget( action_type ) ) {
			if ( match_type === MATCH_AGENT ) {
				return <ActionAgent url_from={ this.state.agent.url_from } url_notfrom={ this.state.agent.url_notfrom } onChange={ this.handleData } />;
			}

			if ( match_type === MATCH_REFERRER ) {
				return <ActionReferrer url_from={ this.state.referrer.url_from } url_notfrom={ this.state.referrer.url_notfrom } onChange={ this.handleData } />;
			}

			if ( match_type === MATCH_LOGIN ) {
				return <ActionLogin logged_in={ this.state.login.logged_in } logged_out={ this.state.login.logged_out } onChange={ this.handleData } />;
			}

			if ( match_type === MATCH_URL ) {
				return <ActionUrl target={ this.state.target } onChange={ this.handleData } />;
			}
		}

		return null;
	}

	getTitle() {
		const { title } = this.state;

		return (
			<tr>
				<th>{ __( 'Title' ) }</th>
				<td>
					<input type="text" name="title" value={ title } onChange={ this.handleChange } />
				</td>
			</tr>
		);
	}

	getMatch() {
		const { match_type } = this.state;

		return (
			<tr>
				<th>{ __( 'Match' ) }</th>
				<td>
					<select name="match_type" value={ match_type } onChange={ this.handleChange }>
						{ MATCHES.map( item => <option value={ item.value } key={ item.value }>{ item.name }</option> ) }
					</select>
				</td>
			</tr>
		);
	}

	getTargetCode() {
		const { action_type, match_type } = this.state;
		const code = this.getCode();

		const remover = item => {
			if ( match_type === MATCH_LOGIN && ! hasUrlTarget( item.value ) ) {
				return false;
			}

			return true;
		};

		return (
			<tr>
				<th>{ __( 'When matched' ) }</th>
				<td>
					<select name="action_type" value={ action_type } onChange={ this.handleChange }>
						{ ACTIONS.filter( remover ).map( item => <option value={ item.value } key={ item.value }>{ item.name }</option> ) }
					</select>

					{ code && <span> <strong>{ __( 'with HTTP code' ) }</strong> { code }</span> }
				</td>
			</tr>
		);
	}

	getGroup() {
		const groups = this.props.group.rows;
		const { group_id, position } = this.state;
		const { advanced } = this.state;

		return (
			<tr>
				<th>{ __( 'Group' ) }</th>
				<td>
					<Select name="group" value={ group_id } items={ nestedGroups( groups ) } onChange={ this.handleGroup } />
					&nbsp;
					{ advanced && <strong>{ __( 'Position' ) }</strong> }
					{ advanced && <input type="number" value={ position } name="position" min="0" size="3" onChange={ this.handleChange } /> }
				</td>
			</tr>
		);
	}

	canSave() {
		if ( this.state.url === '' ) {
			return false;
		}

		if ( hasUrlTarget( this.state.action_type ) && this.state.target === '' ) {
			return false;
		}

		return true;
	}

	render() {
		const { url, regex, advanced } = this.state;
		const { saveButton = __( 'Save' ), onCancel } = this.props;

		return (
			<form onSubmit={ this.handleSave }>
				<table className="edit edit-redirection">
					<tbody>
						<tr>
							<th>{ __( 'Source URL' ) }</th>
							<td>
								<input type="text" name="url" value={ url } onChange={ this.handleChange } /> &nbsp;
								<label>
									{ __( 'Regex' ) } <sup><a tabIndex="-1" target="_blank" rel="noopener noreferrer" href="https://urbangiraffe.com/plugins/redirection/regex/">?</a></sup>
									&nbsp;
									<input type="checkbox" name="regex" checked={ regex } onChange={ this.handleChange } />
								</label>
							</td>
						</tr>

						{ advanced && this.getTitle() }
						{ advanced && this.getMatch() }
						{ advanced && this.getMatchExtra() }
						{ advanced && this.getTargetCode() }

						{ this.getTarget() }
						{ this.getGroup() }

						<tr>
							<th></th>
							<td>
								<div className="table-actions">
									<input className="button-primary" type="submit" name="save" value={ saveButton } disabled={ ! this.canSave() } /> &nbsp;
									{ onCancel && <input className="button-secondary" type="submit" name="cancel" value={ __( 'Cancel' ) } onClick={ onCancel } /> }
									&nbsp;

									{ this.canShowAdvanced() && this.props.advanced !== false && <a href="#" onClick={ this.handleAdvanced } className="advanced" title={ __( 'Show advanced options' ) }>&#9881;</a> }
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</form>
		);
	}
}

EditRedirect.propTypes = {
	item: PropTypes.object.isRequired,
	onCancel: PropTypes.func,
	saveButton: PropTypes.string,
	advanced: PropTypes.bool,
};

function mapStateToProps( state ) {
	const { group } = state;

	return {
		group,
	};
}

function mapDispatchToProps( dispatch ) {
	return {
		onSave: redirect => {
			dispatch( saveRedirect( redirect ) );
		},
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps,
)( EditRedirect );
