/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';
import { translate as __ } from 'lib/locale';

import { STATUS_IN_PROGRESS } from 'state/settings/type';
import { setSearch } from 'state/log/action';

class SearchBox extends React.Component {
	constructor( props ) {
		super( props );

		this.state = { search: props.filter };
		this.handleChange = this.onChange.bind( this );
		this.handleSubmit = this.onSubmit.bind( this );
	}

	componentWillReceiveProps( nextProps ) {
		if ( nextProps.filterBy !== this.props.filterBy ) {
			this.setState( { search: nextProps.filter } );
		}
	}

	onChange( ev ) {
		this.setState( { search: ev.target.value } );
	}

	onSubmit( ev ) {
		ev.preventDefault();
		this.props.onSearch( this.state.search );
	}

	render() {
		const disabled = status === STATUS_IN_PROGRESS || ( this.state.search === '' && this.props.filter === '' );
		const name = this.props.filterBy === 'ip' ? __( 'Search by IP' ) : __( 'Search' );

		return (
			<form onSubmit={ this.handleSubmit }>
				<p className="search-box">
					<input type="search" name="s" value={ this.state.search } onChange={ this.handleChange } />
					<input type="submit" className="button" value={ name } disabled={ disabled } />
				</p>
			</form>
		);
	}
}

function mapStateToProps( state ) {
	const { filter, filterBy, status } = state.log;

	return {
		filter,
		filterBy,
		status,
	};
}

function mapDispatchToProps( dispatch ) {
	return {
		onSearch: search => {
			dispatch( setSearch( search ) );
		}
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps,
)( SearchBox );
