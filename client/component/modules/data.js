/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';

const ModuleData = props => {
	const { data, onClose, onDownload, url, isLoading } = props;
	const downloader = () => {
		onDownload( url );
	};

	if ( isLoading ) {
		return (
			<div className="loader-wrapper loader-textarea">
				<div className="placeholder-loading">
				</div>
			</div>
		);
	}

	return (
		<div>
			<textarea className="module-export" rows="10" readOnly={ true } value={ data ? data : __( 'Failed to load' ) } />

			<input className="button-primary" type="submit" value={ __( 'Download' ) } onClick={ downloader } />&nbsp;
			<input className="button-secondary" type="submit" value={ __( 'Cancel' ) } onClick={ onClose } />
		</div>
	);
};

export default ModuleData;
