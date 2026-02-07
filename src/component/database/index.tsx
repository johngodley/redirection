import { useEffect, useCallback } from 'react';
import { sprintf, __ } from '@wordpress/i18n';
import { Line } from 'rc-progress';
import PreventLeaveWarning from 'component/prevent-leave';
import DatabaseApiError from './api-error';
import DatabaseError from './database-error';
import { Spinner } from '@wp-plugin-components';
import { useDatabaseUpgrade } from 'lib/api/hooks/use-settings';
import { useSettingsStore } from 'stores';
import './style.scss';

const DELAY = 1000;

type DatabaseStatus = 'need-install' | 'need-update' | 'finish-install' | 'finish-update' | string;
type DatabaseResult = 'ok' | 'error' | 'failed';

interface DatabaseProps {
	children?: React.ReactNode;
}

function getTitle( status: DatabaseStatus ): string {
	if ( status === 'need-update' ) {
		return __( 'Upgrading Redirection', 'redirection' );
	}

	return __( 'Setting up Redirection', 'redirection' );
}

function hasWork( status: DatabaseStatus, result: DatabaseResult ): boolean {
	if ( result === 'error' || result === 'failed' ) {
		return false;
	}

	return status === 'need-install' || status === 'need-update';
}

function hasFinished( status: DatabaseStatus ): boolean {
	return status === 'finish-install' || status === 'finish-update';
}

export default function Database( { children }: DatabaseProps ) {
	const database = useSettingsStore( ( state ) => state.database );
	const { status, reason, result, complete } = database;
	const showLoading = result === 'ok' && ! hasFinished( status );
	const { mutate: upgradeDatabaseMutation } = useDatabaseUpgrade();

	const onRetry = useCallback( () => {
		upgradeDatabaseMutation( 'retry' );
	}, [ upgradeDatabaseMutation ] );

	const onTry = useCallback( () => {
		upgradeDatabaseMutation( '' );
	}, [ upgradeDatabaseMutation ] );

	useEffect( () => {
		if ( hasWork( status, result as any ) ) {
			setTimeout( () => {
				onTry();
			}, DELAY );
		}
	}, [ complete, status, result, onTry ] );

	return (
		<div className="redirection-database">
			<PreventLeaveWarning
				message={ __( 'Leaving before the process has completed may cause problems.', 'redirection' ) }
				prevent={ result !== 'error' && result !== 'failed' }
			/>

			<h1>{ getTitle( status ) }</h1>

			<p>{ __( 'Please remain on this page until complete.', 'redirection' ) }</p>

			<div className="redirection-database_progress">
				<h3>
					{
						// translators: %(complete)d is percentage complete as a number
						sprintf( __( 'Progress: %(complete)d%%', 'redirection' ), { complete } )
					}
				</h3>
				<Line percent={ complete } strokeWidth={ 4 } trailWidth={ 4 } strokeLinecap="square" />

				{ reason && result === 'ok' && <p>{ reason }</p> }

				{ showLoading && (
					<div className="redirection-database_spinner">
						<Spinner />
					</div>
				) }

				{ result === 'error' && <DatabaseError error={ reason || '' } onRetry={ onRetry } /> }
				{ result === 'failed' && <DatabaseApiError error={ reason || '' } onRetry={ onRetry } /> }

				{ hasFinished( status ) && children }
			</div>
		</div>
	);
}
