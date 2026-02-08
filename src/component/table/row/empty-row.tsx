import { __ } from '@wordpress/i18n';

interface Header {
	name: string;
	title: string;
}

interface EmptyRowProps {
	headers: Header[];
}

const EmptyRow = ( props: EmptyRowProps ) => {
	const { headers } = props;

	return (
		<tr>
			<td colSpan={ headers.length + 1 }>{ __( 'Nothing to display.', 'redirection' ) }</td>
		</tr>
	);
};

export default EmptyRow;
