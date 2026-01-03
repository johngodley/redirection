import Help from './help';
import HttpTester from './http-tester';
import Status from './status';

function Support() {
	return (
		<>
			<Status />
			<HttpTester />
			<Help />
		</>
	);
}

export default Support;
