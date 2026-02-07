import { Placeholder } from '@wp-plugin-components';
import { useSettings, usePluginDelete } from 'lib/api/hooks';
import { useSettingsStore } from 'stores';
import OptionsForm from './options-form';
import DeletePlugin from './delete-plugin';

function Options() {
	// Direct property access instead of destructuring
	const loadStatus = useSettingsStore( ( state ) => state.loadStatus );
	const values = useSettingsStore( ( state ) => state.values );
	useSettings(); // Fetch settings on mount
	const deletePlugin = usePluginDelete();

	const handleDeletePlugin = () => {
		deletePlugin.mutate();
	};

	// canDelete is a permission that comes from the server
	// For now, we'll assume it's always available if the settings are loaded
	// TODO: Check if canDelete should come from a separate plugin info endpoint
	const canDelete = loadStatus === 'success';

	if ( loadStatus === 'loading' || ! values ) {
		return <Placeholder />;
	}

	return (
		<div>
			{ loadStatus === 'success' && <OptionsForm /> }

			<hr />
			{ canDelete && <DeletePlugin onDelete={ handleDeletePlugin } /> }
		</div>
	);
}

export default Options;
