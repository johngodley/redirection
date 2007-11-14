function show_google (item)
{
  new Ajax.Updater (item, wp_base + 'google.php?old=' + old + '&new=' + newurl,
    { asynchronous: true,
     onLoading: function(request){form_loading(item)} });
}

function update_count ()
{
  if ($('redirections').childNodes.length >= 2)
  {
    if ($('redirections').childNodes.length >= 25)
      Element.show ('pager');
    Element.show ('save_order');
    Element.show ('redirections_header');
  }
  else
  {
    Element.hide ('save_order');
    Element.hide ('redirections_header');
  }
  
  if ($('redirections').childNodes.length < 25)
    Element.hide ('pager');
}

function save_redirect (item,form)
{
  var params = Form.serialize (form, true);
  params['url_new'] = escape (params['url_new']);
  params['old']     = escape (params['old']);
  
  new Ajax.Updater ('r_' + item, wp_base + 'ajax.php?cmd=save_redirect&id=' + item,
    {
      asynchronous: true, evalScripts: true,
      parameters: params,
      onLoading: function(request) { Element.show ('loading')},
      onComplete: function(request) { Element.hide ('loading'); new Effect.Pulsate ('r_' + item, { duration: 1.5, pulses: 2});}
    });
}

function cancel_redirect (item)
{
  new Ajax.Updater ('r_' + item, wp_base + 'ajax.php?cmd=cancel_redirect&id=' + item,
    {
      asynchronous: true, evalScripts: true,
      onLoading: function(request) { Element.show ('loading')},
      onComplete: function(request) { Element.hide ('loading'); }
    });
}

function show_redirect (item)
{
  new Ajax.Updater ('r_' + item, wp_base + 'ajax.php?cmd=show_redirect&id=' + item,
    {
      asynchronous: true, evalScripts: true,
      onLoading: function(request) { $('d_' + item).innerHTML = wp_progress;},
      onComplete: function(request) {  }
    });
}

function delete_redirect (item)
{
  new Effect.Pulsate ('r_' + item, { duration: 1.5, pulses: 2});
  
  if (confirm ("Are you sure you want to delete the redirection?"))
  {
    new Ajax.Request (wp_base + 'ajax.php?cmd=delete_redirect&id=' + item,
      {
        asynchronous: true, evalScripts: true,
        onSuccess: function(request) { Element.remove ('r_' + item);update_count ();},
        onLoading: function(request) { Element.show ('loading')},
        onComplete: function(request) { Element.hide ('loading'); }
      });
  }
}

function add_redirection (form,add_to_screen)
{
  var params = Form.serialize (form, true);
  params['new'] = escape (params['new']);
  params['old'] = escape (params['old']);
  
  new Ajax.Request (wp_base + 'ajax.php?cmd=add_redirect&id=0',
    {
      asynchronous: true, evalScripts: true,
      parameters: params,
      onSuccess: function(request)
      {
        if (add_to_screen == true)
          new Insertion.Bottom('redirections', request.responseText);
        Element.show ('added');
        Element.hide ('none')
      },
      onLoading: function(request) { Element.show ('loading')},
      onComplete: function(request) { Element.hide ('loading'); update_count (); }
    });
}

function save_order (url)
{
  if (confirm ('Are you sure you want to save the current order?'))
  {
    new Ajax.Request (wp_base + 'ajax.php?cmd=save_order&id=0&url=' + url,
      {
      asynchronous: true, evalScripts: true,
        parameters: Sortable.serialize ('redirections'),
        onLoading: function(request) { Element.show ('loading')},
        onComplete: function(request) { Element.hide ('loading'); }
      });
  }
}

function reset_redirect (item)
{
  if (confirm ('Are you sure you want to reset the statistics for that redirection?'))
  {
    new Ajax.Updater ('r_' + item, wp_base + 'ajax.php?cmd=reset_redirect&id=' + item,
      {
      asynchronous: true, evalScripts: true,
        onLoading: function(request) { Element.show ('loading')},
        onComplete: function(request) { Element.hide ('loading'); new Effect.Pulsate ('r_' + item, { duration: 1.5, pulses: 2})}
      });
  }
}

function add_404 (item)
{
  Element.hide ('added');
  
  $('old').value = $('u_' + item).href.gsub (/\w*:\/\/(.*?)\//,'/');
  new Effect.Pulsate ('submit');
  Element.scrollTo ('add');
}

function show_log (item)
{
  new Ajax.Updater ('r_' + item, wp_base + 'ajax.php?cmd=show_log&id=' + item,
    {
      asynchronous: true, evalScripts: true,
      onLoading: function(request) { Element.show ('loading')},
      onComplete: function(request) { Element.hide ('loading'); }
    });
}

function hide_log (item)
{
  new Ajax.Updater ('r_' + item, wp_base + 'ajax.php?cmd=hide_log&id=' + item,
    {
      asynchronous: true, evalScripts: true,
      onLoading: function(request) { Element.show ('loading')},
      onComplete: function(request) { Element.hide ('loading'); }
    });
}

function show_404 (item)
{
  new Ajax.Updater ('r_' + item, wp_base + 'ajax.php?cmd=show_404&id=' + item,
    {
      asynchronous: true, evalScripts: true,
      onLoading: function(request) { Element.show ('loading')},
      onComplete: function(request) { Element.hide ('loading'); }
    });
}

function hide_404 (item)
{
  new Ajax.Updater ('r_' + item, wp_base + 'ajax.php?cmd=hide_404&id=' + item,
    {
      asynchronous: true, evalScripts: true,
      onLoading: function(request) { Element.show ('loading')},
      onComplete: function(request) { Element.hide ('loading'); }
    });
}

function delete_log (item)
{
  new Effect.Pulsate ('r_' + item, { duration: 1.5, pulses: 2});
  
  if (confirm ("Are you sure you want to delete the 404 log?"))
  {
    new Ajax.Request (wp_base + 'ajax.php?cmd=delete_log&id=' + item,
      {
        asynchronous: true, evalScripts: true,
        onSuccess: function(request) { Element.remove ('r_' + item);update_count ();},
        onLoading: function(request) { Element.show ('loading')},
        onComplete: function(request) { Element.hide ('loading'); }
      });
  }
}

function add_new_url (item)
{
  new Insertion.After(item, '<br/><input style="width: 90%" type="text" name="new[]"/>');
}
