<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator\TestAsset;

use Laminas\EventManager\EventManagerInterface;
use Laminas\Session\AbstractManager;
use Laminas\Session\Storage\ArrayStorage;

class SessionManager extends AbstractManager
{
    public $started = false;

    protected $configDefaultClass  = 'Laminas\Session\Configuration\StandardConfiguration';
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
