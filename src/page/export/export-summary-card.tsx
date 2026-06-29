import type { ReactNode } from 'react';
import { __, _n } from '@wordpress/i18n';
import IoCard, { type CardMetaItem, type CardStatItem } from 'component/import-export/card';

interface ExportSummaryCardProps {
	meta: CardMetaItem[];
	isPreviewLoading: boolean;
	previewTotal: number | null;
	previewEstimatedSize: number | null;
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

function formatNumber( value: number ) {
	const locale = window.Redirectioni10n?.locale || undefined;

	return new Intl.NumberFormat( locale ).format( value );
}

function ExportSummaryCard( { meta, isPreviewLoading, previewTotal, previewEstimatedSize }: ExportSummaryCardProps ) {
	const stats: CardStatItem[] = [];
	let badge: ReactNode = __( 'Preview', 'redirection' );

	if ( isPreviewLoading ) {
		badge = __( 'Loading', 'redirection' );
	} else if ( previewTotal !== null ) {
		stats.push( {
			label: _n( 'Item available', 'Items available', previewTotal, 'redirection' ),
			value: formatNumber( previewTotal ),
		} );

		if ( previewEstimatedSize !== null ) {
			stats.push( {
				label: __( 'Approximate file size', 'redirection' ),
				value: formatFileSize( previewEstimatedSize ),
			} );
		}
	}

	return <IoCard title={ __( 'Export preview', 'redirection' ) } badge={ badge } meta={ meta } stats={ stats } />;
}

export default ExportSummaryCard;
