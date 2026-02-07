import { __ } from '@wordpress/i18n';

interface Header {
	name: string;
	title: string;
}

interface FailedRowProps {
	headers: Header[];
}

const FailedRow = ( props: FailedRowProps ) => {
	const { headers } = props;

	return (
		<tr>
			<td colSpan={ headers.length + 1 }>
				<p>{ __( 'Sorry, something went wrong loading the data - please try again', 'redirection' ) }</p>
			</td>
		</tr>
	);
};

export default FailedRow;
