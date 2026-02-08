import { createInterpolateElement as coreInterpolate } from '@wordpress/element';

/**
 * A back-compat wrapper over the WordPress createInterpolateElement function that supports {{code}} instead of <code>
 * @param {string} text Text.
 * @param {object} components Components
 * @returns React.ReactElement|React.ReactNode|WPElement
 */
export default function createInterpolateElement( text: string, components: object ): React.ReactElement<any, string | React.JSXElementConstructor<any>> {
	try {
		return coreInterpolate( text.replace( /\{\{/g, '<' ).replace( /\}\}/g, '>' ), components );
	} catch ( e ) {
		return text;
	}
}