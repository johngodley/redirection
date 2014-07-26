var Redirection;

(function($) {
	Redirection = function( args ) {
		var opts = $.extend({
			ajaxurl: '',
			nonce:   '',
			are_you_sure: 'Are you sure?',
			none_selected: 'No items were selected',
			page: 0
		}, args);

		function do_items( type, command ) {
			var checked = $( '.item :checked' );
			if ( checked.length > 0 ) {
				if ( confirm( opts.are_you_sure ) ) {
					$( '#loading' ).show ();

					$.post( opts.ajaxurl, {
							_ajax_nonce: opts.nonce,
							action: 'red_' + type + '_' + command,
							checked: checked.serialize()
						},
						function() {
							window.location.reload();
						});
				}
			}
			else
				alert( opts.none_selected );

			return false;
		}

		function sort_order_save( type ) {
			if ( confirm( opts.are_you_sure ) ) {
				$( '#loading' ).show();

				$.post( opts.ajaxurl, {
						action: 'red_' + type + '_saveorder',
						page:   opts.page,
						_ajax_nonce: opts.nonce,
						items: $( '#items' ).sortable( 'serialize' )
					},
					function() {
						$( '#loading' ).hide ();
						$( '#toggle_sort_off' ).hide ();
						$( '#toggle_sort_on' ).show ();
						$( '#items' ).sortable( 'disable' );
						$( '#items li' ).removeClass( 'sortable' );
					});
			}

			return false;
		}

		function sort_order() {
			$( '#items' ).sortable();
			$( '#toggle_sort_on' ).hide();
			$( '#toggle_sort_off' ).show();
			$( '#items li' ).addClass( 'sortable' );
			return false;
		}

		function move_all( type ) {
			var checked = $( '.item :checked' );
			if ( checked.length > 0 ) {
				if ( confirm( opts.are_you_sure ) ) {
					$( '#loading' ).show ();

					$.post( opts.ajaxurl, {
							_ajax_nonce: opts.nonce,
							action: 'red_' + type + '_move',
							target: $( '#move' ).val(),
							checked: checked.serialize()
						},
						function() {
							window.location.reload();
						});
				}
			}
			else
				alert( opts.none_selected );

			return false;
		}

		function select_all() {
			$( '.item :checkbox' ).each(function () {
				this.checked = (this.checked ? '' : 'checked');
			});

			return false;
		}

		function delete_all( type ) {
			var checked = $( '.item :checked' );

			if ( checked.length > 0 ) {
				if ( confirm( opts.are_you_sure ) ) {
					var urltype = 'red_redirect_delete';

					if ( type == 'group' )
						urltype = 'red_group_delete';
					else if ( type == 'log' )
						urltype = 'red_log_delete';

					$( '#loading' ).show();

					$.post( opts.ajaxurl, {
							checked: checked.serialize(),
							action: urltype,
							_ajax_nonce: opts.nonce
						},
						function() {
							$( '#loading' ).hide();
							checked.each( function(pos, item) {
								$( '#item_' + $( item ).val() ).fadeOut();
							});
						});
				}
			}
			else
				alert( opts.none_selected );

			return false;
		}

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

		function edit_items( type ) {
			$( '.row-actions a.red-auto' ).on( 'click', function( ev ) {
					ev.preventDefault();
					$( this ).closest( 'tr' ).find( 'input[type=checkbox]' ).prop( 'checked', true );
					$( '#doaction' ).click();
			} );

			$( '.row-actions a.red-ajax' ).on( 'click', function( ev ) {
				var action = $( this ).data( 'action' );
				var container = $( this ).closest( 'td' );

				ev.preventDefault();

				$.post( opts.ajaxurl, {
					action: $( this ).data( 'action' ),
					nonce: $( this ).data( 'nonce' ),
					id: $( this ).data( 'id' )
				}, function( response ) {
					if (response == 0 || response == -1)
						alert('crap');
					else if (response.error)
						alert(response.error);

				container.html( response.html );
				}, 'json' );

			} );
		}

		function logs() {
			$( '.show-log' ).unbind( 'click' ).click( function() {
				var item = $( this ).parents( 'tr' )
				var href = this.href;

				// Set loading icon
				$( item ).find( '.info' ).html( opts.progress );

				// Load info
				$( item ).find( '.info' ).load( this.href, function() {
					// Setup cancel handler
					$( item ).find( '.info input[name=cancel]').click( function() {
						$( item ).find( '.info' ).load( href.replace( 'red_log_show', 'red_log_hide' ), function () {
							logs();
						});

						return false;
					});
				} );

				return false;
			});

			$( '#actionator' ).unbind( 'click' ).click( function() {
				if ( $( '#action2_select' ).val() == 'delete' )
					delete_all( 'log' );
				return false;
			});

			$( '.add-log' ).unbind( 'click' ).click( function( item ) {
				$( '#added' ).hide ();
				$( '#add' ).show ();

				// Copy details
				$( '#old' ).val( $( this ).attr( 'href' ) );
				return false;
			});

			$( '#cb input' ).unbind( 'click' ).click( function() {
				var checked = $( this ).attr( 'checked' );

				$( 'input.check' ).each( function( pos, item ) {
					$( item ).attr( 'checked', checked );
				});
			});
		}

		var api = {
			logs: logs,
			edit_items: edit_items,
			modules: modules
		};

		return api;
	}

	$( document ).ready( function() {
		$( '#support-annoy' ).animate( { opacity: 0.2, backgroundColor: 'red' } ).animate( { opacity: 1, backgroundColor: 'yellow' } );
	} );

})(jQuery);

function update_user_agent (item,box)
{
	jQuery('#user_agent_' + box).attr ('value', jQuery(item).attr ('value'));
}

function change_add_redirect (item)
{
	if (item.value == 'url' || item.value == 'pass')
		jQuery('#target').show ();
	else
		jQuery('#target').hide ();
}
