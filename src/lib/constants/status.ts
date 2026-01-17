/**
 * Common status constants used across the application
 */

// Table/data loading status
export const STATUS_IDLE = 'idle';
export const STATUS_LOADING = 'loading';
export const STATUS_COMPLETE = 'complete';
export const STATUS_ERROR = 'error';
export const STATUS_SAVING = 'saving';

// Database status
export const STATUS_NEED_INSTALL = 'need-install';
export const STATUS_NEED_UPDATE = 'need-update';
export const STATUS_FINISH_INSTALL = 'finish-install';
export const STATUS_FINISH_UPDATE = 'finish-update';

// API/HTTP status
export const STATUS_OK = 'ok';
export const STATUS_FAIL = 'fail';

// Plugin status
export const STATUS_GOOD = 'good';
export const STATUS_PROBLEM = 'problem';

export type LoadingStatus = typeof STATUS_IDLE | typeof STATUS_LOADING | typeof STATUS_COMPLETE | typeof STATUS_ERROR | typeof STATUS_SAVING;
export type DatabaseStatus = typeof STATUS_NEED_INSTALL | typeof STATUS_NEED_UPDATE | typeof STATUS_FINISH_INSTALL | typeof STATUS_FINISH_UPDATE;
export type ApiStatus = typeof STATUS_OK | typeof STATUS_FAIL | typeof STATUS_LOADING;
export type PluginStatus = typeof STATUS_GOOD | typeof STATUS_PROBLEM;
