import { __ } from '@wordpress/i18n';
import { RowActions, RowAction } from 'component/table/row-action';
import UseragentAction from 'component/log-page/log-actions/user-agent';
import ExtraDataAction from 'component/log-page/log-actions/extra-data';
import { CAP_LOG_DELETE, CAP_REDIRECT_MANAGE } from 'lib/capabilities';
import { useTableStore } from 'stores';

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
	groupBy?: string | undefined;
}

function LogRowActions( props: LogRowActionsProps ) {
	const { row, onDelete, disabled, groupBy } = props;
	const { agent, id, request_data, redirection_id, url, ip } = row;
	const { setLogsTable } = useTableStore();
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

	if ( groupBy ) {
		const getShowFilter = () => {
			if ( groupBy === 'ip' ) {
				return { ip: ip ?? '' };
			}
			if ( groupBy === 'agent' ) {
				return { agent: agent ?? '' };
			}
			return { 'url-exact': url ?? '' };
		};

		menu.push(
			<RowAction
				onClick={ () =>
					setLogsTable( { filterBy: getShowFilter(), page: 0, groupBy: '', selected: [], selectAll: false } )
				}
				capability={ CAP_REDIRECT_MANAGE }
				key="6"
			>
				{ __( 'Show All', 'redirection' ) }
			</RowAction>
		);
	}

	return <RowActions disabled={ disabled } actions={ menu } />;
}

export default LogRowActions;
