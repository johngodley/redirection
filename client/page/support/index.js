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
		<>
			<Status />
			<HttpTester />
			<Help />
		</>
	);
};

export default Support;
