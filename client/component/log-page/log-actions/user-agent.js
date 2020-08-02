/**
 * External dependencies
 */

import React, { useState } from 'react';
import { translate as __ } from 'wp-plugin-lib/locale';

/**
 * Internal dependencies
 */

import { RowAction } from 'component/table/row-action';
import Modal from 'wp-plugin-components/modal';
import Useragent from 'component/useragent';

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
				{ __( 'Agent Info' ) }
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
