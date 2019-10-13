/**
 * External dependencies
 */

import React, { useEffect } from 'react';
import ReactDOM from 'react-dom';
import PropTypes from 'prop-types';
import classnames from 'classnames';

/**
 * Internal dependencies
 */

import ModalContent from './content';
import './style.scss';

function ModalWrapper( props ) {
	useEffect( () => {
		document.body.classList.add( 'redirection-modal_shown' );

		return () => {
			document.body.classList.remove( 'redirection-modal_shown' );
		};
	} );

	const classes = classnames( {
		'redirection-modal_wrapper': true,
		'redirection-modal_wrapper-padding': props.padding,
	} );

	return (
		<div className={ classes }>
			<div className="redirection-modal_backdrop"></div>

			<div className="redirection-modal_main">
				<ModalContent { ...props } />
			</div>
		</div>
	);
}

ModalWrapper.propTypes = {
	onClose: PropTypes.func.isRequired,
	children: PropTypes.node,
	height: PropTypes.number,
};

ModalWrapper.defaultProps = {
	padding: true,
	onClose: () => {},
};

const Modal = props => ReactDOM.createPortal(
	<ModalWrapper { ...props } />,
	document.getElementById( 'react-modal' )
);

export default Modal;
