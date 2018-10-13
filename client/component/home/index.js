/* global REDIRECTION_VERSION, Redirectioni10n */
/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */

import { getPluginPage } from 'lib/wordpress-url';
import Options from 'component/options';
import Support from 'component/support';
import Logs from 'component/logs';
import Logs404 from 'component/logs404';
import ImportExport from 'component/io';
import Groups from 'component/groups';
import Redirects from 'component/redirects';
import Error from 'component/error';
import Notice from 'component/notice';
import Progress from 'component/progress';
import Menu from 'component/menu';
import { clearErrors } from 'state/message/action';
import { addToTop } from 'state/redirect/action';

const getTitles = () => ( {
	redirect: __( 'Redirections' ),
	groups: __( 'Groups' ),
	io: __( 'Import/Export' ),
	log: __( 'Logs' ),
	'404s': __( '404 errors' ),
	options: __( 'Options' ),
	support: __( 'Support' ),
} );

class Home extends React.Component {
	constructor( props ) {
		super( props );

		this.state = {
			page: getPluginPage(),
			clicked: 0,
			stack: false,
			error: REDIRECTION_VERSION !== Redirectioni10n.version,
			info: false,
		};

		this.handlePageChange = this.onChangePage.bind( this );
	}

	componentDidCatch( error, info ) {
		this.setState( { error: true, stack: error, info } );
	}

	onChangePage( page, url ) {
		const { errors } = this.props;

		if ( page === '' ) {
			page = 'redirect';
		}

		if ( page === 'support' && errors.length > 0 ) {
			document.location.href = url;
			return;
		}

		history.pushState( {}, null, url );
		this.setState( {
			page,
			clicked: this.state.clicked + 1,
		} );

		this.props.onClear();
	}

	getContent( page ) {
		const { clicked } = this.state;

		switch ( page ) {
			case 'support':
				return <Support />;

			case '404s':
				return <Logs404 key={ clicked } />;

			case 'log':
				return <Logs key={ clicked } />;

			case 'io':
				return <ImportExport />;

			case 'groups':
				return <Groups key={ clicked } />;

			case 'options':
				return <Options />;
		}

		return <Redirects key={ clicked } />;
	}

	renderError() {
		const debug = [
			Redirectioni10n.versions,
			'Buster: ' + REDIRECTION_VERSION + ' === ' + Redirectioni10n.version,
			'',
			this.state.stack,
		];

		if ( this.state.info && this.state.info.componentStack ) {
			debug.push( this.state.info.componentStack );
		}

		if ( REDIRECTION_VERSION !== Redirectioni10n.version ) {
			return (
				<div className="notice notice-error">
					<h2>{ __( 'Cached Redirection detected' ) }</h2>
					<p>{ __( 'Please clear your browser cache and reload this page.' ) }</p>
					<p>
						{ __( 'If you are using a caching system such as Cloudflare then please read this: ' ) }
						<a href="https://redirection.me/support/problems/cloudflare/?utm_source=redirection&utm_medium=plugin&utm_campaign=support" target="_blank" rel="noreferrer noopener">{ __( 'clearing your cache.' ) }</a>
					</p>
					<p><textarea readOnly={ true } rows={ debug.length + 3 } cols="120" value={ debug.join( '\n' ) } spellCheck={ false }></textarea></p>
				</div>
			);
		}

		return (
			<div className="notice notice-error">
				<h2>{ __( 'Something went wrong 🙁' ) }</h2>

				<p>
					{ __( 'Redirection is not working. Try clearing your browser cache and reloading this page.' ) } &nbsp;
					{ __( 'If you are using a page caching plugin or service (CloudFlare, OVH, etc) then you can also try clearing that cache.' ) }
				</p>

				<p>
					{ __( "If that doesn't help, open your browser's error console and create a {{link}}new issue{{/link}} with the details.", {
						components: {
							link: <a target="_blank" rel="noopener noreferrer" href="https://github.com/johngodley/redirection/issues" />,
						},
					} ) }
				</p>
				<p>
					{ __( 'Please mention {{code}}%s{{/code}}, and explain what you were doing at the time', {
						components: {
							code: <code />,
						},
						args: this.state.page,
					} ) }
				</p>
				<p><textarea readOnly={ true } rows={ debug.length + 8 } cols="120" value={ debug.join( '\n' ) } spellCheck={ false }></textarea></p>
			</div>
		);
	}

	onAdd = ev => {
		ev.preventDefault();
		this.props.onAdd();
	}

	render() {
		const title = getTitles()[ this.state.page ];

		if ( this.state.error ) {
			return this.renderError();
		}

		return (
			<div className="wrap redirection">
				<h1 className="wp-heading-inline">{ title }</h1>
				{ this.state.page === 'redirect' && <a href="#" onClick={ this.onAdd } className="page-title-action">{ __( 'Add New' ) }</a> }

				<Menu onChangePage={ this.handlePageChange } />
				<Error />

				{ this.getContent( this.state.page ) }

				<Progress />
				<Notice />
			</div>
		);
	}
}

function mapDispatchToProps( dispatch ) {
	return {
		onClear: () => {
			dispatch( clearErrors() );
		},
		onAdd: () => {
			dispatch( addToTop( true ) );
		},
	};
}

function mapStateToProps( state ) {
	return {
		errors: state.message.errors,
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps,
)( Home );
