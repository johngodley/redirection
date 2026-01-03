import apiFetch from '@wp-plugin-lib/api-fetch';

interface ExportCSVProps {
	logType: string;
	title: string;
}

const ExportCSV = ( { logType, title }: ExportCSVProps ) => {
	return (
		<form method="post" action={ Redirectioni10n.pluginRoot + '&sub=' + logType }>
			<input type="hidden" name="_wpnonce" value={ apiFetch.nonceMiddleware.nonce } />
			<input type="hidden" name="export-csv" value="" />
			<input className="button" type="submit" name="" value={ title } />
		</form>
	);
};

export default ExportCSV;
