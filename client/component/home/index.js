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
import Grouper from 'component/groups';
import Redirects from 'component/redirects';
import Error from 'component/error';
import Notice from 'component/notice';
import Progress from 'component/progress';
import Menu from 'component/menu';
import { clearErrors } from 'state/message/action';

const TITLES = {
	redirect: __( 'Redirections' ),
	groups: __( 'Groups' ),
	io: __( 'Import/Export' ),
	log: __( 'Logs' ),
	'404s': __( '404 errors' ),
	options: __( 'Options' ),
	support: __( 'Support' ),
};

class Home extends React.Component {
	constructor( props ) {
		super( props );

		this.state = {
			page: getPluginPage(),
			clicked: 0,
			error: false,
		};
		this.handlePageChange = this.onChangePage.bind( this );
	}

	componentDidCatch() {
		this.setState( { error: true } );
	}

	onChangePage( page, url ) {
		if ( page === '' ) {
			page = 'redirect';
		}

		history.pushState( {}, null, url );
		this.setState( {
			page,
			clicked: this.state.clicked + 1
		} );

		this.props.onClear();
	}

	getContent( page ) {
		const { clicked } = this.state;

		switch ( page ) {
			case 'support':
				return <Support />;

			case '404s':
				return <Logs404 clicked={ clicked } />;

			case 'log':
				return <Logs clicked={ clicked } />;

			case 'io':
				return <ImportExport />;

			case 'groups':
				return <Grouper clicked={ clicked } />;

			case 'options':
				return <Options />;
		}

		return <Redirects clicked={ clicked } />;
	}

	renderError() {
		return (
			<div className="notice notice-error">
				<h2>{ __( 'Something went wrong 🙁' ) }</h2>

				<p>
					{ __( 'Redirection crashed and needs fixing. Please open your browsers error console and create a {{link}}new issue{{/link}} with the details.', {
						components: {
							link: <a target="_blank" rel="noopener noreferrer" href="https://github.com/johngodley/redirection/issues" />
						}
					} ) }
				</p>
				<p>
					{ __( 'Please mention {{code}}%s{{/code}}, and explain what you were doing at the time', {
						components: {
							code: <code />
						},
						args: this.state.page,
					} ) }
				</p>
			</div>
		);
	}

	render() {
		const title = TITLES[ this.state.page ];

		if ( this.state.error ) {
			return this.renderError();
		}

		return (
			<div className="wrap redirection">
				<h2>{ title }</h2>

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
	};
}

export default connect(
	null,
	mapDispatchToProps,
)( Home );
