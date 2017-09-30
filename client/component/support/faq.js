/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';

const faq = [
	{
		title: __( 'I deleted a redirection, why is it still redirecting?' ),
		text: __( 'Your browser will cache redirections. If you have deleted a redirection and your browser is still performing the redirection then {{a}}clear your browser cache{{/a}}.', {
			components: {
				a: <a href="http://www.refreshyourcache.com/en/home/" />,
			},
		} ),
	},
	{
		title: __( 'Can I open a redirect in a new tab?' ),
		text: __( "It's not possible to do this on the server. Instead you will need to add {{code}}target=\"_blank\"{{/code}} to your link.", {
			components: {
				code: <code />,
			},
		} ),
	},
	{
		title: __( 'Can I redirect all 404 errors?' ),
		text: __( "No, and it isnt advised that you do so. A 404 error is the correct response to return for a page that doesn't exist. If you redirect it you are indicating that it once existed, and this could dilute your site." ),
	},
];

const FaqEntry = props => {
	const { title, text } = props;

	return (
		<li>
			<h3>{ title }</h3>
			<p>{ text }</p>
		</li>
	);
};

const Faq = () => {
	return (
		<div>
			<h3>{ __( 'Frequently Asked Questions' )}</h3>

			<ul className="faq">
				{ faq.map( ( item, pos ) => <FaqEntry title={ item.title } text={ item.text } key={ pos } /> ) }
			</ul>
		</div>
	);
};

export default Faq;
