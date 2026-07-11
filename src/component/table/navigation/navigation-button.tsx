interface NavigationButtonProps {
	title: string;
	button: string;
	className: string;
	disabled: boolean;
	onClick: () => void;
}

function NavigationButton( props: NavigationButtonProps ) {
	const { title, button, className, disabled, onClick } = props;
	function click( ev: React.MouseEvent< HTMLButtonElement > ) {
		ev.preventDefault();
		onClick();
	}

	if ( disabled ) {
		return (
			<span className="tablenav-pages-navspan button disabled" aria-hidden="true">
				<span>{ button }</span>
			</span>
		);
	}

	return (
		<button className={ className + ' button' } type="button" onClick={ click }>
			<span className="screen-reader-text">{ title }</span>
			<span aria-hidden="true">{ button }</span>
		</button>
	);
}

export default NavigationButton;
