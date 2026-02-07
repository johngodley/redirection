import ActionLogin from './login';
import ActionUrl from './url';
import ActionUrlFrom from './url-from';
import { MATCH_URL, MATCH_LOGIN, MATCH_PAGE, hasUrlTarget, getMatchState } from 'lib/redirect-constants';

interface ActionTargetProps {
	actionType: string;
	matchType: string;
	actionData: any;
	onChange: (
		ev: React.ChangeEvent< HTMLInputElement > | { target: { name: string; value: string; type: string } }
	) => void;
}

function getComponentForType( type: string ) {
	if ( type === MATCH_LOGIN ) {
		return ActionLogin;
	}

	if ( type === MATCH_URL || type === MATCH_PAGE ) {
		return ActionUrl;
	}

	return ActionUrlFrom;
}

const ActionTarget = ( { actionType, matchType, actionData, onChange }: ActionTargetProps ) => {
	if ( hasUrlTarget( actionType ) ) {
		const Component = getComponentForType( matchType );
		const state = getMatchState( matchType, actionData );

		return <Component data={ state === null ? {} : state } onChange={ onChange } />;
	}

	return null;
};

export default ActionTarget;
