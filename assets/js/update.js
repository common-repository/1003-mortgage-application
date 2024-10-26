jQuery(document).ready(function ($) {
  var totalPosts = 0;

  function updateProgressBar (percentage) {
    // int value between 0 and 100
    var percentageText = Math.round(percentage).toString();
    $('.progress-bar').css('width', percentage + '%').attr('aria-valuenow', percentage).text(percentageText + '%');
  }

  function getTotalPosts (callback) {
    $.post(mappUpdate.ajax_url, {
      action: 'mapp_count_total_posts',
      nonce: mappUpdate.nonce
    }, function (response) {
      totalPosts = response.data.total_posts;
      callback();
    });
  }

  function mappUpdatePosts () {
    $.post(mappUpdate.ajax_url, {
      action: 'mapp_update_applications_addresses',
      nonce: mappUpdate.nonce
    }, function (response) {
      response.data.forEach(function (siteResult) {
        var percentage = 100 - (siteResult.remaining_posts / totalPosts) * 100;
        updateProgressBar(percentage, siteResult.site_name);
      });

      var remainingPosts = response.data.reduce(function (sum, siteResult) {
        return sum + siteResult.remaining_posts;
      }, 0);

      if (remainingPosts > 0) {
        // If there are still posts to be updated, call the function again after a delay
        setTimeout(mappUpdatePosts, 1050);
      } else {
        // If all posts have been updated, reload the page
        location.reload();
      }
    }).fail(function (jqXHR, textStatus, errorThrown) {
      // handle error
      alert("An error occurred: " + textStatus);
      $('.mapp-update-button').prop('disabled', false).text('Update Database');
    });
  }

  $(document).on('click', '.mapp-update-button', function (e) {
    e.preventDefault();
    $(this).prop('disabled', true).text('Updating...');
    $('.mapp-update-progress').css('display', 'block');
    getTotalPosts(mappUpdatePosts);
  });
});
