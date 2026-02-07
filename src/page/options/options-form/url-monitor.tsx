import { __, sprintf } from '@wordpress/i18n';
import { TableRow } from 'component/form-table';
import { Select } from '@wp-plugin-components';

interface PostTypes {
	[ key: string ]: string;
}

interface GroupOption {
	value: number | Array< { value: number } >;
	label: string;
}

interface Settings {
	associated_redirect: string;
	monitor_post: number;
	monitor_types: string[];
}

interface UrlMonitoringProps {
	onChange: ( settings: Partial< Settings > ) => void;
	settings: Settings;
	groups: GroupOption[];
	getLink: ( rel: string, anchor?: string ) => string;
	postTypes: PostTypes;
}

function getPostTypes(
	postTypes: PostTypes,
	monitor_types: string[],
	onChangeMonitor: ( ev: React.ChangeEvent< HTMLInputElement > ) => void
) {
	const types: JSX.Element[] = [];

	for ( const key in postTypes ) {
		const label = postTypes[ key ];
		const existing = monitor_types.find( ( item ) => item === key );
		const value = existing ? true : false;

		if ( ! label ) {
			continue;
		}

		types.push(
			<p key={ key }>
				<input
					id={ 'monitor-type-' + key }
					type="checkbox"
					name={ 'monitor_type_' + key }
					onChange={ onChangeMonitor }
					checked={ value }
				/>

				<label htmlFor={ 'monitor-type-' + key }>
					{ sprintf(
						// translators: %(type)s is the post type name (e.g. post, page)
						__( 'Monitor changes to %(type)s', 'redirection' ),
						{ type: label.toLowerCase() }
					) }
				</label>
			</p>
		);
	}

	return types;
}

function getMonitorPost( post: number, groups: GroupOption[] ): number {
	if ( parseInt( post.toString(), 10 ) === 0 && groups.length > 0 ) {
		if ( groups.length > 0 && groups[ 0 ] ) {
			if ( Array.isArray( groups[ 0 ].value ) ) {
				return groups[ 0 ].value[ 0 ]?.value || 0;
			}

			return groups[ 0 ].value as number;
		}

		return 0;
	}

	return post;
}

function UrlMonitoring( props: UrlMonitoringProps ) {
	const { onChange, settings, groups, getLink, postTypes } = props;
	const { associated_redirect, monitor_post, monitor_types } = settings;
	const canMonitor = monitor_types.length > 0;

	function onChangeMonitor( ev: React.ChangeEvent< HTMLInputElement > ) {
		const type = ev.target.name.replace( 'monitor_type_', '' );
		const filteredTypes = monitor_types.filter( ( item ) => item !== type );

		if ( ev.target.checked ) {
			filteredTypes.push( type );
		}

		onChange( {
			monitor_types: filteredTypes,
			monitor_post: filteredTypes.length > 0 ? getMonitorPost( monitor_post, groups ) : 0,
			associated_redirect: filteredTypes.length > 0 ? associated_redirect : '',
		} );
	}

	return (
		<>
			<TableRow title={ __( 'URL Monitor', 'redirection' ) + ':' } url={ getLink( 'options', 'monitor' ) }>
				{ getPostTypes( postTypes, monitor_types, onChangeMonitor ) }
			</TableRow>

			{ canMonitor && (
				<TableRow
					title={ __( 'URL Monitor Changes', 'redirection' ) + ':' }
					url={ getLink( 'options', 'monitor' ) }
				>
					<Select
						items={ groups as any }
						name="monitor_post"
						value={ String( monitor_post ) }
						onChange={ onChange as any }
					/>
					&nbsp;
					{ __( 'Save changes to this group', 'redirection' ) }
					<p>
						<input
							type="text"
							className="regular-text"
							name="associated_redirect"
							onChange={ onChange as any }
							placeholder={ __( 'For example "/amp"', 'redirection' ) }
							value={ associated_redirect }
						/>
						&nbsp;
						{ __( 'Create associated redirect (added to end of URL)', 'redirection' ) }
					</p>
				</TableRow>
			) }
		</>
	);
}

export default UrlMonitoring;
