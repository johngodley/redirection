/**
 * External dependencies
 */

import React from 'react';
import classnames from 'classnames';
import enhanceWithClickOutside from 'react-click-outside';

const POPOVER_MIN = 100;
const POPOVER_MAX = 250;

class Popover extends React.Component {
	constructor( props ) {
		super( props );

		this.ref = React.createRef();
		this.state = {
			containerWidth: 0,
			buttonWidth: 0,
		};
	}

	handleClickOutside = ev => {
		const toggle = ev.target.closest( '.redirect-popover__toggle' );

		if ( ! toggle || ( toggle && toggle !== this.props.toggleRef ) ) {
			this.props.onHide();
		}
	}

	componentDidMount() {
		this.setWidths();
	}

	componentDidUpdate() {
		const { width } = this.props.toggleRef.childNodes[ 0 ].getBoundingClientRect();

		if ( width !== this.state.buttonWidth ) {
			this.setWidths();
		}
	}

	setWidths() {
		const { width } = this.props.toggleRef.childNodes[ 0 ].getBoundingClientRect();

		this.setState( {
			containerWidth: this.ref.current.getBoundingClientRect().width,
			buttonWidth: width,
		} );
	}

	getPopoverWidth() {
		const { buttonWidth, containerWidth } = this.state;

		if ( buttonWidth < containerWidth + POPOVER_MIN ) {
			return {
				minWidth: buttonWidth + 'px',
			};
		}

		return {
			minWidth: Math.min( containerWidth + POPOVER_MIN, POPOVER_MAX ) + 'px',
		};
	}

	isRightAligned() {
		const { buttonWidth, containerWidth } = this.state;

		if ( buttonWidth > containerWidth + POPOVER_MIN || this.props.position === 'right' ) {
			return true;
		}

		return false;
	}

	render() {
		const { className, content } = this.props;
		const width = this.getPopoverWidth();
		const classes = classnames(
			'redirect-popover',
			{
				'redirect-popover__right': this.isRightAligned(),
			},
		);

		return (
			<div className={ classes }>
				<div className={ classnames( 'redirect-popover__content', className ) } style={ width } ref={ this.ref }>
					{ content() }
				</div>
			</div>
		);
	}
}

export default enhanceWithClickOutside( Popover );
