/* global Redirectioni10n */
/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */

import RowActions from 'component/table/row-action';
import { getModule, setModule, downloadFile } from 'state/module/action';
import ApacheConfigure from './apache';
import ModuleData from './data';

const MODULE_APACHE = 2;
const EXPORTS = {
	1: [ 'rss', 'csv', 'apache', 'nginx' ],
	2: [ 'csv', 'apache', 'nginx', 'config' ],
	3: [ 'csv', 'apache', 'nginx' ],
};
const EXPORT_NAME = {
	rss: 'RSS',
	csv: 'CSV',
	apache: 'Apache',
	nginx: 'Nginx',
};
const DESCRIPTIONS = {
	1: __( 'WordPress-powered redirects. This requires no further configuration, and you can track hits.' ),
	2: __( 'Uses Apache {{code}}.htaccess{{/code}} files. Requires further configuration. The redirect happens without loading WordPress. No tracking of hits.', {
		components: {
			code: <code />,
		}
	} ),
	3: __( 'For use with Nginx server. Requires manual configuration. The redirect happens without loading WordPress. No tracking of hits. This is an experimental module.' ),
};

const description = name => DESCRIPTIONS[ name ] ? DESCRIPTIONS[ name ] : '';
const getUrl = ( moduleId, modType ) => Redirectioni10n.pluginRoot + '&sub=modules&export=' + moduleId + '&exporter=' + modType;

const exporter = ( modType, moduleId, pos, getData ) => {
	const url = getUrl( moduleId, modType );
	const clicker = ev => {
		ev.preventDefault();
		getData( moduleId, modType );
	};

	if ( modType === 'config' ) {
		return <a key={ pos } href={ url } onClick={ clicker }>{ __( 'Configure' ) }</a>;
	} else if ( modType === 'rss' ) {
		return <a key={ pos } href={ Redirectioni10n.pluginRoot + '&sub=rss&module=1&token=' + Redirectioni10n.token }>RSS</a>;
	}

	return <a key={ pos } href={ url } onClick={ clicker }>{ EXPORT_NAME[ modType ] }</a>;
};

const Loader = () => {
	return (
		<div className="loader-wrapper">
			<div className="placeholder-loading loading-small">
			</div>
		</div>
	);
};

class LogModule extends React.Component {
	constructor( props ) {
		super( props );

		this.state = { showing: false, modType: false };
		this.onClick = this.handleClick.bind( this );
		this.onClose = this.handleClose.bind( this );
		this.onDownload = this.handleDownload.bind( this );
		this.onSave = this.handleSave.bind( this );
	}

	handleClose() {
		this.setState( { showing: false } );
	}

	handleDownload() {
		this.setState( { showing: false } );
		this.props.onDownloadFile( getUrl( this.props.item.name, this.state.modType ) );
	}

	handleClick( moduleId, modType ) {
		if ( modType !== 'config' && ( ! this.props.item.data || this.state.modType !== modType ) ) {
			this.props.onGetData( moduleId, modType );
		}

		this.setState( {
			showing: this.state.showing ? false : moduleId,
			modType: modType,
		} );
	}

	handleSave( moduleId, params ) {
		this.props.onSetData( moduleId, params );
		this.setState( { showing: false } );
	}

	getMenu( module_id, redirects ) {
		if ( redirects > 0 ) {
			return EXPORTS[ module_id ]
				.map( ( item, pos ) => exporter( item, module_id, pos, this.onClick ) )
				.reduce( ( prev, curr ) => [ prev, ' | ', curr ] );
		}

		if ( module_id === MODULE_APACHE && redirects === 0 ) {
			return [ exporter( 'config', 'apache', 0, this.onClick ) ];
		}

		return null;
	}

	render() {
		const { redirects, module_id, displayName, data } = this.props.item;
		const { isLoading } = this.props;
		const menu = this.getMenu( module_id, redirects );
		const total = redirects === null ? '-' : redirects;
		let showItem;

		if ( this.state.showing ) {
			if ( this.state.modType === 'config' ) {
				showItem = <ApacheConfigure onClose={ this.onClose } data={ data } onSave={ this.onSave } />;
			} else {
				showItem = <ModuleData data={ data } onClose={ this.onClose } onDownload={ this.onDownload } isLoading={ isLoading } />;
			}
		}

		return (
			<tr>
				<td className="module-contents">
					<p><strong>{ displayName }</strong></p>
					<p>{ description( module_id ) }</p>

					{ this.state.showing ? showItem : <RowActions>{ menu }</RowActions> }
				</td>
				<td>
					{ isLoading ? <Loader /> : total }
				</td>
			</tr>
		);
	}
}

function mapDispatchToProps( dispatch ) {
	return {
		onGetData: ( moduleId, type ) => {
			dispatch( getModule( moduleId, type ) );
		},
		onSetData: ( moduleId, data ) => {
			dispatch( setModule( moduleId, data ) );
		},
		onDownloadFile: url => {
			dispatch( downloadFile( url ) );
		},
	};
}

export default connect(
	null,
	mapDispatchToProps,
)( LogModule );
