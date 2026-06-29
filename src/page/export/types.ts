type ExportType = 'redirect' | 'log' | '404';
type ExportAction = 'view' | 'download';
type ExportFormat = 'json' | 'csv' | 'apache' | 'nginx';
type RedirectModule = 'all' | '1' | '2' | '3';
type RedirectScopeType = 'all' | 'module' | 'group';

interface GroupRow {
	id: number;
	name: string;
	moduleName?: string;
}

interface ExportTypeOption {
	id: ExportType;
	name: string;
	description: string;
	formats: ExportFormat[];
}

interface ExportState {
	exportType: ExportType;
	redirectScopeType: RedirectScopeType;
	redirectModule: RedirectModule;
	redirectGroup: number;
	format: ExportFormat;
	isExporting: boolean;
	isPreviewLoading: boolean;
	previewTotal: number | null;
	previewEstimatedSize: number | null;
	lastResult: ExportResult | false;
}

interface ExportResult {
	action: ExportAction;
	type: ExportType;
	format: ExportFormat;
	data: string;
	total: number | null;
}

export type {
	ExportAction,
	ExportFormat,
	ExportResult,
	ExportState,
	ExportType,
	ExportTypeOption,
	GroupRow,
	RedirectModule,
	RedirectScopeType,
};
