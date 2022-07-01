interface Redirectioni10n {
	pluginRoot: string;
}

declare global {
	interface Window {
		Redirectioni10n: Redirectioni10n;
		redirection: any;
	}
}

export {}
