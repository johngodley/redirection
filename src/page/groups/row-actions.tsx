import { __ } from '@wordpress/i18n';
import { RowActions, RowAction } from 'component/table/row-action';
import { useGroupBulkAction } from 'lib/api/hooks';
import { has_capability, CAP_GROUP_ADD, CAP_GROUP_DELETE, CAP_REDIRECT_MANAGE } from 'lib/capabilities';

interface Group {
	id: number;
	enabled: boolean;
	name: string;
	module_id: number;
	redirects: number;
}

interface RowParams {
	setRowMode: ( mode: string | false ) => void;
	rowMode: string | false;
}

interface GroupRowActionsProps {
	disabled: boolean;
	rowParams: RowParams;
	row: Group;
}

function GroupRowActions( props: GroupRowActionsProps ) {
	const { disabled, rowParams, row } = props;
	const { mutate: performBulkAction } = useGroupBulkAction();
	const { setRowMode, rowMode } = rowParams;
	const { id, enabled } = row;
	const menu: JSX.Element[] = [];

	if ( rowMode === 'edit' ) {
		return null;
	}

	if ( has_capability( CAP_GROUP_ADD ) ) {
		menu.push(
			<RowAction onClick={ () => setRowMode( rowMode === 'edit' ? false : 'edit' ) } key="0">
				{ __( 'Edit', 'redirection' ) }
			</RowAction>
		);
	}

	if ( has_capability( CAP_GROUP_DELETE ) ) {
		menu.push(
			<RowAction
				onClick={ () => {
					if ( window.confirm( __( 'Are you sure you want to delete this item?', 'redirection' ) ) ) {
						performBulkAction( { action: 'delete', items: [ id ] } );
					}
				} }
				key="1"
			>
				{ __( 'Delete', 'redirection' ) }
			</RowAction>
		);
	}

	if ( has_capability( CAP_REDIRECT_MANAGE ) ) {
		menu.push(
			<RowAction
				key="2"
				href={ Redirectioni10n.pluginRoot + '&' + encodeURIComponent( 'filterby[group]' ) + '=' + id }
			>
				{ __( 'View Redirects', 'redirection' ) }
			</RowAction>
		);
	}

	if ( has_capability( CAP_GROUP_ADD ) ) {
		if ( enabled ) {
			menu.push(
				<RowAction key="3" onClick={ () => performBulkAction( { action: 'disable', items: [ id ] } ) }>
					{ __( 'Disable', 'redirection' ) }
				</RowAction>
			);
		} else {
			menu.push(
				<RowAction key="3" onClick={ () => performBulkAction( { action: 'enable', items: [ id ] } ) }>
					{ __( 'Enable', 'redirection' ) }
				</RowAction>
			);
		}
	}

	return <RowActions disabled={ disabled } actions={ menu } />;
}

export default GroupRowActions;
