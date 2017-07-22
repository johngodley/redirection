/**
 * Internal dependencies
 */

import { MESSAGE_CLEAR_ERRORS, MESSAGE_CLEAR_NOTICES } from './type';

export const clearErrors = () => ( { type: MESSAGE_CLEAR_ERRORS } );
export const clearNotices = () => ( { type: MESSAGE_CLEAR_NOTICES } );
