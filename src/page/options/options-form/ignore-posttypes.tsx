/**
 * External dependencies
 */

import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { TableRow } from 'component/form-table';
import { createInterpolateElement, MultiOptionDropdown, Notice } from 'wp-plugin-components';

interface PostTypes {
	[ key: string ]: string;
}

interface Settings {
	ignore_posttypes: string[];
}

interface IgnorePostTypesOptionsProps {
	settings: Settings;
	onChange: ( ev: React.ChangeEvent< HTMLInputElement | HTMLTextAreaElement > | { [ key: string ]: any } ) => void;
	getLink: ( rel: string, anchor?: string ) => string;
	postTypes: PostTypes;
}

function IgnorePostTypesOptions( props: IgnorePostTypesOptionsProps ) {
	const { settings, onChange, getLink, postTypes } = props;
	const { ignore_posttypes } = settings;
	const ignorePostTypesLength = ignore_posttypes.length;
	return (
		<>
			<tr className="redirect-option__row">
				<td colSpan={ 2 }>
					<h2 className="title">{ __( 'Post types to ignore from redirection', 'redirection' ) }</h2>
				</td>
			</tr>
			<TableRow
				title={ __( 'Post types', 'redirection' ) + ':' }
				url={ getLink( 'options', 'ignore_posttypes' ) }
			>
				<MultiOptionDropdown
					options={ Object.keys( postTypes ).map( ( item ) => {
						return { value: item, label: postTypes[ item ] };
					} ) }
					selected={ ignore_posttypes }
					multiple
					badges={ ignorePostTypesLength > 0 }
					hideTitle={ ignorePostTypesLength > 0 }
					onApply={ ( options: any ) => onChange( { ignore_posttypes: options } ) }
					title={ ignorePostTypesLength === 0 ? __( 'Posttypes', 'redirection' ) : '' }
				/>
				{ ignorePostTypesLength > 0 && (
					<>
						<p>
							{ ' ' }
							{ __(
								'Redirection rules will not be applied to posts of the selected post types.',
								'redirection'
							) }{ ' ' }
						</p>
						<p>
							<Notice status="warning">
								{ createInterpolateElement(
									__(
										'Warning: This feature will not work if your site’s permalink settings are set to <code>Plain</code>.',
										'redirection'
									),
									{ code: <code /> }
								) }
							</Notice>
						</p>
					</>
				) }
			</TableRow>
		</>
	);
}

export default IgnorePostTypesOptions;
