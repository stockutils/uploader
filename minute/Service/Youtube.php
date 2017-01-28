<?php
/**
 * User: Sanchit <dev@minutephp.com>
 * Date: 1/17/2017
 * Time: 10:14 PM
 */
namespace Minute\Service {

    use Google_Client;
    use Google_Http_MediaFileUpload;
    use Google_Service_YouTube;
    use Google_Service_YouTube_Video;
    use Google_Service_YouTube_VideoSnippet;
    use Google_Service_YouTube_VideoStatus;
    use Minute\Config\Config;
    use Minute\Error\ApiKeyError;
    use Minute\Error\UploaderError;
    use Minute\File\TmpDir;
    use Minute\Http\Browser;
    use Minute\Manager\Store;
    use Psr\Http\Message\RequestInterface;

    class Youtube implements IService {
        /**
         * @var Google_Client
         */
        private $google;
        /**
         * @var Store
         */
        private $store;
        /**
         * @var Browser
         */
        private $browser;
        /**
         * @var TmpDir
         */
        private $tmp;

        /**
         * Youtube constructor.
         *
         * @param Config $config
         * @param Google_Client $google
         * @param Store $store
         *
         * @param Browser $browser
         *
         * @param TmpDir $tmp
         *
         * @throws ApiKeyError
         */
        public function __construct(Config $config, Google_Client $google, Store $store, Browser $browser, TmpDir $tmp) {
            $this->google = $google;;
            $this->store   = $store;
            $this->browser = $browser;

            if ($ytConfig = $config->get(IService::Key . '/api_keys/youtube')) {
                $redirect = sprintf('%s/uploader/authorize/youtube', $config->getPublicVars('host'));

                $this->google->setClientId($ytConfig['client_id']);
                $this->google->setClientSecret($ytConfig['client_secret']);
                $this->google->setScopes([Google_Service_YouTube::YOUTUBE, Google_Service_YouTube::YOUTUBE_UPLOAD]);
                $this->google->setAccessType('offline');
                $this->google->setRedirectUri($redirect);
            } else {
                throw new ApiKeyError("Youtube client api configuration is missing!");
            }

            $this->tmp = $tmp;
        }

        public function authorize(int $user_id) {
            try {
                if ($code = $_GET['code'] ?? null) {
                    $this->google->authenticate($code);
                    $token = $this->google->getAccessToken();
                } elseif ($old = $this->store->getData($user_id, 'youtube')) {
                    if ($token = $old['token'] ?? null) {
                        $this->google->setAccessToken($token);
                    }
                }

                if (!empty($token)) {
                    if ($refresh = $token['refresh_token'] ?? null) {
                        if ($this->google->isAccessTokenExpired()) {
                            $this->google->refreshToken($refresh);
                        }
                    }

                    $this->store->putData($user_id, 'youtube', ['token' => array_merge(['refresh_token' => $refresh ?? ''], $this->google->getAccessToken())]);

                    return true;
                }
            } catch (\Throwable $e) {
                if (!empty($token)) {
                    $this->google->revokeToken($token);
                }

                $this->store->putData($user_id, 'youtube', null);
            }

            return false;
        }

        public function getRedirectUrl() {
            return $this->google->createAuthUrl();
        }

        public function upload(string $pathOrUrl, array $attrs = []) {
            try {
                if (filter_var($pathOrUrl, FILTER_VALIDATE_URL)) {
                    $download = $videoPath = $this->tmp->getTempFile('avi');
                    $this->browser->download($pathOrUrl, $videoPath);
                } elseif (file_exists($pathOrUrl)) {
                    $videoPath = $pathOrUrl;
                } else {
                    throw new UploaderError("Invalid path or Url: $pathOrUrl");
                }

                $videoTitle       = @$attrs['title'] ?: basename($videoPath);
                $videoDescription = @$attrs['description'] ?: "Private video";
                $videoCategory    = @$attrs['category'] ?: "26";
                $videoPrivacy     = @$attrs['privacy'] ?: 'public';
                $videoTags        = !empty($attrs['keywords']) ? $attrs['keywords'] : array("youtube", "video", basename($videoPath));

                try {
                    // Client init
                    $client = $this->google;

                    $youtube = new Google_Service_YouTube($client);

                    // Create a snipet with title, description, tags and category id
                    $snippet = new Google_Service_YouTube_VideoSnippet();
                    $snippet->setTitle($videoTitle);
                    $snippet->setDescription($videoDescription);
                    $snippet->setCategoryId($videoCategory);
                    $snippet->setTags($videoTags);

                    // Create a video status with privacy status. Options are "public", "private" and "unlisted".
                    $status = new Google_Service_YouTube_VideoStatus();
                    $status->setPrivacyStatus($videoPrivacy);

                    // Create a YouTube video with snippet and status
                    $video = new Google_Service_YouTube_Video();
                    $video->setSnippet($snippet);
                    $video->setStatus($status);

                    // Size of each chunk of data in bytes. Setting it higher leads faster upload (less chunks,
                    // for reliable connections). Setting it lower leads better recovery (fine-grained chunks)
                    $chunkSizeBytes = 1 * 1024 * 1024;

                    // Setting the defer flag to true tells the client to return a request which can be called
                    // with ->execute(); instead of making the API call immediately.
                    $client->setDefer(true);

                    // Create a request for the API's videos.insert method to create and upload the video.
                    /** @var RequestInterface $insertRequest */
                    $insertRequest = $youtube->videos->insert("status,snippet", $video);

                    // Create a MediaFileUpload object for resumable uploads.
                    $media = new Google_Http_MediaFileUpload($client, $insertRequest, 'video/*', null, true, $chunkSizeBytes);
                    $media->setFileSize(filesize($videoPath));

                    // Read the media file and upload it chunk by chunk.
                    $status = false;
                    $handle = fopen($videoPath, "rb");
                    while (!$status && !feof($handle)) {
                        $chunk  = fread($handle, $chunkSizeBytes);
                        $status = $media->nextChunk($chunk);
                    }

                    fclose($handle);

                    // If you want to make other calls after the file upload, set setDefer back to false
                    $client->setDefer(false);

                    /**
                     * Video has successfully been upload, now lets perform some cleanup functions for this video
                     */
                    if (!empty($status->status) && ($status->status['uploadStatus'] == 'uploaded')) {
                        return sprintf("http://www.youtube.com/watch?v=%s", $status['id']);
                    }
                } catch (\Throwable $e) {
                    $client->revokeToken($client->getAccessToken());

                    throw new UploaderError("Upload error: " . $e->getMessage());
                }
            } finally {
                if (!empty($download)) {
                    @unlink($download);
                }
            }

            return false;
        }
    }
}