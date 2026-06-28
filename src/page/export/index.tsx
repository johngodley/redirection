import { useState } from 'react';
import { __ } from '@wordpress/i18n';
import 'component/import-export/style.scss';
import { useExport } from 'lib/api/hooks';
import { getExportUrl } from 'lib/wordpress-url';
import { LOGS_TYPE_404, LOGS_TYPE_REDIRECT } from 'lib/log-constants';
import ExportCSV from 'page/logs/export-csv';

function ExportPage() {
	const [ module, setModule ] = useState< string >( 'all' );
	const [ format, setFormat ] = useState< string >( 'json' );
	const [ exportData, setExportData ] = useState< string | false >( false );
	const exportMutation = useExport( {
		onSuccess: ( data ) => {
			setExportData( data );
		},
	} );

	let exportStatus = 'idle';
	if ( exportMutation.isPending ) {
		exportStatus = 'loading';
	} else if ( exportMutation.isSuccess ) {
		exportStatus = 'success';
	}

	const onInput = ( event: React.ChangeEvent< HTMLSelectElement > ) => {
		const { name, value } = event.target;

		if ( name === 'module' ) {
			setModule( value );
			if ( value === 'everything' ) {
				setFormat( 'json' );
			}
		} else if ( name === 'format' ) {
			setFormat( value );
		}
	};

	const onView = () => {
		exportMutation.mutate( { moduleId: module, format } );
	};

	const onDownload = () => {
		window.location.href = getExportUrl( module, format );
	};

	const onClose = () => {
		setExportData( false );
	};

	const renderExport = ( data: string ) => {
		return (
			<div>
				<textarea className="module-export" rows={ 14 } readOnly={ true } value={ data } />
				<input
					className="button-secondary"
					type="submit"
					value={ __( 'Close', 'redirection' ) }
					onClick={ onClose }
				/>
			</div>
		);
	};

	const renderExporting = () => {
		return (
			<div className="loader-wrapper loader-textarea">
				<div className="wpl-placeholder__loading"></div>
			</div>
		);
	};

	return (
		<div className="redirect-io-page">
			<section className="io-section io-section--export">
				<p>
					{ __(
						'Export to CSV, Apache .htaccess, Nginx, or Redirection JSON. The JSON format contains full information, and other formats contain partial information appropriate to the format.',
						'redirection'
					) }
				</p>

				<p className="redirect-export_buttons">
					<select name="module" onChange={ onInput } value={ module }>
						<option value="0">{ __( 'Everything', 'redirection' ) }</option>
						<option value="1">{ __( 'WordPress redirects', 'redirection' ) }</option>
						<option value="2">{ __( 'Apache redirects', 'redirection' ) }</option>
						<option value="3">{ __( 'Nginx redirects', 'redirection' ) }</option>
					</select>

					<select name="format" onChange={ onInput } value={ format }>
						<option value="json">{ __( 'Complete data (JSON)', 'redirection' ) }</option>
						<option value="csv">{ __( 'CSV', 'redirection' ) }</option>
						<option value="apache">{ __( 'Apache .htaccess', 'redirection' ) }</option>
						<option value="nginx">{ __( 'Nginx rewrite rules', 'redirection' ) }</option>
					</select>

					<button className="button-primary" onClick={ onView }>
						{ __( 'View', 'redirection' ) }
					</button>
					<button className="button-secondary" onClick={ onDownload }>
						{ __( 'Download', 'redirection' ) }
					</button>
				</p>

				{ exportStatus === 'loading' && renderExporting() }
				{ exportData && exportStatus !== 'loading' && renderExport( exportData ) }

				<div className="io-subsection io-section--logs">
					<h2>{ __( 'Export logs', 'redirection' ) }</h2>
					<ExportCSV logType={ LOGS_TYPE_REDIRECT } title={ __( 'Export redirect', 'redirection' ) } />
					<br />
					<ExportCSV logType={ LOGS_TYPE_404 } title={ __( 'Export 404', 'redirection' ) } />
				</div>
			</section>
		</div>
	);
}

export default ExportPage;
