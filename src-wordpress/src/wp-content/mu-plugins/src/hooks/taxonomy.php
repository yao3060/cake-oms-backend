<?php

add_filter('register_taxonomy_args', function ($args, $name, $objectType) {

    if ($name === 'user-group') {
        $args['show_in_rest'] = true;
        $args['rest_base'] = 'stores';
    }

    return $args;
}, 10, 3);
