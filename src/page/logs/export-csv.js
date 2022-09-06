import { apiFetch } from '@wp-plugin-lib';

const ExportCSV = ( { logType, title } ) => {
	return (
		<form method="post" action={ Redirectioni10n.pluginRoot + '&sub=' + logType }>
			<input type="hidden" name="_wpnonce" value={ apiFetch.nonceMiddleware.nonce } />
			<input type="hidden" name="export-csv" value="" />
			<input className="button" type="submit" name="" value={ title } />
		</form>
	);
};

export default ExportCSV;
