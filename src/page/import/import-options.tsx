import { __ } from '@wordpress/i18n';
import { Select } from '@wp-plugin-components';
import type { SelectOption } from '@wp-plugin-components/select';
import type { DuplicateMode } from 'lib/api/hooks';
import { nestedGroups } from 'lib/wordpress-url';

interface GroupRow {
	id: number;
	[ key: string ]: unknown;
}

interface ImportOptionsProps {
	activeImportType: 'file' | 'plugin' | null;
	activePluginId: string | null;
	file: File | false;
	disabled?: boolean;
	deleteSource: boolean;
	duplicateMode: DuplicateMode;
	group: number;
	groupRows: GroupRow[];
	isJsonFile: ( file: File ) => boolean;
	onChange: ( event: React.ChangeEvent< HTMLSelectElement | HTMLInputElement > ) => void;
}

function ImportOptions( {
	activeImportType,
	activePluginId,
	file,
	disabled = false,
	deleteSource,
	duplicateMode,
	group,
	groupRows,
	isJsonFile,
	onChange,
}: ImportOptionsProps ) {
	const items = nestedGroups( groupRows as any ) as unknown as SelectOption[];
	const showDeleteSource =
		activeImportType === 'plugin' &&
		( activePluginId === 'wordpress-old-slugs' || activePluginId === 'safe-redirect-manager' );
	const groupItems =
		activeImportType === 'file' && file && isJsonFile( file )
			? [ { value: '0', label: __( 'Use groups in file', 'redirection' ) }, ...items ]
			: items;

	return (
		<fieldset className="groups inline-edit-row" disabled={ disabled }>
			<h3>{ __( 'Import options', 'redirection' ) }</h3>
			<div className="groups__row">
				<div className="groups__label">{ __( 'Group', 'redirection' ) }</div>
				<div className="groups__control">
					<Select
						items={ groupItems as any }
						name="group"
						value={ String( group ) }
						onChange={ onChange as any }
						disabled={ disabled }
					/>
				</div>
			</div>
			<div className="groups__row">
				<div className="groups__label">{ __( 'Duplicates', 'redirection' ) }</div>
				<div className="groups__control">
					<Select
						items={ [
							{ value: 'import', label: __( 'Import everything', 'redirection' ) },
							{ value: 'ignore', label: __( 'Ignore duplicates', 'redirection' ) },
							{ value: 'update', label: __( 'Update duplicates', 'redirection' ) },
						] }
						name="duplicate_mode"
						value={ duplicateMode }
						onChange={ onChange as any }
						disabled={ disabled }
					/>
				</div>
			</div>
			{ showDeleteSource && (
				<div className="groups__row">
					<div className="groups__label">{ __( 'Delete original data', 'redirection' ) }</div>
					<div className="groups__control">
						<label className="groups__checkbox">
							<input
								type="checkbox"
								name="delete_source"
								checked={ deleteSource }
								onChange={ onChange }
								disabled={ disabled }
							/>{ ' ' }
							<span>{ __( 'Remove the original redirect data after import', 'redirection' ) }</span>
						</label>
					</div>
				</div>
			) }
		</fieldset>
	);
}

export default ImportOptions;
