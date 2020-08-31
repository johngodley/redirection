/**
 * External dependencies
 */

import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { translate as __ } from 'i18n-calypso';
import classnames from 'classnames';

/**
 * Internal dependencies
 */

import ApiResult from './api-result';
import { Spinner } from 'wp-plugin-components';
import { restApi } from 'page/options/options-form';
import { checkApi } from 'state/settings/action';
import './style.scss';

const STATUS_OK = 'ok';
const STATUS_FAIL = 'fail';
const STATUS_LOADING = 'loading';
const STATUS_WARNING_CURRENT = 'warning-current';
const STATUS_WARNING = 'warning-not-selected';

const getApiResult = ( results, name ) => results && results[ name ] ? results[ name ] : {};
const isError = result => result.GET && result.POST && ( result.GET.status === STATUS_FAIL || result.POST.status === STATUS_FAIL );
const isWorking = result => result.GET && result.POST && ( result.GET.status === STATUS_OK && result.POST.status === STATUS_OK );

class RestApiStatus extends React.Component {
	static propTypes = {
		allowChange: PropTypes.bool,
	};

	static defaultProps = {
		allowChange: true,
	};

	constructor( props ) {
		super( props );

		this.state = { showing: false };
	}

	componentDidMount() {
		this.onTry();
	}

	onTry() {
		const { routes } = this.props;
		const untested = Object.keys( routes ).map( id => ( { id, url: routes[ id ] } ) );

		this.props.onCheckApi( untested.filter( item => item ) );
	}

	onRetry = ev => {
		ev.preventDefault;
		this.setState( { showing: false } );
		this.onTry();
	}

	getPercent( apiTest, routes ) {
		if ( Object.keys( apiTest ).length === 0 ) {
			return 0;
		}

		const total = routes.length * 2;
		let finished = 0;

		for ( let index = 0; index < Object.keys( apiTest ).length; index++ ) {
			const key = Object.keys( apiTest )[ index ];

			if ( apiTest[ key ] && apiTest[ key ].GET && apiTest[ key ].GET.status !== STATUS_LOADING ) {
				finished++;
			}

			if ( apiTest[ key ] && apiTest[ key ].POST && apiTest[ key ].POST.status !== STATUS_LOADING ) {
				finished++;
			}
		}

		return Math.round( ( finished / total ) * 100 );
	}

	getApiStatus( results, routes, current ) {
		const failed = Object.keys( results ).filter( key => isError( results[ key ] ) ).length;

		if ( failed === 0 ) {
			return 'ok';
		} else if ( failed < routes.length ) {
			return isWorking( results[ current ] ) ? STATUS_WARNING_CURRENT : STATUS_WARNING;
		}

		return 'fail';
	}

	getApiStatusText( status ) {
		if ( status === STATUS_OK ) {
			return __( 'Good' );
		} else if ( status === STATUS_WARNING_CURRENT ) {
			return __( 'Working but some issues' );
		} else if ( status === STATUS_WARNING ) {
			return __( 'Not working but fixable' );
		}

		return __( 'Unavailable' );
	}

	onShow = () => {
		this.setState( { showing: true } );
	}

	canShowProblem( status ) {
		const { showing } = this.state;

		return showing || status === STATUS_FAIL || status === STATUS_WARNING;
	}

	renderError( status ) {
		const showing = this.canShowProblem( status );
		let message = __( 'There are some problems connecting to your REST API. It is not necessary to fix these problems and the plugin is able to work.' );

		if ( status === STATUS_FAIL ) {
			message = __( 'Your REST API is not working and the plugin will not be able to continue until this is fixed.' );
		} else if ( status === STATUS_WARNING ) {
			message = __( 'You are using a broken REST API route. Changing to a working API should fix the problem.' );
		}

		return (
			<div className="api-result-log">
				<p><strong>{ __( 'Summary' ) }</strong>: { message }</p>

				{ ! showing && <p><button className="button-secondary" onClick={ this.onShow }>{ __( 'Show Problems' ) }</button></p> }
			</div>
		);
	}

	render() {
		const routeNames = restApi();
		const { apiTest, routes, current, allowChange } = this.props;
		const { showing } = this.state;
		const percent = this.getPercent( apiTest, routeNames );
		const status = this.getApiStatus( apiTest, routeNames, current );
		const showProblem = percent >= 100 && this.canShowProblem( status ) || showing;
		const statusClass = classnames( {
			'api-result-status': true,
			'api-result-status_good': status === STATUS_OK && percent >= 100,
			'api-result-status_problem': status === STATUS_WARNING_CURRENT && percent >= 100,
			'api-result-status_failed': ( status === STATUS_FAIL || status === STATUS_WARNING ) && percent >= 100,
		} );

		return (
			<div className="api-result-wrapper">
				<div className="api-result-header">
					<strong>REST API:</strong>

					<div className="api-result-progress">
						<span className={ statusClass }>
							{ percent < 100 && __( 'Testing - %s%%', { args: [ percent ] } ) }
							{ percent >= 100 && this.getApiStatusText( status ) }
						</span>

						{ percent < 100 && <Spinner /> }
					</div>

					{ percent >= 100 && status !== STATUS_OK && <button className="button button-secondary api-result-retry" onClick={ this.onRetry }>{ __( 'Check Again' ) }</button> }
				</div>

				{ percent >= 100 && status !== STATUS_OK && this.renderError( status ) }

				{ showProblem && routeNames.map( ( item, pos ) =>
					<ApiResult item={ item } result={ getApiResult( apiTest, item.value ) } routes={ routes } key={ pos } isCurrent={ current === item.value } allowChange={ allowChange } />
				) }
			</div>
		);
	}
}

function mapDispatchToProps( dispatch ) {
	return {
		onCheckApi: api => {
			dispatch( checkApi( api ) );
		},
	};
}

function mapStateToProps( state ) {
	const { api: { routes, current }, apiTest } = state.settings;

	return {
		apiTest,
		routes,
		current,
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( RestApiStatus );
