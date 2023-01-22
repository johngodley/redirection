/* global REDIRECTION_VERSION, Redirectioni10n */
/**
 * External dependencies
 */

import { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { useDispatch, useSelector } from 'react-redux';

/**
 * Internal dependencies
 */

import WelcomeWizard from 'component/welcome-wizard';
import DatabaseUpdate from './database-update';
import { Snackbar, Menu, ErrorBoundary, Error } from 'wp-plugin-components';
import PageRouter from 'wp-plugin-lib/page-router';
import { getPluginPage } from 'wp-plugin-lib/wordpress-url';
import DebugReport from './debug';
import ErrorDetails from './error-details';
import CrashHandler from './crash-handler';
import PageContent from './page-content';
import { getErrorLinks, getErrorDetails } from 'lib/error-links';
import CacheDetect from './cache-detect';
import UpdateNotice from './update-notice';
import { clearErrors, clearNotices } from 'state/message/action';
import { addToTop, setTable as setRedirectTable } from 'state/redirect/action';
import { setTable as setErrorTable } from 'state/error/action';
import { setTable as setGroupTable } from 'state/group/action';
import { setTable as setLogTable } from 'state/log/action';
import { getInitialError } from 'state/error/initial';
import { getInitialLog } from 'state/log/initial';
import { getInitialGroup } from 'state/group/initial';
import { getInitialRedirect } from 'state/redirect/initial';
import { showUpgrade } from 'state/settings/action';
import { has_capability, has_page_access, CAP_REDIRECT_ADD } from 'lib/capabilities';
import './style.scss';

const getTitles = () => ( {
	redirect: __( 'Redirections', 'redirection' ),
	site: __( 'Site', 'redirection' ),
	groups: __( 'Groups', 'redirection' ),
	io: __( 'Import/Export', 'redirection' ),
	log: __( 'Logs', 'redirection' ),
	'404s': __( '404 errors', 'redirection' ),
	options: __( 'Options', 'redirection' ),
	support: __( 'Support', 'redirection' ),
} );

const getMenu = () =>
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
	const dispatch = useDispatch();
	const {
		errors,
		notices,
		databaseStatus,
		showDatabase,
		inProgress,
		pluginUpdate,
	} = useSelector( state => {
		return {
			errors: state.message.errors,
			notices: state.message.notices,
			databaseStatus: state.settings.database.status,
			inProgress: state.settings.database.inProgress,
			showDatabase: state.settings.showDatabase,
			pluginUpdate: state.settings.values.plugin_update,
		}
	} );
	const [ page, setPage ] = useState( getPluginPage( ALLOWED_PAGES ) );

	function changePage( page ) {
		setPage( page === '' ? 'redirect' : page );

		if ( page === '404s' ) {
			dispatch( setErrorTable( getInitialError().table ) );
		} else if ( page === 'log' ) {
			dispatch( setLogTable( getInitialLog().table ) );
		} else if ( page === '' ) {
			dispatch( setRedirectTable( getInitialRedirect().table ) );
		} else if ( page === 'groups' ) {
			dispatch( setGroupTable( getInitialGroup().table ) );
		}
	}

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
			<div className="wrap redirection">
				{ needsUpgrader && (
					<DatabaseUpdate
						onShowUpgrade={ () => dispatch( showUpgrade() ) }
						showDatabase={ showDatabase }
					/>
				) }

				{ !inProgress && databaseStatus !== 'finish-update' && !showDatabase && (
					<PageRouter
						page={ page }
						setPage={ setPage }
						onPageChange={ () => dispatch( clearErrors() ) }
						allowedPages={ ALLOWED_PAGES }
						baseUrl="?page=redirection.php"
						defaultPage="redirect"
					>
						<h1 className="wp-heading-inline">{ getTitles()[ page ] }</h1>

						{ page === 'redirect' && has_capability( CAP_REDIRECT_ADD ) && (
							<button type="button" onClick={ () => dispatch( addToTop( true ) ) } className="page-title-action">
								{ __( 'Add New', 'redirection' ) }
							</button>
						) }

						<UpdateNotice />

						<Menu
							onChangePage={ changePage }
							currentPage={ page }
							menu={ getMenu() }
							home="redirect"
							urlBase={ Redirectioni10n.pluginRoot }
						/>

						<Error
							errors={ errors }
							onClear={ () => dispatch( clearErrors() ) }
							renderDebug={ DebugReport }
							details={ getErrorDetails() }
							links={ getErrorLinks() }
							locale="redirection"
						>
							<ErrorDetails />
						</Error>

						<PageContent page={ page } />

						<Snackbar notices={ notices } onClear={ () => dispatch( clearNotices() ) } snackBarViewText={ __( 'View notice', 'redirection' ) } />
					</PageRouter>
				) }
			</div>
		</ErrorBoundary>
	);
}
