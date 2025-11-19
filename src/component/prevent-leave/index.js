/**
 * External dependencies
 */

import React from 'react';
import PropTypes from 'prop-types';

class PreventLeaveWarning extends React.Component {
	static propTypes = {
		message: PropTypes.string.isRequired,
		prevent: PropTypes.bool,
	};

	static defaultProps = {
		prevent: true,
	};

	componentDidMount() {
		if ( this.props.prevent ) {
			this.enable();
		}
	}

	componentWillUnmount() {
		if ( this.props.prevent ) {
			this.disable();
		}
	}

	componentDidUpdate( prevProps ) {
		if ( prevProps.prevent !== this.props.prevent ) {
			if ( this.props.prevent ) {
				this.enable();
			} else {
				this.disable();
			}
		}
	}

	enable() {
		window.addEventListener( 'beforeunload', this.onWarning );
	}

	disable() {
		window.removeEventListener( 'beforeunload', this.onWarning );
	}

	onWarning = event => {
		event.returnValue = this.props.message;
		return event.returnValue;
	}

	render() {
		return null;
	}
}

export default PreventLeaveWarning;
