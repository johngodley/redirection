/**
 * External dependencies
 */

import React from 'react';
import ReactDOM from 'react-dom';

/**
 * Internal dependencies
 */

import ModalWrapper from './wrapper';
import getPortal from '../../wp-plugin-lib/portal';
import { MODAL_PORTAL } from '../constant';
import './style.scss';

/**
 * onClose callback.
 *
 * @callback closeCallback
 */

/**
 * Show a modal dialog, using the element `react-modal`.
 *
 * A global class `wpl-modal_shown` will be added to `body` when the modal is being shown, and removed after.
 *
 * @param {Object} props - Component props
 * @param {Boolean} [props.padding] - Include padding, defaults to `true`
 * @param {closeCallback} props.onClose - Function to call to close the modal
 * @param {Object} props.children - Contents of the modal
 * @param {string} [props.className] - Optional class name
 */
const Modal = ( props ) => ReactDOM.createPortal( <ModalWrapper { ...props } />, getPortal( MODAL_PORTAL ) );

export default Modal;
