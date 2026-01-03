export interface Log {
	id: number;
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
}

export interface Error404 {
	id: number;
	created: string;
	url: string;
	agent: string;
	referrer: string;
	ip: string;
	domain: string;
}
