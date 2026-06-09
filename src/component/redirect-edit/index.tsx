import { useState, useRef, useEffect, useCallback, useMemo } from 'react';
import { __ } from '@wordpress/i18n';
import { Button } from '@wp-plugin-components';
import TableRow from './table-row';
import RedirectSourceUrl from './source-url';
import RedirectSourceQuery from './source-query';
import RedirectGroup from './group';
import RedirectPosition from './position';
import RedirectTitle from './title';
import ActionCode from './action-code';
import ActionType from './action-type';
import MatchType from './match-type';
import MatchTarget from './match';
import ActionTarget from './action';
import { getWarningFromState, Warnings } from './warning';
import { useRedirectUpdate, useRedirectCreate, useGroupList } from 'lib/api/hooks';
import { useTableStore, useSettingsStore, useMessageStore } from 'stores';
import {
	ACTION_URL,
	MATCH_URL,
	MATCH_LOGIN,
	hasUrlTarget,
	getMatchState,
	hasTargetData,
	getDefaultItem,
	getCodeForActionType,
} from 'lib/redirect-constants';
import './style.scss';

interface MatchData {
	source: {
		flag_regex: boolean;
		flag_trailing: boolean;
		flag_case: boolean;
		flag_query: string;
	};
	options?: Record< string, unknown >;
}

interface UrlFlags {
	flag_case: boolean;
	flag_regex: boolean;
	flag_trailing: boolean;
}

interface RedirectItem {
	id?: number;
	url: string | string[];
	title: string;
	match_data?: MatchData | null;
	match_type: string;
	action_type: string;
	action_data: unknown;
	group_id?: number;
	action_code: number;
	position?: number;
}

export interface Group {
	id: number;
	name: string;
	moduleName: string;
	default?: boolean;
	rows?: Group[];
	redirects?: number;
	module_id?: number;
	enabled?: boolean;
}

interface RedirectEditState {
	url: string | string[];
	title: string;
	flag_regex: boolean;
	flag_trailing: boolean;
	flag_case: boolean;
	flag_query: string;
	match_type: string;
	action_type: string;
	action_code: number;
	action_data: Record< string, unknown >;
	options: Record< string, unknown >;
	group_id: number;
	position: number;
	warning: React.ReactNode[];
	advanced: boolean;
}

interface EditRedirectProps {
	item: RedirectItem;
	onCancel?: ( ev: React.FormEvent ) => void;
	saveButton?: string;
	childSave?: () => void;
	callback?: ( height: number ) => void;
	canSave?: ( isArray: boolean ) => boolean;
	autoFocus?: boolean;
	children?: React.ReactNode;
}

function EditRedirect( props: EditRedirectProps ) {
	const {
		item,
		onCancel,
		saveButton = __( 'Save', 'redirection' ),
		childSave,
		callback,
		canSave: canSaveCallback,
		autoFocus,
		children,
	} = props;

	// Get state from stores and queries
	const { data: groupData, isSuccess: hasLoadedGroups } = useGroupList( {} );
	const groups = useMemo( () => groupData?.items ?? [], [ groupData ] );
	const addTop = useTableStore( ( state ) => state.redirectsAddTop );
	const table = useTableStore( ( state ) => state.redirects );
	const { setRedirectsAddTop } = useTableStore();
	const settings = useSettingsStore( ( state ) => state.values );
	const addError = useMessageStore( ( state ) => state.addError );
	const autoTarget = settings?.auto_target || '';
	const flags = useMemo(
		() =>
			settings
				? {
						flag_case: settings.flag_case,
						flag_trailing: settings.flag_trailing,
						flag_regex: settings.flag_regex,
						flag_query: settings.flag_query,
				  }
				: {},
		[ settings ]
	);

	// Get mutations
	const { mutate: updateRedirect } = useRedirectUpdate();
	const { mutate: createRedirect } = useRedirectCreate();

	const ref = useRef< HTMLFormElement >( null );

	const getGroup = ( groupList: any[], group_id: number ): Group | undefined => {
		return groupList.find( ( g: any ) => g.id === group_id );
	};

	const getValidGroup = useCallback(
		( group_id: number ): number => {
			if ( getGroup( groups, group_id ) ) {
				return group_id;
			}

			if (
				table.filterBy &&
				typeof table.filterBy === 'object' &&
				'group' in table.filterBy &&
				table.filterBy.group &&
				parseInt( String( table.filterBy.group ), 10 ) > 0
			) {
				return parseInt( String( table.filterBy.group ), 10 );
			}

			if ( groups && groups.length > 0 ) {
				const def = groups.find( ( group: any ) => group.default );
				if ( def ) {
					return def.id;
				}

				return groups[ 0 ]!.id;
			}

			return 0;
		},
		[ groups, table.filterBy ]
	);

	const hasGroups = groups.length > 0;
	const hasNoGroups = hasLoadedGroups && ! hasGroups;

	const {
		url: initialUrl,
		match_data,
		match_type: initialMatchType,
		action_type: initialActionType,
		action_data: initialActionData,
		group_id: initialGroupId = 0,
		title: initialTitle,
		action_code: initialActionCode,
		position: initialPosition = 0,
	} = item;
	const {
		flag_regex: initialFlagRegex = false,
		flag_trailing: initialFlagTrailing = false,
		flag_case: initialFlagCase = false,
		flag_query: initialFlagQuery = 'exact',
	} = match_data?.source ?? {};

	const initialState: RedirectEditState = {
		url: initialUrl,
		title: initialTitle,
		flag_regex: initialFlagRegex,
		flag_trailing: initialFlagTrailing,
		flag_case: initialFlagCase,
		flag_query: initialFlagQuery,
		match_type: initialMatchType,
		action_type: initialActionType,
		action_code: initialActionCode,
		action_data: getMatchState( initialMatchType, initialActionData ),
		options: match_data?.options ?? {},
		group_id: getValidGroup( initialGroupId ),
		position: initialPosition,
		warning: [],
		advanced: false,
	};

	const [ state, setState ] = useState< RedirectEditState >( initialState );

	const canShowAdvanced = useCallback( () => {
		const { match_type, action_type, title, action_code } = state;
		return match_type === MATCH_URL && action_type === ACTION_URL && title === '' && action_code === 301;
	}, [ state ] );

	// Only set initial advanced state on mount
	useEffect( () => {
		setState( ( prev ) => ( {
			...prev,
			advanced: ! canShowAdvanced(),
		} ) );
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [] );

	// Update warnings when state changes
	useEffect( () => {
		setState( ( prev ) => ( {
			...prev,
			warning: getWarningFromState( prev ),
		} ) );
	}, [ state.url, state.action_type, state.match_type, state.action_data ] );

	useEffect( () => {
		if ( callback && ref.current ) {
			callback( ref.current.clientHeight );
		}
	}, [ state, callback ] );

	const reset = useCallback( () => {
		const defaultItem = getDefaultItem( '', state.group_id, flags );
		setState( ( prev ) => ( {
			...prev,
			url: defaultItem.url,
			title: '',
			flag_regex: defaultItem.match_data.source.flag_regex,
			flag_trailing: defaultItem.match_data.source.flag_trailing,
			flag_case: defaultItem.match_data.source.flag_case,
			flag_query: defaultItem.match_data.source.flag_query,
			match_type: defaultItem.match_type,
			action_type: defaultItem.action_type,
			action_code: defaultItem.action_code,
			action_data: defaultItem.action_data,
			options: defaultItem.match_data.options,
			position: 0,
			advanced: false,
			warning: [],
		} ) );
	}, [ state.group_id, flags ] );

	const onSave = useCallback(
		( ev: React.FormEvent ) => {
			ev.preventDefault();

			const {
				url,
				title,
				flag_regex,
				flag_trailing,
				flag_case,
				flag_query,
				match_type,
				action_type,
				group_id,
				action_code,
				position,
				action_data,
				options,
			} = state;
			const group_value = group_id > 0 ? group_id : groups[ 0 ]?.id;

			if ( group_value === undefined ) {
				addError(
					__(
						'Unable to create a redirect because no groups are available. Please reload the page.',
						'redirection'
					)
				);
				return;
			}

			const redirect: RedirectItem = {
				...( item.id ? { id: parseInt( String( item.id ), 10 ) } : {} ),
				url,
				title,
				match_data: {
					source: {
						flag_regex,
						flag_trailing,
						flag_case,
						flag_query,
					},
					...( options ? { options } : {} ),
				},
				match_type,
				action_type,
				...( position !== undefined ? { position } : {} ),
				...( group_value !== undefined ? { group_id: group_value } : {} ),
				action_code: typeof action_code === 'number' ? action_code : parseInt( String( action_code ), 10 ),
				action_data: getMatchState( match_type, action_data ),
			};

			if ( canSaveCallback && ! canSaveCallback( Array.isArray( url ) ) ) {
				return;
			}

			if ( redirect.id ) {
				updateRedirect( { id: redirect.id, ...redirect } as any );
			} else {
				createRedirect( redirect as any );
			}

			if ( onCancel ) {
				onCancel( ev );
			} else {
				reset();
			}

			if ( childSave ) {
				childSave();
			}
		},
		[
			state,
			groups,
			item.id,
			canSaveCallback,
			updateRedirect,
			createRedirect,
			onCancel,
			reset,
			childSave,
			addError,
		]
	);

	const onUpdateState = useCallback( ( newState: Partial< RedirectEditState > ) => {
		setState( ( prev ) => ( {
			...prev,
			...newState,
			warning: getWarningFromState( { ...prev, ...newState } ),
		} ) );
	}, [] );

	const onToggleAdvanced = useCallback(
		( ev: React.MouseEvent< HTMLButtonElement > ) => {
			ev.preventDefault();
			onUpdateState( { advanced: ! state.advanced } );
		},
		[ state.advanced, onUpdateState ]
	);

	const onSetGroup = useCallback( ( ev: React.ChangeEvent< HTMLSelectElement > ) => {
		setState( ( prev ) => ( { ...prev, group_id: parseInt( ev.target.value, 10 ) } ) );
	}, [] );

	const onFlagChange = useCallback(
		( newFlags: Record< string, any > ) => {
			const changed: Record< string, any > = {};

			Object.keys( flags )
				.filter( ( key ) => key !== 'flag_query' )
				.forEach( ( key ) => {
					changed[ key ] = false;
				} );

			onUpdateState( { ...changed, ...newFlags } );
		},
		[ flags, onUpdateState ]
	);

	const getInputState = useCallback( ( ev: React.ChangeEvent< HTMLInputElement > ) => {
		const { target } = ev;
		const value = target.type === 'checkbox' ? target.checked : target.value;

		return {
			[ target.name ]: value,
		};
	}, [] );

	const onChangeMatch = useCallback(
		( ev: React.ChangeEvent< HTMLInputElement | HTMLSelectElement > ) => {
			const newState: any = getInputState( ev as React.ChangeEvent< HTMLInputElement > );

			newState.action_data = getMatchState( newState.match_type, state.action_data );

			if ( newState.match_type === MATCH_LOGIN ) {
				newState.action_type = ACTION_URL;
			}

			onUpdateState( newState );
		},
		[ state.action_data, getInputState, onUpdateState ]
	);

	const onChange = useCallback(
		( ev: React.ChangeEvent< HTMLInputElement | HTMLSelectElement > ) => {
			onUpdateState( getInputState( ev as React.ChangeEvent< HTMLInputElement > ) );
		},
		[ getInputState, onUpdateState ]
	);

	const onChangeOption = useCallback( ( ev: React.ChangeEvent< HTMLInputElement > ) => {
		setState( ( prev ) => ( {
			...prev,
			options: {
				...prev.options,
				[ ev.target.name ]: ev.target.checked ?? ev.target.value,
			},
		} ) );
	}, [] );

	const onChangeActionType = useCallback(
		( ev: React.ChangeEvent< HTMLInputElement > ) => {
			const action_type = getInputState( ev ).action_type as string;

			onUpdateState( {
				action_type,
				action_code: getCodeForActionType( action_type ),
				action_data: getMatchState( state.match_type, state.action_data || {} ),
			} );
		},
		[ state.match_type, state.action_data, getInputState, onUpdateState ]
	);

	const onChangeActionData = useCallback(
		( ev: React.ChangeEvent< HTMLInputElement > ) => {
			const newState = {
				action_data: {
					...state.action_data,
					...getInputState( ev ),
				},
			};

			onUpdateState( newState );
		},
		[ state.action_data, getInputState, onUpdateState ]
	);

	const canSaveForm = useCallback( () => {
		const { match_type, action_type, action_data, url, group_id } = state;

		if ( ! hasGroups && group_id <= 0 ) {
			return false;
		}

		if ( url.length === 0 && ! autoTarget ) {
			return false;
		}

		if ( hasUrlTarget( action_type ) ) {
			return hasTargetData( match_type, action_data ) || autoTarget !== '';
		}

		return true;
	}, [ state, autoTarget, hasGroups ] );

	const {
		url,
		advanced,
		flag_regex,
		action_type,
		match_type,
		action_data,
		flag_query,
		group_id,
		position,
		title,
		action_code,
		options,
		warning,
	} = state;
	const warningsWithGroups = hasNoGroups
		? [ __( 'No groups are available. Reload the page before creating a redirect.', 'redirection' ), ...warning ]
		: warning;

	const renderOptions = () => {
		if ( ! advanced || ! [ 'url', 'random' ].includes( action_type ) ) {
			return null;
		}

		return (
			<>
				<input
					id="redirect-log-exclude"
					type="checkbox"
					name="log_exclude"
					checked={ Boolean( options.log_exclude ) }
					onChange={ onChangeOption }
				/>
				<label htmlFor="redirect-log-exclude">{ __( 'Exclude from logs', 'redirection' ) }</label>
			</>
		);
	};

	return (
		<form onSubmit={ onSave } ref={ ref }>
			<table className="redirect-edit inline-edit-row">
				<tbody>
					<RedirectSourceUrl
						url={ url }
						flags={ state }
						defaultFlags={ flags as UrlFlags }
						// eslint-disable-next-line jsx-a11y/no-autofocus
						autoFocus={ autoFocus ?? false }
						onFlagChange={ onFlagChange }
						onChange={ onChange }
					/>
					<RedirectSourceQuery
						query={ flag_query }
						regex={ flag_regex }
						onChange={ onChange as any }
						url={ url }
					/>
					{ advanced && (
						<>
							<RedirectTitle title={ title } onChange={ ( newTitle ) => onUpdateState( newTitle ) } />
							<MatchType matchType={ match_type } onChange={ onChangeMatch as any } />
							<MatchTarget
								matchType={ match_type }
								actionData={ action_data }
								onChange={ onChangeActionData as any }
							/>

							<TableRow title={ __( 'When matched', 'redirection' ) } className="redirect-edit__action">
								<ActionType
									actionType={ action_type }
									matchType={ match_type }
									onChange={ onChangeActionType as any }
								/>
								<ActionCode
									actionType={ action_type }
									actionCode={ action_code }
									onChange={ onChange as any }
								/>

								{ renderOptions() }
							</TableRow>
						</>
					) }

					<ActionTarget
						actionType={ action_type }
						matchType={ match_type }
						actionData={ action_data }
						onChange={ onChangeActionData as any }
					/>

					<TableRow title={ __( 'Group', 'redirection' ) } className="redirect-edit__group">
						<RedirectGroup
							groups={ groups as any }
							currentGroup={ group_id }
							onChange={ onSetGroup as any }
						/>
						{ advanced && <RedirectPosition position={ position } onChange={ onChange as any } /> }
					</TableRow>

					{ children && children }

					<TableRow>
						<div className="table-actions">
							<Button isPrimary isSecondary={ false } isSubmit disabled={ ! canSaveForm() }>
								{ saveButton }
							</Button>{ ' ' }
							&nbsp;
							{ onCancel && <Button onClick={ onCancel }>{ __( 'Cancel', 'redirection' ) }</Button> }
							{ addTop && ! onCancel && (
								<Button
									onClick={ ( ev ) => {
										ev.preventDefault();
										setRedirectsAddTop( false );
									} }
								>
									{ __( 'Close', 'redirection' ) }
								</Button>
							) }
							&nbsp;
							{ canShowAdvanced() && (
								<button
									type="button"
									onClick={ onToggleAdvanced }
									className="redirection-edit_advanced"
									title={ __( 'Show advanced options', 'redirection' ) }
								>
									<svg
										aria-hidden="true"
										role="img"
										focusable="false"
										xmlns="http://www.w3.org/2000/svg"
										width="20"
										height="20"
										viewBox="0 0 20 20"
									>
										<path d="M18 12h-2.18c-.17.7-.44 1.35-.81 1.93l1.54 1.54-2.1 2.1-1.54-1.54c-.58.36-1.23.63-1.91.79V19H8v-2.18c-.68-.16-1.33-.43-1.91-.79l-1.54 1.54-2.12-2.12 1.54-1.54c-.36-.58-.63-1.23-.79-1.91H1V9.03h2.17c.16-.7.44-1.35.8-1.94L2.43 5.55l2.1-2.1 1.54 1.54c.58-.37 1.24-.64 1.93-.81V2h3v2.18c.68.16 1.33.43 1.91.79l1.54-1.54 2.12 2.12-1.54 1.54c.36.59.64 1.24.8 1.94H18V12zm-8.5 1.5c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3 1.34 3 3 3z" />
									</svg>
								</button>
							) }
						</div>
					</TableRow>

					<Warnings warnings={ warningsWithGroups } />
				</tbody>
			</table>
		</form>
	);
}

export default EditRedirect;
