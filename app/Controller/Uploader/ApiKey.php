<?php
/**
 * Created by: MinutePHP framework
 */
namespace App\Controller\Uploader {

    use Minute\Error\ApiKeyError;
    use Minute\Model\CollectionEx;

    class ApiKey {
        public function index(CollectionEx $api_keys) {
            if ($api_key = $api_keys->first()) {
                return $api_key->toJson();
            }

            throw new ApiKeyError("Api key not found");
        }
    }
}