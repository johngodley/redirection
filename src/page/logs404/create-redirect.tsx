import { useState } from 'react';
import { __ } from '@wordpress/i18n';
import EditRedirect from 'component/redirect-edit';
import { Modal } from '@wp-plugin-components';
import { getDefaultItem } from 'lib/redirect-constants';
import { has_capability, CAP_404_DELETE } from 'lib/capabilities';
import { useSettingsStore } from 'stores';
import { useErrorBulkAction } from 'lib/api/hooks/use-logs';

interface Row {
	id: number | string;
	url: string;
}

interface RedirectData {
	url: string | string[];
}

interface CreateRedirectProps {
	onClose: () => void;
	redirect: RedirectData;
	rows: Row[];
}

function getRowForId( url: string | number, rows: Row[] ): string {
	const rowUrl = rows.find( ( row ) => row.id === url || row.id === parseInt( url as string, 10 ) );

	if ( rowUrl ) {
		return rowUrl.url;
	}

	return url as string;
}

function getUniqueUrls( urls: string | string[] | undefined, rows: Row[] ): string | string[] {
	if ( ! urls ) {
		return '';
	}

	if ( ! Array.isArray( urls ) ) {
		return urls;
	}

	return [ ...new Set( urls.map( ( url ) => getRowForId( url, rows ) ) ) ];
}

function CreateRedirect( props: CreateRedirectProps ) {
	const { onClose, redirect, rows } = props;
	// Direct property access instead of destructuring
	const values = useSettingsStore( ( state ) => state.values );
	const defaultFlags = {
		flag_regex: values?.flag_regex || false,
		flag_trailing: values?.flag_trailing || false,
		flag_case: values?.flag_case || false,
		flag_query: values?.flag_query || 'exact',
	};
	const { mutate: errorBulkAction } = useErrorBulkAction();
	const uniqueUrls = getUniqueUrls( redirect.url, rows );
	const [ deleteLog, setDeleteLog ] = useState( false );
	const item = {
		...getDefaultItem( Array.isArray( uniqueUrls ) ? uniqueUrls[ 0 ] ?? '' : uniqueUrls, 0, defaultFlags ),
		...redirect,
		url: uniqueUrls,
	};

	function handleDelete( urls: string[] ) {
		errorBulkAction( { action: 'delete', items: urls as any, params: { groupBy: 'url', deleteConfirm: true } } );
	}

	return (
		<Modal onClose={ onClose } padding>
			<div className="add-new">
				<EditRedirect
					item={ item }
					saveButton={ __( 'Add Redirect', 'redirection' ) }
					onCancel={ onClose }
					childSave={ () =>
						deleteLog && handleDelete( Array.isArray( uniqueUrls ) ? uniqueUrls : [ uniqueUrls as string ] )
					}
					canSave={ ( multi: boolean ) =>
						( deleteLog &&
							// eslint-disable-next-line no-alert
							confirm(
								multi
									? __( 'Are you sure you want to delete the selected items?', 'redirection' )
									: __( 'Are you sure you want to delete this item?', 'redirection' )
							) ) ||
						! deleteLog
					}
					// Auto-focus is intentional here to streamline adding redirects from 404 logs.
					// eslint-disable-next-line jsx-a11y/no-autofocus
					autoFocus
				>
					{ has_capability( CAP_404_DELETE ) && (
						<tr>
							<th>{ __( 'Delete Log Entries', 'redirection' ) }</th>
							<td className="edit-left" style={ { padding: '7px 0px' } }>
								<input
									id="create-redirect-delete-log"
									type="checkbox"
									checked={ deleteLog }
									onChange={ ( ev ) => setDeleteLog( ev.target.checked ) }
								/>
								<label htmlFor="create-redirect-delete-log">
									{ ( Array.isArray( uniqueUrls ) && uniqueUrls.length <= 1 ) ||
									! Array.isArray( uniqueUrls )
										? __( 'Delete logs for this entry', 'redirection' )
										: __( 'Delete logs for these entries', 'redirection' ) }
								</label>
							</td>
						</tr>
					) }
				</EditRedirect>
			</div>
		</Modal>
	);
}

export default CreateRedirect;
