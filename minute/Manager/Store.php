<?php
/**
 * User: Sanchit <dev@minutephp.com>
 * Date: 1/17/2017
 * Time: 10:33 PM
 */
namespace Minute\Manager {

    use App\Model\MApiKey;
    use Minute\Session\Session;

    class Store {

        /**
         * Store constructor.
         */
        public function __construct() {
            MApiKey::unguard();
        }

        public function getData(int $user_id, string $service) {
            if ($record = MApiKey::where('user_id', '=', $user_id)->where('site_name', '=', $service)->first()) {
                return json_decode($record['data_json'], true);
            }

            return null;
        }

        public function putData(int $user_id, string $service, $data) {
            /** @var MApiKey $record */
            $record = MApiKey::updateOrCreate(['user_id' => $user_id, 'site_name' => $service]);

            $record->data_json = json_encode($data);
            $record->save();
        }
    }
}