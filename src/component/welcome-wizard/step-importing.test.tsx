import { render, waitFor } from '@testing-library/react';
import { useImportRunner } from 'lib/api/hooks';
import StepImporting from './step-importing';

jest.mock( 'lib/api/hooks', () => ( {
	useImportRunner: jest.fn(),
} ) );

const mockUseImportRunner = useImportRunner as jest.MockedFunction< typeof useImportRunner >;

describe( 'StepImporting', () => {
	beforeEach( () => {
		jest.clearAllMocks();
	} );

	test( 'imports every selected importer in one request', async () => {
		const mutate = jest.fn();

		mockUseImportRunner.mockReturnValue( {
			mutate,
			isPending: false,
			isSuccess: false,
			isError: false,
		} as ReturnType< typeof useImportRunner > );

		render(
			<StepImporting
				step={ 4 }
				setStep={ jest.fn() }
				options={ { importers: [ 'wordpress-old-slugs', 'safe-redirect-manager' ] } }
			/>
		);

		await waitFor( () =>
			expect( mutate ).toHaveBeenCalledWith( {
				sourceType: 'plugin',
				mode: 'import',
				pluginId: [ 'wordpress-old-slugs', 'safe-redirect-manager' ],
				groupId: 1,
				duplicateMode: 'import',
			} )
		);
	} );

	test( 'skips the step if there are no importers', async () => {
		const mutate = jest.fn();
		const setStep = jest.fn();

		mockUseImportRunner.mockReturnValue( {
			mutate,
			isPending: false,
			isSuccess: false,
			isError: false,
		} as ReturnType< typeof useImportRunner > );

		render( <StepImporting step={ 4 } setStep={ setStep } options={ { importers: [] } } /> );

		await waitFor( () => expect( setStep ).toHaveBeenCalledWith( 5 ) );
		expect( mutate ).not.toHaveBeenCalled();
	} );
} );
