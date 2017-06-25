/* global jQuery */
/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';
import * as parseUrl from 'url';
import { translate as __ } from 'lib/locale';

/**
 * Internal dependencies
 */
import { setFilter } from 'state/log/action';

const RowActions = props => {
	return (
		<div className="row-actions">
			{ props.children }
		</div>
	);
};

const Referrer = props => {
	const { url } = props;

	if ( url ) {
		const domain = parseUrl.parse( url ).hostname;

		return (
			<a href={ url } rel="noreferrer noopener" target="_blank">{ domain }</a>
		);
	}

	return null;
};

const LogRow404 = props => {
	const { created, ip, referrer, url, agent, id } = props.item;
	const { selected, isLoading } = props;
	const handleSelected = () => {
		props.onSetSelected( [ id ] );
	};
	const handleShow = ev => {
		ev.preventDefault();
		props.onShowIP( ip );
	};
	const handleAdd = ev => {
		ev.preventDefault();
		props.onAdd( props.item );
	};

	return (
		<tr className={ isLoading ? 'item-loading' : '' }>
			<th scope="row" className="check-column">
				<input type="checkbox" name="item[]" value={ id } disabled={ isLoading } checked={ selected } onClick={ handleSelected } />
			</th>
			<td>
				{ created }
				<RowActions>
					<a href="#" onClick={ handleAdd }>{ __( 'Add Redirect' ) }</a>
				</RowActions>
			</td>
			<td>
				<a href={ url } rel="noreferrer noopener" target="_blank">{ url.substring( 0, 100 ) }</a>
			</td>
			<td>
				<Referrer url={ referrer } />
				<RowActions>
					{ agent }
				</RowActions>
			</td>
			<td>
				<a href={ 'http://urbangiraffe.com/map/?ip=' + ip } rel="noreferrer noopener" target="_blank">
					{ ip }
				</a>
				<RowActions>
					<a href="#" onClick={ handleShow }>{ __( 'Show only this IP' ) }</a>
				</RowActions>
			</td>
		</tr>
	);
};

function mapDispatchToProps( dispatch ) {
	return {
		onAdd: item => {
			// To be replaced with React eventually
			jQuery( '#add' ).show();
			jQuery( '#old' ).val( item.url );
			jQuery( 'html, body' ).scrollTop( jQuery( '#add' ).offset().top );
		},
		onShowIP: ip => {
			dispatch( setFilter( 'ip', ip ) );
		}
	};
}

export default connect(
	null,
	mapDispatchToProps
)( LogRow404 );
