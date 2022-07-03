/**
 * External dependencies
 */

import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */

import { RowAction } from 'component/table/row-action';
import { Modal } from 'wp-plugin-components';
import GeoMap from 'component/geo-map';

/**
 * Geo map row action + modal popup
 *
 * @param {object} props - Component props
 * @param {string} props.ip - IP
 */
function GeoMapAction( props ) {
	const { ip } = props;
	const [ showModal, setShowModal ] = useState( false );

	return (
		<>
			<RowAction
				href={ 'https://redirect.li/map/?ip=' + encodeURIComponent( ip ) }
				onClick={ () => setShowModal( true ) }
			>
				{ __( 'Geo Info', 'redirection' ) }
			</RowAction>

			{ showModal && (
				<Modal onClose={ () => setShowModal( false ) } padding={ false }>
					<GeoMap ip={ ip } />
				</Modal>
			) }
		</>
	);
}

export default GeoMapAction;
