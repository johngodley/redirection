function update_count ()
{
  if ($('items').childNodes.length >= 2)
  {
    if ($('items').childNodes.length >= 25)
      Element.show ('pager');
    Element.show ('save_order');
    Element.show ('redirections_header');
  }
  else
  {
    Element.hide ('save_order');
    Element.hide ('redirections_header');
  }
  
  if ($('items').childNodes.length < 25)
    Element.hide ('pager');
}

// XXX update count for what???

function encode_data (params)
{
  params.each (function (pair)
  {
    if (typeof pair.value == 'object')
      params[pair.key].each (function (otherpair) { params[pair.key][otherpair.key] = escape (otherpair.value);});
    else
      params[pair.key] = escape (pair.value);
  });
  
  return params;
}

function save_redirect (item,form)
{
  var params = $H(Form.serialize (form, true));

  new Ajax.Updater ('item_' + item, wp_base + 'ajax.php?cmd=save_redirect&id=' + item,
    {
      asynchronous: true, evalScripts: true,
      parameters: encode_data (params),
      onLoading: function(request) { $('info_' + item).innerHTML = wp_progress;}
    });
}

function cancel_redirect (item)
{
  new Ajax.Updater ('item_' + item, wp_base + 'ajax.php?cmd=cancel_redirect&id=' + item,
    {
      asynchronous: true, evalScripts: true,
      onLoading: function(request) { $('info_' + item).innerHTML = wp_progress;}
    });
}

function show_redirect (item)
{
  new Ajax.Updater ('item_' + item, wp_base + 'ajax.php?cmd=show_redirect&id=' + item,
    {
      asynchronous: true, evalScripts: true,
      onLoading: function(request) { $('info_' + item).innerHTML = wp_progress;},
      onComplete: function(request) {  }
    });
}

function delete_redirect (item)
{
  new Effect.Pulsate ('item_' + item, { duration: 1.5, pulses: 2});
  
  if (confirm ("Are you sure you want to delete the redirection?"))
  {
    new Ajax.Request (wp_base + 'ajax.php?cmd=delete_redirect&id=' + item,
      {
        asynchronous: true, evalScripts: true,
        onSuccess: function(request) { Element.remove ('item_' + item);update_count ();},
        onLoading: function(request) { Element.show ('loading')},
        onComplete: function(request) { Element.hide ('loading'); }
      });
  }
}

function add_redirection (form,add_to_screen)
{
  var params = $H(Form.serialize (form, true));
  
  new Ajax.Request (wp_base + 'ajax.php?cmd=add_redirect&id=0',
    {
      asynchronous: true, evalScripts: true,
      parameters: params.each (function (pair) { params[pair.key] = escape (pair.value)}),
      onSuccess: function(request)
      {
        if (request.responseText.indexOf ('fade error') != -1)
          $('error').innerHTML = request.responseText;
        else
        {
          if (add_to_screen == true)
            new Insertion.Bottom('items', request.responseText);

          Element.hide ('error');
          Element.show ('added');
          Element.hide ('none')
        }
      },
      onLoading: function(request) { Element.show ('loading')},
      onComplete: function(request) { Element.hide ('loading'); update_count (); }
    });
}

function sort_order ()
{
  Sortable.create ('items', { ghosting: true });
  Element.hide ('toggle_sort_on');
  Element.show ('toggle_sort_off');
  new Effect.Pulsate ('sort');
  
  var elements = $('items').getElementsBySelector ('li');
  elements.each(function(item) { $(item).addClassName ('sortable')});
  return false;
}

function save_redirect_order (start)
{
  if (confirm ('Are you sure you want to save the current order?'))
  {
    new Ajax.Request (wp_base + 'ajax.php?cmd=save_redirect_order&id=' + start,
      {
        asynchronous: true, evalScripts: true,
        parameters: Sortable.serialize ('items'),
        onLoading: function(request) { Element.show ('loading')},
        onComplete: function(request)
        {
          Element.hide ('loading');
          Element.hide ('toggle_sort_off');
          Element.show ('toggle_sort_on');
          
          Sortable.destroy ('items');

          var elements = $('items').getElementsBySelector ('li');
          elements.each(function(item) { $(item).removeClassName ('sortable')});
          
          new Effect.Pulsate ('sort');
        }
      });
  }
  
  return false;
}


function add_log_item (item)
{
  Element.hide ('added');
  Element.show ('add');
  
  $('old').value = $('href_' + item).href.gsub (/\w*:\/\/(.*?)\//,'/');
  new Effect.Pulsate ('submit');
  Element.scrollTo ('add');
  return false;
}

function toggle_log (item)
{
  if ($('info_' + item).getElementsByTagName ('table').length == 0)
  {
    new Ajax.Updater ('info_' + item, wp_base + 'ajax.php?cmd=show_log&id=' + item,
      {
        asynchronous: true, evalScripts: true,
        onLoading: function(request) { $('info_' + item).innerHTML = wp_progress;}
      });
  }
  else
  {
    new Ajax.Updater ('info_' + item, wp_base + 'ajax.php?cmd=hide_log&id=' + item,
      {
        asynchronous: true, evalScripts: true,
        onLoading: function(request) { $('info_' + item).innerHTML = wp_progress;}
      });
  }
  
  return false;
}

function toggle_404 (item)
{
  if ($('info_' + item).getElementsByTagName ('table').length == 0)
  {
    new Ajax.Updater ('info_' + item, wp_base + 'ajax.php?cmd=show_404&id=' + item,
      {
        asynchronous: true, evalScripts: true,
        onLoading: function(request) { $('info_' + item).innerHTML = wp_progress;}
      });
  }
  else
  {
    new Ajax.Updater ('info_' + item, wp_base + 'ajax.php?cmd=hide_404&id=' + item,
      {
        asynchronous: true, evalScripts: true,
        onLoading: function(request) { $('info_' + item).innerHTML = wp_progress;}
      });
  }
  
  return false;
}

function add_new_url (item)
{
  new Insertion.After(item, '<br/><input style="width: 90%" type="text" name="new[]"/>');
}




function save_group_order (start)
{
  if (confirm ('Are you sure you want to save the current order?'))
  {
    new Ajax.Request (wp_base + 'ajax.php?cmd=save_group_order&id=' + start,
      {
        asynchronous: true, evalScripts: true,
        parameters: Sortable.serialize ('items'),
        onLoading: function(request) { Element.show ('loading')},
        onComplete: function(request)
        {
          Element.hide ('loading');
          Element.hide ('toggle_sort_off');
          Element.show ('toggle_sort_on');
          
          Sortable.destroy ('items');

          var elements = $('items').getElementsBySelector ('li');
          elements.each(function(item) { $(item).removeClassName ('sortable')});
          
          new Effect.Pulsate ('sort');
        }
      });
  }
}

function edit_group (item)
{
  new Ajax.Updater ('item_' + item, wp_base + 'ajax.php?cmd=edit_group&id=' + item,
    {
      asynchronous: true, evalScripts: true,
      onLoading: function(request) { $('info_' + item).innerHTML = wp_progress;}
    });
  return false;
}

function cancel_group (item)
{
  new Ajax.Updater ('item_' + item, wp_base + 'ajax.php?cmd=cancel_group&id=' + item,
    {
      asynchronous: true, evalScripts: true,
      onLoading: function(request) { $('info_' + item).innerHTML = wp_progress;}
    });
  return false;
}

function save_group (item,form)
{
  new Ajax.Updater ('item_' + item, wp_base + 'ajax.php?cmd=save_group&id=' + item,
    {
      asynchronous: true, evalScripts: true,
      parameters: Form.serialize (form),
      onLoading: function(request){ $('info_' + item).innerHTML = wp_progress;}
    });
  return false;
}

function select_all ()
{
  var elements = $('items').getElementsBySelector ('input');
  var todelete = '';
  
  elements.each (function(item)
  {
    item.checked = wp_red_select;
  });
  
  wp_red_select = !wp_red_select;
  return false;
}


function reset_items (type)
{
  var tochange = '', url;
  var elements = $('items').getElementsBySelector ('input');

  elements.each (function(item)
  {
    if (item.checked)
      tochange += item.value + '-';
  });
  
  if (tochange != '')
  {
    if (confirm ('Are you sure you want to reset statistics for these items?'))
    {
      if (type == 'group')
       url = wp_base + 'ajax.php?cmd=reset_groups&id=' + tochange;
      else
       url = wp_base + 'ajax.php?cmd=reset_redirects&id=' + tochange;

      new Ajax.Request (url,
      {
        asynchronous: true, evalScripts: true,
        onLoading: function(request) { Element.show ('loading')},
        onComplete: function(request) { window.location.reload (); }
      });
    }
  }
  else
    alert ('You have not selected any items');

  return false;
}

function toggle_items (type)
{
  var tochange = '', url;
  var elements = $('items').getElementsBySelector ('input');

  elements.each (function(item)
  {
    if (item.checked)
      tochange += item.value + '-';
  });
  
  if (tochange != '')
  {
    if (type == 'group')
     url = wp_base + 'ajax.php?cmd=toggle_groups&id=' + tochange;
    else
     url = wp_base + 'ajax.php?cmd=toggle_redirects&id=' + tochange;
 
    new Ajax.Request (url,
    {
      asynchronous: true, evalScripts: true,
      onLoading: function(request) { Element.show ('loading')},
      onComplete: function(request) { window.location.reload (); }
    });
  }
  else
    alert ('You have not selected any items');

  return false;
}

function move_items (type)
{
  var tochange = '', url;
  var elements = $('items').getElementsBySelector ('input');

  elements.each (function(item)
  {
    if (item.checked)
      tochange += item.value + '-';
  });
  
  if (tochange != '')
  {
    if (type == 'group')
      url = wp_base + 'ajax.php?cmd=move_groups&id=' + tochange;
    else
      url = wp_base + 'ajax.php?cmd=move_redirects&id=' + tochange;
    
    url += '&group=' + $('move').value;
    
    new Ajax.Request (url,
      {
        asynchronous: true, evalScripts: true,
        onSuccess: function(request)
        {
          elements.each (function(item)
          {
            if (item.checked)
              Element.remove ('item_' + item.value);
          });
        },
        onLoading: function(request) { Element.show ('loading')},
        onComplete: function(request) { Element.hide ('loading'); }
      });
  }
  else
    alert ('You have not selected any items');
  return false;
}

function delete_items (type)
{
  var tochange = '', url;
  var elements = $('items').getElementsBySelector ('input');

  elements.each (function(item)
  {
    if (item.checked)
      tochange += item.value + '-';
  });
  
  if (tochange != '')
  {
    if (confirm ('Are you sure this is what you want to do?'))
    {
      if (type == 'group')
        url = wp_base + 'ajax.php?cmd=delete_groups&id=' + tochange;
      else if (type == 'log')
        url = wp_base + 'ajax.php?cmd=delete_logs&id=' + tochange;
      else
        url = wp_base + 'ajax.php?cmd=delete_items&id=' + tochange;
      
      new Ajax.Request (url,
        {
          asynchronous: true, evalScripts: true,
          onSuccess: function(request)
          {
            elements.each (function(item)
            {
              if (item.checked)
                Element.remove ('item_' + item.value);
            });
          },
          onLoading: function(request) { Element.show ('loading')},
          onComplete: function(request) { Element.hide ('loading'); }
        });
    }
  }
  else
    alert ('You have not selected any items');
  return false;
}

function delete_module (item)
{
  if (confirm ("Are you sure you want to delete this?"))
  {
    new Ajax.Request (wp_base + 'ajax.php?cmd=delete_module&id=' + item,
      {
        asynchronous: true, evalScripts: true,
        onSuccess: function(request) { Element.remove ('item_' + item);},
        onLoading: function(request) { $('info_' + item).innerHTML = wp_progress;}
      });
  }
  return false;
}

function reset_module (item)
{
  if (confirm ("Are you sure you want to reset this?"))
  {
    new Ajax.Updater ('item_' + item, wp_base + 'ajax.php?cmd=reset_module&id=' + item,
      {
        asynchronous: true, evalScripts: true,
        onLoading: function(request) { $('info_' + item).innerHTML = wp_progress;}
      });
  }
  return false;
}

function edit_module (item)
{
  new Ajax.Updater ('item_' + item, wp_base + 'ajax.php?cmd=edit_module&id=' + item,
    {
      asynchronous: true, evalScripts: true,
      onLoading: function(request) { $('info_' + item).innerHTML = wp_progress;}
    });
  return false;
}

function save_module (item,form)
{
  new Ajax.Updater ('item_' + item, wp_base + 'ajax.php?cmd=save_module&id=' + item,
    {
      asynchronous: true, evalScripts: true,
      parameters: Form.serialize (form),
      onLoading: function(request) { $('info_' + item).innerHTML = wp_progress;}
    });
  return false;
}

function cancel_module (item)
{
  new Ajax.Updater ('item_' + item, wp_base + 'ajax.php?cmd=cancel_module&id=' + item,
    {
      asynchronous: true, evalScripts: true,
      onLoading: function(request) { $('info_' + item).innerHTML = wp_progress;}
    });
  return false;
}

function update_user_agent (item,box)
{
  $('user_agent_' + box).value = $(item).value;
}


function change_add_redirect (item)
{
  if (item.value == 'url' || item.value == 'pass')
    Element.show ('target');
  else
    Element.hide ('target');
}