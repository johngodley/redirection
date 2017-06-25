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

class Home extends React.Component {
	getContent() {
		const parts = document.location.search.split( '&' );

		if ( parts[ 1 ] === 'sub=support' ) {
			return <Support />;
		}

		if ( parts[ 1 ] === 'sub=404s' ) {
			return <Logs404 />;
		}

		if ( parts[ 1 ] === 'sub=log' ) {
			return <Logs />;
		}

		return <Options />;
	}

	render() {
		// Will need a better way of doing this once the other pages are in React
		const content = this.getContent();

		return content;
	}
}

export default Home;
