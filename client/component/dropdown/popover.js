/**
 * External dependencies
 */

import React from 'react';
import classnames from 'classnames';
import enhanceWithClickOutside from 'react-click-outside';

class Popover extends React.Component {
	constructor( props ) {
		super( props );

		this.ref = React.createRef();
		this.state = { width: 0 };
	}

	handleClickOutside = ev => {
		const toggle = ev.target.closest( '.redirect-popover__toggle' );

		if ( ! toggle || ( toggle && toggle !== this.props.toggleRef ) ) {
			this.props.onHide();
		}
	}

	componentDidMount() {
		const { toggle } = this.props;
		const { right } = this.ref.current.childNodes[ 0 ].getBoundingClientRect();

		this.setState( { width: right - toggle.right } );
	}

	getPosition( position ) {
		if ( position === 'right' ) {
			return {
				left: `calc(100% - ${ this.state.width }px)`,
			};
		}

		return null;
	}

	render() {
		const { position, className, content } = this.props;

		return (
			<div className="redirect-popover" style={ this.getPosition( position ) } ref={ this.ref }>
				<div className={ classnames( 'redirect-popover__content', className ) }>
					{ content() }
				</div>
			</div>
		);
	}
}

export default enhanceWithClickOutside( Popover );
