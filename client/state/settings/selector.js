export const getOption = ( { settings }, name ) => {
	if ( name === undefined ) {
		return settings.values;
	}

	return settings.values[ name ] !== undefined ? settings.values[ name ] : null;
};

export const getFlags = state => ( {
	flag_regex: getOption( state, 'flag_regex' ),
	flag_case: getOption( state, 'flag_case' ),
	flag_trailing: getOption( state, 'flag_trailing' ),
	flag_query: getOption( state, 'flag_query' ),
} );
