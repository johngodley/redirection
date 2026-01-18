export interface Log {
	id: number | string; // string when grouped
	created: string;
	url: string;
	sent_to: string;
	agent: string;
	referrer: string;
	ip: string;
	module: string;
	redirect_id: number;
	request_method: string;
	request_data?: Record< string, any >;
	http_code: number;
	// Additional fields for grouped results
	domain?: string | null;
	redirect_by?: string | null;
	count?: number;
}

export interface Error404 {
	id: number | string; // string when grouped
	created: string;
	url: string;
	agent: string;
	referrer: string;
	ip: string;
	domain: string;
	// Additional fields for grouped results
	count?: number;
}
