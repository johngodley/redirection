/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';

/**
 * Internal dependencies
 */
import './style.scss';

const PoweredBy = () => (
	<div className="redirection-poweredby">
		{ __( 'Powered by {{link}}redirect.li{{/link}}', {
			components: {
				link: <a href="https://redirect.li" target="_blank" rel="noopener noreferrer" />,
			},
		} ) }
	</div>
);

export default PoweredBy;
