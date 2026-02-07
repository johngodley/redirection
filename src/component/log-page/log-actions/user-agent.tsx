import { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { RowAction } from 'component/table/row-action';
import { Modal } from '@wp-plugin-components';
import Useragent from 'component/useragent';

interface UseragentActionProps {
	agent: string;
}

function UseragentAction( props: UseragentActionProps ) {
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
