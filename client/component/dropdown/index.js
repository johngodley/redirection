/**
 * External dependencies
 */

import React from 'react';
import classnames from 'classnames';

/**
 * Internal dependencies
 */
import './style.scss';
import Popover from './popover';

class Dropdown extends React.Component {
	constructor( props ) {
		super( props );

		this.state = { showing: false };
		this.toggleRef = React.createRef();
	}

	onHide = () => {
		this.setState( { showing: false } );
	}

	onToggle = () => {
		this.setState( { showing: ! this.state.showing } );
	}

	render() {
		const { renderContent, className, renderToggle, position = 'left' } = this.props;
		const { showing } = this.state;

		return (
			<div className={ classnames( 'redirect-popover__container', className ) }>
				<div className="redirect-popover__toggle" ref={ this.toggleRef }>
					{ renderToggle( showing, this.onToggle ) }
				</div>

				{ showing && (
					<Popover
						position={ position }
						content={ () => renderContent( this.onToggle ) }
						onHide={ this.onHide }
						toggle={ this.toggleRef.current ? this.toggleRef.current.getBoundingClientRect() : 0 }
						toggleRef={ this.toggleRef.current }
					/>
				) }
			</div>
		);
	}
}

export default Dropdown;
