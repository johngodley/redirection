import { __ } from '@wordpress/i18n';
import { RowActions, RowAction } from 'component/table/row-action';
import UseragentAction from 'component/log-page/log-actions/user-agent';
import ExtraDataAction from 'component/log-page/log-actions/extra-data';
import { CAP_LOG_DELETE } from 'lib/capabilities';

interface Log {
	id: number | string;
	ip?: string | null;
	agent?: string | null;
	request_data?: string | Record< string, unknown >;
	redirection_id?: number;
	created?: string;
	sent_to?: string | null;
	url?: string;
}

interface LogRowActionsProps {
	row: Log;
	onDelete: ( id: number | string ) => void;
	disabled: boolean;
}

function LogRowActions( props: LogRowActionsProps ) {
	const { row, onDelete, disabled } = props;
	const { agent, id, request_data, redirection_id } = row;
	const menu: JSX.Element[] = [];

	menu.push(
		<RowAction onClick={ () => onDelete( id ) } capability={ CAP_LOG_DELETE } key="0">
			{ __( 'Delete', 'redirection' ) }
		</RowAction>
	);

	if ( agent ) {
		menu.unshift( <UseragentAction key="3" agent={ agent } /> );
	}

	if ( request_data ) {
		menu.push( <ExtraDataAction data={ request_data } key="4" /> );
	}

	if ( redirection_id && redirection_id > 0 ) {
		menu.push(
			<RowAction
				href={ Redirectioni10n.pluginRoot + '&' + encodeURIComponent( 'filterby[id]' ) + '=' + redirection_id }
				key="5"
			>
				{ __( 'View Redirect', 'redirection' ) }
			</RowAction>
		);
	}

	return <RowActions disabled={ disabled } actions={ menu } />;
}

export default LogRowActions;
