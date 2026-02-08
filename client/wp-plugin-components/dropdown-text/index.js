/**
 * External dependencies
 */

import React, { useState, useEffect, useRef } from 'react';
import { __ } from '@wordpress/i18n';
import classnames from 'classnames';
import { useDebouncedCallback } from 'use-debounce';

/**
 * Internal dependencies
 */
import LoadingDots from '../loading-dots';
import Popover, { getPopoverPosition } from '../popover';
import DropdownSuggestions from './suggestions';
import Badge from '../badge';
import './style.scss';

const DEBOUNCE_DELAY = 450;

/**
 * @typedef SelectOption
 * @type {object}
 * @property {string} label - a descriptive label.
 * @property {string} value - value for the option
 */

/**
 * Callback to determine if a remote request can be made
 *
 * @callback CanMakeRequest
 * @param {string} value - Current autocomplete value
 * @returns {boolean}
 */

/**
 * Callback when values have changed
 *
 * @callback OnChange
 * @param {string|string[]} value - Current value or values
 * @param {string|string[]} label - Current label, if any
 */

/**
 * Callback when input is blurred
 *
 * @callback OnBlur
 * @param {string} value - Current value
 * @returns {string|number}
 */

/**
 * Get suggestions from a data source
 *
 * @callback FetchData
 * @param {string} value - Current autocomplete value
 * @returns {Promise<String>} A promise that returns a list of values
 */

/**
 * A text input with autocomplete dropdown
 *
 * @param {object} props - Component props
 * @param {FetchData} [props.fetchData] - Callback to fetch data
 * @param {CanMakeRequest} [props.canMakeRequest] - Callback to determine if the value can make a request
 * @param {OnChange} props.onChange - Callback to update value
 * @param {string} [props.placeholder] - Text placeholder
 * @param {string|string[]} props.value - Current value(s)
 * @param {string} [props.name] - Input name
 * @param {boolean} [props.disabled] - Disable the component
 * @param {boolean} [props.loadOnFocus] - Load the remote data on focus
 * @param {number} [props.maxLength=0] - Maximum length of text
 * @param {number} [props.maxChoices=0] - Show choices, or 0 to show no choices
 * @param {boolean} [props.onlyChoices=false] - Only allow choices from the suggestions
 * @param {OnBlur} [props.onBlur] - On blur callback
 * @param {string} [props.className] - Extra classname
 */
function DropdownText( props ) {
	const {
		placeholder = '',
		onChange,
		value,
		fetchData,
		name = 'text',
		disabled = false,
		className,
		maxChoices = -1,
		maxLength = 0,
		canMakeRequest = ( value ) => value.length > 0,
		onBlur,
		getLabel,
		setLabel,
		loadOnFocus = false,
		onlyChoices = false,
	} = props;
	const [ makingRequest, setMakingRequest ] = useState( false );
	const [ options, setOptions ] = useState( [] );
	const [ input, setInput ] = useState( Array.isArray( value ) ? '' : value );
	const inputRef = useRef( null );
	const hideInput = maxChoices > 0 && Array.isArray( value ) && value.length >= maxChoices;
	const classes = {
		'wpl-dropdowntext__suggestion__hide': hideInput,
		'wpl-dropdowntext__suggestion': maxChoices > 1,
	};
	const debounced = useDebouncedCallback( getData, DEBOUNCE_DELAY );

	useEffect(() => {
		if ( value !== input ) {
			setInput( Array.isArray( value ) ? '' : value );
		}
	}, [ value ]);

	function getData( currentValue ) {
		if ( ! fetchData ) {
			return;
		}

		setMakingRequest( true );

		// Ignore errors
		fetchData( currentValue )
			.then( ( results ) => {
				if ( document.activeElement === inputRef.current ) {
					setOptions( results );
				}

				setMakingRequest( false );
			} )
			.catch( ( e ) => {
				console.error( 'Failed to get suggestions: ', e );
				setOptions( [] );
				setMakingRequest( false );
			} );
	}

	function changeValue( ev ) {
		setInput( ev.target.value );
		if ( maxChoices < 1 ) {
			onChange( ev.target.value );
		}

		if ( fetchData && debounced ) {
			if ( canMakeRequest( ev.target.value.trim() ) ) {
				debounced( ev.target.value );
			} else {
				setOptions( [] );
			}
		}
	}

	function preload() {
		if ( loadOnFocus && value.length === 0 ) {
			getData( '' );
		}
	}

	function blur( ev ) {
		if ( ev.relatedTarget && ev.relatedTarget.closest( '.wpl-dropdowntext__suggestions' ) ) {
			return;
		}

		const blurValue = onBlur ? onBlur( input ) : input;
		if ( options.length === 0 ) {
			if ( onlyChoices ) {
				// Reset input if it doesn't match a chosen value
				setInput( '' );
			} else if ( maxChoices > 0 && fetchData ) {
				// Choices + custom value
				onSelect( { value: blurValue, label: blurValue } );
			} else {
				// Save the input
				onChange( blurValue );
			}
		} else if ( blurValue !== input ) {
			setInput( blurValue );
		}

		setMakingRequest( false );
	}

	function onKeyDown( ev ) {
		if ( ev.code === 'Enter' ) {
			setMakingRequest( false );
			setOptions( [] );
		}
	}

	function getLabelsForValues( values ) {
		if ( getLabel ) {
			return valueToArray( values ).map( ( valueId ) => getLabel( valueId, values ) );
		}

		return undefined;
	}

	/**
	 * Select an item from the popover choices
	 * @param {SelectOption} selectedItem
	 */
	function onSelect( selectedItem ) {
		if ( maxChoices > 0 ) {
			if ( ! valueToArray( value ).find( ( item ) => item === `${ selectedItem.value }` ) ) {
				// Ensure we don't exceed the number of choices
				const newValues = [ `${ selectedItem.value }` ]
					.concat( valueToArray( value ).filter( ( item ) => item !== `${ selectedItem.value }` ) )
					.slice( 0, maxChoices );

				// Save the values
				onChange(
					maxChoices === 1 && ! onlyChoices ? newValues[ 0 ] : newValues,
					[ selectedItem.title ].concat( getLabelsForValues( newValues ).slice( 1 ) )
				);

				// Ensure we remember the text label for this choice and show that instead of the value
				setLabel( selectedItem.value, selectedItem.title );
			}

			setInput( '' );
		} else {
			// No choices, just set the input box
			setInput( selectedItem.value );
			onChange( selectedItem.value, getLabelsForValues( value ) );
		}

		// Clear choices
		setOptions( [] );
	}

	/**
	 * @param {string} removeValue
	 */
	function clearSuggestion( removeValue ) {
		const newValues = valueToArray( value ).filter( ( item ) => item !== removeValue );

		// Remove the value from the chosen values and the current values
		setLabel( removeValue, null );

		if ( Array.isArray( value ) ) {
			onChange( maxChoices === 1 ? newValues[ 0 ] : newValues, getLabelsForValues( newValues ) );
		} else {
			onChange( '' );
		}

		// Focus back into the field
		inputRef.current.focus();
	}

	function valueToArray( val ) {
		if ( Array.isArray( val ) ) {
			return val;
		}

		if ( val ) {
			return [ val ];
		}

		return [];
	}

	return (
		<div className={ classnames( 'wpl-dropdowntext', className, classes ) }>
			{ maxChoices > 0 &&
				valueToArray( value ).map( ( valueId ) => (
					<Badge
						key={ valueId }
						title={ valueId }
						onCancel={ () => clearSuggestion( valueId ) }
						disabled={ disabled }
					>
						{ getLabel ? getLabel( valueId, value ) : valueId }
					</Badge>
				) ) }

			<input
				type="text"
				className={ classnames( 'regular-text', {
					'wpl-dropdowntext__max': maxChoices >= 0 && valueToArray( value ).length >= maxChoices,
				} ) }
				name={ name }
				value={ input }
				disabled={ disabled }
				onChange={ changeValue }
				placeholder={ placeholder }
				ref={ inputRef }
				onFocus={ preload }
				onBlur={ blur }
				onKeyDown={ onKeyDown }
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
					focusLock={ false }
				>
					<DropdownSuggestions
						options={ options }
						value={ input }
						onSelect={ onSelect }
						onClose={ () => setOptions( [] ) }
					/>
				</Popover>
			) }
		</div>
	);
}

export default DropdownText;
