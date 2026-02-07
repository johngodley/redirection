/**
 * Module utilities
 */

interface Module {
	value: number;
	label: string;
}

const MODULES: Module[] = [
	{ value: 1, label: 'WordPress' },
	{ value: 2, label: 'Apache' },
	{ value: 3, label: 'Nginx' },
];

/**
 * Get all available modules as select options
 */
export function getModules(): Module[] {
	return MODULES;
}

/**
 * Get module name by ID
 * @param moduleId
 */
export function getModuleName( moduleId: number ): string {
	const module = MODULES.find( ( m ) => m.value === moduleId );
	return module ? module.label : 'WordPress';
}
