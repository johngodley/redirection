/**
 * External dependencies
 */

import React from 'react';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

import DropdownButton from 'component/dropdown-button';
import { STATUS_IN_PROGRESS } from 'state/settings/type';
import './style.scss';

class SearchBox extends React.Component {
	static propTypes = {
		table: PropTypes.object.isRequired,
		status: PropTypes.string.isRequired,
		onSearch: PropTypes.func.isRequired,
		searchType: PropTypes.array,
	};

	constructor( props ) {
		super( props );

		const found = props.searchTypes.find( item => props.selected[ item.name ] );

		this.state = {
			search: this.getInitialValue( props.searchTypes, props.selected ),
			selected: found ? found.name : props.searchTypes[ 0 ].name,
		};
	}

	getInitialValue( types, initial ) {
		if ( types ) {
			const found = types.find( item => initial[ item.name ] );

			if ( found ) {
				return initial[ found.name ];
			}

			return '';
		}

		return initial || '';
	}

	onSearch = ( ev ) => {
		this.setState( { search: ev.target.value } );
	}

	onSubmit = ( ev ) => {
		ev && ev.preventDefault();
		this.props.onSearch( this.state.search, this.state.selected );
	}

	onChange = selected => {
		this.setState( { selected } );

		// Trigger a search when you change the type and have a valie
		if ( this.state.search.length > 0 ) {
			this.props.onSearch( this.state.search, selected );
		}
	}

	render() {
		const { status, searchTypes, name = '' } = this.props;
		const disabled = status === STATUS_IN_PROGRESS || ( this.state.search === '' && this.props.table.filter === '' );

		return (
			<form onSubmit={ this.onSubmit } className="redirect-searchbox">
				<input type="search" name="s" value={ this.state.search } onChange={ this.onSearch } />

				{ searchTypes && (
					<DropdownButton
						options={ searchTypes }
						isEnabled={ ! disabled }
						selected={ this.state.selected }
						onChange={ this.onChange }
						onSelect={ this.onSubmit }
					/>
				) }
				{ ! searchTypes && <input type="submit" className="button" value={ name } disabled={ disabled } /> }
			</form>
		);
	}
}

export default SearchBox;
