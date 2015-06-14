<?php

namespace DP\GameServer\GameServerBundle\Twig;

use Dedipanel\PHPSeclibWrapperBundle\SFTP\Exception\InvalidPathException;
use Symfony\Component\Debug\Exception\FlattenException;

class FtpExceptionExtension extends \Twig_Extension
{
    public function getTests()
    {
        return [
            new \Twig_SimpleTest('ftp exception', function ($exception) {
                if ($exception instanceof FlattenException) {
                    $exception = $exception->getClass();
                }

                return is_a($exception, InvalidPathException::CLASSNAME, true);
            }),
        ];
    }

    public function getName()
    {
        return 'ftp_exception';
    }
}
