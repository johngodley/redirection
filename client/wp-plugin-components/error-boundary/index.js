/**
 * External dependencies
 */

import React from 'react';

class ErrorBoundary extends React.Component {
	constructor( props ) {
		super( props );
		this.state = { error: false, stack: null, errorInfo: null };
	}

	static getDerivedStateFromError( error ) {
		return { error: true };
	}

	componentDidCatch( error, errorInfo ) {
		this.setState( { error: true, stack: error, errorInfo } );
		console.error( error, errorInfo );
	}

	render() {
		const { error, stack, errorInfo } = this.state;
		const { renderCrash, children, extra } = this.props;

		if ( error ) {
			// You can render any custom fallback UI
			return renderCrash( stack, errorInfo, extra );
		}

		return children;
	}
}

export default ErrorBoundary;
