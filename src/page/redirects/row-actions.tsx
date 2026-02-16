import { __ } from '@wordpress/i18n';
import { RowActions, RowAction } from 'component/table/row-action';
import { useRedirectBulkAction, useRedirectDelete } from 'lib/api/hooks';
import { has_capability, CAP_REDIRECT_ADD, CAP_REDIRECT_DELETE } from 'lib/capabilities';

interface Redirect {
	id: number;
	enabled: boolean;
	regex: boolean;
	action_type: string;
	match_type: string;
	url: string;
	action_code: number;
	group_id: number;
}

interface RowParams {
	setRowMode: ( mode: string | false ) => void;
	rowMode: string | false;
}

interface RedirectRowActionsProps {
	disabled: boolean;
	rowParams: RowParams;
	row: Redirect;
}

function RedirectRowActions( props: RedirectRowActionsProps ) {
	const { disabled, rowParams, row } = props;
	const { mutate: deleteRedirect } = useRedirectDelete();
	const { mutate: performDisable } = useRedirectBulkAction( 'disable' );
	const { mutate: performEnable } = useRedirectBulkAction( 'enable' );
	const { setRowMode, rowMode } = rowParams;
	const { id, enabled, regex, action_type, match_type } = row;
	const menu: JSX.Element[] = [];

	if ( rowMode === 'edit' ) {
		return null;
	}

	if ( enabled && has_capability( CAP_REDIRECT_ADD ) ) {
		menu.push(
			<RowAction key="1" onClick={ () => setRowMode( 'edit' ) }>
				{ __( 'Edit', 'redirection' ) }
			</RowAction>
		);
	}

	if ( has_capability( CAP_REDIRECT_DELETE ) ) {
		menu.push(
			<RowAction
				key="2"
				onClick={ () => {
					if ( window.confirm( __( 'Are you sure you want to delete this item?', 'redirection' ) ) ) {
						deleteRedirect( { items: [ id ] } );
					}
				} }
			>
				{ __( 'Delete', 'redirection' ) }
			</RowAction>
		);
	}

	if ( has_capability( CAP_REDIRECT_ADD ) ) {
		if ( enabled ) {
			menu.push(
				<RowAction key="3" onClick={ () => performDisable( { items: [ id ] } ) }>
					{ __( 'Disable', 'redirection' ) }
				</RowAction>
			);
		} else {
			menu.push(
				<RowAction key="4" onClick={ () => performEnable( { items: [ id ] } ) }>
					{ __( 'Enable', 'redirection' ) }
				</RowAction>
			);
		}
	}

	if ( enabled && ! regex && action_type === 'url' && ( match_type === 'url' || match_type === 'server' ) ) {
		menu.push(
			<RowAction key="5" onClick={ () => setRowMode( 'check' ) }>
				{ __( 'Check Redirect', 'redirection' ) }
			</RowAction>
		);
	}

	return <RowActions disabled={ disabled } actions={ menu } />;
}

export default RedirectRowActions;
