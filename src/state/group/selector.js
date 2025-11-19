export const nestedGroups = groups => {
	const nested = {};

	for ( let x = 0; x < groups.length; x++ ) {
		const group = groups[ x ];

		if ( ! nested[ group.moduleName ] ) {
			nested[ group.moduleName ] = [];
		}

		nested[ group.moduleName ].push( { value: group.id, label: group.name } );
	}

	return Object.keys( nested ).map( moduleName => ( { label: moduleName, value: nested[ moduleName ] } ) );
};

export const getGroup = ( groups, groupId ) => groups.find( item => item.id === groupId );
