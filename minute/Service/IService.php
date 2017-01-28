<?php
/**
 * User: Sanchit <dev@minutephp.com>
 * Date: 1/17/2017
 * Time: 10:15 PM
 */

namespace Minute\Service {

    interface IService {
        const Key = 'uploader';

        public function authorize(int $user_id);
        public function getRedirectUrl();
        public function upload(string $url, array $attrs = []);
    }
}