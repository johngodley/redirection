/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import debounce from 'debounce-promise';
import enhanceWithClickOutside from 'react-click-outside';

import { RedirectionApi, getApi } from 'lib/api';
import LoadingDots from 'component/loading-dots';

const DEBOUNCE_DELAY = 250;

class UrlChoices extends React.Component {
	onClick = ( ev, url ) => {
		ev.preventDefault();
		this.props.onSelect( url );
	};

	handleClickOutside = () => {
		this.props.onClose();
	}

	render() {
		const { options } = this.props;

		return (
			<div className="redirection-url-autocomplete__options">
				<ul>
					{ options.map( ( item, pos ) => (
						<li key={ pos }>
							<a href="#" onClick={ ev => this.onClick( ev, item.url ) }>
								<span>{ item.title }</span> <code>{ item.slug }</code>
							</a>
						</li>
					) ) }
				</ul>
			</div>
		);
	}
}

const UrlChoicesClick = enhanceWithClickOutside( UrlChoices );

export default class TargetUrl extends React.Component {
	constructor( props ) {
		super( props );

		this.state = { options: [], makingRequest: false };
		this.debouncedLoadOptions = debounce( this.getData, DEBOUNCE_DELAY );
	}

	getData = () => {
		this.setState( { makingRequest: true } );

		// Ignore errors - we just don't show anything
		getApi( RedirectionApi.plugin.matchPost( this.props.url ) )
			.then( options => {
				this.setState( { options, makingRequest: false } );
			} );
	}

	onChange = ev => {
		this.debouncedLoadOptions();
		this.props.onChange( ev );
	}

	onClose = () => {
		this.setState( { options: [] } );
	}

	onSelect = url => {
		this.props.onChange( { target: { name: 'url', value: url, type: 'input' } } );
		this.setState( { options: [] } );
	}

	render() {
		const { url } = this.props;
		const { makingRequest, options } = this.state;

		return (
			<div className="redirection-url-autocomplete redirection-fullflex">
				<input type="text" name="url" value={ url } onChange={ this.onChange } placeholder={ __( 'The target URL you want to redirect, or auto-complete on post name or permalink.' ) } />

				{ makingRequest && <div className="redirection-url-autocomplete__loading"><LoadingDots /></div> }
				{ options.length > 0 && <UrlChoicesClick options={ options } onSelect={ this.onSelect } onClose={ this.onClose } /> }
			</div>
		);
	}
}
