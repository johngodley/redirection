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
import { setUngroupedFilter, setSelected, performTableAction } from 'state/error/action';
import { STATUS_IN_PROGRESS, STATUS_SAVING } from 'state/settings/type';
import { ACTION_NOTHING, ACTION_URL, MATCH_URL } from 'state/redirect/selector';

class LogRow404 extends React.Component {
	static propTypes = {
		item: PropTypes.object.isRequired,
		onCreate: PropTypes.func.isRequired,
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

	onIgnore = ev => {
		ev.preventDefault();
		this.props.onCreate( [ this.props.item.id ], { match_type: MATCH_URL, action_type: ACTION_NOTHING } );
	}

	onAdd = ev => {
		ev.preventDefault();
		this.props.onCreate( [ this.props.item.id ], { match_type: MATCH_URL, action_type: ACTION_URL } );
	}

	onShow = ev => {
		ev.preventDefault();

		const { filterBy } = this.props.filters;

		this.props.onShow( { ...filterBy, 'url-exact': this.props.item.id } );
	}

	render() {
		const { url, id, count } = this.props.item;
		const { selected, status } = this.props;
		const isLoading = status === STATUS_IN_PROGRESS;
		const isSaving = status === STATUS_SAVING;
		const hideRow = isLoading || isSaving;
		const menu = [
			<a href="#" onClick={ this.onDelete } key="0">{ __( 'Delete All' ) }</a>,
			<a href="#" onClick={ this.onAdd } key="1">{ __( 'Redirect All' ) }</a>,
			<a href="#" onClick={ this.onShow } key="2">{ __( 'Show All' ) }</a>,
			<a href="#" onClick={ this.onIgnore } key="3">{ __( 'Ignore URL' ) }</a>,
		];

		return (
			<tr className={ hideRow ? 'disabled' : '' }>
				<th scope="row" className="check-column">
					{ ! isSaving && <input type="checkbox" name="item[]" value={ id } disabled={ isLoading } checked={ selected } onChange={ this.onSelect } /> }
					{ isSaving && <Spinner size="small" /> }
				</th>
				<td className="column-url column-primary">
					<ExternalLink url={ url }>
						<Highlighter searchWords={ [ this.props.filters.url ] } textToHighlight={ url.substring( 0, 100 ) } />
					</ExternalLink>

					<RowActions disabled={ isSaving }>
						{ menu.reduce( ( prev, curr ) => [ prev, ' | ', curr ] ) }
					</RowActions>
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
		onShow: filters => {
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
)( LogRow404 );
