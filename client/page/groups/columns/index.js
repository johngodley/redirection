/**
 * Internal dependencies
 */
import StatusColumn from './status';
import NameColumn from './name';
import ModuleColumn from './module';
import EditColumn from './edit';

export default function getColumns( row, rowParams, disabled ) {
	const { redirects } = row;
	const { rowMode, setRowMode } = rowParams;

	return [
		{
			name: 'status',
			content: <StatusColumn row={ row } />,
		},
		{
			name: 'name',
			content:
				rowMode === 'edit' ? (
					<EditColumn group={ row } onCancel={ () => setRowMode( false ) } />
				) : (
					<NameColumn row={ row } filters={ rowParams.table.filterBy } />
				),
		},
		{
			name: 'redirects',
			content: new Intl.NumberFormat( window.Redirectioni10n.locale ).format( redirects ),
		},
		{
			name: 'module',
			content: <ModuleColumn row={ row } />,
		},
	];
}
