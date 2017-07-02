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

class Home extends React.Component {
	render() {
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

		if ( parts[ 1 ] === 'sub=modules' ) {
			return <Modules />;
		}

		if ( parts[ 1 ] === 'sub=groups' ) {
			return <Grouper />;
		}

		if ( parts[ 1 ] === 'sub=options' ) {
			return <Options />;
		}

		return null;
	}
}

export default Home;
