<?php

declare(strict_types=1);

/*
 * This file is part of Contao Manager.
 *
 * (c) Contao Association
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\ManagerApi\Controller\Server;

use Contao\ManagerApi\ApiKernel;
use Contao\ManagerApi\Config\ManagerConfig;
use Contao\ManagerApi\Exception\ProcessOutputException;
use Contao\ManagerApi\HttpKernel\ApiProblemResponse;
use Contao\ManagerApi\I18n\Translator;
use Contao\ManagerApi\Process\ConsoleProcessFactory;
use Contao\ManagerApi\Process\ContaoApi;
use Contao\ManagerApi\Process\ContaoConsole;
use Contao\ManagerApi\System\ServerInfo;
use Crell\ApiProblem\ApiProblem;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/server/contao", methods={"GET"})
 */
class ContaoController
{
    /**
     * @var ApiKernel
     */
    private $kernel;

    /**
     * @var ContaoApi
     */
    private $contaoApi;

    /**
     * @var ContaoConsole
     */
    private $contaoConsole;

    /**
     * @var ConsoleProcessFactory
     */
    private $processFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(
        ApiKernel $kernel,
        ContaoApi $contaoApi,
        ContaoConsole $contaoConsole,
        ConsoleProcessFactory $processFactory,
        LoggerInterface $logger = null,
        Filesystem $filesystem = null
    ) {
        $this->kernel = $kernel;
        $this->contaoApi = $contaoApi;
        $this->contaoConsole = $contaoConsole;
        $this->processFactory = $processFactory;
        $this->logger = $logger;
        $this->filesystem = $filesystem ?: new Filesystem();
    }

    public function __invoke(ManagerConfig $managerConfig, ServerInfo $serverInfo, Translator $translator): Response
    {
        if (!$managerConfig->has('server') || !$serverInfo->getPhpExecutable()) {
            return new ApiProblemResponse(
                (new ApiProblem('Missing hosting configuration.', '/api/server/config'))
                    ->setStatus(Response::HTTP_SERVICE_UNAVAILABLE)
            );
        }

        try {
            $contaoVersion = $this->getContaoVersion();
        } catch (\RuntimeException $e) {
            if ($e instanceof ProcessOutputException || $e instanceof ProcessFailedException) {
                return new ApiProblemResponse(
                    (new ApiProblem(
                        $translator->trans('integrity.contao_version.title')
                    ))->setDetail(
                        $translator->trans('integrity.contao_version.detail', ['output' => $e->getProcess()->getOutput()])
                    )->setStatus(Response::HTTP_BAD_GATEWAY)
                );
            }

            $contaoVersion = null;
        }

        if (null === $contaoVersion) {
            if (0 === \count($files = $this->getProjectFiles())) {
                return new JsonResponse(
                    [
                        'version' => null,
                        'api' => 0,
                        'supported' => false,
                    ]
                );
            }

            return new ApiProblemResponse(
                (new ApiProblem(
                    $translator->trans('integrity.contao_unknown.title')
                ))->setDetail(
                    $translator->trans('integrity.contao_unknown.detail', ['files' => ' - '.implode("\n - ", $files)])
                )
            );
        }

        return new JsonResponse(
            [
                'version' => $contaoVersion,
                'api' => $this->getApiVersion(),
                'supported' => version_compare($contaoVersion, '4.0.0', '>=') || 0 === strpos($contaoVersion, 'dev-'),
            ]
        );
    }

    /**
     * Gets a list of files in the project root directory, excluding what is allowed to install Contao.
     */
    private function getProjectFiles(): array
    {
        $content = scandir($this->kernel->getProjectDir(), SCANDIR_SORT_NONE);

        return array_diff(
            $content,
            [
                '.',
                '..',
                '.env',
                '.env.local',
                '.git',
                '.idea',
                'cgi-bin',
                'contao-manager',
                'web',
                'plesk-stat',
                '.bash_profile',
                '.bash_logout',
                '.bashrc',
                '.DS_Store',
                '.ftpquota',
                '.htaccess',
                'user.ini',
            ]
        );
    }

    /**
     * Gets the Contao API version.
     */
    private function getApiVersion(): ?int
    {
        try {
            return $this->contaoApi->getVersion();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Tries to detect the Contao 4/3/2 version by analyzing the filesystem.
     */
    private function getContaoVersion(): ?string
    {
        if ($this->filesystem->exists($this->processFactory->getContaoConsolePath())) {
            return $this->contaoConsole->getVersion();
        }

        // Required for Contao 2.11
        \define('TL_ROOT', $this->kernel->getProjectDir());

        $files = [
            $this->kernel->getProjectDir().'/system/constants.php',
            $this->kernel->getProjectDir().'/system/config/constants.php',
        ];

        // Test if the Phar was placed in the Contao 2/3 root
        if ('' !== ($phar = \Phar::running(false))) {
            $files[] = \dirname($phar).'/system/constants.php';
            $files[] = \dirname($phar).'/system/config/constants.php';
        }

        if ($this->logger instanceof LoggerInterface) {
            $this->logger->info('Searching for Contao 2/3', ['files' => $files]);
        }

        foreach ($files as $file) {
            if ($this->filesystem->exists($file)) {
                try {
                    @include $file;
                } catch (\Throwable $e) {
                    // do nothing on error or exception
                }

                if (\defined('VERSION') && \defined('BUILD')) {
                    return VERSION.'.'.BUILD;
                }

                break;
            }
        }

        return null;
    }
}
