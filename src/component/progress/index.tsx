import { __ } from '@wordpress/i18n';
import { Spinner } from '@wp-plugin-components';
import { useMessageStore } from 'stores';
import './style.scss';

function getMessage( inProgress: number ): string {
	if ( inProgress > 1 ) {
		return __( 'Saving…', 'redirection' ) + ' (' + inProgress + ')';
	}

	return __( 'Saving…', 'redirection' );
}

export default function Progress() {
	const inProgress = useMessageStore( ( state ) => state.inProgress );

	if ( inProgress === 0 ) {
		return null;
	}

	return (
		<div className="notice notice-progress redirection-notice">
			<Spinner />
			<p>{ getMessage( inProgress ) }</p>
		</div>
	);
}
