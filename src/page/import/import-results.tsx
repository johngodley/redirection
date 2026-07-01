import { __, _n } from '@wordpress/i18n';
import IoCard, { type CardMetaItem, type CardStatItem } from 'component/import-export/card';
import type { ImportStats } from './types';

interface ImportResultsProps {
	activeImportType: 'file' | 'plugin' | null;
	lastImport: ImportStats | false;
	lastImportWasDryRun: boolean | null;
}

function ImportResults( { activeImportType, lastImport, lastImportWasDryRun }: ImportResultsProps ) {
	if ( lastImport === false ) {
		return null;
	}

	const isPreview = lastImportWasDryRun === true;
	let details: CardMetaItem[] = [];

	if ( lastImport.created === 0 && lastImport.updated === 0 && lastImport.ignored > 0 ) {
		details = [
			{
				label: __( 'Status', 'redirection' ),
				value: isPreview
					? __( 'All matching redirects would be ignored.', 'redirection' )
					: __( 'All matching redirects were ignored.', 'redirection' ),
			},
		];
	} else if (
		lastImport.created === 0 &&
		lastImport.updated === 0 &&
		lastImport.groups_imported === 0 &&
		lastImport.logs_imported === 0 &&
		lastImport.errors_imported === 0 &&
		lastImport.settings_imported === 0
	) {
		details = [
			{
				label: __( 'Status', 'redirection' ),
				value: isPreview
					? __( 'No redirects were found to preview.', 'redirection' )
					: __( 'No redirects were imported.', 'redirection' ),
			},
		];
	}

	if ( isPreview ) {
		details = details.concat( [
			{
				label: __( 'Mode', 'redirection' ),
				value: __( 'Preview only. No changes have been made.', 'redirection' ),
			},
		] );
	}

	const stats: CardStatItem[] = [
		{
			label: _n( 'Redirect created', 'Redirects created', lastImport.created, 'redirection' ),
			value: lastImport.created,
		},
		{
			label: _n( 'Redirect updated', 'Redirects updated', lastImport.updated, 'redirection' ),
			value: lastImport.updated,
		},
		{
			label: _n( 'Duplicate ignored', 'Duplicates ignored', lastImport.ignored, 'redirection' ),
			value: lastImport.ignored,
		},
		{
			label: _n( 'Group created', 'Groups created', lastImport.groups_created, 'redirection' ),
			value: lastImport.groups_created,
		},
		{
			label: _n( 'Group imported', 'Groups imported', lastImport.groups_imported, 'redirection' ),
			value: lastImport.groups_imported,
		},
		{
			label: _n( 'Log imported', 'Logs imported', lastImport.logs_imported, 'redirection' ),
			value: lastImport.logs_imported,
		},
		{
			label: _n( '404 log imported', '404 logs imported', lastImport.errors_imported, 'redirection' ),
			value: lastImport.errors_imported,
		},
		{
			label: _n( 'Setting imported', 'Settings imported', lastImport.settings_imported, 'redirection' ),
			value: lastImport.settings_imported,
		},
	];

	const getViewRedirectUrl = ( redirectId?: number ) => {
		if ( ! redirectId ) {
			return '';
		}

		return window.Redirectioni10n.pluginRoot + '&' + encodeURIComponent( 'filterby[id]' ) + '=' + redirectId;
	};

	const renderSource = ( row: ImportStats[ 'preview' ][ number ] ) => {
		if ( row.result === 'updated' && row.redirect_id ) {
			return <a href={ getViewRedirectUrl( row.redirect_id ) }>{ row.source || ' ' }</a>;
		}

		return row.source || ' ';
	};

	return (
		<>
			<div className="file-sniff">
				<IoCard
					title={ isPreview ? __( 'Preview results', 'redirection' ) : __( 'Import results', 'redirection' ) }
					badge={ __( 'Success', 'redirection' ) }
					meta={ details }
					stats={ stats }
					variant="success"
				/>
			</div>

			{ activeImportType !== null && lastImport.preview.length > 0 && (
				<div className="io-preview-table">
					<table className="wp-list-table widefat fixed striped items table-auto inline-edit-row">
						<thead>
							<tr>
								<th>{ __( 'Source', 'redirection' ) }</th>
								<th>{ __( 'Target', 'redirection' ) }</th>
								<th className="io-preview-table__code">{ __( 'Code', 'redirection' ) }</th>
								<th className="io-preview-table__regex">{ __( 'Regex', 'redirection' ) }</th>
								<th>{ __( 'Group', 'redirection' ) }</th>
							</tr>
						</thead>
						<tbody>
							{ lastImport.preview.map( ( row, index ) => (
								<tr
									key={ `${ row.source }-${ row.target }-${ index }` }
									className={
										row.result === 'ignored' ? 'io-preview-table__row--ignored' : undefined
									}
								>
									<td>{ renderSource( row ) }</td>
									<td>{ row.target || ' ' }</td>
									<td className="io-preview-table__code">{ row.code || '' }</td>
									<td className="io-preview-table__regex">
										{ row.regex ? __( 'Yes', 'redirection' ) : __( 'No', 'redirection' ) }
									</td>
									<td>{ row.group || '' }</td>
								</tr>
							) ) }
						</tbody>
					</table>
				</div>
			) }
		</>
	);
}

export default ImportResults;
