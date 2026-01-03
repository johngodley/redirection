import { sprintf, __ } from '@wordpress/i18n';
import Badge from '@wp-plugin-components/badge';
import { getModuleName } from 'lib/modules';
import { useTableStore } from 'stores';

interface ModuleColumnProps {
	row: { module_id: number };
}

function ModuleColumn( { row }: ModuleColumnProps ) {
	const { module_id } = row;
	const name = getModuleName( module_id );
	const { setGroupsTable } = useTableStore();

	return (
		<>
			<Badge
				onClick={ () => setGroupsTable( { filterBy: { module: String( module_id ) }, page: 0 } ) }
				title={ sprintf(
					// translators: %(type)s is the module type name
					__( 'Filter on: %(type)s', 'redirection' ),
					{ type: name }
				) }
			>
				{ name }
			</Badge>
		</>
	);
}

export default ModuleColumn;
