<?php
/**
 * User: Sanchit <dev@minutephp.com>
 * Date: 1/18/2017
 * Time: 12:20 PM
 */
namespace Minute\Event {

    class UploaderEvent extends UserEvent {
        const USER_UPLOADER_CREATE = 'user.uploader.create';
        /**
         * @var string
         */
        private $pathOrUrl;
        /**
         * @var string
         */
        private $site;
        /**
         * @var string
         */
        private $uploadUrl;
        /**
         * @var array
         */
        private $attrs;

        /**
         * UploaderEvent constructor.
         *
         * @param int $user_id
         * @param string $pathOrUrl
         * @param string $site
         * @param array $attrs
         * @param array $userData
         */
        public function __construct(int $user_id = 0, string $pathOrUrl, string $site, array $attrs = [], array $userData = []) {
            parent::__construct($user_id, $userData);

            $this->pathOrUrl = $pathOrUrl;
            $this->site      = $site;
            $this->attrs     = $attrs;
        }

        /**
         * @return array
         */
        public function getAttrs(): array {
            return $this->attrs ?? [];
        }

        /**
         * @param array $attrs
         *
         * @return UploaderEvent
         */
        public function setAttrs(array $attrs): UploaderEvent {
            $this->attrs = $attrs;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getUploadUrl() {
            return $this->uploadUrl;
        }

        /**
         * @param mixed $uploadUrl
         *
         * @return UploaderEvent
         */
        public function setUploadUrl(string $uploadUrl) {
            $this->uploadUrl = $uploadUrl;

            return $this;
        }

        /**
         * @return string
         */
        public function getSite(): string {
            return $this->site;
        }

        /**
         * @param string $site
         *
         * @return UploaderEvent
         */
        public function setSite(string $site): UploaderEvent {
            $this->site = $site;

            return $this;
        }

        /**
         * @return string
         */
        public function getPathOrUrl(): string {
            return $this->pathOrUrl;
        }

        /**
         * @param string $pathOrUrl
         *
         * @return UploaderEvent
         */
        public function setPathOrUrl(string $pathOrUrl): UploaderEvent {
            $this->pathOrUrl = $pathOrUrl;

            return $this;
        }

    }
}