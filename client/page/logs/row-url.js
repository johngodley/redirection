/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';
import { translate as __, numberFormat } from 'lib/locale';
import PropTypes from 'prop-types';
import Highlighter from 'react-highlight-words';

/**
 * Internal dependencies
 */

import RowActions from 'component/table/row-action';
import Spinner from 'component/spinner';
import ExternalLink from 'component/external-link';
import { setUngroupedFilter, setSelected, performTableAction } from 'state/log/action';
import { STATUS_IN_PROGRESS, STATUS_SAVING } from 'state/settings/type';
import { has_capability, CAP_LOG_DELETE, CAP_LOG_MANAGE } from 'lib/capabilities';

class LogRowUrl extends React.Component {
	static propTypes = {
		item: PropTypes.object.isRequired,
		selected: PropTypes.oneOfType( [
			PropTypes.bool,
			PropTypes.array,
		] ).isRequired,
	};

	onSelect = () => {
		this.props.onSetSelected( [ this.props.item.id ] );
	}

	onDelete = ev => {
		ev.preventDefault();
		this.props.onDelete( this.props.item.id );
	}

	onShow = ev => {
		ev.preventDefault();
		this.props.setFilter( { 'url-exact': this.props.item.id } );
	}

	render() {
		const { url, id, count } = this.props.item;
		const { selected, status } = this.props;
		const isLoading = status === STATUS_IN_PROGRESS;
		const isSaving = status === STATUS_SAVING;
		const hideRow = isLoading || isSaving;
		const menu = [];

		if ( has_capability( CAP_LOG_DELETE ) ) {
			menu.push( <a href="#" onClick={ this.onDelete } key="0">{ __( 'Delete All' ) }</a> );
		}

		if ( has_capability( CAP_LOG_MANAGE ) ) {
			menu.push( <a href="#" onClick={ this.onShow } key="2">{ __( 'Show All' ) }</a> );
		}

		return (
			<tr className={ hideRow ? 'disabled' : '' }>
				<th scope="row" className="check-column">
					{ ! isSaving && <input type="checkbox" name="item[]" value={ id } disabled={ isLoading } checked={ selected } onChange={ this.onSelect } /> }
					{ isSaving && <Spinner size="small" /> }
				</th>
				<td className="column-url column-primary">
					<ExternalLink url={ url }>
						<Highlighter searchWords={ [ this.props.filters.url ] } textToHighlight={ url.substring( 0, 100 ) } autoEscape />
					</ExternalLink>

					{ menu.length > 0 && <RowActions disabled={ isSaving }>
						{ menu.reduce( ( prev, curr ) => [ prev, ' | ', curr ] ) }
					</RowActions> }
				</td>
				<td className="column-total">
					{ numberFormat( count ) }
				</td>
			</tr>
		);
	}
}

function mapDispatchToProps( dispatch ) {
	return {
		onSetSelected: items => {
			dispatch( setSelected( items ) );
		},
		onDelete: item => {
			dispatch( performTableAction( 'delete', item ) );
		},
		setFilter: filters => {
			dispatch( setUngroupedFilter( filters ) );
		},
	};
}

function mapStateToProps( state ) {
	const { status: infoStatus } = state.info;

	return {
		infoStatus,
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( LogRowUrl );
