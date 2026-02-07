import { useState, useCallback, useMemo, useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import WelcomeWizard from 'component/welcome-wizard';
import DatabaseUpdate from './database-update';
import { Snackbar, Menu, ErrorBoundary, Error } from '@wp-plugin-components';
import PageRouter from '@wp-plugin-lib/page-router';
import { getPluginPage } from '@wp-plugin-lib/wordpress-url';
import DebugReport from './debug';
import ErrorDetails from './error-details';
import CrashHandler from './crash-handler';
import PageContent from './page-content';
import { getErrorLinks, getErrorDetails } from 'lib/error-links';
import CacheDetect from './cache-detect';
import UpdateNotice from './update-notice';
import { getInitialError, getInitialLog, getInitialGroup, getInitialRedirect } from 'lib/log-constants';
import { useMessageStore, useSettingsStore, useTableStore } from 'stores';
import { has_capability, has_page_access, CAP_REDIRECT_ADD } from 'lib/capabilities';
import './style.scss';

interface MenuOption {
	name: string;
	value: string;
}

interface PageTitles {
	redirect: string;
	site: string;
	groups: string;
	io: string;
	log: string;
	'404s': string;
	options: string;
	support: string;
	[ key: string ]: string;
}

const getTitles = (): PageTitles => ( {
	redirect: __( 'Redirections', 'redirection' ),
	site: __( 'Site', 'redirection' ),
	groups: __( 'Groups', 'redirection' ),
	io: __( 'Import/Export', 'redirection' ),
	log: __( 'Logs', 'redirection' ),
	'404s': __( '404 errors', 'redirection' ),
	options: __( 'Options', 'redirection' ),
	support: __( 'Support', 'redirection' ),
} );

const getMenu = (): MenuOption[] =>
	[
		{
			name: __( 'Redirects', 'redirection' ),
			value: '',
		},
		{
			name: __( 'Groups', 'redirection' ),
			value: 'groups',
		},
		{
			name: __( 'Site', 'redirection' ),
			value: 'site',
		},
		{
			name: __( 'Log', 'redirection' ),
			value: 'log',
		},
		{
			name: __( '404s', 'redirection' ),
			value: '404s',
		},
		{
			name: __( 'Import/Export', 'redirection' ),
			value: 'io',
		},
		{
			name: __( 'Options', 'redirection' ),
			value: 'options',
		},
		{
			name: __( 'Support', 'redirection' ),
			value: 'support',
		},
	].filter(
		( option ) => has_page_access( option.value ) || ( option.value === '' && has_page_access( 'redirect' ) )
	);

const ALLOWED_PAGES = Redirectioni10n?.caps?.pages || [];

export default function Home() {
	// Access store properties directly to avoid creating new objects on every render
	const errors = useMessageStore( ( state ) => state.errors );
	const notices = useMessageStore( ( state ) => state.notices );
	const { clearErrors, clearNotices } = useMessageStore();

	const databaseStatus = useSettingsStore( ( state ) => state.database.status );
	const showDatabase = useSettingsStore( ( state ) => state.showDatabase );
	const inProgress = useSettingsStore( ( state ) => state.database.inProgress );
	const pluginUpdate = useSettingsStore( ( state ) => state.values?.plugin_update ?? '' );
	const { setShowDatabase, setApi } = useSettingsStore();
	const { setErrorsTable, setLogsTable, setRedirectsTable, setRedirectsAddTop, setGroupsTable } = useTableStore();

	const [ page, setPageState ] = useState< string >( getPluginPage( ALLOWED_PAGES ) );

	// Initialize API routes and preloaded settings from global config on mount
	useEffect( () => {
		if ( window.Redirectioni10n?.api?.routes ) {
			setApi( {
				routes: window.Redirectioni10n.api.routes,
				current: window.Redirectioni10n.api.current ?? '',
			} );
		}

		// Initialize settings from preloaded data if available
		if ( window.Redirectioni10n?.settings && ! useSettingsStore.getState().values ) {
			const { setValues, setLoadStatus } = useSettingsStore.getState();
			setValues( window.Redirectioni10n.settings as any );
			setLoadStatus( 'success' );
		}
	}, [ setApi ] );

	// Wrap setPage in useCallback to ensure stable reference
	const setPage = useCallback( ( newPage: string ) => {
		setPageState( newPage );
	}, [] );

	// Only clear errors if there are actually errors to clear
	const onPageChange = useCallback( () => {
		if ( errors.length > 0 ) {
			clearErrors();
		}
	}, [ errors, clearErrors ] );

	// Memoize menu and titles to prevent creating new objects on every render
	const menu = useMemo( () => getMenu(), [] );
	const titles = useMemo( () => getTitles(), [] );

	const changePage = useCallback(
		( newPage: string ) => {
			setPage( newPage === '' ? 'redirect' : newPage );

			if ( newPage === '404s' ) {
				setErrorsTable( getInitialError() as any );
			} else if ( newPage === 'log' ) {
				setLogsTable( getInitialLog() as any );
			} else if ( newPage === '' ) {
				setRedirectsTable( getInitialRedirect() as any );
			} else if ( newPage === 'groups' ) {
				setGroupsTable( getInitialGroup() as any );
			}
		},
		[ setPage, setErrorsTable, setLogsTable, setRedirectsTable, setGroupsTable ]
	);

	if ( REDIRECTION_VERSION !== Redirectioni10n.version ) {
		return <CacheDetect />;
	}

	if ( databaseStatus === 'need-install' || databaseStatus === 'finish-install' ) {
		return <WelcomeWizard />;
	}

	const needsUpgrader =
		pluginUpdate === 'prompt' && ( databaseStatus === 'need-update' || databaseStatus === 'finish-update' );

	return (
		<ErrorBoundary renderCrash={ CrashHandler } extra={ { page } }>
			<div className="wrap redirection notranslate" translate="no">
				{ needsUpgrader && (
					<DatabaseUpdate onShowUpgrade={ () => setShowDatabase( true ) } showDatabase={ showDatabase } />
				) }

				{ ! inProgress && databaseStatus !== 'finish-update' && ! showDatabase && (
					<PageRouter
						page={ page }
						setPage={ setPage }
						onPageChange={ onPageChange }
						allowedPages={ ALLOWED_PAGES }
						baseUrl="?page=redirection.php"
						defaultPage="redirect"
					>
						<h1 className="wp-heading-inline">{ titles[ page ] }</h1>

						{ page === 'redirect' && has_capability( CAP_REDIRECT_ADD ) && (
							<button
								type="button"
								onClick={ () => setRedirectsAddTop( true ) }
								className="page-title-action"
							>
								{ __( 'Add New', 'redirection' ) }
							</button>
						) }

						<UpdateNotice />

						<Menu
							onChangePage={ changePage }
							currentPage={ page }
							menu={ menu }
							home="redirect"
							urlBase={ Redirectioni10n.pluginRoot }
						/>

						<Error
							errors={ errors }
							onClear={ () => clearErrors() }
							renderDebug={ DebugReport }
							details={ getErrorDetails() }
							links={ getErrorLinks() }
							locale="redirection"
						>
							<ErrorDetails />
						</Error>

						<PageContent page={ page } />

						<Snackbar
							notices={ notices }
							onClear={ () => clearNotices() }
							snackBarViewText={ __( 'View notice', 'redirection' ) }
						/>
					</PageRouter>
				) }
			</div>
		</ErrorBoundary>
	);
}
