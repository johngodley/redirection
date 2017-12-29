/**
 * External dependencies
 */

import React from 'react';
import { translate as __, numberFormat } from 'lib/locale';
import classnames from 'classnames';
import PropTypes from 'prop-types';

const Nav = props => {
	const { title, button, className, enabled, onClick } = props;

	if ( enabled ) {
		return (
			<a className={ className } href="#" onClick={ onClick }>
				<span className="screen-reader-text">{ title }</span>
				<span aria-hidden="true">{ button }</span>
			</a>
		);
	}

	return (
		<span className="tablenav-pages-navspan" aria-hidden="true">{ button }</span>
	);
};

class PaginationLinks extends React.Component {
	constructor( props ) {
		super( props );

		this.onChange = this.handleChange.bind( this );
		this.onSetPage = this.handleSetPage.bind( this );
		this.setClickers( props );
		this.state = { currentPage: props.page };
	}

	componentWillUpdate( nextProps ) {
		this.setClickers( nextProps );

		if ( nextProps.page !== this.props.page ) {
			this.setState( { currentPage: nextProps.page } );
		}
	}

	setClickers( props ) {
		this.onFirst = this.handleClick.bind( this, 0 );
		this.onLast = this.handleClick.bind( this, this.getTotalPages( props ) - 1 );
		this.onNext = this.handleClick.bind( this, props.page + 1 );
		this.onPrev = this.handleClick.bind( this, props.page - 1 );
	}

	handleClick( page, ev ) {
		ev.preventDefault();
		this.setState( { currentPage: page } );
		this.props.onChangePage( page );
	}

	handleChange( ev ) {
		const value = parseInt( ev.target.value, 10 );

		if ( value !== this.state.currentPage ) {
			this.setState( { currentPage: value - 1 } );
		}
	}

	handleSetPage() {
		this.props.onChangePage( this.state.currentPage );
	}

	getTotalPages( props ) {
		const { total, per_page } = props;

		return Math.ceil( total / per_page );
	}

	render() {
		const { page } = this.props;
		const max = this.getTotalPages( this.props );

		return (
			<span className="pagination-links">
				<Nav title={ __( 'First page' ) } button="«" className="first-page" enabled={ page > 0 } onClick={ this.onFirst } />&nbsp;
				<Nav title={ __( 'Prev page' ) } button="‹" className="prev-page" enabled={ page > 0 } onClick={ this.onPrev } />

				<span className="paging-input">
					<label htmlFor="current-page-selector" className="screen-reader-text">{ __( 'Current Page' ) }</label>&nbsp;
					<input className="current-page" type="number" min="1" max={ max } name="paged" value={ this.state.currentPage + 1 } size="2" aria-describedby="table-paging" onBlur={ this.onSetPage } onChange={ this.onChange } />

					<span className="tablenav-paging-text">
						{ __( 'of %(page)s', {
							components: {
								total: <span className="total-pages" />,
							},
							args: {
								page: numberFormat( max ),
							},
						} ) }
					</span>
				</span>
				&nbsp;
				<Nav title={ __( 'Next page' ) } button="›" className="next-page" enabled={ page < max - 1 } onClick={ this.onNext } />&nbsp;
				<Nav title={ __( 'Last page' ) } button="»" className="last-page" enabled={ page < max - 1 } onClick={ this.onLast } />
			</span>
		);
	}
}

class NavigationPages extends React.Component {
	render() {
		const { total, per_page, page, onChangePage, inProgress } = this.props;
		const onePage = total <= per_page;
		const classes = classnames( {
			'tablenav-pages': true,
			'one-page': onePage,
		} );

		return (
			<div className={ classes }>
				<span className="displaying-num">{ __( '%s item', '%s items', { count: total, args: numberFormat( total ) } ) }</span>

				{ ! onePage && <PaginationLinks onChangePage={ onChangePage } total={ total } per_page={ per_page } page={ page } inProgress={ inProgress } /> }
			</div>
		);
	}
}

NavigationPages.propTypes = {
	total: PropTypes.number.isRequired,
	per_page: PropTypes.number.isRequired,
	page: PropTypes.number.isRequired,
	onChangePage: PropTypes.func.isRequired,
	inProgress: PropTypes.bool.isRequired,
};

export default NavigationPages;
