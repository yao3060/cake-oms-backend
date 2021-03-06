<?php

use App\Services\OrderProductMediaService;

/**
 * Filters an attachment returned from the REST API.
 *
 * Allows modification of the attachment right before it is returned.
 *
 * @since 4.7.0
 *
 * @param WP_REST_Response $response The response object.
 * @param WP_Post          $post     The original attachment post.
 * @param WP_REST_Request  $request  Request used to generate the response.
 */
add_filter('rest_prepare_attachment', function ($response, $post, $request) {

    $data = $response->get_data();
    if ($request->get_param('product_id') && isset($data['source_url'])) {

        $data['product_id'] = (int)$request->get_param('product_id');
        error_log('rest_prepare_attachment:' .
            json_encode([(int)$request->get_param('product_id'), $data['product_id']]));

        $action = $request->get_param('action');
        if ($action === 'add_featured_image') {
            (new OrderProductMediaService)->addFeaturedImage(
                $data['product_id'],
                (int)$data['id'],
                $data['source_url']
            );
        }
        if ($action === 'add_gallery_image') {
            (new OrderProductMediaService)->addGalleryImage(
                $data['product_id'],
                (int)$data['id'],
                $data['source_url']
            );
        }

        $response->set_data($data);
    }

    return $response;
}, 10, 3);


// randomize upload filenames
function cake_randomize_uploaded_filename($filename)
{

    // does it have an extension? grab it
    $ext  = empty(pathinfo($filename)['extension']) ? '' : '.' . pathinfo($filename)['extension'];

    // return the first 8 characters of the MD5 hash of the name, along with the extension
    return substr(md5($filename), 0, 8) . $ext;
}

add_filter('sanitize_file_name', 'cake_randomize_uploaded_filename', 10);
