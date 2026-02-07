import { useState } from 'react';
import { __ } from '@wordpress/i18n';

import { ExternalLink } from '@wp-plugin-components';
import TableRow from '../table-row';

interface MatchHeaderProps {
	onChange: ( event: React.ChangeEvent< HTMLInputElement | HTMLSelectElement > ) => void;
	data: {
		name: string;
		value: string;
		regex: boolean;
	};
}

function MatchHeader( { onChange, data }: MatchHeaderProps ) {
	const [ dropdown, setDropdown ] = useState( '' );
	const { name, value, regex } = data;

	const onDropdown = ( ev: React.ChangeEvent< HTMLSelectElement > ) => {
		const headers: Record< string, string > = {
			accept: 'Accept-Language',
		};

		if ( ev.target.value !== '' ) {
			onChange( {
				target: { name: 'name', value: headers[ ev.target.value ] },
			} as React.ChangeEvent< HTMLInputElement > );
		}

		setDropdown( '' );
	};

	return (
		<>
			<TableRow title={ __( 'HTTP Header', 'redirection' ) } className="redirect-edit__match">
				<input
					type="text"
					name="name"
					value={ name }
					onChange={ onChange }
					className="regular-text"
					placeholder={ __( 'Header name', 'redirection' ) }
				/>
				<input
					type="text"
					name="value"
					value={ value }
					onChange={ onChange }
					className="regular-text"
					placeholder={ __( 'Header value', 'redirection' ) }
				/>

				<select name="agent_dropdown" onChange={ onDropdown } value={ dropdown } className="medium">
					<option value="">{ __( 'Custom', 'redirection' ) }</option>
					<option value="accept">{ __( 'Accept Language', 'redirection' ) }</option>
				</select>

				<input
					id="redirect-header-regex"
					type="checkbox"
					name="regex"
					checked={ regex }
					onChange={ onChange }
				/>
				<label className="redirect-edit-regex" htmlFor="redirect-header-regex">
					{ __( 'Regex', 'redirection' ) }{ ' ' }
					<sup>
						<ExternalLink url="https://redirection.me/support/redirect-regular-expressions/">
							?
						</ExternalLink>
					</sup>
				</label>
			</TableRow>

			<TableRow>
				{ __(
					'Note it is your responsibility to pass HTTP headers to PHP. Please contact your hosting provider for support about this.',
					'redirection'
				) }
			</TableRow>
		</>
	);
}

export default MatchHeader;
