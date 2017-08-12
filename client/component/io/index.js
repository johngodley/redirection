/* global Redirectioni10n */
/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import { connect } from 'react-redux';
import Dropzone from 'react-dropzone';
import Select from 'component/wordpress/select';
import classnames from 'classnames';

/**
 * Internal dependencies
 */

import { getGroup } from 'state/group/action';
import { nestedGroups } from 'state/group/selector';
import { importFile, clearFile, addFile } from 'state/io/action';
import { STATUS_IN_PROGRESS, STATUS_COMPLETE } from 'state/settings/type';
import { exportFile, downloadFile } from 'state/io/action';

const getUrl = ( moduleId, modType ) => Redirectioni10n.pluginRoot + '&sub=modules&export=' + moduleId + '&exporter=' + modType;

class ImportExport extends React.Component {
	constructor( props ) {
		super( props );

		this.props.onLoadGroups();

		this.setDropzone = this.onSetZone.bind( this );
		this.handleDrop = this.onDrop.bind( this );
		this.handleOpen = this.onOpen.bind( this );
		this.handleInput = this.onInput.bind( this );
		this.handleCancel = this.onCancel.bind( this );
		this.handleImport = this.onImport.bind( this );
		this.handleEnter = this.onEnter.bind( this );
		this.handleLeave = this.onLeave.bind( this );
		this.handleView = this.onView.bind( this );
		this.handleDownload = this.onDownload.bind( this );

		this.state = {
			group: 0,
			hover: false,
			module: 'everything',
			format: 'json',
		};
	}

	onView() {
		this.props.onExport( this.state.module, this.state.format );
	}

	onDownload() {
		this.props.onDownloadFile( getUrl( this.state.module, this.state.format ) );
	}

	onEnter() {
		const { importingStatus } = this.props.io;

		if ( importingStatus !== STATUS_IN_PROGRESS ) {
			this.setState( { hover: true } );
		}
	}

	onLeave() {
		this.setState( { hover: false } );
	}

	onImport() {
		this.props.onImport( this.props.io.file, this.state.group );
	}

	onCancel() {
		this.setState( { hover: false } );
		this.props.onClearFile();
	}

	onInput( event ) {
		const { target } = event;

		this.setState( { [ target.name ]: target.value } );

		if ( target.name === 'module' && target.value === 'everything' ) {
			this.setState( { format: 'json' } );
		}
	}

	onSetZone( ref ) {
		this.dropzone = ref;
	}

	onDrop( accepted ) {
		const { importingStatus } = this.props.io;

		if ( accepted.length > 0 && importingStatus !== STATUS_IN_PROGRESS ) {
			this.props.onAddFile( accepted[ 0 ] );
		}

		this.setState( { hover: false, group: this.props.group.rows[ 0 ].id } );
	}

	onOpen() {
		this.dropzone.open();
	}

	renderGroupSelect() {
		const { rows } = this.props.group;

		return (
			<div className="groups">
				{ __( 'Import to group' ) }	<Select items={ nestedGroups( rows ) } name="group" value={ this.state.group } onChange={ this.handleInput } />
			</div>
		);
	}

	renderInitialDrop() {
		return (
			<div>
				<h3>{ __( 'Import a CSV, .htaccess, or JSON file.' ) }</h3>
				<p>{ __( "Click 'Add File' or drag and drop here." ) }</p>

				<button type="button" className="button-secondary" onClick={ this.handleOpen }>{ __( 'Add File' ) }</button>
			</div>
		);
	}

	renderDropBeforeUpload() {
		const { file } = this.props.io;
		const isJson = file.type === 'application/json';

		return (
			<div>
				<h3>{ __( 'File selected' ) }</h3>

				<p><code>{ file.name }</code></p>

				{ ! isJson && this.renderGroupSelect() }

				<button className="button-primary" onClick={ this.handleImport }>{ __( 'Upload' ) }</button> &nbsp;
				<button className="button-secondary" onClick={ this.handleCancel }>{ __( 'Cancel' ) }</button>
			</div>
		);
	}

	renderUploading() {
		const { file } = this.props.io;

		return (
			<div>
				<h3>{ __( 'Importing' ) }</h3>

				<p><code>{ file.name }</code></p>

				<div className="is-placeholder">
					<div className="placeholder-loading"></div>
				</div>
			</div>
		);
	}

	renderUploaded() {
		const { lastImport } = this.props.io;

		return (
			<div>
				<h3>{ __( 'Finished importing' ) }</h3>

				<p>{ __( 'Total redirects imported:' ) } { lastImport }</p>
				{ lastImport === 0 && <p>{ __( 'Double-check the file is the correct format!' ) }</p> }

				<button className="button-secondary" onClick={ this.handleCancel }>{ __( 'OK' ) }</button>
			</div>
		);
	}

	renderDropzoneContent() {
		const { importingStatus, lastImport, file } = this.props.io;

		if ( importingStatus === STATUS_IN_PROGRESS ) {
			return this.renderUploading();
		}

		if ( importingStatus === STATUS_COMPLETE && lastImport !== false && file === false ) {
			return this.renderUploaded();
		}

		if ( file === false ) {
			return this.renderInitialDrop();
		}

		return this.renderDropBeforeUpload();
	}

	renderExport( data ) {
		return (
			<div>
				<textarea className="module-export" rows="14" readOnly={ true } value={ data } />
				<input className="button-secondary" type="submit" value={ __( 'Close' ) } onClick={ this.handleCancel } />
			</div>
		);
	}

	renderExporting() {
		return (
			<div className="loader-wrapper loader-textarea">
				<div className="placeholder-loading"></div>
			</div>
		);
	}

	render() {
		const { hover } = this.state;
		const { importingStatus, file, exportData, exportStatus } = this.props.io;
		const classes = classnames( {
			dropzone: true,
			'dropzone-dropped': file !== false,
			'dropzone-importing': importingStatus === STATUS_IN_PROGRESS,
			'dropzone-hover': hover,
		} );

		return (
			<div>
				<h2>{ __( 'Import' ) }</h2>

				<Dropzone ref={ this.setDropzone } onDrop={ this.handleDrop } onDragLeave={ this.handleLeave } onDragEnter={ this.handleEnter } className={ classes } disableClick disablePreview multiple={ false }>
					{ this.renderDropzoneContent() }
				</Dropzone>

				<p>{ __( 'All imports will be appended to the current database.' ) }</p>
				<div className="inline-notice notice-warning">
					<p>
						{ __( 'CSV file format: {{code}}source URL, target URL{{/code}} - and can be optionally followed with {{code}}regex, http code{{/code}} (regex - 0 for no, 1 for yes).', {
							components: {
								code: <code />
							}
						} ) }
					</p>
				</div>

				<h2>{ __( 'Export' ) }</h2>
				<p>{ __( 'Export to CSV, Apache .htaccess, Nginx, or Redirection JSON (which contains all redirects and groups).' ) }</p>

				<select name="module" onChange={ this.handleInput } value={ this.state.module }>
					<option value="0">{ __( 'Everything' ) }</option>
					<option value="1">{ __( 'WordPress redirects' ) }</option>
					<option value="2">{ __( 'Apache redirects' ) }</option>
					<option value="3">{ __( 'Nginx redirects' ) }</option>
				</select>

				<select name="format" onChange={ this.handleInput } value={ this.state.format }>
					<option value="csv">{ __( 'CSV' ) }</option>
					<option value="apache">{ __( 'Apache .htaccess' ) }</option>
					<option value="nginx">{ __( 'Nginx rewrite rules' ) }</option>
					<option value="json">{ __( 'Redirection JSON' ) }</option>
				</select>
				&nbsp;
				<button className="button-primary" onClick={ this.handleView }>{ __( 'View' ) }</button>
				&nbsp;
				<button className="button-secondary" onClick={ this.handleDownload }>{ __( 'Download' ) }</button>

				{ exportStatus === STATUS_IN_PROGRESS && this.renderExporting() }
				{ exportData && this.renderExport( exportData ) }

				<p>{ __( 'Log files can be exported from the log pages.' ) }</p>
			</div>
		);
	}
}

function mapStateToProps( state ) {
	const { group, io } = state;

	return {
		group,
		io,
	};
}

function mapDispatchToProps( dispatch ) {
	return {
		onLoadGroups: () => {
			dispatch( getGroup() );
		},
		onImport: ( file, groupId ) => {
			dispatch( importFile( file, groupId ) );
		},
		onAddFile: ( file ) => {
			dispatch( addFile( file ) );
		},
		onClearFile: () => {
			dispatch( clearFile() );
		},
		onExport: ( moduleId, moduleType ) => {
			dispatch( exportFile( moduleId, moduleType ) );
		},
		onDownloadFile: url => {
			dispatch( downloadFile( url ) );
		},
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps,
)( ImportExport );
