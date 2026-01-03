import StatusColumn from './status';
import SourceColumn from './source';
import getCode from './code';
import GroupColumn from './group';
import EditRedirect from 'component/redirect-edit';
import { Modal } from '@wp-plugin-components';
import getMatchType from './match-type';
import HttpCheck from 'component/http-check';
import { getMatches, getActions } from 'component/redirect-edit/constants';
import { getServerUrl } from 'lib/wordpress-url';

interface ActionData {
	server?: string;
}

interface Item {
	match_type: string;
	action_data: ActionData;
	url: string;
	action_code: number;
	last_access: string;
	hits: number;
	position: number;
	action_type: string;
}

interface RowParams {
	rowMode: string | false;
	setRowMode: ( mode: string | false | null ) => void;
	table: any;
}

interface Column {
	name: string;
	content: JSX.Element | string | number;
	alwaysDisplay?: boolean;
}

function getServer( item: Item ): string {
	if ( item.match_type === 'server' ) {
		return item.action_data.server ?? '';
	}

	return document.location.origin;
}

function escapeUrl( url: string ): string {
	let newUrl = encodeURIComponent( url ).replace( /%2F/gi, '/' );

	newUrl = newUrl.replace( /%3F/gi, '?' );
	newUrl = newUrl.replace( /%3D/gi, '=' );
	newUrl = newUrl.replace( /%26/gi, '&' );
	return newUrl;
}

export default function getColumns(
	row: any,
	rowParams: RowParams,
	_disabled: boolean,
	defaultFlags: any,
	group: any
): JSX.Element | Column[] {
	const { last_access, hits, position, match_type, action_type, action_code, action_data } = row;
	const { rowMode, setRowMode } = rowParams;

	if ( rowMode === 'edit' ) {
		return <EditRedirect item={ row } onCancel={ () => setRowMode( false ) } />;
	}

	return [
		{
			name: 'status',
			content: <StatusColumn row={ row } />,
		},
		{
			name: 'source',
			content: (
				<>
					<SourceColumn row={ row } table={ rowParams.table } defaultFlags={ defaultFlags } />
					{ rowMode === 'check' && (
						<Modal onClose={ () => setRowMode( null ) }>
							<HttpCheck
								url={ getServerUrl( getServer( row ), escapeUrl( row.url ) ) }
								desiredCode={ action_code }
								desiredTarget={ action_data }
							/>
						</Modal>
					) }
				</>
			),
			alwaysDisplay: true,
		},
		{
			name: 'match_type',
			content: getMatchType( match_type, getMatches() ),
		},
		{
			name: 'action_type',
			content: getMatchType( action_type, getActions() ),
		},
		{
			name: 'code',
			content: getCode( row ),
		},
		{
			name: 'group',
			content: <GroupColumn row={ row } group={ group } />,
		},
		{
			name: 'position',
			content: new Intl.NumberFormat( window.Redirectioni10n.locale ).format( position ),
		},
		{
			name: 'hits',
			content: new Intl.NumberFormat( window.Redirectioni10n.locale ).format( hits ),
		},
		{
			name: 'last_access',
			content: last_access,
		},
	];
}
