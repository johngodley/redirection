/**
 * External dependencies
 */

import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { TableRow } from 'component/form-table';
import { Select } from 'wp-plugin-components';

/**
 * Get post types as selectable options
 * @param {object} postTypes Post type => post type label
 * @param {string[]} monitor_types Current selected types
 * @param {object} onChangeMonitor Callback
 */
function getPostTypes( postTypes, monitor_types, onChangeMonitor ) {
	const types = [];

	for ( const key in postTypes ) {
		const label = postTypes[ key ];
		const existing = monitor_types.find( ( item ) => item === key );
		const value = existing ? true : false;

		if ( !label ) {
			continue;
		}

		types.push(
			<p key={ key }>
				<label>
					<input
						type="checkbox"
						name={ 'monitor_type_' + key }
						onChange={ onChangeMonitor }
						checked={ value }
					/>

					{ sprintf( __( 'Monitor changes to %(type)s', 'redirection' ), { type: label.toLowerCase() } ) }
				</label>
			</p>
		);
	}

	return types;
}

/**
 * Get the monitor post ID
 * @param {string} post Post ID
 * @param {object[]} groups Groups
 */
function getMonitorPost( post, groups ) {
	if ( parseInt( post, 10 ) === 0 && groups.length > 0 ) {
		if ( groups.length > 0 ) {
			if ( groups[ 0 ].value.length !== undefined ) {
				return groups[ 0 ].value[ 0 ].value;
			}

			return groups[ 0 ].value;
		}

		return 0;
	}

	return post;
}

function UrlMonitoring( props ) {
	const { onChange, settings, groups, getLink, postTypes } = props;
	const { associated_redirect, monitor_post, monitor_types } = settings;
	const canMonitor = monitor_types.length > 0;

	function onChangeMonitor( ev ) {
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
				<TableRow title={ __( 'URL Monitor Changes', 'redirection' ) + ':' } url={ getLink( 'options', 'monitor' ) }>
					<Select items={ groups } name="monitor_post" value={ monitor_post } onChange={ onChange } />
					&nbsp;
					{ __( 'Save changes to this group', 'redirection' ) }
					<p>
						<input
							type="text"
							className="regular-text"
							name="associated_redirect"
							onChange={ onChange }
							placeholder={ __( 'For example "/amp"', 'redirection' ) }
							value={ associated_redirect }
						/>&nbsp;
						{ __( 'Create associated redirect (added to end of URL)', 'redirection' ) }
					</p>
				</TableRow>
			) }
		</>
	);
}

export default UrlMonitoring;
