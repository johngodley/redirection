import HttpCheckResponse from './response';
import './style.scss';

interface HttpCheckProps {
	url: string;
	desiredCode?: number;
	desiredTarget?: any;
}

export default function HttpCheck( { url, desiredCode = 0, desiredTarget = null }: HttpCheckProps ) {
	return <HttpCheckResponse url={ url } desiredCode={ desiredCode } desiredTarget={ desiredTarget } />;
}
