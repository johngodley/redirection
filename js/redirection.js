function editItems (cmd)
{
  jQuery('a.redirection-edit').click(function (item)
  {
    var redirect = jQuery(item.target).parent ().parent ();   
    var itemid   = jQuery(redirect).attr ('id').replace (/\w*_/, '');
    
    jQuery('#info_' + itemid).html (wp_progress);
    jQuery(redirect).load (wp_base + 'ajax.php?cmd=' + cmd + '&id=' + itemid);
    return false;
  });
}

function show_redirect (item)
{
  jQuery('#info_' + item).html (wp_progress);
  jQuery('#item_' + item).load (wp_base + 'ajax.php?cmd=show_redirect&id=' + item, {}, function () { editItems ('edit_redirect'); });
  return false;
}

// Select functions

function select_all ()
{
  jQuery('.item :checkbox').each (function ()
  {
    this.checked = (this.checked ? '' : 'checked');
  });

  return false;
}

function reset_items (type,nonce)
{
  var checked = jQuery('.item :checked');
  if (checked.length > 0)
  {
    if (confirm (wp_are_you_sure))
    {
      jQuery('#loading').show ();
      jQuery.post (wp_base + 'ajax.php?id=0&cmd=' + (type == 'group' ? 'reset_groups' : 'reset_redirects') + '&_ajax_nonce=' + nonce, checked.serialize (), function () { window.location.reload ();});
    }
  }
  else
    alert (wp_none_select);
  
  return false;
}

function toggle_items (type)
{
  var checked = jQuery('.item :checked');
  if (checked.length > 0)
  {
    if (confirm (wp_are_you_sure))
    {
      jQuery('#loading').show ();
      jQuery.post (wp_base + 'ajax.php?id=0&cmd=' + (type == 'group' ? 'toggle_groups' : 'toggle_redirects'), checked.serialize (), function () { window.location.reload ()});
    }
  }
  else
    alert (wp_none_select);
  
  return false;
}

function move_items (type,nonce)
{
  var checked = jQuery('.item :checked');
  if (checked.length > 0)
  {
    if (confirm (wp_are_you_sure))
    {
      jQuery('#loading').show ();
      jQuery.post (wp_base + 'ajax.php?id=' + jQuery('#move').attr ('value') + '&cmd=' + (type == 'group' ? 'move_groups' : 'move_redirects') + '&_ajax_nonce=' + nonce, checked.serialize (), function () { window.location.reload ()});
    }
  }
  else
    alert (wp_none_select);

  return false;
}

function delete_items (type,nonce)
{
  var checked = jQuery('.item :checked');
  if (checked.length > 0)
  {
    if (confirm (wp_are_you_sure))
    {
      var urltype = 'delete_items';
      if (type == 'group')
        urltype = 'delete_groups';
      else if (type == 'log')
        urltype = 'delete_logs';
        
      jQuery('#loading').show ();
      jQuery.post (wp_base + 'ajax.php?id=0&cmd=' + urltype + '&_ajax_nonce=' + nonce, checked.serialize (), function ()
        {
          jQuery('#loading').hide ();
          checked.each (function ()
          {
            jQuery(this).parent ().parent ().remove ();
          });
        });
    }
  }
  else
    alert (wp_none_select);

  return false;
}

function sort_order ()
{
  jQuery('#items').sortable ();
  jQuery('#toggle_sort_on').hide ();
  jQuery('#toggle_sort_off').show ();
  jQuery('#items li').addClass ('sortable');
  return false;
}

function save_redirect_order (start,nonce)
{
  if (confirm (wp_are_you_sure))
  {
    jQuery('#loading').show ();
    jQuery.post (wp_base + 'ajax.php?cmd=save_redirect_order&id=' + start + '&_ajax_nonce=' + nonce, jQuery('#items').sortable ('serialize'), function ()
      {
        jQuery('#loading').hide ();
        jQuery('#toggle_sort_off').hide ();
        jQuery('#toggle_sort_on').show ();
        jQuery('#items').sortable ('disable');
        jQuery('#items li').removeClass ('sortable');
      });
  }
  
  return false;
}

function save_group_order (start)
{
  if (confirm (wp_are_you_sure))
  {
    jQuery('#loading').show ();
    jQuery.post (wp_base + 'ajax.php?cmd=save_group_order&id=' + start, jQuery('#items').sortable ('serialize'), function ()
      {
        jQuery('#loading').hide ();
        jQuery('#toggle_sort_off').hide ();
        jQuery('#toggle_sort_on').show ();
        jQuery('#items').sortable ('disable');
        jQuery('#items li').removeClass ('sortable');
      });
  }
  
  return false;
}

function show_group (item)
{
  jQuery('#info_' + item).html (wp_progress);
  jQuery('#item_' + item).load (wp_base + 'ajax.php?cmd=show_group&id=' + item, {}, function () { editItems ('edit_group'); });
  return false;
}

function delete_module (item, nonce)
{
  if (confirm (wp_are_you_sure))
  {
    jQuery('#info_' + item).html (wp_progress);
    jQuery.post (wp_base + 'ajax.php?cmd=delete_module&id=' + item + '&_ajax_nonce=' + nonce, {}, function()
    {
      jQuery('#item_' + item);
    });
  }
  return false;
}

function reset_module (item, nonce)
{
  if (confirm (wp_are_you_sure))
  {
    jQuery('#info_' + item).html (wp_progress);
    jQuery('#item_' + item).load (wp_base + 'ajax.php?cmd=reset_module&id=' + item + '&_ajax_nonce=' + nonce);
  }
  return false;
}

function edit_module (item, nonce)
{
  jQuery('#info_' + item).html (wp_progress);
  jQuery('#item_' + item).load (wp_base + 'ajax.php?cmd=edit_module&id=' + item + '&_ajax_nonce=' + nonce);
  return false;
}

function show_module (item)
{
  jQuery('#info_' + item).html (wp_progress);
  jQuery('#item_' + item).load (wp_base + 'ajax.php?cmd=show_module&id=' + item);
  return false;
}

function showLogs ()
{
  jQuery('.show-log').click (function (item)
  {
    var itemid = jQuery(item.target).parent ().parent ().attr ('id').replace (/\w*_/, '');   
    
    jQuery('#info_' + itemid).html (wp_progress);
    jQuery('#info_' + itemid).load (wp_base + 'ajax.php?cmd=show_log&id=' + itemid);
    return false;
  });
  
  jQuery('.add-log').click (function (item)
  {
    var itemid = jQuery(item.target).parent ().parent ().parent ().attr ('id').replace (/\w*_/, '');   
    
    jQuery('#added').hide ();
    jQuery('#add').show ();
    
    jQuery('#old').attr ('value', jQuery('#href_' + itemid).attr ('href').replace (/\w*:\/\/(.*?)\//,'/'));
    item.target = '#add';
    return true;
  });
}

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
