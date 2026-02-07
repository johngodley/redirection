import { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { RowAction } from 'component/table/row-action';
import { Modal } from '@wp-plugin-components';
import RequestData from 'component/request-data';

interface ExtraDataActionProps {
	data: any;
}

function ExtraDataAction( props: ExtraDataActionProps ) {
	const { data } = props;
	const [ showModal, setShowModal ] = useState( false );

	return (
		<>
			<RowAction onClick={ () => setShowModal( true ) }>{ __( 'View Data', 'redirection' ) }</RowAction>

			{ showModal && (
				<Modal onClose={ () => setShowModal( false ) }>
					<RequestData data={ data } />
				</Modal>
			) }
		</>
	);
}

export default ExtraDataAction;
