/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import ReactSelect from 'react-select';
import {
	getSourceFlags,
	FLAG_CASE,
	FLAG_REGEX,
	FLAG_TRAILING,
} from './constants';
import TableRow from './table-row';

const FLAG_DEFAULT = '#ffb900';
const FLAG_DEFAULT_HOVER = '#C48E00';

function getFlagValue( { flag_regex, flag_trailing, flag_case } ) {
	const flags = getSourceFlags();

	return [
		flag_regex ? flags[ FLAG_REGEX ] : false,
		flag_case ? flags[ FLAG_CASE ] : false,
		flag_trailing ? flags[ FLAG_TRAILING ] : false,
	].filter( item => item );
}

function isDifferentFlag( flag, value, existing ) {
	const { flag_case, flag_trailing } = existing;

	if ( flag === 'flag_case' && value !== flag_case ) {
		return true;
	}

	if ( flag === 'flag_trailing' && value !== flag_trailing ) {
		return true;
	}

	return flag === 'flag_regex';
}

const RedirectSourceUrl = ( { url, flags, defaultFlags, onFlagChange, onChange, autoFocus = false } ) => {
	const flagOptions = getSourceFlags();

	if ( Array.isArray( url ) ) {
		return (
			<TableRow title={ __( 'Source URL' ) } className="top">
				<textarea value={ url.join( '\n' ) } readOnly></textarea>
			</TableRow>
		);
	}

	const getFlagStyle = ( provided, state ) => {
		if ( isDifferentFlag( state.data.value, state.hasValue, defaultFlags ) ) {
			return { ... provided, backgroundColor: FLAG_DEFAULT };
		}

		return provided;
	};

	const getIndicatorStyle = ( provided, state ) => {
		return { ... provided, height: '28px' };
	};

	const getPlaceholderStyle = ( provided, state ) => {
		return { ... provided, top: '40%' };
	};

	const getRemoveFlag = ( provided, state ) => {
		if ( isDifferentFlag( state.data.value, state.hasValue, defaultFlags ) ) {
			return { ... provided, ':hover': { backgroundColor: FLAG_DEFAULT_HOVER } };
		}

		return provided;
	};

	return (
		<TableRow title={ __( 'Source URL' ) }>
			<input
				type="text"
				name="url"
				value={ url }
				onChange={ onChange }
				autoFocus={ autoFocus }
				placeholder={ __( 'The relative URL you want to redirect from' ) }
			/>

			<ReactSelect
				options={ flagOptions }
				placeholder={ __( 'URL options / Regex' ) }
				isMulti
				onChange={ onFlagChange }
				isSearchable={ false }
				className="redirection-edit_flags"
				classNamePrefix="redirection-edit_flags"
				defaultValue={ getFlagValue( flags ) }
				noOptionsMessage={ () => __( 'No more options' ) }
				value={ getFlagValue( flags ) }
				styles={ { multiValue: getFlagStyle, multiValueRemove: getRemoveFlag, indicatorsContainer: getIndicatorStyle, placeholder: getPlaceholderStyle } }
			/>
		</TableRow>
	);
};

RedirectSourceUrl.propTypes = {
	url: PropTypes.string.isRequired,
	flags: PropTypes.object.isRequired,
	onFlagChange: PropTypes.func.isRequired,
	onChange: PropTypes.func.isRequired,
	autoFocus: PropTypes.bool,
	defaultFlags: PropTypes.object.isRequired,
};

export default RedirectSourceUrl;
