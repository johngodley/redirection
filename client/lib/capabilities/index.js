/* global Redirectioni10n */

export function has_capability( cap ) {
	return Redirectioni10n?.caps?.capabilities.indexOf( cap ) !== -1;
}

export function has_page_access( page ) {
	return Redirectioni10n?.caps?.pages.indexOf( page ) !== -1;
}

export const CAP_REDIRECT_MANAGE = 'redirection_cap_redirect_manage';
export const CAP_REDIRECT_ADD = 'redirection_cap_redirect_add';
export const CAP_REDIRECT_DELETE = 'redirection_cap_redirect_delete';
export const CAP_REDIRECT_ADVANCED = 'redirection_cap_redirect_advanced';

export const CAP_GROUP_MANAGE = 'redirection_cap_group_manage';
export const CAP_GROUP_ADD = 'redirection_cap_group_add';
export const CAP_GROUP_DELETE = 'redirection_cap_group_delete';

export const CAP_404_MANAGE = 'redirection_cap_404_manage';
export const CAP_404_DELETE = 'redirection_cap_404_delete';

export const CAP_LOG_MANAGE = 'redirection_cap_log_manage';
export const CAP_LOG_DELETE = 'redirection_cap_log_delete';

export const CAP_IO_MANAGE = 'redirection_cap_log_delete';

export const CAP_OPTION_MANAGE = 'redirection_cap_option_manage';

export const CAP_SUPPORT_MANAGE = 'redirection_cap_support_manage';

export const CAP_SITE_MANAGE = 'redirection_cap_site_manage';
