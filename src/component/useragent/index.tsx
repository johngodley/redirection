import { __ } from '@wordpress/i18n';
import clsx from 'clsx';
import PoweredBy from 'component/powered-by';
import { Spinner, ExternalLink } from '@wp-plugin-components';
import { useUserAgentInfo } from 'lib/api/hooks';
import { useInfoStore } from 'stores';
import './style.scss';

interface DeviceInfo {
	type?: string;
	vendor?: string;
	name?: string;
}

interface DetailInfo {
	name?: string;
	version?: string;
}

interface AgentDetail {
	device: DeviceInfo;
	os: DetailInfo;
	browser: DetailInfo;
	engine: DetailInfo;
	url?: string;
}

interface UseragentProps {
	agent: string;
}

function getDetail( info: DetailInfo ): string | false {
	if ( info && info.name && info.version ) {
		return info.name + ' ' + info.version;
	}

	return false;
}

function getDevice( device: DeviceInfo ): string {
	const parts: string[] = [];

	if ( device.vendor ) {
		parts.push( device.vendor );
	}

	if ( device.name ) {
		parts.push( device.name );
	}

	return parts.join( ' ' );
}

function getType( type: string | undefined, url: string | undefined ): JSX.Element | string | null {
	if ( ! type ) {
		return null;
	}

	const name = type.slice( 0, 1 ).toUpperCase() + type.slice( 1 );

	if ( url ) {
		return <ExternalLink url={ url }>{ name }</ExternalLink>;
	}

	return name;
}

function UserAgentError( { error }: { error: string } ) {
	return (
		<div className="wpl-modal_error">
			<h2>{ __( 'Useragent Error', 'redirection' ) }</h2>
			<p>{ __( 'Something went wrong obtaining this information', 'redirection' ) }</p>
			<p>
				<code>{ error }</code>
			</p>
		</div>
	);
}

function UserAgentUnknown( { agent }: { agent: string } ) {
	return (
		<div className="redirection-useragent_unknown">
			<h2>{ __( 'Unknown Useragent', 'redirection' ) }</h2>
			<br />
			<p>{ agent }</p>
		</div>
	);
}

function UserAgentDetails( { agent, detail }: { agent: string; detail: AgentDetail } ) {
	const type = getType( detail.device.type, detail.url );
	const device = getDevice( detail.device );
	const os = getDetail( detail.os );
	const browser = getDetail( detail.browser );
	const engine = getDetail( detail.engine );
	const parts: [ string, string | false ][] = [];

	if ( device ) {
		parts.push( [ __( 'Device', 'redirection' ), device ] );
	}

	if ( os ) {
		parts.push( [ __( 'Operating System', 'redirection' ), os ] );
	}

	if ( browser ) {
		parts.push( [ __( 'Browser', 'redirection' ), browser ] );
	}

	if ( engine ) {
		parts.push( [ __( 'Engine', 'redirection' ), engine ] );
	}

	return (
		<div>
			<h2>
				{ __( 'Useragent', 'redirection' ) }: { type }
			</h2>
			<table>
				<tbody>
					<tr>
						<th>{ __( 'Agent', 'redirection' ) }</th>
						<td className="redirection-useragent_agent">{ agent }</td>
					</tr>

					{ parts.map( ( item, key ) => {
						return (
							<tr key={ key }>
								<th>{ item[ 0 ] }</th>
								<td>{ item[ 1 ] }</td>
							</tr>
						);
					} ) }
				</tbody>
			</table>

			<PoweredBy />
		</div>
	);
}

export default function Useragent( { agent }: UseragentProps ) {
	// Direct property access instead of destructuring
	const status = useInfoStore( ( state ) => state.status );
	const error = useInfoStore( ( state ) => state.error );
	const agents = useInfoStore( ( state ) => state.agents );

	useUserAgentInfo( agent );

	const klass = clsx( {
		'redirection-useragent': true,
		'wpl-modal_loading': status === 'loading',
	} );

	const detail = agents[ agent ];

	return (
		<div className={ klass }>
			{ status === 'loading' && <Spinner /> }
			{ status === 'error' && error && <UserAgentError error={ error } /> }
			{ status === 'success' && ! detail && <UserAgentUnknown agent={ agent } /> }
			{ status === 'success' && detail && <UserAgentDetails agent={ agent } detail={ detail as any } /> }
		</div>
	);
}
