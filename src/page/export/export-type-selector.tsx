import { __, _n } from '@wordpress/i18n';
import clsx from 'clsx';
import IoCard from 'component/import-export/card';
import type { ExportType, ExportTypeOption, GroupRow, RedirectModule, RedirectScopeType } from './types';

interface ExportTypeSelectorProps {
	exportTypes: ExportTypeOption[];
	selectedTypes: ExportType[];
	redirectScopeType: RedirectScopeType;
	redirectModule: RedirectModule;
	redirectGroup: number;
	groupRows: GroupRow[];
	isExporting: boolean;
	onChange: ( name: 'redirectScopeType' | 'redirectModule' | 'redirectGroup', value: string ) => void;
	onToggle: ( exportType: ExportType ) => void;
}

function ExportTypeSelector( {
	exportTypes,
	selectedTypes,
	redirectScopeType,
	redirectModule,
	redirectGroup,
	groupRows,
	isExporting,
	onChange,
	onToggle,
}: ExportTypeSelectorProps ) {
	return (
		<div className="export-types">
			{ exportTypes.map( ( exportType ) => {
				const isActive = selectedTypes.includes( exportType.id );

				return (
					<IoCard
						key={ exportType.id }
						title={ exportType.name }
						badge={ __( 'Export', 'redirection' ) }
						meta={ [
							{
								label: __( 'Export type', 'redirection' ),
								value: exportType.description,
								fullWidth: true,
							},
						] }
						stats={ [
							{
								label: _n( 'Format', 'Formats', exportType.formats.length, 'redirection' ),
								value: exportType.formats.length,
							},
						] }
						actions={
							<label className="groups__checkbox" htmlFor={ `export-type-${ exportType.id }` }>
								<input
									id={ `export-type-${ exportType.id }` }
									type="checkbox"
									checked={ isActive }
									onChange={ () => onToggle( exportType.id ) }
									disabled={ isExporting }
								/>
								<span>{ __( 'Include in export', 'redirection' ) }</span>
							</label>
						}
						className={ clsx( 'import-source-card', {
							'import-source-card--active': isActive,
						} ) }
					>
						{ exportType.id === 'redirect' && isActive && (
							<div className="import-source-card__options">
								<div className="groups__row">
									<div className="groups__label">{ __( 'Scope', 'redirection' ) }</div>
									<div className="groups__control">
										<select
											value={ redirectScopeType }
											disabled={ isExporting }
											onChange={ ( event ) => onChange( 'redirectScopeType', event.target.value ) }
										>
											<option value="all">{ __( 'Everything', 'redirection' ) }</option>
											<option value="module">{ __( 'Module', 'redirection' ) }</option>
											<option value="group">{ __( 'Group', 'redirection' ) }</option>
										</select>
									</div>
								</div>

								{ redirectScopeType === 'module' && (
									<div className="groups__row">
										<div className="groups__label">{ __( 'Module', 'redirection' ) }</div>
										<div className="groups__control">
											<select
												value={ redirectModule }
												disabled={ isExporting }
												onChange={ ( event ) => onChange( 'redirectModule', event.target.value ) }
											>
												<option value="1">{ __( 'WordPress redirects', 'redirection' ) }</option>
												<option value="2">{ __( 'Apache redirects', 'redirection' ) }</option>
												<option value="3">{ __( 'Nginx redirects', 'redirection' ) }</option>
											</select>
										</div>
									</div>
								) }

								{ redirectScopeType === 'group' && (
									<div className="groups__row">
										<div className="groups__label">{ __( 'Group', 'redirection' ) }</div>
										<div className="groups__control">
											<select
												value={ String( redirectGroup ) }
												disabled={ isExporting }
												onChange={ ( event ) => onChange( 'redirectGroup', event.target.value ) }
											>
												{ groupRows.map( ( group ) => (
													<option key={ group.id } value={ group.id }>
														{ group.moduleName ? `${ group.name } (${ group.moduleName })` : group.name }
													</option>
												) ) }
											</select>
										</div>
									</div>
								) }
							</div>
						) }
					</IoCard>
				);
			} ) }
		</div>
	);
}

export default ExportTypeSelector;
