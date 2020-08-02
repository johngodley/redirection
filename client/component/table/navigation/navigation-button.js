/**
 * External dependencies
 */

function NavigationButton( props ) {
	const { title, button, className, disabled, onClick } = props;

	if ( disabled ) {
		return (
			<span className="tablenav-pages-navspan button disabled" aria-hidden="true">
				{ button }
			</span>
		);
	}

	return (
		<a className={ className + ' button' } href="#" onClick={ onClick }>
			<span className="screen-reader-text">{ title }</span>
			<span aria-hidden="true">{ button }</span>
		</a>
	);
}

export default NavigationButton;
