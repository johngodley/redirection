/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';

/**
 * Internal dependencies
 */

import { RowActions, RowAction } from 'component/table/row-action';
import GeoMapAction from 'component/log-page/log-actions/geo-map';
import UseragentAction from 'component/log-page/log-actions/user-agent';
import ExtraDataAction from 'component/log-page/log-actions/extra-data';

/**
 * Internal dependencies
 */

import { CAP_LOG_DELETE } from 'lib/capabilities';

function LogRowActions( props ) {
	const { row, onDelete, disabled } = props;
	const { ip, agent, id, request_data, redirection_id } = row;
	const menu = [];

	menu.push(
		<RowAction onClick={ () => onDelete( id ) } capability={ CAP_LOG_DELETE } key="0">
			{ __( 'Delete' ) }
		</RowAction>
	);

	if ( ip ) {
		menu.unshift( <GeoMapAction key="2" ip={ ip } /> );
	}

	if ( agent ) {
		menu.unshift( <UseragentAction key="3" agent={ agent } /> );
	}

	if ( request_data ) {
		menu.push( <ExtraDataAction data={ request_data } key="4" /> );
	}

	if ( redirection_id > 0 ) {
		menu.push(
			<RowAction
				href={ Redirectioni10n.pluginRoot + '&' + encodeURIComponent( 'filterby[id]' ) + '=' + redirection_id }
				key="5"
			>
				{ __( 'View Redirect' ) }
			</RowAction>
		);
	}

	return <RowActions disabled={ disabled } actions={ menu } />;
}

export default LogRowActions;
