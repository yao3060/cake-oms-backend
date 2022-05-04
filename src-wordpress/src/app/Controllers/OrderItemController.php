<?php

namespace App\Controllers;

use WP_REST_Request;
use WP_REST_Server;
use WP_REST_Response;
use Exception;
use WP_Error;

class OrderItemController extends \WP_REST_Controller
{
    private $db = null;
    private $dbPrefix = '';

    public function __construct()
    {
        $this->db = \WeDevs\ORM\Eloquent\Database::instance();
        $this->dbPrefix = $this->db->db->prefix;
    }

    public function register_routes()
    {
        $version = '1';
        $namespace = 'oms/v' . $version;
        $base = 'order-items';
        register_rest_route($namespace, '/' . $base . '/(?P<id>[\d]+)/image', [
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [$this, 'deleteFeaturedImage'],
                'permission_callback' => [$this, 'create_item_permissions_check'],
                'args'                => $this->get_endpoint_args_for_item_schema(true),
            ],
        ]);

        register_rest_route($namespace, '/' . $base . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [$this, 'update_item'],
                'permission_callback' => [$this, 'create_item_permissions_check'],
                'args'                => $this->get_endpoint_args_for_item_schema(true),
            ],
        ]);

        register_rest_route($namespace, '/' . $base . '/schema', [
            'methods'  => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_public_item_schema'],
            'permission_callback' => '__return_true'
        ]);
    }

    /**
     * Update Order Item
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_Error|WP_REST_Response
     */
    public function update_item($request): WP_Error|WP_REST_Response
    {
        try {
            $id = $request->get_param('id');
            $data = [];

            if ($request['note']) {
                $data['note'] = $request['note'];
            }

            if (count($data) < 1) {
                throw new Exception('Nothing to update');
            }

            $this->db->table('order_items')
                ->where('id', $id)
                ->update($data);

            return new WP_REST_Response([
                'code' => 'order_item_updated',
                'message' => 'Updated'
            ], 200);
        } catch (\Throwable $th) {
            return new WP_Error(
                'cant_order_item',
                $th->getMessage(),
                ['status' => 500]
            );
        }
    }

    /**
     * @deprecated 不需要了
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_Error|WP_REST_Response
     */
    public function deleteFeaturedImage(WP_REST_Request $request): WP_Error|WP_REST_Response
    {
        $id = $request->get_param('id');
        try {
            $this->db->table('order_items')
                ->where('id', $id)
                ->update([
                    'media_id' => 0,
                    'media_url' => ''
                ]);

            return new WP_REST_Response([
                'code' => 'order_featured_image_deleted',
                'message' => 'Deleted'
            ], 200);
        } catch (\Exception $e) {
            return new WP_Error(
                'cant_delete_order_featured_image',
                $e->getMessage(),
                ['status' => 500]
            );
        }
    }

    /**
     * Check if a given request has access to get items
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function get_items_permissions_check($request)
    {
        return current_user_can('read');
    }

    /**
     * Check if a given request has access to get a specific item
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function get_item_permissions_check($request)
    {
        return $this->get_items_permissions_check($request);
    }

    /**
     * Check if a given request has access to create items
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function create_item_permissions_check($request)
    {
        return true; //current_user_can('edit_something');
    }

    /**
     * Check if a given request has access to update a specific item
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function update_item_permissions_check($request)
    {
        return $this->create_item_permissions_check($request);
    }

    /**
     * Check if a given request has access to delete a specific item
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function delete_item_permissions_check($request)
    {
        return $this->create_item_permissions_check($request);
    }

    /**
     * Prepare the item for create or update operation
     *
     * @param WP_REST_Request $request Request object
     * @return WP_Error|array $prepared
     */
    protected function prepare_item_for_database($request)
    {
        return array();
    }

    /**
     * Prepare the item for the REST response
     *
     * @param mixed $item WordPress representation of the item.
     * @param WP_REST_Request $request Request object.
     * @return mixed
     */
    public function prepare_item_for_response($item, $request)
    {
        return array();
    }

    /**
     * Get the query params for collections
     *
     * @return array
     */
    public function get_collection_params()
    {
        return array(
            'page'     => array(
                'description'       => 'Current page of the collection.',
                'type'              => 'integer',
                'default'           => 1,
                'sanitize_callback' => 'absint',
            ),
            'per_page' => array(
                'description'       => 'Maximum number of items to be returned in result set.',
                'type'              => 'integer',
                'default'           => 10,
                'sanitize_callback' => 'absint',
            ),
            'search'   => array(
                'description'       => 'Limit results to those matching a string.',
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
        );
    }
}
