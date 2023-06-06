function set_view(resource) {
  this_view = get_cookie(resource + '_view');
  other_view = this_view == 'list' ? 'blocks' : 'list';

  $('#'+resource+'-'+this_view).show();
  $('#'+resource+'-'+other_view).hide();

  $('#toggle_view').click(function() {
      $(this).find('.fa-list,.fa-th').toggleClass('fa-list').toggleClass('fa-th');
      $('#' + resource + '-blocks').toggle();
      $('#' + resource + '-list').toggle();

      if($('#' + resource + '-blocks').is(':visible')) {
          cookie_data = "blocks";
      } else {
          cookie_data = "list";
      }
      set_cookie(resource + '_view', cookie_data, 30);
  });
}

