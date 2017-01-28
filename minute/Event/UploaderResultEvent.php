<?php
/**
 * User: Sanchit <dev@minutephp.com>
 * Date: 1/18/2017
 * Time: 2:55 PM
 */
namespace Minute\Event {

    class UploaderResultEvent extends UserEvent {
        const USER_UPLOADER_PASS = 'user.uploader.pass';
        const USER_UPLOADER_FAIL = 'user.uploader.fail';
    }
}