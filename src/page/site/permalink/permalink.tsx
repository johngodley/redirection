interface PermalinkProps {
	link: string;
	onChange: ( ev: React.ChangeEvent< HTMLInputElement > ) => void;
	onDelete: () => void;
}

function Permalink( { link, onChange, onDelete }: PermalinkProps ) {
	const deleteIt = ( ev: React.MouseEvent< HTMLButtonElement > ) => {
		ev.preventDefault();
		onDelete();
	};

	return (
		<tr className="redirect-alias__item">
			<td>
				<input className="regular-text" type="text" name="link" value={ link } onChange={ onChange } />
			</td>
			<td className="redirect-alias__delete">
				<button onClick={ deleteIt }>
					<span className="dashicons dashicons-trash" />
				</button>
			</td>
		</tr>
	);
}

export default Permalink;
