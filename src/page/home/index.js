/* global REDIRECTION_VERSION, Redirectioni10n */
/**
 * External dependencies
 */

import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */

import WelcomeWizard from '../../component/welcome-wizard';
import DatabaseUpdate from './database-update';
import { Snackbar, Menu, ErrorBoundary, Error } from '@wp-plugin-components';
import { PageRouter } from '@wp-plugin-lib';
import { getPluginPage } from '../../lib/wordpress-url';
import DebugReport from './debug';
import ErrorDetails from './error-details';
import CrashHandler from './crash-handler';
import PageContent from './page-content';
import { getErrorLinks, getErrorDetails } from '../../lib/error-links';
import CacheDetect from './cache-detect';
import UpdateNotice from './update-notice';
import { clearErrors, clearNotices } from '../../state/message/action';
import { addToTop, setTable as setRedirectTable } from '../../state/redirect/action';
import { setTable as setErrorTable } from '../../state/error/action';
import { setTable as setGroupTable } from '../../state/group/action';
import { setTable as setLogTable } from '../../state/log/action';
import { getInitialError } from '../../state/error/initial';
import { getInitialLog } from '../../state/log/initial';
import { getInitialGroup } from '../../state/group/initial';
import { getInitialRedirect } from '../../state/redirect/initial';
import { showUpgrade } from '../../state/settings/action';
import { has_capability, has_page_access, CAP_REDIRECT_ADD } from '../../lib/capabilities';
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

function Home( props ) {
	const {
		onClearErrors,
		errors,
		onClearNotices,
		notices,
		onAdd,
		databaseStatus,
		onShowUpgrade,
		showDatabase,
		result,
		inProgress,
		pluginUpdate,
	} = props;
	const [ page, setPage ] = useState( getPluginPage( ALLOWED_PAGES ) );

	function changePage( page ) {
		const { onSet404Table, onSetLogTable, onSetRedirectTable, onSetGroupTable } = props;

		setPage( page === '' ? 'redirect' : page );

		if ( page === '404s' ) {
			onSet404Table( getInitialError().table );
		} else if ( page === 'log' ) {
			onSetLogTable( getInitialLog().table );
		} else if ( page === '' ) {
			onSetRedirectTable( getInitialRedirect().table );
		} else if ( page === 'groups' ) {
			onSetGroupTable( getInitialGroup().table );
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
						onShowUpgrade={ onShowUpgrade }
						showDatabase={ showDatabase }
						result={ result }
					/>
				) }

				{ ! inProgress && databaseStatus !== 'finish-update' && ! showDatabase && (
					<PageRouter
						page={ page }
						setPage={ setPage }
						onPageChange={ onClearErrors }
						allowedPages={ ALLOWED_PAGES }
						baseUrl="?page=redirection.php"
						defaultPage="redirect"
					>
						<h1 className="wp-heading-inline">{ getTitles()[ page ] }</h1>

						{ page === 'redirect' && has_capability( CAP_REDIRECT_ADD ) && (
							<button type="button" onClick={ onAdd } className="page-title-action">
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
							onClear={ onClearErrors }
							renderDebug={ DebugReport }
							details={ getErrorDetails() }
							links={ getErrorLinks() }
							locale="redirection"
						>
							<ErrorDetails />
						</Error>

						<PageContent page={ page } />

						<Snackbar notices={ notices } onClear={ onClearNotices } snackBarViewText={ __( 'View notice', 'redirection' ) } />
					</PageRouter>
				) }
			</div>
		</ErrorBoundary>
	);
}

function mapDispatchToProps( dispatch ) {
	return {
		onClearErrors: () => {
			dispatch( clearErrors() );
		},
		onAdd: () => {
			dispatch( addToTop( true ) );
		},
		onSet404Table: ( table ) => {
			dispatch( setErrorTable( table ) );
		},
		onSetLogTable: ( table ) => {
			dispatch( setLogTable( table ) );
		},
		onSetGroupTable: ( table ) => {
			dispatch( setGroupTable( table ) );
		},
		onSetRedirectTable: ( table ) => {
			dispatch( setRedirectTable( table ) );
		},
		onShowUpgrade: () => {
			dispatch( showUpgrade() );
		},
		onClearNotices: () => {
			dispatch( clearNotices() );
		},
	};
}

function mapStateToProps( state ) {
	const {
		message: { errors, notices },
		settings: { showDatabase, values },
	} = state;
	const { status: databaseStatus, result, inProgress } = state.settings.database;

	return {
		errors,
		notices,
		showDatabase,
		databaseStatus,
		result,
		inProgress,
		pluginUpdate: values.plugin_update,
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( Home );
