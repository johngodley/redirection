/**
 * External dependencies
 */

import React from 'react';
import * as parseUrl from 'url';
import { connect } from 'react-redux';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

import RowActions from 'component/table/row-action';
import { setFilter, setSelected, performTableAction } from 'state/log/action';
import Spinner from 'component/wordpress/spinner';
import { STATUS_IN_PROGRESS, STATUS_SAVING } from 'state/settings/type';

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

const LogRow = props => {
	const { created, ip, referrer, url, agent, sent_to, id } = props.item;
	const { selected, status } = props;
	const isLoading = status === STATUS_IN_PROGRESS;
	const isSaving = status === STATUS_SAVING;
	const hideRow = isLoading || isSaving;

	const handleShow = ev => {
		ev.preventDefault();
		props.onShowIP( ip );
	};
	const handleSelected = () => {
		props.onSetSelected( [ id ] );
	};
	const handleDelete = ev => {
		ev.preventDefault();
		props.onDelete( id );
	};

	return (
		<tr className={ hideRow ? 'disabled' : '' }>
			<th scope="row" className="check-column">
				{ ! isSaving && <input type="checkbox" name="item[]" value={ id } disabled={ isLoading } checked={ selected } onClick={ handleSelected } /> }
				{ isSaving && <Spinner size="small" /> }
			</th>
			<td>
				{ created }
				<RowActions disabled={ isSaving }>
					<a href="#" onClick={ handleDelete }>{ __( 'Delete' ) }</a>
				</RowActions>
			</td>
			<td>
				<a href={ url } rel="noreferrer noopener" target="_blank">{ url.substring( 0, 100 ) }</a>
				<RowActions>
					{ [ sent_to.substring( 0, 100 ) ] }
				</RowActions>
			</td>
			<td>
				<Referrer url={ referrer } />
				<RowActions>
					{ [ agent ] }
				</RowActions>
			</td>
			<td>
				<a href={ 'http://urbangiraffe.com/map/?ip=' + ip } rel="noreferrer noopener" target="_blank">{ ip }</a>
				<RowActions>
					<a href="#" onClick={ handleShow }>{ __( 'Show only this IP' ) }</a>
				</RowActions>
			</td>
		</tr>
	);
};

LogRow.propTypes = {
	item: PropTypes.object.isRequired,
	selected: PropTypes.bool.isRequired,
	status: PropTypes.string.isRequired,
};

function mapDispatchToProps( dispatch ) {
	return {
		onShowIP: ip => {
			dispatch( setFilter( 'ip', ip ) );
		},
		onSetSelected: items => {
			dispatch( setSelected( items ) );
		},
		onDelete: item => {
			dispatch( performTableAction( 'delete', item ) );
		},
	};
}

export default connect(
	null,
	mapDispatchToProps
)( LogRow );
