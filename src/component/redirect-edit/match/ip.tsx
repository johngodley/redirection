import { __ } from '@wordpress/i18n';
import TableRow from '../table-row';

interface MatchIpProps {
	data: {
		ip?: string[];
	};
	onChange: ( ev: React.ChangeEvent< HTMLTextAreaElement > | { target: { name: string; value: string[] } } ) => void;
}

const MatchIp = ( { data, onChange }: MatchIpProps ) => {
	const { ip = [] } = data;
	const changer = ( ev: React.ChangeEvent< HTMLTextAreaElement > ) => {
		onChange( { target: { name: ev.target.name, value: ev.target.value.split( '\n' ) } } );
	};

	return (
		<TableRow title={ __( 'IP', 'redirection' ) } className="redirect-edit__match">
			<textarea
				value={ ip.join( '\n' ) }
				name="ip"
				placeholder={ __( 'Enter IP addresses (one per line)', 'redirection' ) }
				onChange={ changer }
			/>
		</TableRow>
	);
};

export default MatchIp;
