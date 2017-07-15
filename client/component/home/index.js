/**
 * External dependencies
 */

import React from 'react';

/**
 * Internal dependencies
 */

import Options from 'component/options';
import Support from 'component/support';
import Logs from 'component/logs';
import Logs404 from 'component/logs404';
import Modules from 'component/modules';
import Grouper from 'component/groups';
import Redirects from 'component/redirects';
import Error from 'component/error';
import Notice from 'component/notice';

class Home extends React.Component {
	getPage() {
		const page = document.location.search.split( '&' );

		if ( page.length > 1 ) {
			return page[ 1 ].split( '=' )[ 1 ];
		}

		return 'redirect';
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
		const page = this.getPage();

		return (
			<div className="redirection">
				<Error />

				{ this.getContent( page ) }

				<Notice />
			</div>
		);
	}
}

export default Home;
