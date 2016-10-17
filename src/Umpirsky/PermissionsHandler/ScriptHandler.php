<?php

namespace Umpirsky\PermissionsHandler;

use Composer\Script\Event;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ScriptHandler
{
    public static function setPermissions(Event $event)
    {
        if ('WIN' === strtoupper(substr(PHP_OS, 0, 3))) {
            $event->getIO()->write('<info>No permissions setup is required on Windows.</info>');
            return;
        }

        $event->getIO()->write('Setting up permissions.');

        try {
            self::setPermissionsSetfacl($event);

            return;
        } catch (ProcessFailedException $setfaclException) {
            $event->getIO()->write(sprintf('<error>%s</error>', $setfaclException->getMessage()));
            $event->getIO()->write('<info>Trying chmod...</info>');
        }

        try {
            self::setPermissionsChmod($event);

            return;
        } catch (ProcessFailedException $chmodException) {
            $event->getIO()->write(sprintf('<error>%s</error>', $chmodException->getMessage()));
        }
    }

    public static function setPermissionsSetfacl(Event $event)
    {
        self::setPermissionsWithSetter($event, new SetfaclPermissionsSetter());
    }

    public static function setPermissionsChmod(Event $event)
    {
        self::setPermissionsWithSetter($event, new ChmodPermissionsSetter());
    }

    private static function setPermissionsWithSetter(Event $event, PermissionsSetterInterface $permissionsSetter)
    {
        foreach ((new Configuration($event))->getWritableDirs() as $path) {
            $permissionsSetter->setPermissions($path);
        }
    }
}
