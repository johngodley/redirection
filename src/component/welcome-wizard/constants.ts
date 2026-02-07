export const STEP_WELCOME = 0;
export const STEP_BASIC = 1;
export const STEP_IMPORT = 2;
export const STEP_API = 3;
export const STEP_DATABASE = 4;
export const STEP_SAVE_IMPORT = 5;
export const STEP_FINISH = 6;

export type WizardStep =
	| typeof STEP_WELCOME
	| typeof STEP_BASIC
	| typeof STEP_IMPORT
	| typeof STEP_API
	| typeof STEP_DATABASE
	| typeof STEP_SAVE_IMPORT
	| typeof STEP_FINISH;
