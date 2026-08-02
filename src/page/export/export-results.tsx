import { __ } from '@wordpress/i18n';
import IoCard, { type CardMetaItem, type CardStatItem } from 'component/import-export/card';
import { getExportFormatLabel, getExportTypesLabel } from './export-helpers';
import type { ExportResult } from './types';

interface ExportResultsProps {
	lastResult: ExportResult | false;
}

function formatNumber( value: number ) {
	const locale = window.Redirectioni10n?.locale || undefined;

	return new Intl.NumberFormat( locale ).format( value );
}

function formatFileSize( size: number ) {
	if ( size < 1024 ) {
		return `${ size } B`;
	}

	if ( size < 1024 * 1024 ) {
		return `${ ( size / 1024 ).toFixed( 1 ) } KB`;
	}

	return `${ ( size / ( 1024 * 1024 ) ).toFixed( 1 ) } MB`;
}

function getFileSize( data: string ) {
	return new Blob( [ data ] ).size;
}

function ExportResults( { lastResult }: ExportResultsProps ) {
	if ( lastResult === false ) {
		return null;
	}

	const details: CardMetaItem[] = [
		{
			label: __( 'Action', 'redirection' ),
			value:
				lastResult.action === 'view'
					? __( 'Viewed in browser', 'redirection' )
					: __( 'Downloaded', 'redirection' ),
		},
		{
			label: __( 'Format', 'redirection' ),
			value: getExportFormatLabel( lastResult.format ),
		},
		{
			label: __( 'Export', 'redirection' ),
			value: getExportTypesLabel( lastResult.types ),
		},
		{
			label: __( 'File size', 'redirection' ),
			value: formatFileSize( getFileSize( lastResult.data ) ),
		},
	];
	const stats: CardStatItem[] = [];

	if ( lastResult.total !== null ) {
		stats.push( {
			label: __( 'Items exported', 'redirection' ),
			value: formatNumber( lastResult.total - ( lastResult.skipped ?? 0 ) ),
		} );
	}

	if ( lastResult.skipped ) {
		stats.push( {
			label: __( 'Skipped (unsupported for this format)', 'redirection' ),
			value: formatNumber( lastResult.skipped ),
		} );
	}

	return (
		<div className="export-results">
			<IoCard
				title={
					lastResult.action === 'view'
						? __( 'View results', 'redirection' )
						: __( 'Download ready', 'redirection' )
				}
				badge={ __( 'Success', 'redirection' ) }
				meta={ details }
				stats={ stats }
				variant="success"
			>
				{ lastResult.action === 'view' && (
					<textarea className="module-export" rows={ 14 } readOnly={ true } value={ lastResult.data } />
				) }
			</IoCard>
		</div>
	);
}

export default ExportResults;
