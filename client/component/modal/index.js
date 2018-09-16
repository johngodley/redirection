/**
 * External dependencies
 */

import React from 'react';
import PropTypes from 'prop-types';
import classnames from 'classnames';

class Modal extends React.Component {
	static propTypes = {
		onClose: PropTypes.func.isRequired,
		children: PropTypes.node,
		width: PropTypes.string,
	};

	static defaultProps = {
		padding: true,
	};

	constructor( props ) {
		super( props );

		this.handleClick = this.onBackground.bind( this );
		this.ref = null;
		this.height = 0;
	}

	componentDidMount() {
		this.height = 0;
		this.resize();

		document.body.classList.add( 'redirection-modal' );
	}

	componentWillUnmount() {
		document.body.classList.remove( 'redirection-modal' );
	}

	componentWillReceiveProps() {
		this.resize();
	}

	componentDidUpdate() {
		this.resize();
	}

	resize() {
		let height = 0;

		for ( let x = 0; x < this.ref.children.length; x++ ) {
			height += this.ref.children[ x ].clientHeight;
		}

		this.ref.style.height = ( height ) + 'px';
	}

	onBackground( ev ) {
		if ( ev.target.className === 'modal' ) {
			this.props.onClose();
		}
	}

	nodeRef = node => {
		this.ref = node;
	};

	render() {
		const { onClose } = this.props;
		const classes = classnames( {
			'modal-wrapper': true,
			'modal-wrapper-padding': this.props.padding,
		} );

		const style = {};

		if ( this.height ) {
			style.height = this.height + 'px';
		}

		return (
			<div className={ classes } onClick={ this.handleClick }>
				<div className="modal-backdrop"></div>
				<div className="modal">
					<div className="modal-content" ref={ this.nodeRef } style={ style }>
						<div className="modal-close">
							<button onClick={ onClose }>&#x2716;</button>
						</div>

						{ React.cloneElement( this.props.children, { parent: this } ) }
					</div>
				</div>
			</div>
		);
	}
}

export default Modal;
