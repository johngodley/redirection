/* global Redirectioni10n */
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
import MatchHeader from './match/header';
import MatchCustom from './match/custom';
import MatchCookie from './match/cookie';
import MatchRole from './match/role';
import MatchServer from './match/server';
import MatchIp from './match/ip';
import MatchPage from './match/page';
import ActionLogin from './action/login';
import ActionUrl from './action/url';
import ActionUrlFrom from './action/url-from';
import Select from 'component/select';
import { nestedGroups } from 'state/group/selector';
import { updateRedirect, createRedirect, addToTop } from 'state/redirect/action';
import { getHttpError, getHttpCodes, getActions, getMatches } from './constants';
import {
	ACTION_URL,
	ACTION_RANDOM,
	ACTION_ERROR,

	MATCH_URL,
	MATCH_LOGIN,
	MATCH_REFERRER,
	MATCH_AGENT,
	MATCH_COOKIE,
	MATCH_HEADER,
	MATCH_CUSTOM,
	MATCH_ROLE,
	MATCH_SERVER,
	MATCH_IP,
	MATCH_PAGE,

	getActionData,
	hasUrlTarget,
} from 'state/redirect/selector';
import getWarningFromState from './warning';
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

		const { url, regex, match_type, action_type, action_data, group_id = 0, title, action_code, position = 0 } = props.item;
		const { logged_in = '', logged_out = '' } = action_data ? action_data : {};

		this.state = {
			warning: [],
			url,
			title,
			regex,
			match_type,
			action_type,
			action_code,
			action_data,
			group_id: this.getValidGroup( group_id ),
			position,

			login: {
				logged_in,
				logged_out,
			},
			target: action_data ? action_data : {},
			agent: this.getAgentState( action_data ),
			referrer: this.getReferrerState( action_data ),
			cookie: this.getHeaderState( action_data ),
			header: this.getHeaderState( action_data ),
			custom: this.getCustomState( action_data ),
			role: this.getRoleState( action_data ),
			server: this.getServerState( action_data ),
			ip: this.getIpState( action_data ),
			page: this.getPageState( action_data ),
		};

		this.state.advanced = ! this.canShowAdvanced();
		this.ref = React.createRef();
	}

	getWarning( newState ) {
		return getWarningFromState( { ... this.state, ... newState } );
	}

	getValidGroup( group_id ) {
		const groups = this.props.group.rows;
		const { table } = this.props;

		if ( groups.find( item => item.id === group_id ) ) {
			return group_id;
		}

		if ( groups.length > 0 ) {
			if ( table.filterBy === 'group' && parseInt( table.filter, 10 ) > 0 ) {
				return parseInt( table.filter, 10 );
			}

			const def = groups.find( item => item.default );

			if ( def ) {
				return def.id;
			}

			return groups[ 0 ].id;
		}

		return 0;
	}

	reset() {
		this.setState( {
			url: '',
			regex: false,
			match_type: MATCH_URL,
			action_type: ACTION_URL,
			action_data: '',
			title: '',
			action_code: 301,
			position: 0,
			... this.resetActionData(),
		} );
	}

	resetActionData() {
		return {
			login: {
				logged_in: '',
				logged_out: '',
			},
			target: {
				url: '',
			},
			agent: {
				url_from: '',
				agent: '',
				regex: false,
				url_notfrom: '',
			},
			referrer: {
				referrer: '',
				regex: false,
				url_from: '',
				url_notfrom: '',
			},
			cookie: {
				name: '',
				value: '',
				regex: false,
				url_from: '',
				url_notfrom: '',
			},
			header: {
				name: '',
				value: '',
				regex: false,
				url_from: '',
				url_notfrom: '',
			},
			custom: {
				filter: '',
				url_from: '',
				url_notfrom: '',
			},
			role: {
				role: '',
				url_from: '',
				url_notfrom: '',
			},
			server: {
				server: '',
				url_from: '',
				url_notfrom: '',
			},
			ip: {
				ip: [],
				url_from: '',
				url_notfrom: '',
			},
			page: {
				page: '404',
				url: '',
			},
		};
	}

	canShowAdvanced() {
		const { match_type, action_type } = this.state;

		return match_type === MATCH_URL && action_type === ACTION_URL;
	}

	getAgentState( action_data ) {
		const { agent = '', regex = false, url_from = '', url_notfrom = '' } = action_data ? action_data : {};

		return {
			agent,
			regex,
			url_from,
			url_notfrom,
		};
	}

	getReferrerState( action_data ) {
		const { referrer = '', regex = false, url_from = '', url_notfrom = '' } = action_data ? action_data : {};

		return {
			referrer,
			regex,
			url_from,
			url_notfrom,
		};
	}

	getRoleState( action_data ) {
		const { role = '', url_from = '', url_notfrom = '' } = action_data ? action_data : {};

		return {
			role,
			url_from,
			url_notfrom,
		};
	}

	getServerState( action_data ) {
		const { server = '', url_from = '', url_notfrom = '' } = action_data ? action_data : {};

		return {
			server,
			url_from,
			url_notfrom,
		};
	}

	getIpState( action_data ) {
		const { ip = [], url_from = '', url_notfrom = '' } = action_data ? action_data : {};

		return {
			ip,
			url_from,
			url_notfrom,
		};
	}

	getPageState( action_data ) {
		const { page = '404', url = '' } = action_data ? action_data : {};

		return {
			page,
			url,
		};
	}

	getHeaderState( action_data ) {
		const { name = '', value = '', regex = false, url_from = '', url_notfrom = '' } = action_data ? action_data : {};

		return {
			name,
			value,
			regex,
			url_from,
			url_notfrom,
		};
	}

	getCustomState( action_data ) {
		const { filter = '', url_from = '', url_notfrom = '' } = action_data ? action_data : {};

		return {
			filter,
			url_from,
			url_notfrom,
		};
	}

	onSetData = ( name, subname, value ) => {
		const newState = {};

		if ( value !== undefined ) {
			newState[ name ] = { ... this.state[ name ], [ subname ]: value };
		} else {
			newState[ name ] = subname;
		}

		newState.warning = this.getWarning( newState );
		this.setState( newState, this.triggerCallback );
	}

	onCustomAgent = newAgent => {
		const { agent } = this.state;

		agent.agent = newAgent;
		agent.regex = true;

		this.setState( { agent } );
	}

	onSave = ev => {
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

		if ( redirect.id ) {
			this.props.onSave( redirect.id, redirect );
		} else {
			this.props.onCreate( redirect );
		}

		if ( this.props.onCancel ) {
			this.props.onCancel( ev );
		} else {
			this.reset();
		}

		if ( this.props.childSave ) {
			this.props.childSave();
		}
	}

	onAdvanced = ev => {
		ev.preventDefault();

		this.setState( { advanced: ! this.state.advanced }, this.triggerCallback );
	}

	onGroup = ev => {
		this.setState( { group_id: parseInt( ev.target.value, 10 ) } );
	}

	onChange = ev => {
		const { target } = ev;
		const value = target.type === 'checkbox' ? target.checked : target.value;
		let newState = {
			[ target.name ]: value,
		};

		if ( target.name === 'action_type' ) {
			if ( target.value === ACTION_URL ) {
				newState.action_code = 301;
			} else if ( target.value === ACTION_ERROR ) {
				newState.action_code = 404;
			}
		} else if ( target.name === 'match_type' ) {
			newState = { ... newState, ... this.resetActionData() };

			if ( target.value === MATCH_LOGIN ) {
				newState.action_type = ACTION_URL;
			}
		}

		newState.warning = this.getWarning( newState ),
		this.setState( newState, this.triggerCallback );
	}

	triggerCallback = () => {
		if ( this.props.callback ) {
			this.props.callback( this.ref.current.clientHeight );
		}
	}

	getCode() {
		if ( this.state.action_type === ACTION_ERROR ) {
			return (
				<select name="action_code" value={ this.state.action_code } onChange={ this.onChange }>
					{ getHttpError().map( item => <option key={ item.value } value={ item.value }>{ item.name }</option> ) }
				</select>
			);
		}

		if ( this.state.action_type === ACTION_URL || this.state.action_type === ACTION_RANDOM ) {
			return (
				<select name="action_code" value={ this.state.action_code } onChange={ this.onChange }>
					{ getHttpCodes().map( item => <option key={ item.value } value={ item.value }>{ item.name }</option> ) }
				</select>
			);
		}

		return null;
	}

	getMatchExtra() {
		const { match_type, agent, referrer, cookie, header, custom, role, server, ip, page } = this.state;

		switch ( match_type ) {
			case MATCH_AGENT:
				return <MatchAgent agent={ agent.agent } regex={ agent.regex } onChange={ this.onSetData } onCustomAgent={ this.onCustomAgent } />;

			case MATCH_REFERRER:
				return <MatchReferrer referrer={ referrer.referrer } regex={ referrer.regex } onChange={ this.onSetData } />;

			case MATCH_COOKIE:
				return <MatchCookie name={ cookie.name } value={ cookie.value } regex={ cookie.regex } onChange={ this.onSetData } />;

			case MATCH_HEADER:
				return <MatchHeader name={ header.name } value={ header.value } regex={ header.regex } onChange={ this.onSetData } />;

			case MATCH_CUSTOM:
				return <MatchCustom filter={ custom.filter } onChange={ this.onSetData } />;

			case MATCH_ROLE:
				return <MatchRole role={ role.role } onChange={ this.onSetData } />;

			case MATCH_SERVER:
				return <MatchServer server={ server.server } onChange={ this.onSetData } />;

			case MATCH_IP:
				return <MatchIp ip={ ip.ip } onChange={ this.onSetData } />;

			case MATCH_PAGE:
				return <MatchPage page={ page.page } onChange={ this.onSetData } />;
		}

		return null;
	}

	getTarget() {
		const { match_type, action_type, agent, referrer, login, cookie, target, header, custom, role, server, ip, page } = this.state;

		if ( ! hasUrlTarget( action_type ) ) {
			return null;
		}

		switch ( match_type ) {
			case MATCH_AGENT:
				return <ActionUrlFrom url_from={ agent.url_from } url_notfrom={ agent.url_notfrom } target="agent" onChange={ this.onSetData } />;

			case MATCH_REFERRER:
				return <ActionUrlFrom url_from={ referrer.url_from } url_notfrom={ referrer.url_notfrom } target="referrer" onChange={ this.onSetData } />;

			case MATCH_LOGIN:
				return <ActionLogin logged_in={ login.logged_in } logged_out={ login.logged_out } onChange={ this.onSetData } />;

			case MATCH_URL:
				return <ActionUrl url={ target.url } target="target" onChange={ this.onSetData } />;

			case MATCH_COOKIE:
				return <ActionUrlFrom url_from={ cookie.url_from } url_notfrom={ cookie.url_notfrom } target="cookie" onChange={ this.onSetData } />;

			case MATCH_HEADER:
				return <ActionUrlFrom url_from={ header.url_from } url_notfrom={ header.url_notfrom } target="header" onChange={ this.onSetData } />;

			case MATCH_CUSTOM:
				return <ActionUrlFrom url_from={ custom.url_from } url_notfrom={ custom.url_notfrom } target="custom" onChange={ this.onSetData } />;

			case MATCH_ROLE:
				return <ActionUrlFrom url_from={ role.url_from } url_notfrom={ role.url_notfrom } target="role" onChange={ this.onSetData } />;

			case MATCH_SERVER:
				return <ActionUrlFrom url_from={ server.url_from } url_notfrom={ server.url_notfrom } target="server" onChange={ this.onSetData } />;

			case MATCH_IP:
				return <ActionUrlFrom url_from={ ip.url_from } url_notfrom={ ip.url_notfrom } target="ip" onChange={ this.onSetData } />;

			case MATCH_PAGE:
				return <ActionUrl url={ page.url } target="page" onChange={ this.onSetData } />;
		}

		return null;
	}

	getTitle() {
		const { title } = this.state;

		return (
			<tr>
				<th>{ __( 'Title' ) }</th>
				<td>
					<input type="text" name="title" value={ title } onChange={ this.onChange } placeholder={ __( 'Describe the purpose of this redirect (optional)' ) } />
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
					<select name="match_type" value={ match_type } onChange={ this.onChange }>
						{ getMatches().map( item => <option value={ item.value } key={ item.value }>{ item.name }</option> ) }
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
				<td className="edit-left">
					<select name="action_type" value={ action_type } onChange={ this.onChange }>
						{ getActions().filter( remover ).map( item => <option value={ item.value } key={ item.value }>{ item.name }</option> ) }
					</select>

					{ code && <React.Fragment><strong className="small-flex">{ __( 'with HTTP code' ) }</strong> <span>{ code }</span></React.Fragment> }
				</td>
			</tr>
		);
	}

	getGroup() {
		const groups = this.props.group.rows;
		const { group_id } = this.state;
		const position = parseInt( this.state.position, 10 );
		const { advanced } = this.state;

		return (
			<tr>
				<th>{ __( 'Group' ) }</th>
				<td className="edit-left">
					<Select name="group" value={ group_id } items={ nestedGroups( groups ) } onChange={ this.onGroup } />

					{ advanced &&
						<span className="edit-redirection-position">
							<strong>{ __( 'Position' ) }</strong>
							<input type="number" value={ position } name="position" min="0" size="3" onChange={ this.onChange } />
						</span>
					}
				</td>
			</tr>
		);
	}

	canSave() {
		const { url, match_type, target, action_type, referrer, login, agent, header, cookie, role, server, ip, page } = this.state;

		if ( Redirectioni10n.autoGenerate === '' && url === '' ) {
			return false;
		}

		if ( hasUrlTarget( action_type ) ) {
			if ( match_type === MATCH_URL && target === '' ) {
				return false;
			}

			if ( match_type === MATCH_REFERRER && referrer.url_from === '' && referrer.url_notfrom === '' ) {
				return false;
			}

			if ( match_type === MATCH_LOGIN && login.logged_in === '' && login.logged_out === '' ) {
				return false;
			}

			if ( match_type === MATCH_AGENT && agent.url_from === '' && agent.url_notfrom === '' ) {
				return false;
			}

			if ( match_type === MATCH_COOKIE && cookie.url_from === '' && cookie.url_notfrom === '' ) {
				return false;
			}

			if ( match_type === MATCH_HEADER && header.url_from === '' && header.url_notfrom === '' ) {
				return false;
			}

			if ( match_type === MATCH_ROLE && role.url_from === '' && role.url_notfrom === '' ) {
				return false;
			}

			if ( match_type === MATCH_SERVER && server.url_from === '' && server.url_notfrom === '' ) {
				return false;
			}

			if ( match_type === MATCH_IP && ip.url_from === '' && ip.url_notfrom === '' ) {
				return false;
			}

			if ( match_type === MATCH_PAGE && page.url === '' ) {
				return false;
			}
		}

		return true;
	}

	renderExtra() {
		return (
			<React.Fragment>
				{ this.getTitle() }
				{ this.getMatch() }
				{ this.getMatchExtra() }
				{ this.getTargetCode() }
			</React.Fragment>
		);
	}

	renderSingleUrl() {
		const { url, regex } = this.state;
		const { autoFocus = false } = this.props;

		return (
			<React.Fragment>
				<input type="text" name="url" value={ url } onChange={ this.onChange } autoFocus={ autoFocus } placeholder={ __( 'The relative URL you want to redirect from' ) } />
				<label className="edit-redirection-regex">
					{ __( 'Regex' ) } <sup><a tabIndex="-1" target="_blank" rel="noopener noreferrer" href="https://redirection.me/support/redirect-regular-expressions/">?</a></sup>
					&nbsp;
					<input type="checkbox" name="regex" checked={ regex } onChange={ this.onChange } />
				</label>
			</React.Fragment>
		);
	}

	renderMultiUrl() {
		const { url } = this.state;

		return (
			<textarea value={ url.join( '\n' ) } readOnly></textarea>
		);
	}

	render() {
		const { url, advanced, warning } = this.state;
		const { saveButton = __( 'Save' ), onCancel, addTop, onClose } = this.props;

		return (
			<form onSubmit={ this.onSave } ref={ this.ref }>
				<table className="edit edit-redirection">
					<tbody>
						<tr>
							<th className={ Array.isArray( url ) ? 'top' : '' }>{ __( 'Source URL' ) }</th>
							<td>
								{ Array.isArray( url ) ? this.renderMultiUrl() : this.renderSingleUrl() }
							</td>
						</tr>

						{ advanced && this.renderExtra() }

						{ this.getTarget() }
						{ this.getGroup() }

						{ this.props.children && this.props.children }

						<tr>
							<th></th>
							<td className="edit-left">
								<div className="table-actions">
									<input className="button-primary" type="submit" name="save" value={ saveButton } disabled={ ! this.canSave() } /> &nbsp;
									{ onCancel && <input className="button-secondary" type="submit" name="cancel" value={ __( 'Cancel' ) } onClick={ onCancel } /> }
									{ addTop && ! onCancel && <input className="button-secondary" type="submit" name="cancel" value={ __( 'Close' ) } onClick={ onClose } /> }
									&nbsp;

									{ this.canShowAdvanced() && <a href="#" onClick={ this.onAdvanced } className="advanced" title={ __( 'Show advanced options' ) }>&#9881;</a> }
								</div>
							</td>
						</tr>
						{ warning.length > 0 &&
							<tr>
								<th></th>
								<td className="edit-left">
									<div className="edit-redirection_warning notice notice-warning">
										{ warning.map( ( text, pos ) => <p key={ pos }><span className="dashicons dashicons-info"></span>{ text }</p> ) }
									</div>
								</td>
							</tr>
						}
					</tbody>
				</table>
			</form>
		);
	}
}

function mapStateToProps( state ) {
	const { group, redirect } = state;

	return {
		group,
		addTop: redirect.addTop,
		table: redirect.table,
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
