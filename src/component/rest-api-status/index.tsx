import React, { useEffect } from 'react';
import { __, sprintf } from '@wordpress/i18n';
import clsx from 'clsx';
import ApiResult from './api-result';
import { Spinner } from '@wp-plugin-components';
import { restApi } from 'page/options/options-form/other-options';
import { useApiCheck } from 'lib/api/hooks/use-settings';
import { useSettingsStore } from 'stores';
import './style.scss';

const STATUS_OK = 'ok';
const STATUS_FAIL = 'fail';
const STATUS_LOADING = 'loading';
const STATUS_WARNING_CURRENT = 'warning-current';
const STATUS_WARNING = 'warning-not-selected';

interface ApiError {
	code?: string;
	name?: string;
	message: string;
	data?: {
		status: number;
	};
	request?: any;
}

interface ApiTestResult {
	status: string;
	error?: ApiError;
	code?: string;
}

interface TestResult {
	GET: ApiTestResult;
	POST: ApiTestResult;
	[ key: string ]: any;
}

interface ApiTest {
	[ key: string ]: TestResult;
}

interface RouteItem {
	text: string;
	value: string;
}

interface RestApiStatusProps {
	allowChange?: boolean;
}

const getApiResult = ( results: ApiTest, name: string ): TestResult =>
	results && results[ name ] ? results[ name ] : ( {} as TestResult );
const isError = ( result: TestResult | undefined ): boolean =>
	!! result &&
	!! result.GET &&
	!! result.POST &&
	( result.GET.status === STATUS_FAIL || result.POST.status === STATUS_FAIL );
const isWorking = ( result: TestResult | undefined ): boolean =>
	!! result && !! result.GET && !! result.POST && result.GET.status === STATUS_OK && result.POST.status === STATUS_OK;

function getPercent( apiTest: ApiTest, routes: RouteItem[] ): number {
	if ( Object.keys( apiTest ).length === 0 ) {
		return 0;
	}

	const total = routes.length * 2;
	let finished = 0;

	for ( let index = 0; index < Object.keys( apiTest ).length; index++ ) {
		const key = Object.keys( apiTest )[ index ];

		if ( key && apiTest[ key ] && apiTest[ key ].GET && apiTest[ key ].GET.status !== STATUS_LOADING ) {
			finished++;
		}

		if ( key && apiTest[ key ] && apiTest[ key ].POST && apiTest[ key ].POST.status !== STATUS_LOADING ) {
			finished++;
		}
	}

	return Math.round( ( finished / total ) * 100 );
}

function getApiStatus( results: ApiTest, routes: RouteItem[], current: string ): string {
	const failed = Object.keys( results ).filter( ( key ) => isError( results[ key ] ) ).length;

	if ( failed === 0 ) {
		return 'ok';
	} else if ( failed < routes.length ) {
		return isWorking( results[ current ] ) ? STATUS_WARNING_CURRENT : STATUS_WARNING;
	}

	return 'fail';
}

function getApiStatusText( status: string ): string {
	if ( status === STATUS_OK ) {
		return __( 'Good', 'redirection' );
	} else if ( status === STATUS_WARNING || status === STATUS_WARNING_CURRENT ) {
		return __( 'Working but some issues', 'redirection' );
	}

	return __( 'Unavailable', 'redirection' );
}

export default function RestApiStatus( { allowChange = true }: RestApiStatusProps ) {
	const [ showing, setShowing ] = React.useState( false );
	const [ hasChecked, setHasChecked ] = React.useState( false );
	// Direct property access instead of destructuring
	const apiTest = useSettingsStore( ( state ) => state.apiTest );
	const api = useSettingsStore( ( state ) => state.api );
	const { mutate: checkApiMutation } = useApiCheck();

	const { routes, current } = api;

	useEffect( () => {
		const untested = Object.keys( routes ).map( ( id ) => ( { id, url: routes[ id ] } ) );
		if ( untested.length > 0 && ! hasChecked ) {
			checkApiMutation(
				untested.filter( ( item ): item is { id: string; url: string } => !! item && !! item.url )
			);
			setHasChecked( true );
		}
	}, [ routes, checkApiMutation, hasChecked ] );

	const onRetry = ( ev: React.MouseEvent< HTMLButtonElement > ) => {
		ev.preventDefault();
		setShowing( false );
		setHasChecked( false );
		const untested = Object.keys( routes ).map( ( id ) => ( { id, url: routes[ id ] } ) );
		checkApiMutation( untested.filter( ( item ): item is { id: string; url: string } => !! item && !! item.url ) );
	};

	const onShow = () => {
		setShowing( true );
	};

	const canShowProblem = ( status: string ): boolean => {
		return showing || status === STATUS_FAIL;
	};

	const renderError = ( status: string ) => {
		const showingProblem = canShowProblem( status );
		let message: string = __(
			'There are some problems connecting to your REST API. It is not necessary to fix these problems and the plugin is able to work.',
			'redirection'
		);

		if ( status === STATUS_FAIL ) {
			message = __(
				'Your REST API is not working and the plugin will not be able to continue until this is fixed.',
				'redirection'
			) as string;
		}

		return (
			<div className="api-result-log">
				<p>
					<strong>{ __( 'Summary', 'redirection' ) }</strong>: { message }
				</p>

				{ ! showingProblem && (
					<p>
						<button className="button-secondary" onClick={ onShow }>
							{ __( 'Show Problems', 'redirection' ) }
						</button>
					</p>
				) }
			</div>
		);
	};

	const routeNames: RouteItem[] = restApi().map( ( item ) => ( { text: item.label, value: String( item.value ) } ) );
	const percent = getPercent( apiTest, routeNames );
	const status = getApiStatus( apiTest, routeNames, current );
	const showProblem = ( percent >= 100 && canShowProblem( status ) ) || showing;
	const statusClass = clsx( {
		'api-result-status': true,
		'api-result-status_good': status === STATUS_OK && percent >= 100,
		'api-result-status_problem': status === STATUS_WARNING_CURRENT && STATUS_WARNING && percent >= 100,
		'api-result-status_failed': status === STATUS_FAIL && percent >= 100,
	} );

	return (
		<div className="api-result-wrapper">
			<div className="api-result-header">
				<strong>REST API:</strong>

				<div className="api-result-progress">
					<span className={ statusClass }>
						{ percent < 100 &&
							sprintf(
								// translators: %s is the percentage complete
								__( 'Testing - %s%%', 'redirection' ),
								String( percent )
							) }
						{ percent >= 100 && getApiStatusText( status ) }
					</span>

					{ percent < 100 && <Spinner /> }
				</div>

				{ percent >= 100 && status !== STATUS_OK && (
					<button className="button button-secondary api-result-retry" onClick={ onRetry }>
						{ __( 'Check Again', 'redirection' ) }
					</button>
				) }
			</div>

			{ percent >= 100 && status !== STATUS_OK && renderError( status ) }

			{ showProblem &&
				routeNames.map( ( item, pos ) => (
					<ApiResult
						item={ item }
						result={ getApiResult( apiTest, item.value ) }
						routes={ routes }
						key={ pos }
						isCurrent={ current === item.value }
						allowChange={ allowChange || false }
					/>
				) ) }
		</div>
	);
}
