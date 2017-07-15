export const nestedGroups = groups => {
	const nested = {};

	for ( let x = 0; x < groups.length; x++ ) {
		const group = groups[ x ];

		if ( ! nested[ group.moduleName ] ) {
			nested[ group.moduleName ] = [];
		}

		nested[ group.moduleName ].push( { value: group.id, text: group.name } );
	}

	return Object.keys( nested ).map( moduleName => ( { text: moduleName, value: nested[ moduleName ] } ) );
};
