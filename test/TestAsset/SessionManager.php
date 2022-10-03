<?php

declare(strict_types=1);

namespace LaminasTest\Validator\TestAsset;

use Laminas\EventManager\EventManagerInterface;
use Laminas\Session\AbstractManager;
use Laminas\Session\Storage\ArrayStorage;

final class SessionManager extends AbstractManager
{
    /** @var bool */
    public $started = false;

    /** @var string */
    protected $configDefaultClass = 'Laminas\Session\Configuration\StandardConfiguration';

    /** @var string */
    protected $storageDefaultClass = ArrayStorage::class;

    /**
     * @return void
     */
    public function start()
    {
        $this->started = true;
    }

    /**
     * @return void
     */
    public function destroy()
    {
        $this->started = false;
    }

    public function stop(): void
    {
    }

    /**
     * @return void
     */
    public function writeClose()
    {
        $this->started = false;
    }

    /**
     * @return void
     */
    public function getName()
    {
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
    }

    /**
     * @return void
     */
    public function getId()
    {
    }

    /**
     * @param string $id
     * @return void
     */
    public function setId($id)
    {
    }

    /**
     * @return void
     */
    public function regenerateId()
    {
    }

    /**
     * @param null|int $ttl
     * @return void
     */
    public function rememberMe($ttl = null)
    {
    }

    /**
     * @return void
     */
    public function forgetMe()
    {
    }

    /**
     * @return void
     */
    public function setValidatorChain(EventManagerInterface $chain)
    {
    }

    /**
     * @return void
     */
    public function getValidatorChain()
    {
    }

    /**
     * @return void
     */
    public function isValid()
    {
    }

    /**
     * @return void
     */
    public function sessionExists()
    {
    }

    /**
     * @return void
     */
    public function expireSessionCookie()
    {
    }
}
