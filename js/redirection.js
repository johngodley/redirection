var Redirection;

(function($) {
	Redirection_Items = function() {
		function edit_items() {
			$( 'table.items' ).on( 'click', '.row-actions a.red-auto', function( ev ) {
				ev.preventDefault();

				$( this ).closest( 'tr' ).find( 'input[type=checkbox]' ).prop( 'checked', true );
				$( 'select[name=action]' ).find( 'option[value=' + $( this ).data( 'action' ) + ']' ).prop( 'selected', true );
				$( '#doaction' ).click();
			} );

			$( 'table.items' ).on( 'click', '.row-actions a.red-ajax', function( ev ) {
				var action = $( this ).data( 'action' );
				var container = $( this ).closest( 'td' );

				ev.preventDefault();

				container.data( 'cancel', container.html() );

				show_loading( container.find( '.row-actions' ) );

				$.post( ajaxurl, {
					action: $( this ).data( 'action' ),
					nonce: $( this ).data( 'nonce' ),
					id: $( this ).data( 'id' )
				}, function( response ) {
					if ( show_error( response ) )
						return;

					container.html( response.html );
					connect_redirect_edit( container );
				}, 'json' );
			} );

			$( document ).on( 'change', 'select.change-user-agent', function( ev ) {
				$( this ).closest( 'td' ).find( 'input[type=text]' ).val( $( this ).val() );
			} );
		}

		function show_loading( container ) {
			container.replaceWith( '<div class="spinner" style="display: block"></div>' );
		}

		function show_error( response ) {
			if (response == 0 || response == -1) {
				alert( Redirectioni10n.error_msg );
				return true;
			}
			else if (response.error) {
				alert( response.error );
				return true;
			}

			return false;
		}

		function connect_redirect_edit( container ) {
			container.find( 'input[name=save]' ).on( 'click', function( ev ) {
				var form = $( this ).closest( 'table.edit' );

				ev.preventDefault();

				form.addClass( 'loading' );

				$.post( form.data( 'url' ), form.find( 'input, select' ).serialize(), function( response ) {
					if ( show_error( response ) )
						return;

					container.html( response.html );
					container.closest( 'tr' ).find( '.column-type' ).html( response.code );
				}, 'json' );
			} );

			container.find( 'input[name=cancel]' ).on( 'click', function( ev ) {
				ev.preventDefault();

				container.html( container.data( 'cancel' ) );
				container.data( 'cancel', null );
			} );
		}

		edit_items();
	};

	Redirection_Modules = function() {
		function modules() {
			// Edit module
			$( 'a.edit' ).unbind( 'click' ).click( function() {
				return form_loader( this, 'tr', modules );
			} );

			// Reset links
			$( 'a.reset' ).unbind( 'click' ).click( function() {
				var item = $( this ).parents( 'tr' )
				var href = this.href;

				$( item ).find( '.operations div' ).hide();
				$( item ).find( '.loader' ).show();
				$( item ).load( this.href, function() {
					modules();
				});

				return false;
			});

			// Delete links
			$( 'a.rdelete' ).unbind( 'click' ).click( function() {
				var item = $( this ).parents( 'tr' )

				$( item ).find( '.operations a' ).hide();
				$( item ).find( '.loader' ).show();
				$.get( this.href, function() {
					$( item ).fadeOut();
				});

				return false;
			});
		}

		modules();
	};

	Redirection_Logs = function() {
		function logs() {
			$( '.add-log' ).unbind( 'click' ).click( function( item ) {
				$( '#added' ).hide ();
				$( '#add' ).show ();

				// Copy details
				$( '#old' ).val( $( this ).attr( 'href' ) );
				return false;
			} );
		}

		logs();
	};

	Redirection_Add = function( selector, target, add_to_screen ) {
		$( selector ).on( 'change', function() {
			if ( $( this ).val() == 'url' || $( this ).val() == 'pass' )
				$( target ).show();
			else
				$( target ).hide();
		} );

		$( '#new-redirection' ).ajaxForm( {
			beforeSubmit: function () {
				$( '#loading' ).show ();
				$( '#error' ).hide();
				$( '#added' ).hide();
			},
			success: function( response ) {
				$( '#loading' ).hide ();

				if ( response == 0 || response == -1 )
					$( '#error' ).html( Redirectioni10n.error_msg );
				else if ( response.error )
					$( '#error' ).html( response.error );
				else {
					if ( add_to_screen === true ) {
						$( 'table.items' ).append( response.html );
						$( 'table.items tr' ).each( function( pos, item ) {
							$( item ).removeClass( 'alternate' );
							if ( pos % 2 == 0 )
								$( item ).addClass( 'alternate' );
						} );
					}

					$( '#error' ).hide();
					$( '#added' ).show();
					$( '#none' ).hide();
				}
			},
			dataType: 'json'
		});
	};
} )( jQuery );
