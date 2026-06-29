import { __ } from '@wordpress/i18n';
import ExportFormatSelector from './export-format-selector';
import type { ExportFormat, ExportType, GroupRow, RedirectModule, RedirectScopeType } from './types';

interface ExportOptionsProps {
	exportType: ExportType;
	redirectScopeType: RedirectScopeType;
	redirectModule: RedirectModule;
	redirectGroup: number;
	groupRows: GroupRow[];
	format: ExportFormat;
	availableFormats: ExportFormat[];
	disabled: boolean;
	onChange: ( name: 'redirectScopeType' | 'redirectModule' | 'redirectGroup' | 'format', value: string ) => void;
}

function ExportOptions( {
	exportType,
	redirectScopeType,
	redirectModule,
	redirectGroup,
	groupRows,
	format,
	availableFormats,
	disabled,
	onChange,
}: ExportOptionsProps ) {
	return (
		<div className="groups export-options">
			<h3>{ __( 'Export options', 'redirection' ) }</h3>

			{ exportType === 'redirect' && (
				<>
					<div className="groups__row">
						<div className="groups__label">{ __( 'Scope', 'redirection' ) }</div>
						<div className="groups__control">
							<select
								value={ redirectScopeType }
								disabled={ disabled }
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
									disabled={ disabled }
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
									disabled={ disabled }
									onChange={ ( event ) => onChange( 'redirectGroup', event.target.value ) }
								>
									{ groupRows.map( ( group ) => (
										<option key={ group.id } value={ group.id }>
											{ group.moduleName
												? `${ group.name } (${ group.moduleName })`
												: group.name }
										</option>
									) ) }
								</select>
							</div>
						</div>
					) }
				</>
			) }

			<ExportFormatSelector
				format={ format }
				availableFormats={ availableFormats }
				disabled={ disabled }
				onChange={ ( value ) => onChange( 'format', value ) }
			/>
		</div>
	);
}

export default ExportOptions;
