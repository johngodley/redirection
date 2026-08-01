import { useEffect, useCallback, useRef } from 'react';
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

// Number of consecutive polls with no progress before treating the process as stuck.
// A real stage transition always changes `complete`, so this only fires when something
// (server-side or environmental) is preventing the upgrade from ever advancing or erroring.
const STUCK_THRESHOLD = 30;

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
	const { mutate: upgradeDatabaseMutation, mutateAsync: upgradeDatabaseMutationAsync } = useDatabaseUpgrade();
	const setDatabase = useSettingsStore( ( state ) => state.setDatabase );

	const stuckCount = useRef( 0 );
	const lastComplete = useRef< number | null >( null );

	const onRetry = useCallback( () => {
		stuckCount.current = 0;
		lastComplete.current = null;
		upgradeDatabaseMutation( 'retry' );
	}, [ upgradeDatabaseMutation ] );

	// Polls on its own schedule rather than being re-armed by a dependency-array change.
	// If the server ever returns the same status/result/complete twice in a row (e.g. a
	// stage that isn't persisting between requests), none of those values change - and an
	// effect that only reschedules when its dependencies change would silently stop polling
	// forever, leaving the user stuck with no error and no way to leave the page. Reading
	// fresh state from the store on every tick avoids that, and also lets us detect and
	// report a genuinely stuck upgrade instead of polling forever.
	useEffect( () => {
		if ( ! hasWork( status, result as any ) ) {
			stuckCount.current = 0;
			lastComplete.current = null;
			return;
		}

		let cancelled = false;
		let timer: ReturnType< typeof setTimeout >;

		async function tick() {
			const current = useSettingsStore.getState().database;

			if ( ! hasWork( current.status, current.result as any ) ) {
				return;
			}

			if ( lastComplete.current === current.complete ) {
				stuckCount.current += 1;
			} else {
				stuckCount.current = 0;
				lastComplete.current = current.complete;
			}

			if ( stuckCount.current >= STUCK_THRESHOLD ) {
				setDatabase( {
					result: 'error',
					reason: __(
						'The process made no progress for a while and may be stuck. You can retry, skip this stage, or stop the process.',
						'redirection'
					),
				} );
				return;
			}

			// Wait for this attempt to fully settle before scheduling the next one, so a slow
			// request can't cause overlapping/concurrent upgrade requests.
			try {
				await upgradeDatabaseMutationAsync( '' );
			} catch {
				// Failure is already reflected in the store by the mutation itself.
			}

			if ( ! cancelled ) {
				timer = setTimeout( tick, DELAY );
			}
		}

		timer = setTimeout( tick, DELAY );

		return () => {
			cancelled = true;
			clearTimeout( timer );
		};
	}, [ status, result, upgradeDatabaseMutationAsync, setDatabase ] );

	return (
		<div className="redirection-database">
			<PreventLeaveWarning
				message={ __( 'Leaving before the process has completed may cause problems.', 'redirection' ) }
				prevent={ result !== 'error' && result !== 'failed' && ! hasFinished( status ) }
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
