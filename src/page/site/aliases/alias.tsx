import { Fragment } from 'react';

interface AliasProps {
	domain: string;
	asDomain: string;
	onChange: ( ev: React.ChangeEvent< HTMLInputElement > ) => void;
	onDelete: () => void;
	site: string;
}

const Alias = ( { domain, asDomain, onChange, onDelete, site }: AliasProps ) => {
	const deleteIt = ( ev: React.MouseEvent< HTMLButtonElement > ) => {
		ev.preventDefault();
		onDelete();
	};

	return (
		<tr className="redirect-alias__item">
			<td>
				<input className="regular-text" type="text" name="domain" value={ domain } onChange={ onChange } />
			</td>
			<td className="redirect-alias__item__asdomain">
				{ domain.length > 0 && (
					<Fragment>
						<code>{ asDomain }</code> ⇒ <code>{ site }</code>
					</Fragment>
				) }
			</td>
			<td className="redirect-alias__delete">
				<button onClick={ deleteIt }>
					<span className="dashicons dashicons-trash"></span>
				</button>
			</td>
		</tr>
	);
};

export default Alias;
