import { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { Select } from '@wp-plugin-components';
import { useFixStatus } from 'lib/api/hooks/use-settings';

interface VersionOption {
	value: string;
	label: string;
}

interface IpHeader {
	[ key: string ]: string;
}

interface Database {
	current: string;
}

interface DebugInfo {
	ip_header: IpHeader;
	database: Database;
}

interface DebugProps {
	debug: DebugInfo;
}

const getVersions = (): VersionOption[] => [
	{
		value: '2.3.1',
		label: '2.3.1',
	},
	{
		value: '2.3.2',
		label: '2.3.2',
	},
	{
		value: '2.4',
		label: '2.4',
	},
	{
		value: '4.0',
		label: '4.0',
	},
	{
		value: '4.1',
		label: '4.1',
	},
	{
		value: '4.2',
		label: '4.2',
	},
];

function Debug( props: DebugProps ) {
	const { debug } = props;
	const [ version, setVersion ] = useState( debug.database.current );
	const { mutate: fixStatus } = useFixStatus( {
		onSuccess: () => {
			// Reload the page to show the upgrade progress
			window.location.reload();
		},
	} );

	function onChange( ev: React.ChangeEvent< HTMLSelectElement > ) {
		setVersion( ev.target.value );
	}

	function handleSave( ev: React.MouseEvent ) {
		ev.preventDefault();
		fixStatus( { reason: 'database', current: version } );
	}

	const { ip_header, database } = debug;

	return (
		<table className="plugin-status">
			<tbody>
				<tr>
					<th>{ __( 'Database version', 'redirection' ) }</th>
					<td>
						<Select
							items={ getVersions() }
							value={ version }
							name="database_version"
							onChange={ onChange }
						/>{ ' ' }
						&nbsp;
						{ version !== database.current && (
							<>
								<strong>{ __( 'Do not change unless advised to do so!', 'redirection' ) }</strong>{ ' ' }
								&nbsp;
								<button className="button-secondary button" onClick={ handleSave }>
									{ __( 'Save', 'redirection' ) }
								</button>
							</>
						) }
					</td>
				</tr>
				<tr>
					<th>{ __( 'IP Headers', 'redirection' ) }</th>
					<td>
						{ Object.keys( ip_header )
							.filter( ( key ) => ip_header[ key ] )
							.map( ( key, pos ) => (
								<code key={ pos }>
									{ key } = { ip_header[ key ] ? ip_header[ key ] : '-' }&nbsp;
								</code>
							) ) }
					</td>
				</tr>
			</tbody>
		</table>
	);
}

export default Debug;
