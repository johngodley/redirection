import { __ } from '@wordpress/i18n';

interface ApiResultPassProps {
	methods: string[];
}

const ApiResultPass = ( { methods }: ApiResultPassProps ) => {
	return (
		<p key={ methods.join( '' ) }>
			<span className="dashicons dashicons-yes"></span>

			{ methods.map( ( method, key ) => (
				<span key={ key } className="api-result-method_pass">
					{ method }
				</span>
			) ) }

			{ __( 'Working!', 'redirection' ) }
		</p>
	);
};

export default ApiResultPass;
