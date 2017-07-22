/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';

/**
 * Internal dependencies
 */

import { getPluginPage } from 'lib/wordpress-url';
import Options from 'component/options';
import Support from 'component/support';
import Logs from 'component/logs';
import Logs404 from 'component/logs404';
import Modules from 'component/modules';
import Grouper from 'component/groups';
import Redirects from 'component/redirects';
import Error from 'component/error';
import Notice from 'component/notice';
import Progress from 'component/progress';
import Menu from 'component/menu';

const TITLES = {
	redirect: __( 'Redirections' ),
	groups: __( 'Groups' ),
	modules: __( 'Modules' ),
	log: __( 'Logs' ),
	'404s': __( '404 errors' ),
	options: __( 'Options' ),
	support: __( 'Support' ),
};

class Home extends React.Component {
	constructor( props ) {
		super( props );

		this.state = { page: getPluginPage() };
		this.handlePageChange = this.onChangePage.bind( this );
	}

	onChangePage( page, url ) {
		if ( page === '' ) {
			page = 'redirect';
		}

		history.pushState( {}, null, url );
		this.setState( { page } );
	}

	getContent( page ) {
		switch ( page ) {
			case 'support':
				return <Support />;

			case '404s':
				return <Logs404 />;

			case 'log':
				return <Logs />;

			case 'modules':
				return <Modules />;

			case 'groups':
				return <Grouper />;

			case 'options':
				return <Options />;
		}

		return <Redirects />;
	}

	render() {
		const title = TITLES[ this.state.page ];

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

export default Home;
