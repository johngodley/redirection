export interface Importer {
	id: string;
	name: string;
	description: string;
	total: number;
}

export interface IpInfo {
	ip: string;
	country?: string;
	region?: string;
	city?: string;
	latitude?: number;
	longitude?: number;
	timezone?: string;
}

export interface UserAgentInfo {
	agent: string;
	browser?: string;
	version?: string;
	platform?: string;
	mobile?: boolean;
}

export interface HttpInfo {
	url: string;
	status: number;
	headers: Array< {
		name: string;
		value: string;
	} >;
	redirect_chain?: string[];
}
