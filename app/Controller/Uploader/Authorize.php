<?php
/**
 * Created by: MinutePHP framework
 */
namespace App\Controller\Uploader {

    use Auryn\Injector;
    use Minute\Error\ApiKeyError;
    use Minute\Service\IService;
    use Minute\Session\Session;
    use Minute\View\Redirection;

    class Authorize {
        /**
         * @var Injector
         */
        private $injector;
        /**
         * @var Session
         */
        private $session;

        /**
         * Authorize constructor.
         *
         * @param Injector $injector
         * @param Session $session
         */
        public function __construct(Injector $injector, Session $session) {
            $this->injector = $injector;
            $this->session  = $session;
        }

        public function index(string $service = 'youtube') {
            $class   = sprintf('Minute\\Service\\%s', ucfirst($service));
            $user_id = $this->session->getLoggedInUserId();

            if (class_exists($class)) {
                /** @var IService $instance */
                $instance = $this->injector->make($class);

                if ($instance->authorize($user_id) === true) {
                    return sprintf('<script' . '>self.opener.authComplete(); self.close();</script>');
                } else {
                    return new Redirection($instance->getRedirectUrl());
                }
            }

            throw new ApiKeyError("Service $service is not enabled!");
        }
    }
}