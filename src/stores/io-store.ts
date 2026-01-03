import { create } from 'zustand';
import { devtools } from 'zustand/middleware';
import type { Importer } from 'types';

export type IoStatus = 'idle' | 'loading' | 'success' | 'error';

export interface IoStore {
	exportStatus: IoStatus;
	exportData: string | false;
	importingStatus: IoStatus;
	lastImport: number | false;
	file: File | false;
	importers: Importer[];
	error: string | false;
	setExportStatus: ( status: IoStatus ) => void;
	setExportData: ( data: string | false ) => void;
	setImportingStatus: ( status: IoStatus ) => void;
	setLastImport: ( count: number | false ) => void;
	setFile: ( file: File | false ) => void;
	setImporters: ( importers: Importer[] ) => void;
	setError: ( error: string | false ) => void;
	clearFile: () => void;
	reset: () => void;
}

const initialState = {
	exportStatus: 'idle' as IoStatus,
	exportData: false as string | false,
	importingStatus: 'idle' as IoStatus,
	lastImport: false as number | false,
	file: false as File | false,
	importers: [],
	error: false as string | false,
};

export const useIoStore = create< IoStore >()(
	devtools(
		( set ) => ( {
			...initialState,

			setExportStatus: ( status ) => set( { exportStatus: status } ),

			setExportData: ( data ) => set( { exportData: data } ),

			setImportingStatus: ( status ) => set( { importingStatus: status } ),

			setLastImport: ( count ) => set( { lastImport: count } ),

			setFile: ( file ) => set( { file } ),

			setImporters: ( importers ) => set( { importers } ),

			setError: ( error ) => set( { error } ),

			clearFile: () => set( { file: false } ),

			reset: () => set( initialState ),
		} ),
		{ name: 'IoStore' }
	)
);
