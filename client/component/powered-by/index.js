/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';

/**
 * Internal dependencies
 */
import ExternalLink from 'component/external-link';
import './style.scss';

const PoweredBy = () => (
	<div className="redirection-poweredby">
		{ __( 'Powered by {{link}}redirect.li{{/link}}', {
			components: {
				link: <ExternalLink url="https://redirect.li" />,
			},
		} ) }
	</div>
);

export default PoweredBy;
