/* global Redirectioni10n, ajaxurl, document, alert */
"use strict";

/**
 * Saves data in a table to the server
 */
var Redirection_Ajax_Form = function( $, form, on_success ) {
	this.$ = $;
	this.form = form;
	this.on_success = on_success;
	this.LOADING_CLASS = 'loading';
};

Redirection_Ajax_Form.prototype.start = function() {
	var data = this.form.find( 'input, select' ).serialize();
	var url = this.form.data( 'url' );

	this.form.addClass( this.LOADING_CLASS );

	this.$.post( url, data, this.$.proxy( this.on_submit_form, this ), 'json' );
};

Redirection_Ajax_Form.prototype.on_submit_form = function( response ) {
	this.form.removeClass( this.LOADING_CLASS );
	this.on_success( response );
};

/**
 * Handles ajax fetching when a link in the table is clicked
 */
var Redirection_Ajax = function( $, container ) {
	this.$ = $;
	this.container = container;
	this.old_content = container.html();

	this.ERROR_CONTAINER = '.error-container';
	this.SAVE_BUTTON = 'input[name=save]';
	this.CANCEL_BUTTON = 'input[name=cancel]';
	this.FORM = 'table.edit';
	this.COLUMN_TYPE = '.column-type';
	this.COLUMN_MODULE = '.column-module';
};

Redirection_Ajax.prototype.start = function( ajax_data ) {
	this.container.find( this.ERROR_CONTAINER ).hide();
	this.show_loading();

	this.$.post( ajaxurl, ajax_data, this.$.proxy( this.on_ajax_response, this ), 'json' );
};

Redirection_Ajax.prototype.on_ajax_response = function( response ) {
	if ( this.show_error( response ) )
		return;

	this.container.html( response.html );
	this.container.find( this.SAVE_BUTTON ).on( 'click', this.$.proxy( this.on_save, this ) );
	this.container.find( this.CANCEL_BUTTON ).on( 'click', this.$.proxy( this.on_cancel, this ) );
};

Redirection_Ajax.prototype.show_loading = function() {
	this.container.prepend( '<div class="spinner" style="display: block"></div>' );
};

Redirection_Ajax.prototype.hide_loading = function() {
	this.container.find( '.spinner' ).remove();
};

Redirection_Ajax.prototype.show_error = function( response, error_container ) {
	var error = false;

	this.hide_loading();

	if ( parseInt( response, 10 ) <= -1 )
		error = Redirectioni10n.error_msg;
	else if ( response.error )
		error = response.error;

	if ( error ) {
		if ( typeof error_container !== 'undefined' && error_container )
			error_container.text( Redirectioni10n.error_msg ).show();
		else
			alert( Redirectioni10n.error_msg );

		return true;
	}

	return false;
};

Redirection_Ajax.prototype.on_save = function( ev ) {
	ev.preventDefault();

	this.form = new Redirection_Ajax_Form( this.$, this.$( ev.target ).closest( this.FORM ), this.$.proxy( this.on_save_success, this ) );
	this.form.start();
};

Redirection_Ajax.prototype.on_cancel = function( ev ) {
	ev.preventDefault();

	this.container.html( this.old_content );
};

Redirection_Ajax.prototype.on_save_success = function( response ) {
	this.form = null;

	if ( this.show_error( response, this.container.find( this.ERROR_CONTAINER ) ) )
		return;

	this.container.html( response.html );

	if ( response.code )
		this.set_column( this.COLUMN_TYPE, response.code );
	else if ( response.module )
		this.set_column( this.COLUMN_MODULE, response.module );
};

Redirection_Ajax.prototype.set_column = function( selector, content ) {
	var column = this.container.closest( 'tr' ).find( selector );

	column.html( content );
};

/**
 * Items in a table
 */
var Redirection_Items = function( $ ) {
	this.$ = $;
};

Redirection_Items.prototype.setup = function( table_items ) {
	this.$( table_items ).on( 'click', '.advanced-toggle', this.$.proxy( this.toggle_advanced_editing, this ) );
	this.$( table_items ).on( 'click', '.row-actions a.red-auto', this.$.proxy( this.perform_single_bulk_action, this ) );
	this.$( table_items ).on( 'click', '.row-actions a.red-ajax', this.$.proxy( this.perform_ajax_action, this ) );

	this.$( document ).on( 'change', 'select.change-user-agent', this.$.proxy( this.copy_user_agent, this ) );
};

Redirection_Items.prototype.copy_user_agent = function( ev ) {
	var select = this.$( ev.target );

	select.closest( 'td' ).find( 'input[type=text]' ).val( select.val() );
};

Redirection_Items.prototype.toggle_advanced_editing = function( ev ) {
	var edit_button = this.$( ev.target );

	ev.preventDefault();

	edit_button.toggleClass( 'advanced-toggled' );
	edit_button.closest( 'table' ).find( '.advanced' ).toggle();
};

Redirection_Items.prototype.perform_single_bulk_action = function( ev ) {
	var action = this.$( ev.target );

	ev.preventDefault();

	action.closest( 'tr' ).find( 'input[type=checkbox]' ).prop( 'checked', true );
	this.$( 'select[name=action]' ).find( 'option[value=' + action.data( 'action' ) + ']' ).prop( 'selected', true );
	this.$( '#doaction' ).click();
};

Redirection_Items.prototype.perform_ajax_action = function( ev ) {
	var container = this.$( ev.target ).closest( 'td' );
	var action = this.$( ev.target );
	var ajax_data = {
		action: action.data( 'action' ),
		_ajax_nonce: action.data( 'nonce' ),
		id: action.data( 'id' )
	};

	ev.preventDefault();

	if ( typeof this.ajax !== 'undefined' )
		delete this.ajax;

	this.ajax = new Redirection_Ajax( this.$, container );
	this.ajax.start( ajax_data );
};

/**
 * Connects the 'add redirect' link to the Redirection_Add form
 */

var Redirection_Logs = function( $, add_form, added_notice, source_url ) {
	this.$ = $;
	this.add_form = add_form;
	this.addded_notice = added_notice;
	this.source_url = source_url;
};

Redirection_Logs.prototype.setup = function( add_log ) {
	this.$( add_log ).on( 'click', this.$.proxy( this.on_add_log, this ) );
};

Redirection_Logs.prototype.on_add_log = function( ev ) {
	ev.preventDefault();

	this.$( this.added_notice ).hide();
	this.$( this.add_form ).show();

	// Copy details
	this.$( this.source_url ).val( this.$( ev.target ).attr( 'href' ) );
	this.$( 'html, body' ).scrollTop( this.$( this.add_form ).offset().top );
};


/**
 * Add a redirect form
 */
var Redirection_Add = function( $, target, add_to_screen ) {
	this.$ = $;
	this.target = target;
	this.add_to_screen = add_to_screen;

	this.ALT_CLASS = 'alternate';
	this.LOADING_CLASS = 'loading';
	this.LOADED_CLASS = 'loaded';
	this.LOADED_ERROR_CLASS = 'loaded-error';
	this.TEXT_FIELD = 'input[type=text]';
	this.ERROR_CONTAINER = '.red-error';
	this.ITEM_TABLE = 'table.items';
	this.EMPTY_TABLE = 'table tr.no-items';
};

Redirection_Add.prototype.setup = function( selector, add_form ) {
	this.add_form = add_form;

	this.$( selector ).on( 'change', this.$.proxy( this.on_change_type, this ) );
	this.$( add_form ).find( 'form' ).ajaxForm( {
		beforeSubmit: this.$.proxy( this.before_submit, this ),
		success: this.$.proxy( this.on_success, this ),
		dataType: 'json'
	} );
};

Redirection_Add.prototype.on_change_type = function( ev ) {
	var val = this.$( ev.target ).val();

	this.$( this.target ).toggle( val === 'url' || val === 'pass' );
};

Redirection_Add.prototype.before_submit = function() {
	this.$( this.add_form ).removeClass( this.LOADED_ERROR_CLASS + ' ' + this.LOADED_CLASS ).addClass( this.LOADING_CLASS );
};

Redirection_Add.prototype.on_success = function( response ) {
	var error = this.get_redirect_error( response );

	this.reset_form();

	if ( error )
		this.add_error( this.add_form, error );
	else if ( this.add_to_screen === true )
		this.add_redirect_to_list( this.add_form, response.html );
	else
		this.show_added_notice( this.add_form );
};

Redirection_Add.prototype.reset_form = function() {
	this.$( this.add_form ).find( this.TEXT_FIELD ).val( '' );
	this.$( this.add_form ).removeClass( this.LOADING_CLASS );
};

Redirection_Add.prototype.add_error = function( add_form, error ) {
	this.$( add_form ).addClass( this.LOADED_ERROR_CLASS );
	this.$( add_form ).find( this.ERROR_CONTAINER ).html( error );
};

Redirection_Add.prototype.add_redirect_to_list = function( add_form, html ) {
	this.$( this.ITEM_TABLE ).append( html );

	this.show_added_notice( add_form );
	this.set_alternate_rows( this.ITEM_TABLE + ' tr' );
	this.remove_empty_row( this.EMPTY_TABLE );
};

Redirection_Add.prototype.show_added_notice = function( add_form ) {
	this.$( add_form ).addClass( this.LOADED_CLASS );
};

Redirection_Add.prototype.remove_empty_row = function( empty_rows ) {
	this.$( empty_rows ).remove();
};

Redirection_Add.prototype.set_alternate_rows = function( table_rows ) {
	this.$( table_rows ).each( this.$.proxy( this.alt_rows, this ) );
};

Redirection_Add.prototype.alt_rows = function( pos, item ) {
	this.$( item ).removeClass( this.ALT_CLASS );

	if ( pos % 2 === 0 )
		this.$( item ).addClass( this.ALT_CLASS );
};

Redirection_Add.prototype.get_redirect_error = function( response ) {
	if ( parseInt( response, 10 ) === 0 || parseInt( response, 10 ) === -1 )
		return Redirectioni10n.error_msg;
	else if ( response.error )
		return response.error;
	return false;
};
