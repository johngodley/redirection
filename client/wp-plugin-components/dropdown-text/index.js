/**
 * External dependencies
 */

import React, { useEffect, useState, useRef } from 'react';
import { translate as __ } from 'wp-plugin-lib/locale';
import debounce from 'debounce-promise';

/**
 * Internal dependencies
 */
import LoadingDots from '../loading-dots';
import Popover, { getPopoverPosition } from '../popover';
import DropdownSuggestions from './suggestions';
import './style.scss';

const DEBOUNCE_DELAY = 250;

let debouncedDelay = null;

/**
 * Get suggestions from a data source
 *
 * @typedef FetchData
 * @property {string} value - Current autocomplete value
 * @returns {Promise<String>} A promise that returns a list of values
 */
/**
 * A text input with autocomplete dropdown
 *
 * @param {object} props - Component props
 * @param {string} props.placeholder - Text placeholder
 * @param {string} props.value - Current value
 * @param {FetchData} props.fetchData - Callback to fetch data
 * @param {} props.onChange - Callback to update value
 */
function DropdownText( props ) {
	const { placeholder, onChange, value, fetchData } = props;
	const [ makingRequest, setMakingRequest ] = useState( false );
	const [ options, setOptions ] = useState( [] );
	const inputRef = useRef( null );

	useEffect(() => {
		debouncedDelay = debounce( getData, DEBOUNCE_DELAY );
	}, []);

	function getData( currentValue ) {
		setMakingRequest( true );

		// Ignore errors
		fetchData( currentValue )
			.then( ( results ) => {
				setOptions( results );
				setMakingRequest( false );
			} )
			.catch( ( e ) => {
				console.error( 'Failed to get suggestions: ', e );
			} );
	}

	function changeValue( ev ) {
		debouncedDelay( ev.target.value );
		onChange( ev.target.value );
	}

	function onSelect( value ) {
		onChange( value );
		setOptions( [] );
	}

	return (
		<div className="wpl-dropdowntext">
			<input
				type="text"
				className="regular-text"
				name="text"
				value={ value }
				onChange={ changeValue }
				placeholder={ placeholder }
				ref={ inputRef }
			/>

			{ makingRequest && (
				<div className="wpl-dropdowntext__loading">
					<LoadingDots />
				</div>
			) }

			{ options.length > 0 && (
				<Popover
					align="left"
					onClose={ () => setOptions( [] ) }
					popoverPosition={ getPopoverPosition( inputRef.current ) }
					className="wpl-dropdowntext__suggestions"
				>
					<DropdownSuggestions
						options={ options }
						value={ value }
						onSelect={ onSelect }
						onClose={ () => setOptions( [] ) }
					/>
				</Popover>
			) }
		</div>
	);
}

export default DropdownText;
