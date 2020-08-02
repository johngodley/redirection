/**
 * External dependencies
 */

import React, { useState } from 'react';
import { translate as __ } from 'wp-plugin-wp-plugin-lib/locale';
import Dropzone from 'react-dropzone';

/**
 * Internal dependencies
 */

import UploaderContent from './content';
import './style.scss';

function Uploader( props ) {
	const [ hover, setHover ] = useState( false );
	const [ file, setFile ] = useState( null );

	function onDrop( accepted ) {
		setHover( false );
		setFile( accepted[ 0 ] );
	}

	return (
		<Dropzone
			multiple={ false }
			onDrop={ onDrop }
			onDragLeave={ () => setHover( false ) }
			onDragEnter={ () => setHover( true ) }
		>
			{ ( upload ) => (
				<UploaderContent
					dropzone={ upload }
					hover={ hover }
					file={ file }
					clearFile={ () => setFile( null ) }
					{ ...props }
				/>
			) }
		</Dropzone>
	);
}

export default Uploader;
