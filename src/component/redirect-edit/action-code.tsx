import { __ } from '@wordpress/i18n';
import { Select } from '@wp-plugin-components';
import { ACTION_URL, ACTION_ERROR, ACTION_RANDOM } from 'lib/redirect-constants';
import { getHttpError, getHttpCodes } from './constants';

interface LabelValue {
	value: string;
	label: string;
}

function getCodes( actionType: string ): LabelValue[] | null {
	if ( actionType === ACTION_ERROR ) {
		return getHttpError();
	}

	if ( actionType === ACTION_URL || actionType === ACTION_RANDOM ) {
		return getHttpCodes();
	}

	return null;
}

interface ActionCodeProps {
	actionType: string;
	actionCode: string | number;
	onChange: ( ev: React.ChangeEvent< HTMLSelectElement > ) => void;
}

const ActionCode = ( { actionType, actionCode, onChange }: ActionCodeProps ) => {
	const codes = getCodes( actionType );

	if ( codes ) {
		return (
			<>
				<strong className="small-flex">{ __( 'with HTTP code', 'redirection' ) }</strong>

				<Select name="action_code" value={ String( actionCode ) } items={ codes } onChange={ onChange } />
			</>
		);
	}

	return null;
};

export default ActionCode;
