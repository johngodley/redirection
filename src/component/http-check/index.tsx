import { useEffect } from 'react';
import { useHttpCheck, useClearHttp } from 'lib/api/hooks';
import HttpCheckResponse from './response';
import './style.scss';

interface HttpCheckProps {
	url: string;
	desiredCode?: number;
	desiredTarget?: any;
}

export default function HttpCheck( { url, desiredCode = 0, desiredTarget = null }: HttpCheckProps ) {
	const clearHttp = useClearHttp();

	useHttpCheck( url );

	useEffect( () => {
		return () => {
			clearHttp();
		};
	}, [ clearHttp ] );

	return <HttpCheckResponse url={ url } desiredCode={ desiredCode } desiredTarget={ desiredTarget } />;
}
