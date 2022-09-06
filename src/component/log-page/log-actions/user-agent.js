/**
 * External dependencies
 */

import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */

import { RowAction } from '../../table/row-action';
import { Modal } from '@wp-plugin-components';
import Useragent from '../../useragent';

/**
 * User agent row action + modal popup
 *
 * @param {object} props - Component props
 * @param {string} props.agent - User agent
 */
function UseragentAction( props ) {
	const { agent } = props;
	const [ showModal, setShowModal ] = useState( false );

	return (
		<>
			<RowAction
				href={ 'https://redirect.li/agent/?agent=' + encodeURIComponent( agent ) }
				onClick={ () => setShowModal( true ) }
			>
				{ __( 'Agent Info', 'redirection' ) }
			</RowAction>

			{ showModal && (
				<Modal onClose={ () => setShowModal( false ) }>
					<Useragent agent={ agent } />
				</Modal>
			) }
		</>
	);
}

export default UseragentAction;
