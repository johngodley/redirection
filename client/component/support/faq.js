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
				a: <a href="http://www.refreshyourcache.com/en/home/" />
			}
		} )
	},
	{
		title: __( 'Can I open a redirect in a new tab?' ),
		text: __( "It's not possible to do this on the server. Instead you will need to add {{code}}target=\"blank\"{{/code}} to your link.", {
			components: {
				code: <code />,
			}
		} )
	},
	{
		title: __( "Something isn't working!" ),
		text: __( 'Please disable all other plugins and check if the problem persists. If it does please report it {{a}}here{{/a}} with full details about the problem and a way to reproduce it.', {
			components: {
				a: <a href="https://github.com/johngodley/redirection/issues" />
			}
		} )
	}
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

			<p>{ __( 'Need some help? Maybe one of these questions will provide an answer' ) }</p>

			<ul className="faq">
				{ faq.map( ( item, pos ) => <FaqEntry title={ item.title } text={ item.text } key={ pos } /> ) }
			</ul>
		</div>
	);
};

export default Faq;
