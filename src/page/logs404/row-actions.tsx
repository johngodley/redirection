import { __ } from '@wordpress/i18n';
import { RowActions, RowAction } from 'component/table/row-action';
import { CAP_REDIRECT_MANAGE, CAP_404_DELETE, CAP_REDIRECT_ADD } from 'lib/capabilities';
import UseragentAction from 'component/log-page/log-actions/user-agent';
import getCreateAction from './create-action';
import { useTableStore } from 'stores';
import { getShowFilter } from 'lib/api/utils';

interface Error404 {
	id: number;
	url: string;
	ip: string;
	agent: string;
	created: string;
	http_code: number;
	count: number;
}

interface Table {
	groupBy: string;
}

interface ErrorRowActionsProps {
	row: Error404;
	onDelete: ( id: number ) => void;
	onCreate: ( action: any ) => void;
	table: Table;
	disabled: boolean;
}

function ErrorRowActions( props: ErrorRowActionsProps ) {
	const { row, onDelete, onCreate, table, disabled } = props;
	const { setErrorsTable } = useTableStore();
	const { url, ip, agent, id } = row;
	const { groupBy } = table;
	const menu: JSX.Element[] = [];

	const setFilter = ( filterBy: any ) => {
		setErrorsTable( { filterBy, page: 0, groupBy: '', selected: [], selectAll: false } );
	};

	menu.push(
		<RowAction onClick={ () => onDelete( id ) } capability={ CAP_404_DELETE } key="0">
			{ __( 'Delete', 'redirection' ) }
		</RowAction>
	);

	const getActionData = () => {
		if ( groupBy === 'ip' ) {
			return [ id ];
		}
		if ( groupBy === '' ) {
			return url;
		}
		return id;
	};

	menu.push(
		<RowAction
			onClick={ () => onCreate( getCreateAction( groupBy, getActionData() as any ) ) }
			capability={ CAP_REDIRECT_ADD }
			key="1"
		>
			{ __( 'Add Redirect', 'redirection' ) }
		</RowAction>
	);

	if ( agent ) {
		menu.unshift( <UseragentAction key="3" agent={ agent } /> );
	}

	menu.push(
		<RowAction
			onClick={ () => setFilter( getShowFilter( groupBy, groupBy === '' ? row.url : String( row.id ) ) ) }
			capability={ CAP_REDIRECT_MANAGE }
			key="4"
		>
			{ __( 'Show All', 'redirection' ) }
		</RowAction>
	);

	if ( groupBy === 'ip' ) {
		menu.push(
			<RowAction
				onClick={ () => onCreate( getCreateAction( 'block', [ ip ] ) ) }
				capability={ CAP_REDIRECT_ADD }
				key="5"
			>
				{ __( 'Block IP', 'redirection' ) }
			</RowAction>
		);
	} else if ( groupBy !== 'agent' ) {
		menu.push(
			<RowAction
				onClick={ () => onCreate( getCreateAction( 'ignore', url ) ) }
				capability={ CAP_REDIRECT_ADD }
				key="6"
			>
				{ __( 'Ignore URL', 'redirection' ) }
			</RowAction>
		);
	}

	return <RowActions disabled={ disabled } actions={ menu } />;
}

export default ErrorRowActions;
