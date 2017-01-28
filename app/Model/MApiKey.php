<?php
/**
 * Created by: MinutePHP Framework
 */
namespace App\Model {

    use Minute\Model\ModelEx;

    class MApiKey extends ModelEx {
        protected $table      = 'm_api_keys';
        protected $primaryKey = 'api_key_id';
    }
}