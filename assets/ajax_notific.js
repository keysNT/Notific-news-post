/**
 * Created by magenest4 on 31/08/2017.
 */
    function insertDatabase(post_id) {
        var data = {action : 'remove_news_seen'};
        data['post_id'] = post_id;
        jQuery.get(ajaxurl , data , function(response) {
            var obj = JSON.parse(response);
            //var update = obj.update;
            if (obj.type ='success') {
                // count_notific
                var x = parseInt(jQuery('#count_notific').html());
                var y = x-1;
                jQuery('#count_notific').html(y);
		var id = '#'+obj.id;
		window.location.href = jQuery(id).html();

            } else {
                alert(obj.message);
            }
        });
    }
