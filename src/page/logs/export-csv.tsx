import apiFetch from '@wp-plugin-lib/api-fetch';
import { LOGS_TYPE_404, LOGS_TYPE_REDIRECT } from 'lib/log-constants';

interface ExportCSVProps {
	logType: string;
	title: string;
}

// Map log types to the page names that PHP expects
function getPageName( logType: string ): string {
	if ( logType === LOGS_TYPE_REDIRECT ) {
		return 'log';
	}
	if ( logType === LOGS_TYPE_404 ) {
		return '404s';
	}
	return logType;
}

const ExportCSV = ( { logType, title }: ExportCSVProps ) => {
	const pageName = getPageName( logType );

	return (
		<form method="post" action={ Redirectioni10n.pluginRoot + '&sub=' + pageName }>
			<input type="hidden" name="_wpnonce" value={ apiFetch.nonceMiddleware?.nonce } />
			<input type="hidden" name="export-csv" value="" />
			<input className="button" type="submit" name="" value={ title } />
		</form>
	);
};

export default ExportCSV;
