import type { ImportSniffResult } from 'component/import-export/import-sniff';
import type { DuplicateMode } from 'lib/api/hooks';

interface ImportPreviewRow {
	source: string;
	target: string;
	code: number;
	regex: boolean;
	group: string;
	result: 'created' | 'updated' | 'ignored';
	redirect_id?: number;
}

interface ImportStats {
	created: number;
	updated: number;
	ignored: number;
	groups_created: number;
	preview: ImportPreviewRow[];
}

interface ImportPlugin {
	id: string;
	name: string;
	description: string;
	source: string;
	preview_supported?: boolean;
	total: number;
}

interface ImportState {
	activeImportType: 'file' | 'plugin' | null;
	activePluginId: string | null;
	group: number;
	hover: boolean;
	file: File | false;
	duplicateMode: DuplicateMode;
	deleteSource: boolean;
	fileInfo: ImportSniffResult | null;
	isSniffing: boolean;
	lastImport: ImportStats | false;
	lastImportWasDryRun: boolean | null;
	groupRows: Array< { id: number } & Record< string, unknown > >;
	importers: ImportPlugin[];
	isLoadingImporters: boolean;
	isImporting: boolean;
	hasCompletedImport: boolean;
	hasActiveImport: boolean;
	previewSupported: boolean;
}

export type { ImportPlugin, ImportPreviewRow, ImportState, ImportStats, ImportSniffResult };
