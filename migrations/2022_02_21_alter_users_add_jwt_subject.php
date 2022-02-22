<?php

use Flarum\Database\Migration;

return Migration::addColumns('users', [
    'jwt_subject' => ['string', 'length' => 255, 'nullable' => true, 'unique' => true],
]);
