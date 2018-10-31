/**
 * External dependencies
 */

import React from 'react';
import ReactDOM from 'react-dom';
import PropTypes from 'prop-types';
import classnames from 'classnames';

import './style.scss';

class Modal extends React.Component {
	static propTypes = {
		onClose: PropTypes.func.isRequired,
		children: PropTypes.node,
		width: PropTypes.string,
		height: PropTypes.number,
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

		document.body.classList.add( 'redirection-modal_shown' );
	}

	componentWillUnmount() {
		document.body.classList.remove( 'redirection-modal_shown' );
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
			'redirection-modal_wrapper': true,
			'redirection-modal_wrapper-padding': this.props.padding,
		} );

		const style = {};

		if ( this.height ) {
			style.height = this.height + 'px';
		}

		return ReactDOM.createPortal(
			<div className={ classes } onClick={ this.handleClick }>
				<div className="redirection-modal_backdrop"></div>
				<div className="redirection-modal_main">
					<div className="redirection-modal_content" ref={ this.nodeRef } style={ style }>
						<div className="redirection-modal_close">
							<button onClick={ onClose }>&#x2716;</button>
						</div>

						{ React.cloneElement( this.props.children, { parent: this } ) }
					</div>
				</div>
			</div>, document.getElementById( 'react-modal' ) );
	}
}

export default Modal;
