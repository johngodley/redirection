import Options from 'page/options';
import Support from 'page/support';
import Site from 'page/site';
import Logs from 'page/logs';
import Logs404 from 'page/logs404';
import ImportPage from 'page/import';
import ExportPage from 'page/export';
import Groups from 'page/groups';
import Redirects from 'page/redirects';

interface PageContentProps {
	page: string;
}

function PageContent( { page }: PageContentProps ) {
	switch ( page ) {
		case 'support':
			return <Support />;

		case '404s':
			return <Logs404 />;

		case 'log':
			return <Logs />;

		case 'import':
			return <ImportPage />;

		case 'export':
			return <ExportPage />;

		case 'groups':
			return <Groups />;

		case 'options':
			return <Options />;

		case 'site':
			return <Site />;
	}

	return <Redirects />;
}

export default PageContent;
