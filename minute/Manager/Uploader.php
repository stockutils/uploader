<?php
/**
 * User: Sanchit <dev@minutephp.com>
 * Date: 1/18/2017
 * Time: 12:30 PM
 */
namespace Minute\Manager {

    use Auryn\Injector;
    use Minute\Event\Dispatcher;
    use Minute\Event\UploaderEvent;
    use Minute\Event\UploaderResultEvent;
    use Minute\Service\IService;

    class Uploader {
        /**
         * @var Injector
         */
        private $injector;
        /**
         * @var Dispatcher
         */
        private $dispatcher;

        /**
         * Authorize constructor.
         *
         * @param Injector $injector
         * @param Dispatcher $dispatcher
         */
        public function __construct(Injector $injector, Dispatcher $dispatcher) {
            $this->injector   = $injector;
            $this->dispatcher = $dispatcher;
        }

        public function uploadMedia(UploaderEvent $event) {
            $service = $event->getSite();
            $class   = sprintf('Minute\\Service\\%s', ucfirst($service));

            if (class_exists($class)) {
                /** @var IService $instance */
                $instance = $this->injector->make($class);
                $user_id  = $event->getUserId();

                if ($instance->authorize($user_id) === true) {
                    if ($url = $instance->upload($event->getPathOrUrl(), $event->getAttrs())) {
                        $event->setUploadUrl($url);
                    }

                    $resultEvent = new UploaderResultEvent($user_id, array_merge($event->getUserData(), ['url' => $url ?? '']));
                    $this->dispatcher->fire(!empty($url) ? UploaderResultEvent::USER_UPLOADER_PASS : UploaderResultEvent::USER_UPLOADER_FAIL, $resultEvent);
                }
            }
        }
    }
}