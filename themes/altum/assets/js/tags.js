function getTags(target_url, object_type, object_id) {
    $.ajax({
        type: "POST",
        url: target_url,
        data: {
            'object_type': object_type,
            'object_id': object_id,
        },
        dataType: "JSON",
        success: function (response) {
            console.log(response);
            $('#tag-list').html('');
            $('.available-tags-list').html('');
            for (let i in response[1]) {
                $('#tag-list').append(
                    `<span class="badge badge-secondary py-0 pl-2 pr-0 mr-2" tag_name="` + response[1][i] + `">` + response[1][i] + `<button class="px-2 py-1 badge-secondary border-0 rounded-right tag-remove"><i class="fa-solid fa-xmark"></i></button></span>`
                );

            }
            for (let i in response[0]) {
                $('.available-tags-list').append(
                    `<p class="available-tags" tag_type="add_new"><span>` + response[0][i] + `</span></p>`
                );
            }
            if ($('#tag-list').html() == "") {
                $('#tag-list').html(
                    `<p class="m-0">No tags yet</p>`
                );
                $('.tag-caption').text("Suggestion(s):");
            } else {
                $('.tag-caption').text("Suggestion(s):");
            }
        }
    });
}

function deleteTag(target_url, object_type, object_id, tag_name) {
    var send_data = {
        'object_type': object_type,
        'object_id': object_id,
        'tag_name': tag_name
    };
    $.ajax({
        type: "POST",
        url: target_url,
        data: send_data,
        dataType: "JSON",
        success: function (response) {
            return response;
        }
    });
    $(this).parent().remove();
}

function searchTag(target_url, object_type, object_id, search_string) {
    var send_data = {
        'object_type': object_type,
        'object_id': object_id,
        'search_string': search_string
    };
    $.ajax({
        type: "POST",
        url: target_url,
        data: send_data,
        dataType: "JSON",
        success: function (response) {
            $('.available-tags-list').html('');
            if (response[1].length == 0) {
                $('.available-tags-list').html(
                    `<p class="available-tags" tag_type="add_new">` + search_string + `</p>`
                );
                $('.tag-caption').text('Create a New Tag:');
            } else {
                for (let i in response[1]) {
                    if (response[0] != null && response[0].indexOf(response[1][i]) < 0) {
                        $('.available-tags-list').append(
                            `<p class="available-tags" tag_type="add_new">` + response[1][i] + `</p>`
                        );
                    } else {
                        $('.available-tags-list').append(
                            `<p class="available-tags" tag_type="available"><i class=" pr-2 fa-solid fa-check"></i><span>` + response[1][i] + `</span></p>`
                        );
                    }

                }
                $('.tag-caption').text("Available Tag('s):");
            }
        }
    });
}

function saveTag(target_url, object_type, object_id, tag_name) {
    var send_data = {
        'object_type': object_type,
        'object_id': object_id,
        'tag_name': tag_name
    };
    $.ajax({
        type: "POST",
        url: target_url,
        data: send_data,
        dataType: "JSON",
        success: function (response) {
            $('#tag-search').val('');
            return response;
        }
    });
}

