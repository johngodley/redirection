/**
 * External dependencies
 */

import React from 'react';
import PropTypes from 'prop-types';
import classnames from 'classnames';

class Modal extends React.Component {
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
		this.resize();
	}

	componentWillReceiveProps() {
		this.resize();
	}

	componentDidUpdate() {
		this.resize();
	}

	resize() {
		if ( this.props.show ) {
			let height = 5;

			for ( let x = 0; x < this.ref.children.length; x++ ) {
				height += this.ref.children[ x ].clientHeight;
			}

			this.ref.style.height = height + 'px';
			this.height = height - this.height;
		}
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
		const { show, onClose, width } = this.props;
		const classes = classnames( {
			'modal-wrapper' : true,
			'modal-wrapper-padding': this.props.padding,
		} );

		if ( ! show ) {
			return null;
		}

		const style = width ? { width: width + 'px' } : {};

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

						{ this.props.children }
					</div>
				</div>
			</div>
		);
	}
}

Modal.propTypes = {
	onClose: PropTypes.func.isRequired,
	show: PropTypes.bool,
	children: PropTypes.node,
	width: PropTypes.string,
};

export default Modal;
