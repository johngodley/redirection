export const getModuleName = ( store, moduleId ) => {
	const result = store.rows.find( item => item.module_id === moduleId );

	return result ? result.displayName : '';
};
