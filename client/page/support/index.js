/**
 * External dependencies
 */

import React from 'react';

/**
 * Internal dependencies
 */

import Help from './help';
import HttpTester from './http-tester';
import Status from './status';

const Support = () => {
	return (
		<React.Fragment>
			<Status />
			<HttpTester />
			<Help />
		</React.Fragment>
	);
};

export default Support;
