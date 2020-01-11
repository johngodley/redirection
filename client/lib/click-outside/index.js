/**
 * External dependencies
 */

import React from 'react';

export default ( WrappedComponent ) => {
	return class extends React.Component {
		constructor( props ) {
			super( props );

			this.node = React.createRef();
		}

		componentDidMount() {
			addEventListener( 'mousedown', this.onClick );
		}

		onClick = ev => {
			if ( this.node.current && ev.target.closest( '.redirect-click-outside' ) === null ) {
				this.node.current.handleClickOutside( ev );
			}
		}

		render() {
			return (
				<div className="redirect-click-outside">
					<WrappedComponent { ...this.props } ref={ this.node } />
				</div>
			);
		}
	};
};
