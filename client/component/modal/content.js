/**
 * External dependencies
 */

import React from 'react';
import onClickOutside from 'lib/click-outside';

class ModalContent extends React.Component {
	handleClickOutside = () => {
		this.props.onClose();
	}

	render() {
		const { onClose } = this.props;

		return (
			<div className="redirection-modal_content">
				<div className="redirection-modal_close">
					<button onClick={ onClose }>&#x2716;</button>
				</div>

				{ this.props.children }
			</div>
		);
	}
}

export default onClickOutside( ModalContent );
