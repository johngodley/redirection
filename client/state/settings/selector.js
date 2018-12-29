export const getOption = ( state, name ) => {
	if ( name === undefined ) {
		return state.settings.values;
	}

	return state.settings.values[ name ] ? state.settings.values[ name ] : null;
};
