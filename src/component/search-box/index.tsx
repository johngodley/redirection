import { useState, useEffect } from 'react';
import DropdownButton from '@wp-plugin-components/dropdown-button';
import './style.scss';

interface SearchType {
	name: string;
	title: string;
}

interface SearchBoxProps {
	table: {
		filter: string;
	};
	onSearch: ( search: string, selected: string ) => void;
	searchTypes?: SearchType[];
	selected: Record< string, string >;
	disabled?: boolean;
	name?: string;
}

function getInitialValue( types: SearchType[] | undefined, initial: Record< string, string > ): string {
	if ( types ) {
		const found = types.find( ( item ) => initial[ item.name ] );

		if ( found ) {
			return initial[ found.name ] || '';
		}

		return '';
	}

	return '';
}

function SearchBox( { table, onSearch, searchTypes, selected, disabled = false, name = '' }: SearchBoxProps ) {
	const found = searchTypes?.find( ( item ) => selected[ item.name ] );
	const initialSearch = getInitialValue( searchTypes, selected );
	const initialSelected = found ? found.name : searchTypes?.[ 0 ]?.name || '';

	const [ search, setSearch ] = useState( initialSearch );
	const [ selectedType, setSelectedType ] = useState( initialSelected );
	const [ initial, setInitial ] = useState( initialSearch );

	useEffect( () => {
		const newInitial = getInitialValue( searchTypes, selected );

		if ( newInitial !== initial ) {
			const foundType = searchTypes?.find( ( item ) => selected[ item.name ] );
			const newSelected = foundType ? foundType.name : searchTypes?.[ 0 ]?.name || '';

			setInitial( newInitial );
			setSearch( newInitial );
			setSelectedType( newSelected );
		}
	}, [ searchTypes, selected, initial ] );

	const handleSearchChange = ( ev: React.ChangeEvent< HTMLInputElement > ) => {
		setSearch( ev.target.value );
	};

	const handleSubmit = ( ev?: React.FormEvent ) => {
		ev?.preventDefault();
		onSearch( search, selectedType );
	};

	const handleTypeChange = ( value: string ) => {
		setSelectedType( value );
		onSearch( search, value );
	};

	const isDisabled = disabled || ( search === '' && table.filter === '' );

	const dropdownOptions = searchTypes
		? searchTypes.map( ( item ) => ( {
				value: item.name,
				label: item.title,
		  } ) )
		: [];

	const selectedOption = searchTypes?.find( ( item ) => item.name === selectedType );
	const dropdownTitle = selectedOption ? selectedOption.title : '';

	return (
		<form onSubmit={ handleSubmit } className="redirect-searchbox">
			<input type="search" name="s" value={ search } onChange={ handleSearchChange } />

			{ searchTypes && (
				<DropdownButton
					options={ dropdownOptions }
					disabled={ disabled }
					title={ dropdownTitle }
					onSelect={ handleTypeChange }
					selected={ selectedType }
				/>
			) }
			{ ! searchTypes && <input type="submit" className="button" value={ name } disabled={ isDisabled } /> }
		</form>
	);
}

export default SearchBox;
