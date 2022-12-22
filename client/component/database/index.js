/* global Redirectioni10n */
/**
 * External dependencies
 */

import { useEffect } from 'react';
import { sprintf, __ } from '@wordpress/i18n';
import { useDispatch, useSelector } from 'react-redux';
import { Line } from 'rc-progress';

/**
 * Internal dependencies
 */

import PreventLeaveWarning from 'component/prevent-leave';
import DatabaseApiError from './api-error';
import DatabaseError from './database-error';
import { Spinner } from 'wp-plugin-components';
import { STATUS_FAILED } from 'state/settings/type';
import { upgradeDatabase } from 'state/settings/action';
import './style.scss';

const DELAY = 1000;

function getTitle( status ) {
	if ( status === 'need-update' ) {
		return __( 'Upgrading Redirection', 'redirection' );
	}

	return __( 'Setting up Redirection', 'redirection' );
}

function hasWork( status, result ) {
	if ( result === 'error' ) {
		return false;
	}

	return status === 'need-install' || status === 'need-update';
}

function hasFinished( status ) {
	return status === 'finish-install' || status === 'finish-update';
}

export default function Database( { children } ) {
	const { status, complete = 0, reason, result } = useSelector( state => state.settings.database );
	const showLoading = result === 'ok' && !hasFinished( status );
	const dispatch = useDispatch();

	useEffect( () => {
		// Have we updated the status or time?
		if ( hasWork( status, result ) ) {
			// Start next call, after a slight pause to allow the server a bit of breathing room
			setTimeout( () => {
				onTry();
			}, DELAY );
		}
	}, [ complete ] );

	function onRetry() {
		dispatch( upgradeDatabase( 'retry' ) );
	}

	function onTry() {
		dispatch( upgradeDatabase() );
	}

	return (
		<div className="redirection-database">
			<PreventLeaveWarning
				message={ __( 'Leaving before the process has completed may cause problems.', 'redirection' ) }
				prevent={ result !== 'error' && result !== STATUS_FAILED }
			/>

			<h1>{ getTitle( status ) }</h1>

			<p>{ __( 'Please remain on this page until complete.', 'redirection' ) }</p>

			<div className="redirection-database_progress">
				<h3>
					{ sprintf( __( 'Progress: %(complete)d%%', 'redirection' ), { complete } ) }
				</h3>
				<Line percent={ complete } strokeWidth={ 4 } trailWidth={ 4 } strokeLinecap="square" />

				{ reason && result === 'ok' && <p>{ reason }</p> }

				{ showLoading && (
					<div className="redirection-database_spinner">
						<Spinner />
					</div>
				) }

				{ result === 'error' && <DatabaseError error={ reason } onRetry={ onRetry } /> }
				{ result === STATUS_FAILED && <DatabaseApiError error={ reason } onRetry={ onRetry } /> }

				{ hasFinished( status ) && children }
			</div>
		</div>
	);
}
