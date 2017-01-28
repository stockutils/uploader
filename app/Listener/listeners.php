<?php

/** @var Binding $binding */
use Minute\Event\Binding;
use Minute\Event\UploaderEvent;
use Minute\Manager\Uploader;

$binding->addMultiple([
    //static event listeners go here
    ['event' => UploaderEvent::USER_UPLOADER_CREATE, 'handler' => [Uploader::class, 'uploadMedia'], 'priority' => 0]
]);