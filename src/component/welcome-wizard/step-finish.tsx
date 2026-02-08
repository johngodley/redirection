import { __ } from '@wordpress/i18n';
import getFirstApi from './first-api';
import { ExternalLink, createInterpolateElement } from '@wp-plugin-components';
import { useSettingsStore } from 'stores';
import { useSettingsUpdate, useFinishUpgrade } from 'lib/api/hooks/use-settings';

const WEEK = 7;
const NEVER = -1;

interface StepOptions {
	settings: {
		ip: boolean;
		log: boolean;
		monitor: boolean;
	};
}

interface StepFinishProps {
	step: number;
	setStep: ( step: number ) => void;
	options: StepOptions;
	setOptions: ( options: any ) => void;
}

export default function StepFinish( { options }: StepFinishProps ) {
	const apiTest = useSettingsStore( ( state ) => state.apiTest );
	const { mutate: updateSettings } = useSettingsUpdate();
	const { mutate: finishUpgrade } = useFinishUpgrade();

	function onFinish() {
		const { ip, log, monitor } = options.settings;
		const selectedApi = getFirstApi( apiTest );

		updateSettings( {
			expire_redirect: log ? WEEK : NEVER,
			expire_404: log ? WEEK : NEVER,
			ip_logging: ip ? 1 : 0,
			rest_api: typeof selectedApi === 'number' ? selectedApi : undefined,
			monitor_types: monitor ? [ 'post', 'page' ] : undefined,
			monitor_post: monitor ? 1 : 0,
		} );

		finishUpgrade();
	}

	return (
		<div>
			<h2>{ __( 'Installation Complete', 'redirection' ) }</h2>

			<p>{ __( 'Redirection is now installed!', 'redirection' ) }</p>

			<p>
				{ createInterpolateElement(
					__(
						'Please take a moment to consult the {{support}}support site{{/support}} for information about how to use Redirection.',
						'redirection'
					),
					{
						support: <ExternalLink url="https://redirection.me" />,
					}
				) }
			</p>

			<button className="button button-primary" onClick={ onFinish } type="button">
				{ __( 'Ready to begin! 🎉', 'redirection' ) }
			</button>
		</div>
	);
}
